<?php

namespace App\Http\Repository;

use App\Models\Payment;
use App\Repository\Interface\CreateInterface;
use App\Repository\Interface\OrderExistsInterface;
use App\Enums\PaymentStatusEnum;

class PaymentRepository implements OrderExistsInterface, CreateInterface 
{
    public function create(array $request): Payment
    {
        return Payment::create($request);
    }

    public function orderExists (int $orderId): bool
    {
        return Payment::where('order_id', $orderId)->exists();
    }

    public function orderDone(int $orderId): bool
    {
        return Payment::query()
            ->where('order_id', $orderId)
            ->where('inventory_status', PaymentStatusEnum::PAID)
            ->exists();
    }
}