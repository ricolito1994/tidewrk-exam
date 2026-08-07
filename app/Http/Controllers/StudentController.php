<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Services\UploaderService;

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

    public function processStudentExcelData (Request $request): JsonResponse
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls,csv'
            ]);

            $res = $this->uploaderService->uploadFile($request);

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
        }
    }


}
