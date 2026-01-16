<?php

namespace App\Observers;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TeacherObserver
{
    public function created(Teacher $teacher): void
    {
        if (! $teacher->email) {
            return;
        }

        if ($teacher->user_id) {
            return;
        }

        $user = User::where('email', $teacher->email)->first();

        if (! $user) {
            $user = User::create([
                'name' => trim(($teacher->first_name ?? 'Docente') . ' ' . ($teacher->last_name ?? '')),
                'email' => $teacher->email,
                'password' => Hash::make('Password123!'),
                'must_change_password' => true,
            ]);
        }

        if (! $user->hasRole('docente')) {
            $user->assignRole('docente');
        }

        $teacher->user_id = $user->id;
        $teacher->saveQuietly();
    }
}
