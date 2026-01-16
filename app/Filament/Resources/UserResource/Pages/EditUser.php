<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['name'] = trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));
        return $data;
    }

    protected function afterSave(): void
{
    $role = $this->data['staff_role'] ?? null;

    if ($role && in_array($role, ['amministrazione', 'segreteria'], true)) {
        $this->record->syncRoles([$role]);
    }
}

}
