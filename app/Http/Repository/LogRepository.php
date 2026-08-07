<?php

namespace App\Http\Repository;

use App\Models\Log;

use App\Http\Repository\Interface\CreateInterface;

class LogRepository implements CreateInterface
{
    public function create(array $request): Log
    {
        return Log::create($request);
    }
}