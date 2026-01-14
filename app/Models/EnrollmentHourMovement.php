<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnrollmentHourMovement extends Model
{
    protected $fillable = [
        'enrollment_id',
        'minutes',
        'type',
        'lesson_id',
        'note',
        'occurred_at',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
    ];

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }
}
