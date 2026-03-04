<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\FetchRequest;
use App\Http\Requests\Api\ReportRequest;
use App\Services\DeviceService;
use Illuminate\Http\JsonResponse;

class DeviceController extends Controller
{
    public function __construct(
        private readonly DeviceService $deviceService,
    ) {}

    public function fetch(FetchRequest $request): JsonResponse
    {
        $items = $this->deviceService->fetchItems(
            $request->validated('phone_number'),
        );

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    public function report(ReportRequest $request): JsonResponse
    {
        $this->deviceService->reportItems(
            $request->validated('phone_number'),
            $request->validated('items'),
        );

        return response()->json([
            'success' => true,
            'message' => 'Report received successfully.',
        ]);
    }
}
