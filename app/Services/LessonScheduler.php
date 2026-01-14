<?php

namespace App\Services;

use App\Models\ClosureDay;
use App\Models\Enrollment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LessonScheduler
{
    /**
     * Genera (o rigenera) le lezioni per una iscrizione.
     * - 1 lezione a settimana
     * - usa weekly_day (1..7) + weekly_time (HH:MM) + lesson_duration_minutes
     * - numero lezioni = courses.lessons_count
     * - se la data è in closure_days => slitta di 7 giorni (e ripete finché non trova un giorno valido)
     * - imposta teacher_id iniziale = default_teacher_id
     * - aggiorna enrollment.ends_at con la data dell’ultima lezione
     */
    public function generateForEnrollment(Enrollment $enrollment, bool $forceRegenerate = true): void
    {
        $enrollment->loadMissing(['course', 'student']);

        if (!$enrollment->course) {
            throw new \RuntimeException('Enrollment senza course associato.');
        }

        $lessonsCount = (int) ($enrollment->course->lessons_count ?? 0);
        if ($lessonsCount <= 0) {
            // niente da generare
            return;
        }

        // requisiti minimi
        if (empty($enrollment->starts_at)) {
            throw new \RuntimeException('Imposta la data inizio corso (starts_at) per generare le lezioni.');
        }
        if (empty($enrollment->weekly_day)) {
            throw new \RuntimeException('Imposta il giorno settimanale (weekly_day) per generare le lezioni.');
        }
        if (empty($enrollment->weekly_time)) {
            throw new \RuntimeException('Imposta l’ora (weekly_time) per generare le lezioni.');
        }

        $durationMinutes = (int) ($enrollment->lesson_duration_minutes ?? 60);
        $durationMinutes = max(15, min(240, $durationMinutes));

        DB::transaction(function () use ($enrollment, $lessonsCount, $durationMinutes, $forceRegenerate) {

            if ($forceRegenerate) {
                $enrollment->lessons()->delete();
            } else {
                // se non forziamo, evitiamo duplicati e ripartiamo dal prossimo numero
                $existing = $enrollment->lessons()->count();
                if ($existing >= $lessonsCount) {
                    return;
                }
            }

            // calcolo prima data lezione: primo giorno weekly_day a partire da starts_at
            $firstStart = $this->firstOccurrenceDateTime(
                Carbon::parse($enrollment->starts_at)->startOfDay(),
                (int) $enrollment->weekly_day,
                (string) $enrollment->weekly_time
            );

            // slitta se cade su chiusura
            $firstStart = $this->skipClosuresByWeek($firstStart);

            $created = 0;
            $currentStart = $firstStart->copy();

            while ($created < $lessonsCount) {
                $start = $this->skipClosuresByWeek($currentStart->copy());
                $end = $start->copy()->addMinutes($durationMinutes);

                $lessonNumber = $created + 1;

                $enrollment->lessons()->create([
                    'enrollment_id'     => $enrollment->id,
                    'student_id'        => $enrollment->student_id,
                    'course_id'         => $enrollment->course_id,
                    'teacher_id'        => $enrollment->default_teacher_id,
                    'lesson_number'     => $lessonNumber,
                    'starts_at'         => $start,
                    'ends_at'           => $end,
                    'duration_minutes'  => $durationMinutes,
                    'status'            => 'scheduled',
                ]);

                $created++;

                // prossima settimana (base): stesso giorno/ora
                $currentStart = $start->copy()->addWeek();
            }

            // aggiorna ends_at dell’iscrizione = data ultima lezione (solo data)
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

    /**
     * Trova la prima data/orario che cade sul weekly_day (1=Lun..7=Dom) a partire da $startDate (inclusa).
     */
    private function firstOccurrenceDateTime(Carbon $startDate, int $weeklyDay, string $weeklyTime): Carbon
    {
        $weeklyDay = max(1, min(7, $weeklyDay));

        [$h, $m] = array_pad(explode(':', $weeklyTime), 2, '00');
        $h = (int) $h;
        $m = (int) $m;

        // Carbon: dayOfWeekIso => 1 (Mon) .. 7 (Sun)
        $candidate = $startDate->copy()->setTime($h, $m, 0);

        $diff = $weeklyDay - $candidate->dayOfWeekIso;
        if ($diff < 0) {
            $diff += 7;
        }
        $candidate->addDays($diff);

        return $candidate;
    }

    /**
     * Se la data (solo giorno) è in closure_days, slitta di +7 giorni finché non trova un giorno aperto.
     */
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
