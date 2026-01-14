<?php

namespace App\Filament\Pages\Student;

use App\Models\Enrollment;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Database\Eloquent\Builder;

class MyCourses extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'I miei corsi';
    protected static ?string $navigationGroup = 'Studente';
    protected static string $view = 'filament.pages.student.my-courses';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('student.courses.view_own') ?? false;
    }

    protected function baseQuery(): Builder
    {
        $studentId = auth()->user()?->student?->id;

        return Enrollment::query()
            ->when(
                $studentId,
                fn (Builder $q) => $q->where('student_id', $studentId),
                fn (Builder $q) => $q->whereRaw('1=0')
            )
            ->with(['course.subject'])
            ->select(['id', 'course_id', 'created_at']);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->baseQuery())
            ->columns([
                Tables\Columns\TextColumn::make('course.name')
                    ->label('Corso')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('course.subject.name')
                    ->label('Lingua')
                    ->badge()
                    ->placeholder('—')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Iscritto il')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
