<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Student extends Model
{
    protected $guarded = [];

/*************  ✨ Windsurf Command ⭐  *************/
    /**
     * Get the student's enrollments.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
/*******  df3756a2-a066-4180-9a9b-3fa2b785c2b3  *******/


    public function enrollments(): \Illuminate\Database\Eloquent\Relations\HasMany
{
    return $this->hasMany(Enrollment::class);
}

public function user()
{
    return $this->belongsTo(\App\Models\User::class);
}

public function installments()
{
    return $this->hasManyThrough(
        \App\Models\Installment::class,
        \App\Models\Enrollment::class,
        'student_id',     // FK su enrollments
        'enrollment_id',  // FK su installments
        'id',
        'id'
    );
}


public function lessons(): \Illuminate\Database\Eloquent\Relations\HasMany
{
    return $this->hasMany(\App\Models\Lesson::class);
}


}
