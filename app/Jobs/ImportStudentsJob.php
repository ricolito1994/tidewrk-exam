<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use App\Http\Services\UploaderService;

class ImportStudentsJob implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    public $tries = 3;

    public function __construct(
        public readonly string $filePath,
        public readonly string $hashKey,
    )
    {
    }

    public function handle(UploaderService $uploader): void
    {
        $uploader->uploadFile($this->filePath);

        $uploader->deleteFile($this->filePath);
    }

    public function uniqueId(): string
    {
        return $this->filePath . '-' . $this->hashKey;
    }

    public function failed()
    {
        $uploader = app(UploaderService::class);

        $uploader->deleteFile($this->filePath);
    }

    public function backoff(): array
    {
        return [10, 30, 60];
    }
}
