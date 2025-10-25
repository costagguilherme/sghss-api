<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        try {
            $data = $this->authService->login($request->email, $request->password);
            return $this->sendSucess($data, 'Login realizado com sucesso');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), $e->getCode() ?? 400);
        }
    }
}
