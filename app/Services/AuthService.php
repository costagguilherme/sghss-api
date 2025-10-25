<?php

namespace App\Services;

use App\Repositories\UserRepository;
use App\Types\Enums\RoleEnum;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    protected UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function login(string $email, string $password): array
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user || !Hash::check($password, $user->password)) {
            throw new \Exception('Credenciais inválidas.', 401);
        }

        if ($user->role !== RoleEnum::PATIENT->value) {
            throw new \Exception('Usuário não autorizado para login como patient.', 400);
        }

        $token = $user->createToken('auth_token')->plainTextToken;
        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ]
        ];
    }
}
