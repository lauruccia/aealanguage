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
        'first_name',
        'last_name',
        'phone',
        'address',
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

    public function canAccessPanel(Panel $panel): bool
    {
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

    // =========================
    // COMODITÀ
    // =========================

    public function getFullNameAttribute(): string
    {
        $full = trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
        return $full !== '' ? $full : ($this->name ?? '');
    }
}
