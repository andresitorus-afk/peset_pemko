<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->role?->name !== 'Super Admin') {
            abort(403, 'Hanya Super Admin yang dapat mengakses.');
        }

        return $next($request);
    }
}
