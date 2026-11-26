<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

/**
 * GET /api/integration/v1/health (no API key).
 * B5: {"success":true,"system":"INV","version":"v1","time":"<ISO>"}
 */
class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'system' => config('integration.system_code'),
            'version' => config('integration.version'),
            'time' => now()->toIso8601String(),
        ]);
    }
}
