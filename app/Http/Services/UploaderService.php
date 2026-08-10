<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\Storage;

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

    public function uploadFile (mixed $filePath): mixed 
    {
        try {
            Excel::import(
                new StudentImport (
                    $this->studentRepository,
                    $this->schoolRepository
                ),
                Storage::path($filePath)
            );

            return [
                'success' => true
            ];

        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function deleteFile(string $filePath): void
    {
        Storage::delete($filePath);
    }

}