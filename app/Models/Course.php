<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Course extends Model
{
    protected $guarded = [];

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Numero di lezioni incluse nel corso.
     * 1 lezione = 1 ora acquistata (durata standard 60 min).
     */
    public function lessonsIncluded(): int
    {
        return (int) ($this->lessons_count ?? 0);
    }

}
