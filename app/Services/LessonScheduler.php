<?php

namespace App\Services;

use App\Models\ClosureDay;
use App\Models\Enrollment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LessonScheduler
{
    /**
     * Requisiti minimi per schedulare.
     */
    public function canSchedule(Enrollment $enrollment): bool
    {
        return ! empty($enrollment->starts_at)
            && ! empty($enrollment->weekly_day)
            && ! empty($enrollment->weekly_time);
    }

    /**
     * Genera (o rigenera) le lezioni per una iscrizione.
     *
     * forceRegenerate = true:
     *  - cancella tutte le lezioni e rigenera da zero
     *
     * forceRegenerate = false:
     *  - NON tocca le lezioni completed
     *  - cancella tutte le NON completed da oggi in poi
     *  - rigenera da “oggi (o starts_at se futura) in poi” rispettando count totale corso
     */
    public function generateForEnrollment(Enrollment $enrollment, bool $forceRegenerate = true): void
    {
        $enrollment->loadMissing(['course', 'lessons']);

        if (! $enrollment->course) {
            return;
        }

        $lessonsCount = (int) ($enrollment->course->lessons_count ?? 0);
        if ($lessonsCount <= 0) {
            return;
        }

        // Se non ho dati di pianificazione, non genero
        if (! $this->canSchedule($enrollment)) {
            return;
        }

        $durationMinutes = (int) ($enrollment->lesson_duration_minutes ?? 60);
        $durationMinutes = max(15, min(240, $durationMinutes));

        DB::transaction(function () use ($enrollment, $lessonsCount, $durationMinutes, $forceRegenerate) {

            if ($forceRegenerate) {
                // reset totale
                $enrollment->lessons()->delete();

                $alreadyDoneCount  = 0;
                $startFrom         = $this->firstLessonDateTime($enrollment);
                $nextLessonNumber  = 1;
            } else {
                // preservo svolte
                $completed = $enrollment->lessons()
                    ->where('status', 'completed')
                    ->orderBy('lesson_number')
                    ->get();

                $alreadyDoneCount = $completed->count();

                if ($alreadyDoneCount >= $lessonsCount) {
                    return;
                }

                // Cancello tutte le NON completed da oggi in poi
                // (se preferisci: dalla prossima lezione “starts_at”, ma questo è più sicuro)
                $enrollment->lessons()
                    ->where('status', '!=', 'completed')
                    ->where('starts_at', '>=', now()->startOfDay())
                    ->delete();

                // Riparto dalla prossima occorrenza valida:
                // - se starts_at è nel futuro: parto da starts_at
                // - se starts_at è nel passato: parto da oggi
                $baseDay = Carbon::parse($enrollment->starts_at)->startOfDay();
                $baseDay = $baseDay->greaterThan(now()->startOfDay()) ? $baseDay : now()->startOfDay();

                $startFrom = $this->firstOccurrenceDateTime(
                    $baseDay,
                    (int) $enrollment->weekly_day,
                    (string) $enrollment->weekly_time
                );

                $startFrom = $this->skipClosuresByWeek($startFrom);

                $nextLessonNumber = $alreadyDoneCount + 1;
            }

            $toCreate = $lessonsCount - $alreadyDoneCount;
            if ($toCreate <= 0) {
                return;
            }

            $created = 0;
            $currentStart = $startFrom->copy();

            while ($created < $toCreate) {
                $start = $this->skipClosuresByWeek($currentStart->copy());
                $end   = $start->copy()->addMinutes($durationMinutes);

                $enrollment->lessons()->create([
                    'enrollment_id'    => $enrollment->id,
                    'student_id'       => $enrollment->student_id,
                    'course_id'        => $enrollment->course_id,
                    'teacher_id'       => $enrollment->default_teacher_id,
                    'lesson_number'    => $nextLessonNumber,
                    'starts_at'        => $start,
                    'ends_at'          => $end,
                    'duration_minutes' => $durationMinutes,
                    'status'           => 'scheduled',
                ]);

                $created++;
                $nextLessonNumber++;

                $currentStart = $start->copy()->addWeek();
            }

            // aggiorna ends_at = data dell'ultima lezione generata (o completata se già esiste)
            $lastLesson = $enrollment->lessons()
                ->orderByDesc('lesson_number')
                ->first();

            if ($lastLesson) {
                $enrollment->update([
                    'ends_at' => Carbon::parse($lastLesson->starts_at)->toDateString(),
                ]);
            }

            // ❌ QUI NON CREIAMO EVENTI GOOGLE
            // L’evento Meet ricorrente verrà creato con un'azione separata (1 solo invito).
        });
    }

    /**
     * Ripianifica mantenendo le lezioni completate.
     */
    public function rescheduleFutureNotCompleted(Enrollment $enrollment): void
    {
        $this->generateForEnrollment($enrollment, false);
    }

    private function firstLessonDateTime(Enrollment $enrollment): Carbon
    {
        $firstStart = $this->firstOccurrenceDateTime(
            Carbon::parse($enrollment->starts_at)->startOfDay(),
            (int) $enrollment->weekly_day,
            (string) $enrollment->weekly_time
        );

        return $this->skipClosuresByWeek($firstStart);
    }

    /**
     * Trova la prima data/orario che cade sul weekly_day (1=Lun..7=Dom) a partire da $startDate (inclusa).
     */
    private function firstOccurrenceDateTime(Carbon $startDate, int $weeklyDay, string $weeklyTime): Carbon
    {
        $weeklyDay = max(1, min(7, $weeklyDay));

        [$h, $m] = array_pad(explode(':', $weeklyTime), 2, '00');
        $h = (int) $h;
        $m = (int) $m;

        $candidate = $startDate->copy()->setTime($h, $m, 0);

        $diff = $weeklyDay - $candidate->dayOfWeekIso;
        if ($diff < 0) {
            $diff += 7;
        }

        return $candidate->addDays($diff);
    }

    private function skipClosuresByWeek(Carbon $dateTime): Carbon
    {
        while ($this->isClosureDay($dateTime)) {
            $dateTime->addWeek();
        }

        return $dateTime;
    }

    private function isClosureDay(Carbon $dateTime): bool
    {
        return ClosureDay::query()
            ->whereDate('date', $dateTime->toDateString())
            ->exists();
    }
}
