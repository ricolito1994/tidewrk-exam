<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

use App\Http\Services\CheckStatusCompletionService;
use App\Http\Repository\InventoryRepository;
use App\Http\Repository\LogRepository;
use App\Enums\InventoryStatusEnum;
use App\Enums\LogStatusEnum;
use App\Events\OrderConfirmed;
use App\Events\OnFailureCompensate;
use App\Models\Order;

class ReserveInventory implements ShouldQueue
{
    use InteractsWithQueue;

    public $tries = 3;

    public function __construct(
        protected readonly InventoryRepository $inventoryRepository,
        protected readonly LogRepository $logRepository,
        protected readonly CheckStatusCompletionService $checkCompletion
    )
    {
    }

    public function handle (OrderConfirmed $event): void
    {
        $now = now ();

        $order = $event->order;

        $failSimulate = $event->failSimulateInventory ?? false;

        if ($this->attempts() > 1) {
            $this->logRepository->create([
                'order_id' => $order->id,
                'listener' => self::class,
                'log_status' => LogStatusEnum::RETRYING,
                'message' => 'Retrying inventory reservation.',
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
                $inventory = $this->inventoryRepository->orderExists($order->id);

                // idempotency check
                if (
                    (
                        $inventory && 
                        $inventory->isReserved()
                    ) ||
                    $order->isCancelled()
                ) {
                    return;
                }

                if (! $inventory) {
                    $inventory = $this->inventoryRepository->create([
                        'order_id' => $order->id,
                        'inventory_status' => InventoryStatusEnum::PENDING,
                        'reserved_quantity' => 1,
                        'reserved_at' => null,
                    ]);
                }

                // simulate a random failure
                if ($failSimulate) {
                    throw new \Exception('Inventory service temporarily unavailable.');
                }

                $inventory->markStatus(InventoryStatusEnum::RESERVED, $now);

                $this->logRepository->create([
                    'order_id' => $order->id,
                    'listener' => self::class,
                    'log_status' => LogStatusEnum::SUCCESS,
                    'message' => 'Inventory reserved.',
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
        DB::transaction(function() use ($order, $e) {
            if ($inventory = $this->inventoryRepository->orderExists($order->id)) {
                if (! $inventory->isFailed()) {
                    $inventory->markStatus(InventoryStatusEnum::FAILED, null);
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
     