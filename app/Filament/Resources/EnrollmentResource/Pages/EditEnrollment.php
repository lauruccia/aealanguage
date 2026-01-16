<?php

namespace App\Filament\Resources\EnrollmentResource\Pages;

use App\Filament\Resources\EnrollmentResource;
use App\Services\GoogleCalendarService;
use App\Services\InstallmentGenerator;
use App\Services\LessonScheduler;
use Filament\Actions;
use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditEnrollment extends EditRecord
{
    protected static string $resource = EnrollmentResource::class;

    public function getTitle(): string
    {
        return 'Modifica Modulo Iscrizione';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('rigenera_rate')
                ->label('Rigenera rate')
                ->requiresConfirmation()
                ->action(function () {
                    app(InstallmentGenerator::class)->generateForEnrollment($this->record);

                    $this->record->refresh();
                    $this->fillForm();
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

            Actions\Action::make('crea_meet_ricorrente')
                ->label('Crea / Aggiorna Meet ricorrente')
                ->icon('heroicon-o-video-camera')
                ->requiresConfirmation()
                ->action(function () {

                    /** @var \App\Models\Enrollment $enrollment */
                    $enrollment = $this->record->loadMissing([
                        'lessons' => fn ($q) => $q->orderBy('lesson_number'),
                        'student',
                        'defaultTeacher',
                        'course',
                    ]);

                    // ✅ Pianificazione obbligatoria
                    if (empty($enrollment->starts_at) || empty($enrollment->weekly_day) || empty($enrollment->weekly_time)) {
                        Notification::make()
                            ->title('Pianificazione incompleta')
                            ->body('Imposta data inizio, giorno e ora della lezione. Poi salva e riprova.')
                            ->danger()
                            ->send();
                        return;
                    }

                    $firstLesson = $enrollment->lessons->first();
                    $lastLesson  = $enrollment->lessons->last();

                    if (! $firstLesson || ! $lastLesson) {
                        Notification::make()
                            ->title('Lezioni non generate')
                            ->body('Prima devi generare le lezioni: clicca su "Rigenera lezioni", poi riprova.')
                            ->danger()
                            ->send();
                        return;
                    }

                    $studentEmail = $enrollment->student?->email;
                    $teacherEmail = $enrollment->defaultTeacher?->email;
                    $attendees    = array_values(array_filter([$studentEmail, $teacherEmail]));

                    if (empty($attendees)) {
                        Notification::make()
                            ->title('Email mancanti')
                            ->body('Inserisci l’email dello studente e/o del docente per poter inviare l’invito.')
                            ->danger()
                            ->send();
                        return;
                    }

                    $hadExisting = ! empty($enrollment->google_event_id);

                    $result = app(GoogleCalendarService::class)->createOrUpdateRecurringMeetEvent([
                        'summary'           => 'Lezione ' . ($enrollment->course?->name ?? 'A&A Language Center'),
                        'description'       => 'A&A Language Center - Lezione online (Google Meet)',
                        'start'             => $firstLesson->starts_at,
                        'end'               => $firstLesson->ends_at,
                        'weekly_day_iso'    => (int) $enrollment->weekly_day,
                        'until'             => $lastLesson->ends_at,
                        'attendees_emails'  => $attendees,
                        'existing_event_id' => $enrollment->google_event_id,
                    ]);

                    // salva su enrollment
                    $enrollment->google_event_id  = $result['event_id'] ?? null;
                    $enrollment->google_meet_url  = $result['meet_url'] ?? null;
                    $enrollment->google_html_link = $result['html_link'] ?? null;
                    $enrollment->save();

                    $this->record->refresh();
                    $this->fillForm();

                    $title = $hadExisting ? 'Invito ricorrente aggiornato' : 'Invito ricorrente creato';
                    $body  = $hadExisting
                        ? 'Invito aggiornato. Google invia l’aggiornamento agli invitati.'
                        : 'Invito creato. Google ha inviato l’email di invito a docente e studente.';

                    $actions = [];

                    if (! empty($enrollment->google_meet_url)) {
                        $actions[] = NotificationAction::make('apri_meet')
                            ->label('Apri Meet')
                            ->url($enrollment->google_meet_url, shouldOpenInNewTab: true);
                    }

                    if (! empty($enrollment->google_html_link)) {
                        $actions[] = NotificationAction::make('apri_evento')
                            ->label('Apri evento')
                            ->url($enrollment->google_html_link, shouldOpenInNewTab: true);
                    }

                    Notification::make()
                        ->title($title)
                        ->body($body)
                        ->actions($actions)
                        ->success()
                        ->persistent()
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
