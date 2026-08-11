<?php

namespace App\Http\Repository;

use App\Models\Payment;
use App\Http\Repository\Interface\CreateInterface;
use App\Http\Repository\Interface\OrderExistsInterface;
use App\Enums\PaymentStatusEnum;

class PaymentRepository implements OrderExistsInterface, CreateInterface 
{
    public function create(array $request): Payment|null
    {
        return Payment::create($request);
    }

    public function orderExists (int $orderId): Payment|null
    {
        return Payment::where('order_id', $orderId)
            ->lockForUpdate()
            ->first();
    }

    public function orderDone(int $orderId): bool
    {
        return Payment::query()
            ->where('order_id', $orderId)
            ->where('inventory_status', PaymentStatusEnum::PAID)
            ->exists();
    }
}