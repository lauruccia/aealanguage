<?php

namespace App\Observers;

use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class StudentObserver
{
    public function created(Student $student): void
    {
        if (! $student->email) {
            return;
        }

        if ($student->user_id) {
            return;
        }

        $user = User::where('email', $student->email)->first();

        if (! $user) {
            $user = User::create([
                'name' => trim(($student->first_name ?? 'Studente') . ' ' . ($student->last_name ?? '')),
                'email' => $student->email,
                'password' => Hash::make('Password123!'),
                'must_change_password' => true,
            ]);
        }

        // assicura ruolo
        if (! $user->hasRole('studente')) {
            $user->assignRole('studente');
        }

        $student->user_id = $user->id;
        $student->saveQuietly();
    }
}
