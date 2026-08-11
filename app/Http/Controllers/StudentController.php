<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Services\UploaderService;
use App\Http\Requests\UploadFileRequest;
use App\Jobs\ImportStudentsJob;

class StudentController extends Controller
{
    public function __construct(
        protected readonly UploaderService $uploaderService
    )
    {}

    //
    public function index(Request $request): JsonResponse
    {
        try {
            return response()->json([]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function uploadStudentData (UploadFileRequest $request): JsonResponse
    {

        $file = $request->file('file');

        $filePath = $file->store('imports', 'local');

        try {

            $hashKey = hash_file('sha256', $file->getRealPath());

            $res = $this->uploaderService->uploadFile($filePath);

            if (! $res['success'])
                throw new \Exception ("Something went wrong");
            
            return response()->json([
                'success' => true,
                'message' => 'Student upload file complete.'
            ]);

        } catch (\Exception $e) {
            return response ()->json([
                'message' => $e->getMessage()
            ], 500);
        } finally {
            $this->uploaderService->deleteFile($filePath);
        }
    }

    public function uploadStudentDataV2 (UploadFileRequest $request): JsonResponse
    {
        try {

            $file = $request->file('file');

            $hashKey = hash_file('sha256', $file->getRealPath());

            $filePath = $file->store('imports', 'local');

            ImportStudentsJob::dispatch(filePath: $filePath, hashKey: $hashKey);
            
            return response()->json([
                'success' => true,
                'message' => 'Student upload file queued.'
            ], 202);

        } catch (\Exception $e) {
            return response ()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }


}
