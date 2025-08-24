<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $role): Response
    {
        if (auth()->user()->role !== $role) {
            abort(403, 'Accesso negato');
        }
        return $next($request);
    }
}
