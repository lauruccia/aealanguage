<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClosureDay extends Model
{
    protected $guarded = [];

    protected $casts = [
        'date' => 'date',
    ];
}
