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
        return !empty($enrollment->starts_at)
            && !empty($enrollment->weekly_day)
            && !empty($enrollment->weekly_time);
    }

    /**
     * Genera (o rigenera) le lezioni per una iscrizione.
     *
     * forceRegenerate = true:
     *  - cancella tutte le lezioni e rigenera da zero
     *
     * forceRegenerate = false (PRO):
     *  - NON tocca le lezioni già svolte
     *  - cancella solo future non svolte
     *  - rigenera da “oggi in poi” rispettando count totale corso
     */
    public function generateForEnrollment(Enrollment $enrollment, bool $forceRegenerate = true): void
    {
        $enrollment->loadMissing(['course', 'student', 'lessons']);

        if (!$enrollment->course) {
            return;
        }

        $lessonsCount = (int) ($enrollment->course->lessons_count ?? 0);
        if ($lessonsCount <= 0) {
            return;
        }

        // ✅ NUOVO: se non ho dati di pianificazione, non genero e non crasho
        if (!$this->canSchedule($enrollment)) {
            return;
        }

        $durationMinutes = (int) ($enrollment->lesson_duration_minutes ?? 60);
        $durationMinutes = max(15, min(240, $durationMinutes));

        DB::transaction(function () use ($enrollment, $lessonsCount, $durationMinutes, $forceRegenerate) {

            if ($forceRegenerate) {
                // reset totale
                $enrollment->lessons()->delete();
                $alreadyDoneCount = 0;
                $startFrom = $this->firstLessonDateTime($enrollment);
                $nextLessonNumber = 1;
            } else {
                // ✅ PRO: preservo svolte e cancello solo future non svolte
                $completed = $enrollment->lessons()
                    ->where('status', 'completed')
                    ->orderBy('lesson_number')
                    ->get();

                $alreadyDoneCount = $completed->count();
                if ($alreadyDoneCount >= $lessonsCount) {
                    return;
                }

                // cancello le future NON svolte (scheduled/cancelled ecc.)
                $enrollment->lessons()
                    ->where('status', '!=', 'completed')
                    ->where('starts_at', '>=', now())
                    ->delete();

                // riparto dalla prossima occorrenza valida (da oggi o da starts_at se futura)
                $startFrom = $this->firstOccurrenceDateTime(
                    Carbon::parse($enrollment->starts_at)->startOfDay()->max(now()->startOfDay()),
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
                $end = $start->copy()->addMinutes($durationMinutes);

                $enrollment->lessons()->create([
                    'enrollment_id'     => $enrollment->id,
                    'student_id'        => $enrollment->student_id,
                    'course_id'         => $enrollment->course_id,
                    'teacher_id'        => $enrollment->default_teacher_id,
                    'lesson_number'     => $nextLessonNumber,
                    'starts_at'         => $start,
                    'ends_at'           => $end,
                    'duration_minutes'  => $durationMinutes,
                    'status'            => 'scheduled',
                ]);

                $created++;
                $nextLessonNumber++;

                $currentStart = $start->copy()->addWeek();
            }

            // aggiorna ends_at = ultima lezione in assoluto (anche se completata)
            $lastLesson = $enrollment->lessons()
                ->orderByDesc('lesson_number')
                ->first();

            if ($lastLesson) {
                $enrollment->update([
                    'ends_at' => Carbon::parse($lastLesson->starts_at)->toDateString(),
                ]);
            }
        });
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
