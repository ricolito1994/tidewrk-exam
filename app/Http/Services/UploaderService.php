<?php

namespace App\Http\Services;

use Illuminate\Http\Request;

use Maatwebsite\Excel\Facades\Excel;

use App\Imports\StudentImport;
use App\Http\Repository\StudentRepository;
use App\Http\Repository\SchoolRepository;


class UploaderService
{
    public function __construct (
        protected readonly StudentRepository $studentRepository,
        protected readonly SchoolRepository $schoolRepository
    ) {}

    public function uploadFile (Request $request): mixed 
    {
        try {
            Excel::import(
                new StudentImport (
                    $this->studentRepository,
                    $this->schoolRepository
                ),
                $request->file('file')
            );

            return [
                'success' => true
            ];

        } catch (\Throwable $e) {
            throw $e;
        }
    }

}