<?php

namespace App\Filament\Resources\EnrollmentResource\Pages;

use App\Filament\Resources\EnrollmentResource;
use App\Services\InstallmentGenerator;
use App\Services\LessonScheduler;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\QueryException;

class CreateEnrollment extends CreateRecord
{
    protected static string $resource = EnrollmentResource::class;

    public function getTitle(): string
    {
        return 'Nuovo Modulo Iscrizione';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Controllo "user-friendly" prima ancora dell'insert (evita errore SQL)
        $exists = \App\Models\Enrollment::query()
            ->where('student_id', $data['student_id'] ?? null)
            ->where('course_id', $data['course_id'] ?? null)
            ->exists();

        if ($exists) {
            Notification::make()
                ->title('Modulo già presente')
                ->body('Questo studente risulta già iscritto a questo corso. Apri il modulo esistente e modificalo.')
                ->danger()
                ->send();

            // blocca la creazione senza 500
            $this->halt();
        }

        return $data;
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        try {
            return parent::handleRecordCreation($data);
        } catch (QueryException $e) {
            // Fallback: se per qualche motivo arriva comunque dal DB
            if (str_contains($e->getMessage(), 'enrollments_student_id_course_id_unique')) {
                Notification::make()
                    ->title('Modulo già presente')
                    ->body('Questo studente risulta già iscritto a questo corso. Apri il modulo esistente e modificalo.')
                    ->danger()
                    ->send();

                $this->halt();
            }

            throw $e;
        }
    }

    protected function afterCreate(): void
    {
        app(InstallmentGenerator::class)->generateForEnrollment($this->record);

        // se hai già creato LessonScheduler, lo lasciamo.
        // se non esiste ancora, commenta la riga qui sotto.
        app(LessonScheduler::class)->generateForEnrollment($this->record, true);

        $this->redirect(
            EnrollmentResource::getUrl('edit', ['record' => $this->record])
        );
    }
}
