<?php

namespace App\Observers;

use App\Models\Lesson;
use App\Models\EnrollmentHourMovement;
use Illuminate\Support\Facades\DB;

class LessonObserver
{
    public function saved(Lesson $lesson): void
    {
        // Serve enrollment_id per scalare ore sul contratto
        if (!$lesson->enrollment_id) {
            return;
        }

        DB::transaction(function () use ($lesson) {

            $shouldCount = $lesson->countsAsAttended(); // completed o cancelled_counted

            // movimento principale di consumo ore per questa lezione (unico)
            $movement = EnrollmentHourMovement::query()
                ->where('lesson_id', $lesson->id)
                ->where('type', 'lesson')
                ->first();

            // minuti che dovrebbero essere consumati ORA
            $duration = (int) ($lesson->duration_minutes ?? 60);
if ($duration <= 0) $duration = 60;

$desiredMinutes = -1 * $duration; // debito

            // 1) Se deve contare e non esiste movimento -> crealo
            if ($shouldCount && !$movement) {
                EnrollmentHourMovement::create([
                    'enrollment_id' => $lesson->enrollment_id,
                    'lesson_id'     => $lesson->id,
                    'type'          => 'lesson',
                    'minutes'       => $desiredMinutes,
                    'note'          => 'Consumo ore per lezione (completed o cancelled_counted)',
                    'occurred_at'   => now(),
                ]);
                return;
            }

            // 2) Se NON deve contare e c’è un movimento -> storno (audit)
            if (!$shouldCount && $movement) {
                EnrollmentHourMovement::create([
                    'enrollment_id' => $lesson->enrollment_id,
                    'lesson_id'     => $lesson->id,
                    'type'          => 'adjustment',
                    'minutes'       => abs((int) $movement->minutes), // riaccredito
                    'note'          => 'Storno consumo ore: lezione non più conteggiata',
                    'occurred_at'   => now(),
                ]);

                // opzionale: puoi anche eliminare il movimento "lesson" per non tenerlo attivo
                // ma io preferisco tenerlo e usare lo storno per audit.
                return;
            }

            // 3) Se deve contare e movimento esiste, ma durata cambiata -> aggiusta delta
            if ($shouldCount && $movement) {
                $current = (int) $movement->minutes; // negativo
                if ($current !== $desiredMinutes) {
                    // delta da applicare come adjustment (es. da -60 a -90 => delta -30)
                    $delta = $desiredMinutes - $current;

                    EnrollmentHourMovement::create([
                        'enrollment_id' => $lesson->enrollment_id,
                        'lesson_id'     => $lesson->id,
                        'type'          => 'adjustment',
                        'minutes'       => $delta, // può essere negativo o positivo
                        'note'          => 'Rettifica consumo ore per variazione durata lezione',
                        'occurred_at'   => now(),
                    ]);

                    // aggiorno anche il movimento base per riflettere la nuova durata
                    $movement->update([
                        'minutes' => $desiredMinutes,
                    ]);
                }
            }
        });
    }
}
