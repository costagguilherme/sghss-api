<?php

namespace App\Http\Middleware;

use App\Types\Enums\RoleEnum;
use Closure;
use Illuminate\Http\Request;

class RolePatientAdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user->role !== RoleEnum::PATIENT->value && $user->role !== RoleEnum::ADMIN->value) {
            return response()->json([
                'message' => 'Não autorizado, não é paciente e nem admin'
            ], 403);
        }

        return $next($request);
    }
}
