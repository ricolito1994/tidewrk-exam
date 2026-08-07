<?php

namespace App\Http\Repository;

use App\Models\Shipment;
use App\Repository\Interface\CreateInterface;
use App\Repository\Interface\OrderExistsInterface;
use App\Enums\ShipmentStatusEnum;

class ShipmentRepository implements OrderExistsInterface, CreateInterface {

    public function create(array $request): Shipment
    {
        return Shipment::create($request);
    }

    public function orderExists (int $orderId): Shipment
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