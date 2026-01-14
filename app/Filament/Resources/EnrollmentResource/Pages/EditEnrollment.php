<?php

namespace App\Filament\Resources\EnrollmentResource\Pages;

use App\Filament\Resources\EnrollmentResource;
use App\Services\InstallmentGenerator;
use App\Services\LessonScheduler;
use Filament\Actions;
use Filament\Notifications\Notification;
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
                    app(InstallmentGenerator::class)->generateForEnrollment($this->record);

                    // ricarica record + relazioni
                    $this->record->refresh();

                    // ricarica i valori nel form (utile se mostri riepiloghi/calcoli)
                    $this->fillForm();

                    // refresh tabella Rate (RelationManager)
                    $this->dispatch('refreshInstallments');

                    Notification::make()
                        ->title('Rate rigenerate')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('rigenera_lezioni')
                ->label('Rigenera lezioni')
                ->requiresConfirmation()
                ->action(function () {
                    app(LessonScheduler::class)->generateForEnrollment($this->record, true);

                    $this->record->refresh();
                    $this->fillForm();

                    Notification::make()
                        ->title('Lezioni rigenerate')
                        ->success()
                        ->send();
                }),

            Actions\DeleteAction::make()->label('Elimina'),
        ];
    }

    protected function afterSave(): void
    {
        app(InstallmentGenerator::class)->generateForEnrollment($this->record);

        $this->record->refresh();
        $this->fillForm();
        $this->dispatch('refreshInstallments');
    }
}
