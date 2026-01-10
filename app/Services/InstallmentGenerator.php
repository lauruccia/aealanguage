<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Installment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InstallmentGenerator
{
    public function generateForEnrollment(Enrollment $enrollment): void
{
    $course = Course::findOrFail($enrollment->course_id);

    DB::transaction(function () use ($enrollment, $course) {
        $enrollment->installments()->delete();

        $enrolledAt = $enrollment->enrolled_at
            ? Carbon::parse($enrollment->enrolled_at)
            : now();

$totalCents = $this->moneyToCents($enrollment->course_price_eur ?? $course->prezzo);
$feeCents   = $this->moneyToCents($enrollment->registration_fee_eur ?? $course->tassa_iscrizione);


        // 1) Tassa iscrizione separata (number=0)
        if ($feeCents > 0) {
            $enrollment->installments()->create([
                'number'       => 0,
                'due_date'     => $enrolledAt->toDateString(),
                'amount_cents' => $feeCents,
                'paid_cents'   => 0,
                'status'       => 'da_pagare',

            ]);
        }

        $remaining = max(0, $totalCents - $feeCents);
        if ($remaining === 0) {
            return;
        }

        $n = (int) ($enrollment->installments_count ?? 1);
        $n = max(1, min($n, 24));

        // Prima scadenza:
        // 1) se compilata first_installment_due_date
        // 2) altrimenti starts_at
        // 3) altrimenti +30 giorni da enrolled_at
        if (!empty($enrollment->first_installment_due_date)) {
            $firstDue = Carbon::parse($enrollment->first_installment_due_date);
        } elseif (!empty($enrollment->starts_at)) {
            $firstDue = Carbon::parse($enrollment->starts_at);
        } else {
            $firstDue = $enrolledAt->copy()->addDays(30);
        }

        // Split in cents con gestione resto (per importi tipo 100,80€)
        $base = intdiv($remaining, $n);
        $rest = $remaining - ($base * $n);

        for ($i = 1; $i <= $n; $i++) {
            $amount = $base + ($i <= $rest ? 1 : 0); // distribuisce 1 cent ai primi "rest"

            $due = $firstDue->copy()->addMonthsNoOverflow($i - 1);

            $enrollment->installments()->create([
                'number'       => $i,
                'due_date'     => $due->toDateString(),
                'amount_cents' => $amount,
                'paid_cents'   => 0,
                'status'       => 'da_pagare',

            ]);
        }
    });
}


    /**
     * Converte un DECIMAL euro (es. "100.80") in cents (10080).
     * Supporta anche input con virgola (es. "100,80") per sicurezza.
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
