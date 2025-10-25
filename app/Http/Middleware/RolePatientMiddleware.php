<?php

namespace App\Http\Middleware;

use App\Types\Enums\RoleEnum;
use Closure;
use Illuminate\Http\Request;

class RolePatientMiddleware
{

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user->role !== RoleEnum::PATIENT->value) {
            return response()->json([
                'message' => 'Não autorizado'
            ], 403);
        }

        return $next($request);
    }
}
