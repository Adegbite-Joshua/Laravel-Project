<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Log;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $role)
    {
        Log::info('Api accessed');
        if (auth()->check() && auth()->user()->role === $role) {
            return $next($request);
        }

        return response()->json(['message' => 'Unauthorized'], 403);
    }
}
