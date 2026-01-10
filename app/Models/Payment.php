<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'enrollment_id',
        'installment_id',
        'data_pagamento',
        'importo',
        'metodo',
        'note',
    ];

    protected $casts = [
        'data_pagamento' => 'datetime',
        'importo' => 'decimal:2',
    ];

    public function iscrizione(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }

    public function rata(): BelongsTo
    {
        return $this->belongsTo(Installment::class, 'installment_id');
    }
}
