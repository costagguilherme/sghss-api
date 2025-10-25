<?php

namespace App\Http\Middleware;

use App\Types\Enums\RoleEnum;
use Closure;
use Illuminate\Http\Request;

class RoleAdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user->role !== RoleEnum::ADMIN->value) {
            return response()->json([
                'message' => 'Usúario não autorizado para esta rota'
            ], 403);
        }

        return $next($request);
    }
}
