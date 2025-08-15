<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if (Auth::user()->role !== $role) {
            return response()->json(['message' => 'Forbidden: Role mismatch'], 403);
        }

        return $next($request);
    }
}
