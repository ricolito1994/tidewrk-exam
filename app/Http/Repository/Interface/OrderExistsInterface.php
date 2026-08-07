<?php

namespace App\Http\Repository\Interface;

interface OrderExistsInterface 
{
    public function orderExists (int $orderId): mixed;

    public function orderDone (int $orderId): bool;
}