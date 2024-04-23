<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CanImpersonate
{
    public function handle($request, Closure $next)
    {
        // Check if the authenticated user has the necessary permissions for impersonation
        // if (Auth::check() && Auth::user()->canImpersonate()) {
        //     return $next($request);
        // }

        if (Auth::check()) {
            return $next($request);
        }

        abort(403, 'Unauthorized action.');

        // If you want to redirect to a login page or show a custom error page, modify the logic accordingly
    }
}
