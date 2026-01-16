<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // costruisci "name" compatibile (se usi ancora name in giro)
        $data['name'] = trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));

        if (empty($data['password'])) {
            $data['password'] = Hash::make('Password123!');
            $data['must_change_password'] = true;
        }

        return $data;
    }

    protected function afterCreate(): void
{
    $role = $this->data['staff_role'] ?? null;

    if ($role && in_array($role, ['amministrazione', 'segreteria'], true)) {
        $this->record->syncRoles([$role]);
    }
}

}
