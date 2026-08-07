<?php

namespace App\Http\Repository;

use App\Models\Order;

use App\Repository\Interface\CreateInterface;

class OrderRepository implements CreateInterface 
{
    public function create(array $request): Order
    {
        return Order::create($request);
    }
}