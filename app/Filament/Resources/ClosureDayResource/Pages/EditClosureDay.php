<?php

namespace App\Filament\Resources\ClosureDayResource\Pages;

use App\Filament\Resources\ClosureDayResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditClosureDay extends EditRecord
{
    protected static string $resource = ClosureDayResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
