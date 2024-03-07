<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if ($guard == 'api') {
            if (!auth()->guard('api')->check()) {
                return response()->json(['error' => 'Unauthenticated.'], 401);
            }
        } else {
            if ($guard == 'admin') {
                if (! $request->expectsJson()) {
                    return redirect()->guest(route('login'));
                }
            } else {
                if (!auth()->guard($guard)->check()) {
                    return redirect()->guest(route('login'));
                }
            }
        }

        return $next($request);
    }
}
