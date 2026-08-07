<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

use App\Http\Repository\StudentRepository;
use App\Http\Repository\SchoolRepository;

use PhpOffice\PhpSpreadSheet\Shared\Date;

class StudentImport implements ToCollection, WithHeadingRow, WithChunkReading
{

    public function __construct (
        protected readonly StudentRepository $studentRepository,
        protected readonly SchoolRepository $schoolRepository
    )
    {}

    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows): void
    {
        $schools = [];
        $students = [];

        foreach ($rows as $row):
            $schools[$row['school_code']] = [
                'school_code' => $row['school_code'],
                'school_name' => $row['school_name'],
            ];

            $students[] = [
                'student_id'     => $row['student_id'],
                'student_code'   => $row['student_code'],
                'first_name'     => $row['first_name'],
                'last_name'      => $row['last_name'],
                'date_of_birth'  => Date::excelToDateTimeObject($row['date_of_birth'])->format('Y-m-d'),
                'school_code'    => $row['school_code'],
            ];
        endforeach;
        
        $schools = array_values($schools);

        DB::transaction (function() use ($schools, $students) {
            $this->schoolRepository->upsert($schools);
            $this->studentRepository->upsert($students);
        });
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
