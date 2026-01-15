<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class AdminDashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $title = 'Dashboard';

    public static function canAccess(): bool
{
    $u = auth()->user();
    if (! $u) return false;

    return $u->hasAnyRole(['superadmin', 'amministrazione', 'segreteria', 'docente', 'studente']);
}


    public function getHeaderWidgets(): array
    {
        return [];
    }

   public function getWidgets(): array
{
    return [
        \App\Filament\Widgets\TodayLessonsTable::class,
    ];
}

    public function getColumns(): int | string | array
    {
        return 2;
    }
}
