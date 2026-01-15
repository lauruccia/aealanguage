<?php

namespace App\Filament\Pages;

use App\Models\Lesson;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MyCourses extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon  = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'I miei corsi';
    protected static ?string $navigationGroup = 'Docente';
    protected static string  $view            = 'filament.pages.my-courses';

    // ✅ IMPORTANTISSIMO: slug fisso (evita route “strane” o collisioni)
    //protected static ?string $slug = 'my-courses';
    protected static ?string $slug = 'teacher/my-courses';

    /**
     * ✅ Accesso consentito solo ai docenti (usa guard di Filament).
     * Se qui torna false -> Filament risponde 403.
     */
public static function canAccess(): bool
{
    return auth()->user()?->hasRole('docente') ?? false;
}





    public function getTitle(): string
    {
        return 'I miei corsi';
    }

    protected function baseQuery(): Builder
    {
        $teacherId = Filament::auth()->user()?->teacher?->id;

        return Lesson::query()
            ->when(
                $teacherId,
                fn (Builder $q) => $q->where('teacher_id', $teacherId),
                fn (Builder $q) => $q->whereRaw('1=0')
            )
            ->whereNotNull('course_id')
            ->whereNotNull('student_id')
            ->with(['course.subject', 'student'])
            ->selectRaw('MAX(id) as id, course_id, student_id, MAX(starts_at) as last_lesson_at, COUNT(*) as lessons_total')
            ->groupBy('course_id', 'student_id');
    }

    public function table(Table $table): Table
    {
        $teacherId = Filament::auth()->user()?->teacher?->id;

        return $table
            ->query($this->baseQuery())
            ->columns([
                Tables\Columns\TextColumn::make('course.name')
                    ->label('Corso')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('course.subject.name')
                    ->label('Lingua')
                    ->badge()
                    ->sortable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('student_display')
                    ->label('Studente')
                    ->state(function ($record) {
                        $s = $record->student;
                        if (! $s) return '—';

                        return $s->full_name
                            ?? trim(($s->first_name ?? '') . ' ' . ($s->last_name ?? ''))
                            ?: ($s->email ?? ('ID ' . $s->id));
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('student', function (Builder $q) use ($search) {
                            $q->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhereRaw("CONCAT(first_name,' ',last_name) LIKE ?", ["%{$search}%"]);
                        });
                    }),

                Tables\Columns\TextColumn::make('last_lesson_at')
                    ->label('Ultima lezione')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('lessons_total')
                    ->label('Lezioni')
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('lessons_completed')
                    ->label('Svolte')
                    ->alignCenter()
                    ->getStateUsing(function ($record) use ($teacherId) {
                        if (! $teacherId) return 0;

                        return Lesson::query()
                            ->where('teacher_id', $teacherId)
                            ->where('course_id', $record->course_id)
                            ->where('student_id', $record->student_id)
                            ->where('status', Lesson::STATUS_COMPLETED)
                            ->count();
                    }),
            ])
            ->defaultSort('last_lesson_at', 'desc');
    }

    public function getTableRecordKey($record): string
    {
        return (string) ($record->course_id ?? '0') . '-' . (string) ($record->student_id ?? '0');
    }
}
