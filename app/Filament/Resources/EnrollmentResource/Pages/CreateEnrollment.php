<?php

namespace App\Filament\Resources\EnrollmentResource\Pages;

use App\Filament\Resources\EnrollmentResource;
use App\Services\InstallmentGenerator;
use Filament\Resources\Pages\CreateRecord;

class CreateEnrollment extends CreateRecord
{
    protected static string $resource = EnrollmentResource::class;

    protected function afterCreate(): void
    {
        app(InstallmentGenerator::class)->generateForEnrollment($this->record);
    }
}
