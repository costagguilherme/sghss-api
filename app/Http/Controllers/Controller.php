<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

abstract class Controller
{
    public function sendSucess(array $data = [], string $message = '', int $code = 200): JsonResponse
    {
        $response = [
            'message' => $message,
            'data' => $data,
        ];

        return response()->json($response, $code);
    }

    public function sendError(string $message, int $code = 400): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        return response()->json($response, $code);
    }

    protected function getUserId(): int
    {
        $user = Auth::user();
        return $user->id;
    }

    protected function getRole(): string
    {
        $user = Auth::user();
        return $user->role;
    }
}
