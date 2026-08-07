<?php

namespace App\Http\Repository\Interface;

interface CreateInterface 
{
    public function create (array $request): mixed;
}