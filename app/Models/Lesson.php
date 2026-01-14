<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lesson extends Model
{
    protected $guarded = [];

    /* =========================
     | CASTS
     ========================= */


protected $casts = [
    'starts_at'          => 'datetime',
    'ends_at'            => 'datetime',
    'cancelled_at'       => 'datetime',
    'previous_start_at'  => 'datetime',
    'previous_end_at'    => 'datetime',
];

    /* =========================
     | STATI
     ========================= */
    public const STATUS_SCHEDULED         = 'scheduled';
    public const STATUS_COMPLETED         = 'completed';
    public const STATUS_CANCELLED_RECOVER = 'cancelled_recover'; // >24h → NON conta
    public const STATUS_CANCELLED_COUNTED = 'cancelled_counted'; // <=24h → CONTA

    /* =========================
     | LABEL & COLOR (UI)
     ========================= */
    public static function statusLabel(?string $status): string
    {
        return match ($status) {
            self::STATUS_SCHEDULED         => 'Programmata',
            self::STATUS_COMPLETED         => 'Completata',
            self::STATUS_CANCELLED_RECOVER => 'Annullata (da recuperare)',
            self::STATUS_CANCELLED_COUNTED => 'Annullata (conteggiata)',
            default                        => $status ?: '—',
        };
    }

    public static function statusColor(?string $status): string
    {
        return match ($status) {
            self::STATUS_COMPLETED         => 'success',
            self::STATUS_SCHEDULED         => 'info',
            self::STATUS_CANCELLED_RECOVER => 'warning',
            self::STATUS_CANCELLED_COUNTED => 'danger',
            default                        => 'gray',
        };
    }

    /* =========================
     | BUSINESS LOGIC
     ========================= */

    /** Durata lezione in minuti */
public function durationMinutes(): int
{
    if ($this->starts_at && $this->ends_at && $this->ends_at->gt($this->starts_at)) {
        return max(0, $this->ends_at->diffInMinutes($this->starts_at));
    }

    return (int) ($this->duration_minutes ?? 60);
}

    /** La lezione scala le ore allo studente */
    public function countsAsAttended(): bool
    {
        return in_array($this->status, [
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED_COUNTED,
        ], true);
    }

    /** La lezione conta come lavorata per il docente */
    public function countsAsWorked(): bool
    {
        // tua richiesta: late-cancel conteggiate
        return $this->countsAsAttended();
    }

    /** Lezione da recuperare */
    public function needsRecovery(): bool
    {
        return $this->status === self::STATUS_CANCELLED_RECOVER;
    }

    /* =========================
     | RELATIONS
     ========================= */

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function getStatusLabel(): string
{
    return self::statusLabel($this->status);
}

public function getStatusColor(): string
{
    return self::statusColor($this->status);
}

}
