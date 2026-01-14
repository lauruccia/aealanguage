<?php

namespace App\Observers;

use App\Models\Enrollment;
use App\Models\EnrollmentHourMovement;
use Illuminate\Support\Facades\DB;

class EnrollmentObserver
{
    public function created(Enrollment $enrollment): void
    {
        $this->syncPurchaseMovement($enrollment);
    }

    public function updated(Enrollment $enrollment): void
    {
        if ($enrollment->wasChanged(['course_id', 'lesson_duration_minutes'])) {
            $this->syncPurchaseMovement($enrollment);
        }
    }

    private function syncPurchaseMovement(Enrollment $enrollment): void
    {
        $enrollment->loadMissing('course');

        if (!$enrollment->course) {
            return;
        }

        DB::transaction(function () use ($enrollment) {

            // 1) Calcolo minuti acquistati dal corso
            $lessonsIncluded = $enrollment->course->lessonsIncluded();
            $minutesPerLesson = (int) ($enrollment->lesson_duration_minutes ?? 60);

            $desiredMinutes = $lessonsIncluded * $minutesPerLesson;

            // 2) Movimento purchase principale (uno solo)
            $purchase = EnrollmentHourMovement::query()
                ->where('enrollment_id', $enrollment->id)
                ->where('type', 'purchase')
                ->orderBy('id')
                ->first();

            // 3) Se non esiste, crealo
            if (!$purchase) {
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

            // 4) Se esiste ma i minuti sono cambiati → rettifica
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
