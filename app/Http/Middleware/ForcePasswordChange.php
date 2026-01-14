<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForcePasswordChange
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->must_change_password) {
            // evita loop se già sulla pagina cambio password
            if (! $request->routeIs('password.force-change')) {
                return redirect()->route('password.force-change');
            }
        }

        return $next($request);
    }
}
