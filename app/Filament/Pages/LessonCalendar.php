<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class LessonCalendar extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Calendario lezioni';
    protected static ?string $navigationGroup = 'Didattica';
    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.lesson-calendar';

    public static function canAccess(): bool
    {
        $u = auth()->user();
        if (! $u) {
            return false;
        }

        return $u->hasAnyRole(['superadmin', 'amministrazione', 'segreteria']);
    }

    public function getTitle(): string
    {
        return 'Calendario generale lezioni';
    }
}
