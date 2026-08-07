<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;

class OrderConfirmed
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Order $order,
        public bool $failSimulateInventory = false,
        public bool $failSimulateShipment = false,
        public bool $failSimulatePayment = false
    )
    {
    }

}
