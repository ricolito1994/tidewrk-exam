<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

use App\Http\Services\CheckStatusCompletionService;
use App\Http\Repository\PaymentRepository;
use App\Http\Repository\LogRepository;
use App\Enums\PaymentStatusEnum;
use App\Enums\LogStatusEnum;
use App\Events\OrderConfirmed;

class CapturePayment implements ShouldQueue
{
    use InteractsWithQueue;

    public $tries = 3;

    public function __construct(
        protected readonly PaymentRepository $paymentRepository,
        protected readonly LogRepository $logRepository,
        protected readonly CheckStatusCompletionService $checkCompletion
    )
    {
    }

    public function handle (OrderConfirmed $event): void
    {
        $now = now ();

        $order = $event->order;

        $failSimulate = $event->failSimulatePayment ?? false;

        if ($this->attempts() > 1) {
            $this->logRepository->create([
                'order_id' => $order->id,
                'listener' => self::class,
                'log_status' => LogStatusEnum::RETRYING,
                'message' => 'Retrying payment.',
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
                $payment = $this->paymentRepository->orderExists($order->id);

                // idempotency check
                if (
                    $payment && 
                    $payment->isPaid() &&
                    $order->isCancelled()
                ) {
                    return;
                }

                if (! $payment) {
                    $payment = $this->paymentRepository->create([
                        'order_id' => $order->id,
                        'payment_status' => PaymentStatusEnum::PENDING,
                        'paid_at' => null,
                    ]);
                }

                // simulate a random failure
                if ($failSimulate) {
                    throw new \Exception('Payment service temporarily unavailable.');
                }

                $payment->markStatus(PaymentStatusEnum::RESERVED, $now);

                $this->logRepository->create([
                    'order_id' => $order->id,
                    'listener' => self::class,
                    'log_status' => LogStatusEnum::SUCCESS,
                    'message' => 'Already paid.',
                    'attempt' => $this->attempts(),
                    'processed_at' => $now
                ]); 
            });

            $this->checkCompletion->handle($order);
            
        } catch (\Throwable $e) {

            $this->handleFailure($e);

            throw $e;
        }
    }

    public function backoff(): array
    {
        return [10, 30, 60];
    }

    public function handleFailure(\Throwable $e)
    {
        if ($shipment = $this->inventoryRepository->orderExists($this->order->id)) {
            $shipment->markStatus(PaymentStatusEnum::FAILED);
        }

        $this->order->markPartiallyFailed();

        $this->logRepository->create([
            'order_id' => $this->order->id,
            'listener' => self::class,
            'log_status' => LogStatusEnum::FAILED,
            'message' => $e->getMessage(),
            'attempt' => $this->attempts(),
            'processed_at' => $now
        ]); 
    }

    public function failed(OrderConfirmed $event, \Throwable $exception): void
    {
        $this->handleFailure($exception);

        event(new OnFailureCompensate($event->order));
    }
}
     