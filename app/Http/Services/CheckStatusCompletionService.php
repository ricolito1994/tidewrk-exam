<?php

namespace App\Http\Services;


use App\Models\Order;

class CheckStatusCompletionService
{
    public function __construct(
    )
    {
    }

    public function handle(Order $order): void
    {
        $isShipmentDone = $order->shipment?->isBooked() ?? false;
        $isPaymentDone = $order->payment?->isPaid() ?? false;
        $isInventoryDone = $order->inventory?->isReserved() ?? false;

        if (
            $isShipmentDone &&
            $isPaymentDone &&
            $isInventoryDone
        ){
            $order->complete();
        }
    }
}