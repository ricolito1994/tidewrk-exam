<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Relations\BelongsTo;

use App\Enum\ShipmentStatusEnum;

class Shipment extends Model
{
    use SoftDeletes;

    protected $table = "shipment";

    protected $fillable = [
        'order_id',
        'shipment_status',
        'tracking_number',
        'shipped_at',
    ];

    protected $casts = [
        'shipment_status' => ShipmentStatusEnum::class,
        'shipped_at' => 'datetime'
    ];

    public function order (): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function markStatus(string $status, mixed $now): void
    {
        $this->inventory_status = $status;

        if ($status === ShipmentStatusEnum::BOOKED) {
            $this->shipped_at = $now;
        }

        $this->save();
    }

    public function isBooked(): bool
    {
        return $this->shipment_status === ShipmentStatusEnum::BOOKED;
    }

    public function compensate(): bool
    {
        try {
            $this->order->cancel();
            return $this->cancelShipmentBooking();
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function cancelShipmentBooking(): mixed
    {
        if ($this->isBooked()) {
            $this->shipment_status = ShipmentStatusEnum::CANCELLED;
            $this->save();
            return true;
        }

        return false;
    }
}
