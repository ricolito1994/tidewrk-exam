<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

use App\Http\Services\CheckStatusCompletionService;
use App\Http\Repository\ShipmentRepository;
use App\Http\Repository\LogRepository;
use App\Enums\ShipmentStatusEnum;
use App\Enums\LogStatusEnum;
use App\Events\OrderConfirmed;
use App\Events\OnFailureCompensate;
use App\Models\Order;

class BookShipment implements ShouldQueue
{
    use InteractsWithQueue;

    public $tries = 3;

    public function __construct(
        protected readonly ShipmentRepository $shipmentRepository,
        protected readonly LogRepository $logRepository,
        protected readonly CheckStatusCompletionService $checkCompletion
    )
    {
    }

    public function handle (OrderConfirmed $event): void
    {
        $now = now ();

        $order = $event->order;

        $failSimulate = $event->failSimulateShipment ?? false;

        if ($this->attempts() > 1) {
            $this->logRepository->create([
                'order_id' => $order->id,
                'listener' => self::class,
                'log_status' => LogStatusEnum::RETRYING,
                'message' => 'Retrying shipment booking.',
                'attempt' => $this->attempts(),
                'processed_at' => $now,
            ]);
        }

        try {

            DB::transaction (function () use (
                $order, 
                $now, 
                $failSimulate,
            ) {
                $shipment = $this->shipmentRepository->orderExists($order->id);

                // idempotency check
                if (
                    (
                        $shipment && 
                        $shipment->isBooked()
                    ) ||
                    $order->isCancelled()
                ) {
                    return;
                }

                if (! $shipment) {
                    $shipment = $this->shipmentRepository->create([
                        'order_id' => $order->id,
                        'shipment_status' => ShipmentStatusEnum::PENDING,
                        'tracking_number' => null,
                        'shipped_at' => null,
                    ]);
                }

                // simulate a random failure
                if ($failSimulate) {
                    throw new \Exception('Shipment service temporarily unavailable.');
                }

                $shipment->markStatus(ShipmentStatusEnum::BOOKED, $now);

                $this->logRepository->create([
                    'order_id' => $order->id,
                    'listener' => self::class,
                    'log_status' => LogStatusEnum::SUCCESS,
                    'message' => 'Shipment Booked.',
                    'attempt' => $this->attempts(),
                    'processed_at' => $now
                ]); 
            });

            $this->checkCompletion->handle($order);
            
        } catch (\Throwable $e) {

            $this->recordFailure($order->id, $e);

            throw $e;
        }
    }

    public function backoff(): array
    {
        return [10, 30, 60];
    }

    public function recordFailure (int $recordId, \Throwable $e) 
    {
        $this->logRepository->create([
            'order_id' => $recordId,
            'listener' => self::class,
            'log_status' => LogStatusEnum::FAILED,
            'message' => $e->getMessage(),
            'attempt' => $this->attempts(),
            'processed_at' => now()
        ]); 
    }

    public function handleFailure(Order $order, \Throwable $e)
    {
        DB::transaction(function () use ($order, $e) {
            if ($shipment = $this->shipmentRepository->orderExists($order->id)) {
                if (! $shipment->isFailed()) {
                    $shipment->markStatus(ShipmentStatusEnum::FAILED, null);
                }
            }

            if (! $order->isPartiallyFailed()) {
                $order->markPartiallyFailed();
            }

            $this->recordFailure($order->id, $e); 
        });
    }

    public function failed(OrderConfirmed $event, \Throwable $e): void
    {
        $this->handleFailure($event->order, $e);

        event(new OnFailureCompensate($event->order));
    }
}
     