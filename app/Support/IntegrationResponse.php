<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

/**
 * B5 response format, shared by every integration endpoint.
 *
 *   ok:    {"success": true,  "message": "...", "data": ...}
 *   error: {"success": false, "message": "...", "errors": {...}}
 */
class IntegrationResponse
{
    public static function ok(mixed $data = null, string $message = 'OK', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    public static function error(string $message, array $errors = [], int $status = 422): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            // An empty PHP array would become [], but B5 expects an object.
            'errors' => (object) $errors,
        ], $status);
    }
}
