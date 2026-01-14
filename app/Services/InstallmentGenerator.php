<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Enrollment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InstallmentGenerator
{
    /**
     * Regole:
     * - Tassa iscrizione: sempre separata, number = 0
     * - Acconto: separato, scala SOLO dal prezzo corso, number = 1 (se > 0)
     * - Rate: calcolate su (prezzo_corso - acconto), number = 2..(n+1)
     * - Nessun numero negativo (DB unsigned)
     */
    public function generateForEnrollment(Enrollment $enrollment): void
    {
        DB::transaction(function () use ($enrollment) {
            // Lock riga enrollment per evitare doppie rigenerazioni concorrenti (Livewire / doppio submit)
            $enrollment = Enrollment::query()
                ->whereKey($enrollment->id)
                ->lockForUpdate()
                ->firstOrFail();

            $course = Course::findOrFail($enrollment->course_id);

            // Cancella tutte le rate esistenti (idempotente)
            $enrollment->installments()->delete();

            $enrolledAt = $enrollment->enrolled_at
                ? Carbon::parse($enrollment->enrolled_at)
                : now();

            $coursePriceCents = $this->moneyToCents($enrollment->course_price_eur ?? $course->prezzo);
            $feeCents         = $this->moneyToCents($enrollment->registration_fee_eur ?? $course->tassa_iscrizione);
            $depositCents     = $this->moneyToCents($enrollment->deposit ?? 0);

            // Acconto non può superare il prezzo corso (non include tassa)
            $depositCents = min(max(0, $depositCents), max(0, $coursePriceCents));

            // 1) Tassa iscrizione separata: number = 0
            if ($feeCents > 0) {
                $enrollment->installments()->create([
                    'number'       => 0,
                    'due_date'     => $enrolledAt->toDateString(),
                    'amount_cents' => $feeCents,
                    'paid_cents'   => 0,
                    'status'       => 'da_pagare',
                ]);
            }

            // 2) Acconto separato: number = 1
            if ($depositCents > 0) {
                $enrollment->installments()->create([
                    'number'       => 1,
                    'due_date'     => $enrolledAt->toDateString(),
                    'amount_cents' => $depositCents,
                    'paid_cents'   => 0,
                    'status'       => 'da_pagare',
                ]);
            }

            // 3) Residuo rateizzabile = prezzo corso - acconto
            $remainingCourseCents = max(0, $coursePriceCents - $depositCents);

            if ($remainingCourseCents === 0) {
                return; // niente rate corso
            }

            // Se payment_plan = single -> una sola rata (oltre tassa e acconto)
            $paymentPlan = $enrollment->payment_plan ?? 'monthly';
            if ($paymentPlan === 'single') {
                $due = $this->resolveFirstDueDate($enrollment, $enrolledAt);

                $enrollment->installments()->create([
                    'number'       => 2,
                    'due_date'     => $due->toDateString(),
                    'amount_cents' => $remainingCourseCents,
                    'paid_cents'   => 0,
                    'status'       => 'da_pagare',
                ]);

                return;
            }

            // Monthly: split in N rate
            $n = (int) ($enrollment->installments_count ?? 1);
            $n = max(1, min($n, 24));

            $firstDue = $this->resolveFirstDueDate($enrollment, $enrolledAt);

            // Split in cents con gestione resto
            $base = intdiv($remainingCourseCents, $n);
            $rest = $remainingCourseCents - ($base * $n);

            for ($i = 1; $i <= $n; $i++) {
                $amount = $base + ($i <= $rest ? 1 : 0);

                $due = $firstDue->copy()->addMonthsNoOverflow($i - 1);

                // number: 2..(n+1) per lasciare 0=tassa, 1=acconto
                $enrollment->installments()->create([
                    'number'       => $i + 1,
                    'due_date'     => $due->toDateString(),
                    'amount_cents' => $amount,
                    'paid_cents'   => 0,
                    'status'       => 'da_pagare',
                ]);
            }
        });
    }

    private function resolveFirstDueDate(Enrollment $enrollment, Carbon $enrolledAt): Carbon
    {
        if (!empty($enrollment->first_installment_due_date)) {
            return Carbon::parse($enrollment->first_installment_due_date);
        }

        if (!empty($enrollment->starts_at)) {
            return Carbon::parse($enrollment->starts_at);
        }

        return $enrolledAt->copy()->addDays(30);
    }

    /**
     * Converte un DECIMAL euro (es. "100.80") in cents (10080).
     * Supporta virgola (es. "100,80").
     */
    private function moneyToCents($value): int
    {
        if ($value === null || $value === '') {
            return 0;
        }

        $normalized = str_replace(',', '.', (string) $value);
        return (int) round(((float) $normalized) * 100);
    }
}
