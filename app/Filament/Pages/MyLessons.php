<?php

namespace App\Filament\Pages;

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
    protected static ?string $navigationGroup = 'Didattica';

    protected static string $view = 'filament.pages.my-lessons';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('lessons.view_own') ?? false;
    }

    public function getTitle(): string
    {
        return 'Le mie lezioni';
    }

    protected function lessonsQuery(): Builder
    {
        $teacherId = auth()->user()?->teacher?->id;

        return Lesson::query()
            ->when(
                $teacherId,
                fn (Builder $q) => $q->where('teacher_id', $teacherId),
                fn (Builder $q) => $q->whereRaw('1 = 0')
            )
            ->with(['student', 'course'])
            ->orderBy('starts_at', 'asc');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->lessonsQuery())
            ->defaultSort('starts_at', 'asc')
            ->columns([
                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Inizio')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('course.name')
                    ->label('Corso')
                    ->searchable()
                    ->sortable(),

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
                    ->color(fn (string $state): string => match ($state) {
                        Lesson::STATUS_COMPLETED         => 'success',
                        Lesson::STATUS_SCHEDULED         => 'warning',
                        Lesson::STATUS_CANCELLED_RECOVER => 'danger',
                        Lesson::STATUS_CANCELLED_COUNTED => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state, Lesson $record) => $record->getStatusLabel())
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('future')
                    ->label('Solo future')
                    ->query(fn (Builder $query) => $query->where('starts_at', '>=', now())),

                Tables\Filters\Filter::make('past')
                    ->label('Solo passate')
                    ->query(fn (Builder $query) => $query->where('starts_at', '<', now())),
            ])
            ->actions([
                Tables\Actions\Action::make('details')
                    ->label('Vedi')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Dettaglio lezione')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Chiudi')
                    ->form([
                        \Filament\Forms\Components\Placeholder::make('starts_at')
                            ->label('Inizio')
                            ->content(fn (Lesson $record) => optional($record->starts_at)->format('d/m/Y H:i') ?? '—'),

                        \Filament\Forms\Components\Placeholder::make('course')
                            ->label('Corso')
                            ->content(fn (Lesson $record) => $record->course?->name ?? '—'),

                        \Filament\Forms\Components\Placeholder::make('student')
                            ->label('Studente')
                            ->content(function (Lesson $record) {
                                $s = $record->student;
                                if (! $s) return '—';
                                return $s->full_name
                                    ?? trim(($s->first_name ?? '') . ' ' . ($s->last_name ?? ''))
                                    ?: ($s->email ?? ('ID ' . $s->id));
                            }),

                        \Filament\Forms\Components\Placeholder::make('status')
                            ->label('Stato')
                            ->content(fn (Lesson $record) => $record->getStatusLabel()),

                        \Filament\Forms\Components\Placeholder::make('notes')
                            ->label('Note / Compiti')
                            ->content(fn (Lesson $record) => $record->notes ?: '—'),
                    ]),

                Tables\Actions\Action::make('mark_completed')
                    ->label('Svolta')
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->visible(fn (Lesson $record) =>
                        (auth()->user()?->can('lessons.update_own') ?? false)
                        && $record->teacher_id === auth()->user()?->teacher?->id
                        && $record->status !== Lesson::STATUS_COMPLETED
                    )
                    ->action(function (Lesson $record) {
                        if ($record->teacher_id !== auth()->user()?->teacher?->id) {
                            abort(403);
                        }

                        $record->update([
                            'status' => Lesson::STATUS_COMPLETED,
                            'cancelled_at' => null,
                            'cancel_reason' => null,
                        ]);
                    }),

                Tables\Actions\Action::make('notes')
                    ->label('Note')
                    ->icon('heroicon-o-pencil-square')
                    ->modalHeading('Note e compiti')
                    ->form([
                        \Filament\Forms\Components\Textarea::make('notes')
                            ->label('Note lezione / Compiti per la prossima')
                            ->rows(6)
                            ->maxLength(5000)
                            ->required(),
                    ])
                    ->fillForm(fn (Lesson $record) => [
                        'notes' => $record->notes,
                    ])
                    ->visible(fn (Lesson $record) =>
                        (auth()->user()?->can('lessons.update_own') ?? false)
                        && $record->teacher_id === auth()->user()?->teacher?->id
                    )
                    ->action(function (Lesson $record, array $data) {
                        if ($record->teacher_id !== auth()->user()?->teacher?->id) {
                            abort(403);
                        }

                        $record->update([
                            'notes' => $data['notes'],
                        ]);
                    }),
            ]);
    }
}
