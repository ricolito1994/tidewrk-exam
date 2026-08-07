<?php

namespace App\Http\Repository;

use App\Models\Shipment;
use App\Http\Repository\Interface\CreateInterface;
use App\Http\Repository\Interface\OrderExistsInterface;
use App\Enums\ShipmentStatusEnum;

class ShipmentRepository implements OrderExistsInterface, CreateInterface {

    public function create(array $request): Shipment
    {
        return Shipment::create($request);
    }

    public function orderExists (int $orderId): Shipment|null
    {
        return Shipment::where('order_id', $orderId)->first();
    }

    public function orderDone (int $orderId): bool
    {
        return Shipment::query()
            ->where('order_id', $orderId)
            ->where('shipment_status', ShipmentStatusEnum::BOOKED)
            ->exists();
    }
}