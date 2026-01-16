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

        // soldi
        'deposit'           => 'decimal:2',
        'course_price'      => 'decimal:2',
        'enrollment_fee'    => 'decimal:2',
        'rateable_residual' => 'decimal:2',

        // opzionale ma utile
        'google_html_link'  => 'string',
        'google_meet_url'   => 'string',
        'google_event_id'   => 'string',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public function defaultTeacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'default_teacher_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
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

    public function hourMovements(): HasMany
    {
        return $this->hasMany(EnrollmentHourMovement::class);
    }

    public function purchasedMinutes(): int
    {
        return (int) $this->hourMovements()
            ->where('minutes', '>', 0)
            ->sum('minutes');
    }

    public function consumedMinutes(): int
    {
        return (int) $this->hourMovements()
            ->where('minutes', '<', 0)
            ->sum('minutes');
    }

    public function remainingMinutes(): int
    {
        return $this->purchasedMinutes() + $this->consumedMinutes();
    }
}
