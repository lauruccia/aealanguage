<?php

namespace App\Observers;

use App\Models\Enrollment;
use App\Models\EnrollmentHourMovement;
use App\Services\LessonScheduler;
use Illuminate\Support\Facades\DB;

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
        if ($enrollment->wasChanged(['course_id', 'lesson_duration_minutes'])) {
            $this->syncPurchaseMovement($enrollment);
        }

        // ✅ Trigger “pro” quando cambia la pianificazione
        if ($enrollment->wasChanged(['starts_at', 'weekly_day', 'weekly_time', 'default_teacher_id'])) {

            // Se non ho tutti i dati, non faccio nulla
            if (empty($enrollment->starts_at) || empty($enrollment->weekly_day) || empty($enrollment->weekly_time)) {
                return;
            }

            // Se non ho lezioni: generazione normale
            if (! $enrollment->lessons()->exists()) {
                app(LessonScheduler::class)->generateForEnrollment($enrollment, false);
                return;
            }

            // Se ho lezioni: ripianifica SOLO future non svolte
            app(LessonScheduler::class)->rescheduleFutureNotCompleted($enrollment);
        }
    }

    private function syncPurchaseMovement(Enrollment $enrollment): void
    {
        $enrollment->loadMissing('course');

        if (! $enrollment->course) {
            return;
        }

        DB::transaction(function () use ($enrollment) {

            $lessonsIncluded = $enrollment->course->lessonsIncluded();
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
