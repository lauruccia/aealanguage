<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'must_change_password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'must_change_password' => 'boolean',
        ];
    }

    /**
     * ✅ Filament v3
     * Consenti accesso al pannello admin a tutti gli utenti autenticati.
     */
   
    public function canAccessPanel(Panel $panel): bool
    {
        // Se hai SOLO questo pannello, va benissimo così.
        // Se in futuro avrai altri pannelli, questa condizione evita accessi indesiderati.
        if ($panel->getId() !== 'admin') {
            return false;
        }

        return true;
    } 







    // =========================
    // RUOLI (Spatie)
    // =========================

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('superadmin');
    }

    public function isStaff(): bool
    {
        return $this->hasAnyRole(['superadmin', 'amministrazione', 'segreteria']);
    }

    public function isTeacher(): bool
    {
        return $this->hasRole('docente');
    }

    public function isStudent(): bool
    {
        return $this->hasRole('studente');
    }

    // =========================
    // RELAZIONI
    // =========================

    public function student()
    {
        return $this->hasOne(\App\Models\Student::class, 'user_id');
    }

    public function teacher()
    {
        return $this->hasOne(\App\Models\Teacher::class, 'user_id');
    }
}
