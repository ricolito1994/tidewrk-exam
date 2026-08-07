<?php

namespace App\Listeners;

use App\Events\OnFailureCompensate;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

use App\Repository\LogRepository;

class FulfillmentFailed implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        protected readonly LogRepository $logRepository
    )
    {
    }

    public function handle(OnFailureCompensate $event): void
    {
        $order = $event->order;

        if ($order->isCancelled()) {
            return;
        }

        // each will trigger on fail
        // instead of rollingback compensate on fail

        if($order->payment->compensate()) {
            $this->logCompensation(
                'CapturePayment',
                "Payment refunded."
            );
        }

        if($order->inventory->compensate()) {
            $this->logCompensation(
                'ReserveInventory',
                "Inventory released."
            );
        }

        if ($order->shipment->compensate()) {
            $this->logCompensation(
                'BookShipment',
                "Shipment cancelled."
            );
        }
    }

    public function logCompensation(
        string $listener,
        string $message,
    )
    {
        $this->logRepository->create([
            'order_id' => $event->order->id,
            'listener' => $listener,
            'log_status' => LogStatusEnum::COMPENSATED,
            'message' => $message,
            'attempt' => $this->attempts(),
            'processed_at' => now()
        ]); 
    }

    public function failed(
        OnFailureCompensate $event,
        \Throwable $e
    )
    {
        $this->logRepository->create([
            'order_id' => $event->order->id,
            'listener' => self::class,
            'log_status' => LogStatusEnum::FAILED,
            'message' => $e->getMessage(),
            'attempt' => $this->attempts(),
            'processed_at' => $now
        ]); 
    }
}
