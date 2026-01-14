<?php

namespace App\Filament\Resources\TeacherResource\Pages;

use App\Filament\Resources\TeacherResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;

class CreateTeacher extends CreateRecord
{
    protected static string $resource = TeacherResource::class;

    protected function afterCreate(): void
    {
        $teacher = $this->record;

        // Se già collegato, non fare nulla
        if (! empty($teacher->user_id)) {
            return;
        }

        // Serve l'email
        $email = trim((string) ($teacher->email ?? ''));
        if ($email === '') {
            return;
        }

        // Password fissa decisa dall'admin
        $passwordPlain = 'DemoPass#2026';

        // Crea o recupera utente
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => trim(($teacher->first_name ?? '') . ' ' . ($teacher->last_name ?? '')) ?: 'Docente',
                'password' => $passwordPlain,
            ]
        );

        // Ruolo docente
        $user->syncRoles(['docente']);

        // Collega teacher -> user
        $teacher->user_id = $user->id;
        $teacher->save();
    }
}
