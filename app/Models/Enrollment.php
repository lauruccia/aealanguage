<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Enrollment extends Model
{
    protected $guarded = [];

    protected $casts = [
        'enrolled_at' => 'date',
        'starts_at'   => 'date',
        'ends_at'     => 'date',
        'deposit'     => 'decimal:2',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function defaultTeacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'default_teacher_id');
    }

    public function installments(): HasMany
    {
        return $this->hasMany(Installment::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }

    public function hourMovements()
{
    return $this->hasMany(\App\Models\EnrollmentHourMovement::class);
}

public function purchasedMinutes(): int
{
    return (int) $this->hourMovements()->where('minutes', '>', 0)->sum('minutes');
}

public function consumedMinutes(): int
{
    return (int) $this->hourMovements()->where('minutes', '<', 0)->sum('minutes'); // negativo
}

public function remainingMinutes(): int
{
    return $this->purchasedMinutes() + $this->consumedMinutes(); // consumed è negativo
}

}
