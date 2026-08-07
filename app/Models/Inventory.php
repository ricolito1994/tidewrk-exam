<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Relation\BelongsTo;

use App\Enums\InventoryStatusEnum;

class Inventory extends Model
{
    //
    use SoftDeletes;

    protected $table = "inventory";

    protected $fillable = [
        'order_id',
        'inventory_status',
        'reserved_quantity',
        'reserved_at'
    ];

    protected $casts = [
        'inventory_status' => InventoryStatusEnum::class,
        'reserved_at' => 'datetime'
    ];

    public function order (): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function markStatus(string $status, mixed $now): void
    {
        $this->inventory_status = $status;

        if ($status === InventoryStatusEnum::RESERVED) {
            $this->reserved_at = $now;
        }

        $this->save();
    }

    public function isReserved(): bool
    {
        return $this->inventory_status === InventoryStatusEnum::RESERVED;
    }

    public function compensate(): bool
    {
        try {
            $this->order->cancel();
            return $this->releaseInventoryReservation();
        } catch (\Throwable $e) {
            throw $e;
        } 
    }

    public function releaseInventoryReservation()
    {
        if ($this->isReserved()) {
            $this->inventory_status = InventoryStatusEnum::RELEASED;
            $this->save();
            return true;
        }

        return false;
    }

}
