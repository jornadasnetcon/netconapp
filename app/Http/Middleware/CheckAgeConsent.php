<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Route;


class CheckAgeConsent
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = auth()->user();
        $route = Route::getRoutes()->match($request);

        if ($route->getName() === 'consent' || $route->getName() === 'consent_store') {
            return $next($request);
        }

        if ($user && !$user->age_consent) {
            return redirect('consent');
        }
        return $next($request);
    }
}
