<?php

namespace App\Http\Middleware;

use App\Filament\Pages\Profile;
use Closure;
use Illuminate\Http\Request;

class ForcePasswordChange
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // se non loggato, lascia gestire a Filament/Auth
        if (! $user) {
            return $next($request);
        }

        // se deve cambiare password, forza su Profilo
        if ($user->must_change_password) {
            $profileUrl = Profile::getUrl();

            // evita loop se già su profilo o su logout
            if (! $request->is('admin/profile') && ! $request->is('admin/logout')) {
                return redirect($profileUrl);
            }
        }

        return $next($request);
    }
}
