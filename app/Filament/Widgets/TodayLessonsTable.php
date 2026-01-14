<?php

namespace App\Filament\Widgets;

use App\Models\Lesson;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class TodayLessonsTable extends BaseWidget
{
    protected static ?string $heading = 'Prossime lezioni (oggi)';
    protected static ?int $sort = 2;

protected function getTableQuery(): Builder
{
    return Lesson::query()
        ->where('starts_at', '>=', now())
        ->whereDate('starts_at', today())
        ->orderBy('starts_at');
}

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('starts_at')
                ->label('Ora')
                ->dateTime('H:i')
                ->sortable(),

            Tables\Columns\TextColumn::make('student.full_name')
                ->label('Studente')
                ->placeholder('—')
                ->wrap()
                ->limit(28)
                ->searchable()
                ->formatStateUsing(fn ($state, $record) => $state ?: ($record->student_id ?? '—')),

            Tables\Columns\TextColumn::make('course.name')
                ->label('Corso')
                ->placeholder('—')
                ->limit(24)
                ->formatStateUsing(fn ($state, $record) => $state ?: ($record->course_id ?? '—')),

            Tables\Columns\TextColumn::make('teacher.full_name')
                ->label('Docente')
                ->placeholder('—')
                ->limit(24)
                ->formatStateUsing(fn ($state, $record) => $state ?: ($record->teacher_id ?? '—')),
        ];
    }

    protected function getTableDefaultPaginationPageOption(): int
    {
        return 5;
    }

    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [5, 10, 25];
    }

    protected function getTableEmptyStateHeading(): ?string
{
    return 'Nessuna lezione in programma oggi';
}

protected function getTableEmptyStateDescription(): ?string
{
    return 'Quando saranno pianificate lezioni per oggi, compariranno qui.';
}
}
