<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $email = $request->input('email');
        $password = $request->input('password');
        print_r($email, $password);
        return $this->sendSucess([], 'Login realizado com sucesso');
    }
}
