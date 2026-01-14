<?php

namespace App\Observers;

use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class StudentObserver
{
    public function created(Student $student): void
    {
        // se non c'è email, non creiamo l'account
        if (! $student->email) {
            return;
        }

        // se già collegato, stop
        if ($student->user_id) {
            return;
        }

        // evita conflitti: se esiste già un user con quella email, collegalo
        $user = User::where('email', $student->email)->first();

        if (! $user) {
            $defaultPassword = 'Password123!'; // scegli la tua password di default

            $user = User::create([
                'name' => trim(($student->first_name ?? 'Studente') . ' ' . ($student->last_name ?? '')),
                'email' => $student->email,
                'password' => Hash::make($defaultPassword),
                'must_change_password' => true,
            ]);

            $user->assignRole('studente');
        }

        $student->user_id = $user->id;
        $student->saveQuietly();
    }
}
