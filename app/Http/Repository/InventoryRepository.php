<?php

namespace App\Http\Repository;

use App\Models\Inventory;
use App\Http\Repository\Interface\CreateInterface;
use App\Http\Repository\Interface\OrderExistsInterface;
use App\Enums\InventoryStatusEnum;

class InventoryRepository implements OrderExistsInterface, CreateInterface {

    public function create(array $request): Inventory|null
    {
        return Inventory::create($request);
    }

    public function orderExists (int $orderId): Inventory|null
    {
        return Inventory::where('order_id', $orderId)->first();
    }

    public function orderDone(int $orderId): bool
    {
        return Inventory::query()
            ->where('order_id', $orderId)
            ->where('inventory_status', InventoryStatusEnum::RESERVED)
            ->exists();
    }
}