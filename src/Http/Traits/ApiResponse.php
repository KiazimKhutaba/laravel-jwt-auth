<?php

namespace Devkit2026\JwtAuth\Http\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Return a success JSON response.
     */
    protected function successResponse(array $data, int $statusCode = 200): JsonResponse
    {
        return response()->json($data, $statusCode);
    }

    /**
     * Return an error JSON response.
     */
    protected function errorResponse(string $code, string $message, int $statusCode = 400, array $details = []): JsonResponse
    {
        $response = [
            'error' => [
                'code' => $code,
                'message' => $message,
            ]
        ];

        if (!empty($details)) {
            $response['error']['details'] = $details;
        }

        return response()->json($response, $statusCode);
    }
}
