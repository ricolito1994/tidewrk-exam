<?php

namespace App\Http\Repository;

use App\Models\School;

class SchoolRepository
{
    public function upsert (array $school): void
    {
        School::upsert(
            $school,
            ['school_code'],
            ['school_name']
        );
    }
}