<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Enrollment extends Model
{
    /**
     * Se vuoi proteggere campi specifici, cambia in $fillable.
     * Per ora lasciamo libero come avevi tu.
     */
    protected $guarded = [];

    protected $casts = [
        'enrolled_at' => 'date',
        'starts_at'   => 'date',
        'ends_at'     => 'date',

        // soldi
        'deposit'          => 'decimal:2',
        'course_price'     => 'decimal:2',
        'enrollment_fee'   => 'decimal:2',
        'rateable_residual'=> 'decimal:2',
    ];

    /**
     * Relazioni base
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Lingua scelta in fase di iscrizione (Modulo Iscrizione)
     * Richiede colonna language_id su enrollments.
     */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
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

    /**
     * Ore/minuti acquistati/consumati
     */
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
        // somma negativa
        return (int) $this->hourMovements()
            ->where('minutes', '<', 0)
            ->sum('minutes');
    }

    public function remainingMinutes(): int
    {
        // consumed è negativo
        return $this->purchasedMinutes() + $this->consumedMinutes();
    }

    public function subject(): \Illuminate\Database\Eloquent\Relations\BelongsTo
{
    return $this->belongsTo(\App\Models\Subject::class);
}

}
