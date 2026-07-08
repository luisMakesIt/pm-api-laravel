<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HealthController extends Controller
{
    public function status(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'message' => 'PM API is running',
            'version' => '1.0.0',
            'timestamp' => now()->toIso8601String(),
        ], 200);
    }

    public function refreshToken(Request $request): JsonResponse
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'New token generated',
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }
}
