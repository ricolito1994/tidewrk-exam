<?php

namespace App\Http\Repository;

use App\Models\Inventory;
use App\Repository\Interface\CreateInterface;
use App\Repository\Interface\OrderExistsInterface;
use App\Enums\InventoryStatusEnum;

class InventoryRepository implements OrderExistsInterface, CreateInterface {

    public function create(array $request): Inventory
    {
        return Inventory::create($request);
    }

    public function orderExists (int $orderId): bool
    {
        return Inventory::where('order_id', $orderId)->exists();
    }

    public function orderDone(int $orderId): bool
    {
        return Inventory::query()
            ->where('order_id', $orderId)
            ->where('inventory_status', InventoryStatusEnum::RESERVED)
            ->exists();
    }
}