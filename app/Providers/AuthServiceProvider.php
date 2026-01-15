<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Registra le policy (se presenti)
        $this->registerPolicies();

        Gate::before(function ($user, $ability) {
            if (! $user) {
                return null;
            }

            // FULL ACCESS allo staff
            // NB: i ruoli nel tuo DB sono: superadmin, amministrazione, segreteria, docente, studente
            if ($user->hasAnyRole(['superadmin', 'amministrazione', 'segreteria'])) {
                return true;
            }

            return null; // lascia decidere a policy/permessi specifici
        });
    }
}
