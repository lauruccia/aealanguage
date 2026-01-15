<?php

namespace App\Filament\Pages;

use App\Models\Lesson;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class Lessons extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon  = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Lezioni';
    protected static ?string $navigationGroup = 'Docente';
    protected static string  $view            = 'filament.pages.lessons';

    protected static ?string $slug = 'teacher-lessons';

    public static function canAccess(): bool
{
    $u = auth()->user();
    if (! $u) return false;

    return $u->hasRole('docente');
}

    public function getTitle(): string
    {
        return 'Lezioni';
    }

    protected function lessonsQuery(): Builder
    {
        $teacherId = Filament::auth()->user()?->teacher?->id;

        return Lesson::query()
            ->when(
                $teacherId,
                fn (Builder $q) => $q->where('teacher_id', $teacherId),
                fn (Builder $q) => $q->whereRaw('1=0')
            )
            ->with(['student', 'course.subject'])
            ->orderBy('starts_at', 'asc');
    }

    public function table(Table $table): Table
    {
        $teacherId = Filament::auth()->user()?->teacher?->id;

        return $table
            ->query($this->lessonsQuery())
            ->defaultSort('starts_at', 'asc')
            ->searchable(false)
            ->columns([
                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Inizio')
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

                Tables\Columns\TextColumn::make('studente')
                    ->label('Studente')
                    ->state(fn (Lesson $record) => $record->student?->full_name
                        ?? trim(($record->student?->first_name ?? '') . ' ' . ($record->student?->last_name ?? ''))
                        ?: ($record->student?->email ?? ('ID ' . ($record->student?->id ?? '—')))
                    )
                    ->searchable(),

                Tables\Columns\TextColumn::make('notes')
                    ->label('Note')
                    ->limit(40)
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Stato')
                    ->badge()
                    ->color(fn (Lesson $record) => $record->getStatusColor())
                    ->formatStateUsing(fn ($state, Lesson $record) => $record->getStatusLabel())
                    ->sortable(),
            ])

->filters([
    // 👤 Filtro Studente
    Tables\Filters\SelectFilter::make('student_id')
        ->label('Studente')
        ->options(function () use ($teacherId) {
            if (! $teacherId) return [];

            return \App\Models\Student::query()
                ->whereHas('lessons', fn (Builder $q) => $q->where('teacher_id', $teacherId))
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get()
                ->mapWithKeys(fn ($s) => [
                    $s->id => ($s->full_name
                        ?? trim(($s->last_name ?? '') . ' ' . ($s->first_name ?? ''))
                        ?: ($s->email ?? ('ID ' . $s->id))
                    )
                ])
                ->toArray();
        })
        ->searchable()
        ->placeholder('Tutti'),

    // 🏷️ Filtro Stato
    Tables\Filters\SelectFilter::make('status')
        ->label('Stato')
        ->options([
            Lesson::STATUS_SCHEDULED         => Lesson::statusLabel(Lesson::STATUS_SCHEDULED),
            Lesson::STATUS_COMPLETED         => Lesson::statusLabel(Lesson::STATUS_COMPLETED),
            Lesson::STATUS_CANCELLED_RECOVER => Lesson::statusLabel(Lesson::STATUS_CANCELLED_RECOVER),
            Lesson::STATUS_CANCELLED_COUNTED => Lesson::statusLabel(Lesson::STATUS_CANCELLED_COUNTED),
        ])
        ->placeholder('Tutti'),
])


            ->actions([
                // 👁️ Vedi dettagli
                Tables\Actions\Action::make('details')
                    ->label('Vedi')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Dettaglio lezione')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Chiudi')
                    ->form([
                        Placeholder::make('starts_at')
                            ->label('Data e ora')
                            ->content(fn (Lesson $record) => optional($record->starts_at)->format('d/m/Y H:i') ?? '—'),

                        Placeholder::make('course')
                            ->label('Corso')
                            ->content(fn (Lesson $record) => $record->course?->name ?? '—'),

                        Placeholder::make('subject')
                            ->label('Lingua')
                            ->content(fn (Lesson $record) => $record->course?->subject?->name ?? '—'),

                        Placeholder::make('student')
                            ->label('Studente')
                            ->content(function (Lesson $record) {
                                $s = $record->student;
                                if (! $s) return '—';
                                return $s->full_name
                                    ?? trim(($s->first_name ?? '') . ' ' . ($s->last_name ?? ''))
                                    ?: ($s->email ?? ('ID ' . $s->id));
                            }),

                        Placeholder::make('status')
                            ->label('Stato')
                            ->content(fn (Lesson $record) => $record->getStatusLabel()),

                        Placeholder::make('notes')
                            ->label('Note / Compiti')
                            ->content(fn (Lesson $record) => $record->notes ?: '—'),
                    ]),

                // ✅ Segna come svolta
                Tables\Actions\Action::make('mark_completed')
                    ->label('Segna come svolta')
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->visible(fn (Lesson $record) =>
                        $record->teacher_id === $teacherId
                        && $record->status !== Lesson::STATUS_COMPLETED
                    )
                    ->action(function (Lesson $record) use ($teacherId) {
                        if ($record->teacher_id !== $teacherId) {
                            abort(403);
                        }

                        $record->update([
                            'status' => Lesson::STATUS_COMPLETED,
                            'cancelled_at' => null,
                            'cancel_reason' => null,
                        ]);
                    }),

                // 📝 Note / Compiti
                Tables\Actions\Action::make('notes')
                    ->label('Note / Compiti')
                    ->icon('heroicon-o-pencil-square')
                    ->modalHeading('Note e compiti')
                    ->form([
                        Textarea::make('notes')
                            ->label('Note lezione / Compiti per la prossima')
                            ->rows(6)
                            ->maxLength(5000),
                    ])
                    ->fillForm(fn (Lesson $record) => [
                        'notes' => $record->notes,
                    ])
                    ->visible(fn (Lesson $record) => $record->teacher_id === $teacherId)
                    ->action(function (Lesson $record, array $data) use ($teacherId) {
                        if ($record->teacher_id !== $teacherId) {
                            abort(403);
                        }

                        $record->update([
                            'notes' => $data['notes'] ?? null,
                        ]);
                    }),
            ]);
    }
}
