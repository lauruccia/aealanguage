<?php

namespace App\Console\Commands;

use App\Models\Installment;
use Illuminate\Console\Command;

class MarkOverdueInstallments extends Command
{
    protected $signature = 'installments:mark-overdue';
    protected $description = 'Marca come scadute le rate oltre la scadenza e non saldate';

    public function handle(): int
    {
        $today = now()->toDateString();

        $count = Installment::query()
            ->whereDate('due_date', '<', $today)
            ->whereIn('status', ['da_pagare', 'parziale'])
            ->whereColumn('paid_cents', '<', 'amount_cents')
            ->update(['status' => 'scaduta']);

        $this->info("Rate marcate come scadute: {$count}");

        return self::SUCCESS;
    }
}
