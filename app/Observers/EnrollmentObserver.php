<?php

namespace App\Observers;

use App\Models\Enrollment;
use App\Models\EnrollmentHourMovement;
use App\Services\GoogleCalendarService;
use App\Services\LessonScheduler;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EnrollmentObserver
{
    public function created(Enrollment $enrollment): void
    {
        $this->syncPurchaseMovement($enrollment);

        // Non genera se mancano i dati (LessonScheduler fa return)
        app(LessonScheduler::class)->generateForEnrollment($enrollment, false);
    }

    public function updated(Enrollment $enrollment): void
    {
        // 1) ore acquistate
        if ($enrollment->wasChanged(['course_id', 'lesson_duration_minutes'])) {
            $this->syncPurchaseMovement($enrollment);
        }

        // 2) se cambia pianificazione => lezioni (+ sync Google se evento esiste)
        $scheduleFields = ['starts_at', 'weekly_day', 'weekly_time', 'default_teacher_id', 'lesson_duration_minutes'];

        if ($enrollment->wasChanged($scheduleFields)) {

            // Se manca pianificazione, non rigenero lezioni e non aggiorno Google
            if (empty($enrollment->starts_at) || empty($enrollment->weekly_day) || empty($enrollment->weekly_time)) {
                return;
            }

            // Lezioni
            if (! $enrollment->lessons()->exists()) {
                app(LessonScheduler::class)->generateForEnrollment($enrollment, false);
            } else {
                app(LessonScheduler::class)->rescheduleFutureNotCompleted($enrollment);
            }

            // Google (solo se evento già creato)
            $this->syncGoogleMeetRecurringEventIfExists($enrollment);

            return;
        }

        /**
         * 3) Se cambiano invitati/titolo (studente/docente/corso) aggiorno evento Google (se esiste)
         */
        if ($enrollment->wasChanged(['student_id', 'course_id', 'default_teacher_id'])) {
            $this->syncGoogleMeetRecurringEventIfExists($enrollment);
        }
    }

    /**
     * Aggiorna l’evento Google ricorrente SOLO se è già stato creato (google_event_id presente).
     * Non crea eventi nuovi (quello lo fa il bottone in Filament).
     */
    private function syncGoogleMeetRecurringEventIfExists(Enrollment $enrollment): void
    {
        if (empty($enrollment->google_event_id)) {
            return;
        }

        if (empty($enrollment->starts_at) || empty($enrollment->weekly_day) || empty($enrollment->weekly_time)) {
            return;
        }

        try {
            // Ricarico relazioni e lezioni ordinate
            $enrollment->load([
                'course',
                'student',
                'defaultTeacher',
                'lessons' => fn ($q) => $q->orderBy('lesson_number'),
            ]);

            $firstLesson = $enrollment->lessons->first();
            $lastLesson  = $enrollment->lessons->last();

            if (! $firstLesson || ! $lastLesson) {
                return;
            }

            $studentEmail = $enrollment->student?->email;
            $teacherEmail = $enrollment->defaultTeacher?->email;

            $attendees = array_values(array_filter([$studentEmail, $teacherEmail]));
            if (empty($attendees)) {
                return;
            }

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

            // Salvo, preservando se Google non rimanda meet_url/html_link
            $enrollment->google_event_id  = $result['event_id'] ?? $enrollment->google_event_id;
            $enrollment->google_meet_url  = $result['meet_url'] ?? $enrollment->google_meet_url;
            $enrollment->google_html_link = $result['html_link'] ?? $enrollment->google_html_link;

            // Evito loop observer
            $enrollment->saveQuietly();
        } catch (\Throwable $e) {
            Log::error('Google Meet sync failed for enrollment ' . $enrollment->id . ': ' . $e->getMessage(), [
                'enrollment_id' => $enrollment->id,
            ]);
        }
    }

    private function syncPurchaseMovement(Enrollment $enrollment): void
    {
        $enrollment->loadMissing('course');

        if (! $enrollment->course) {
            return;
        }

        DB::transaction(function () use ($enrollment) {

            $lessonsIncluded  = $enrollment->course->lessonsIncluded();
            $minutesPerLesson = (int) ($enrollment->lesson_duration_minutes ?? 60);

            $desiredMinutes = $lessonsIncluded * $minutesPerLesson;

            $purchase = EnrollmentHourMovement::query()
                ->where('enrollment_id', $enrollment->id)
                ->where('type', 'purchase')
                ->orderBy('id')
                ->first();

            if (! $purchase) {
                EnrollmentHourMovement::create([
                    'enrollment_id' => $enrollment->id,
                    'lesson_id'     => null,
                    'type'          => 'purchase',
                    'minutes'       => $desiredMinutes,
                    'note'          => 'Acquisto iniziale (da corso)',
                    'occurred_at'   => now(),
                ]);
                return;
            }

            $currentMinutes = (int) $purchase->minutes;

            if ($currentMinutes !== $desiredMinutes) {
                $delta = $desiredMinutes - $currentMinutes;

                EnrollmentHourMovement::create([
                    'enrollment_id' => $enrollment->id,
                    'lesson_id'     => null,
                    'type'          => 'adjustment',
                    'minutes'       => $delta,
                    'note'          => 'Rettifica ore acquistate (variazione corso/durata)',
                    'occurred_at'   => now(),
                ]);

                $purchase->update([
                    'minutes' => $desiredMinutes,
                ]);
            }
        });
    }
}
