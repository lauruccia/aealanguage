<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Studenti attivi', '—')->icon('heroicon-o-users'),
            Stat::make('Iscrizioni attive', '—')->icon('heroicon-o-document-check'),
            Stat::make('Lezioni oggi', '—')->icon('heroicon-o-calendar-days'),
            Stat::make('Incassi mese', '—')->icon('heroicon-o-banknotes'),
        ];
    }
}
