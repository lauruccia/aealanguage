<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Teacher extends Model
{
    protected $guarded = [];

    protected $casts = [
        'gross_hourly_rate' => 'decimal:2',
        'vat_percentage' => 'decimal:2',
    ];

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);

    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class)->withTimestamps();
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->last_name . ' ' . $this->first_name);
    }

    public function user()
{
    return $this->belongsTo(\App\Models\User::class);
}

}
