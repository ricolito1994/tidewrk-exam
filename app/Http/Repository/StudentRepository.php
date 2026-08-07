<?php

namespace App\Http\Repository;

use App\Models\Student;

class StudentRepository
{
    public function upsert (array $student): void
    {
        Student::upsert(
            $student,
            ['student_id'],
            [
                'student_code',
                'first_name',
                'last_name',
                'date_of_birth',
                'school_code'
            ]
        );
    }
}