<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;

use App\Enums\OrderStatusEnum;
use App\Events\OrderConfirmed;

use DomainException;


class Order extends Model
{   
    use SoftDeletes;
    
    protected $table = "orders";

    protected $fillable = [
        'status',
        'total'
    ];

    protected $casts = [
        'order_status' => OrderStatusEnum::class
    ];

    // relationships

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function inventory(): HasOne
    {
        return $this->hasOne(Inventory::class);
    }

    public function shipment(): HasOne
    {
        return $this->hasOne(Shipment::class);
    }

    // domain functions
    public function confirm(
        bool $failSimulateInventory = false,
        bool $failSimulateShipment = false,
        bool $failSimulatePayment = false
    ): void
    {
        if ($this->order_status !== OrderStatusEnum::PENDING) {
            throw new DomainException('Only pending orders can be confirmed.');
        }

        $this->order_status = OrderStatusEnum::CONFIRMED;

        $this->save();

        event(new OrderConfirmed(
            order: $this, 
            failSimulateInventory: $failSimulateInventory,
            failSimulateShipment: $failSimulateShipment,
            failSimulatePayment: $failSimulatePayment
        ));
    }

    public function complete(): void
    {
        if (
            $this->order_status !== OrderStatusEnum::CONFIRMED &&
            $this->order_status !== OrderStatusEnum::PARTIALLY_FAILED
        ) {
            throw new DomainException('Order cannot be completed.');
        }

        $this->order_status = OrderStatusEnum::COMPLETED;

        $this->save();
    }

    public function markPartiallyFailed(): void
    {
        if ($this->order_status !== OrderStatusEnum::CONFIRMED) {
            throw new DomainException('Only confirmed orders can become partially failed.');
        }

        $this->order_status = OrderStatusEnum::PARTIALLY_FAILED;

        $this->save();
    }

    public function cancel(): void
    {
        if ($this->isCancelled()) {
            return;
        }
        
        if (
            $this->order_status !== OrderStatusEnum::PENDING &&
            $this->order_status !== OrderStatusEnum::PARTIALLY_FAILED
        ) {
            throw new DomainException('Order cannot be cancelled.');
        }

        $this->order_status = OrderStatusEnum::CANCELLED;

        $this->save();
    }

    public function isCancelled(): bool
    {
        return $this->order_status === OrderStatusEnum::CANCELLED;
    }

    public function isPartiallyFailed(): bool
    {
        return $this->order_status === OrderStatusEnum::PARTIALLY_FAILED;
    }

}
