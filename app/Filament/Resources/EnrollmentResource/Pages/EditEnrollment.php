<?php

namespace App\Filament\Resources\EnrollmentResource\Pages;

use App\Filament\Resources\EnrollmentResource;
use App\Services\InstallmentGenerator;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEnrollment extends EditRecord
{
    protected static string $resource = EnrollmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('rigenera_rate')
                ->label('Rigenera rate')
                ->requiresConfirmation()
                ->action(function () {
                    app(InstallmentGenerator::class)->generate($this->record);
                }),
            Actions\DeleteAction::make()->label('Elimina'),
        ];
    }

    protected function afterSave(): void
{
    app(InstallmentGenerator::class)->generateForEnrollment($this->record);
}
}
