<?php

namespace App\Filament\Pages\Student;

use App\Models\Lesson;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Database\Eloquent\Builder;

class MyLessons extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Le mie lezioni';
    protected static ?string $navigationGroup = 'Studente';
    protected static string $view = 'filament.pages.student.my-lessons';

    protected static ?string $slug = 'student/my-lessons';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('studente') ?? false;
    }

    protected function baseQuery(): Builder
    {
        $studentId = auth()->user()?->student?->id;

        return Lesson::query()
            ->when(
                $studentId,
                fn (Builder $q) => $q->where('student_id', $studentId),
                fn (Builder $q) => $q->whereRaw('1=0')
            )
            ->with(['course.subject', 'teacher'])
            ->orderBy('starts_at', 'desc');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->baseQuery())
            ->columns([
                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Data e ora')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('course.name')
                    ->label('Corso')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('course.subject.name')
                    ->label('Lingua')
                    ->badge()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('teacher_id')
                    ->label('Docente')
                    ->formatStateUsing(fn ($state, $record) => trim(($record->teacher?->last_name ?? '') . ' ' . ($record->teacher?->first_name ?? '')) ?: '—')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Stato')
                    ->badge(),
            ])
            ->defaultSort('starts_at', 'desc');
    }
}
