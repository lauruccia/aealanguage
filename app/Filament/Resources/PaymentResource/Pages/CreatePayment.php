<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Installment;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;


    protected function afterCreate(): void
{
    $payment = $this->record;

    if ($payment->installment_id) {
        $inst = Installment::find($payment->installment_id);

        if ($inst) {
            $inst->paid_cents = (int) $inst->paid_cents + (int) $payment->amount_cents;

            if ($inst->paid_cents >= $inst->amount_cents) {
                $inst->status = 'pagata';
            } elseif ($inst->paid_cents > 0) {
                $inst->status = 'parziale';
            } else {
                $inst->status = 'da_pagare';
            }

            $inst->save();
        }
    }
}
}
