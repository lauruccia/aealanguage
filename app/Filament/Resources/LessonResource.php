<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LessonResource\Pages;
use App\Models\Lesson;
use App\Models\Teacher;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class LessonResource extends Resource
{
    protected static ?string $model = Lesson::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Lezioni';
    protected static ?string $modelLabel = 'Lezione';
    protected static ?string $pluralModelLabel = 'Lezioni';
    protected static ?string $navigationGroup = 'Didattica';
    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool
    {
        $u = auth()->user();
        if (! $u) {
            return false;
        }

        return $u->hasAnyRole(['superadmin', 'amministrazione', 'segreteria']);
    }

    protected static function blockingStatuses(): array
    {
        return [
            Lesson::STATUS_SCHEDULED,
            Lesson::STATUS_COMPLETED,
            Lesson::STATUS_CANCELLED_COUNTED,
            Lesson::STATUS_CANCELLED_RECOVER,
        ];
    }

    /**
     * Ritorna gli ID dei docenti OCCUPATI nel range [start, end)
     */
    protected static function busyTeacherIds(?string $startsAt, ?int $durationMinutes, ?Lesson $record = null): array
    {
        if (blank($startsAt)) {
            return [];
        }

        $start = Carbon::parse($startsAt);
        $minutes = max(1, (int) ($durationMinutes ?? 60));
        $end = (clone $start)->addMinutes($minutes);

        $q = Lesson::query()
            ->whereNotNull('teacher_id')
            ->whereIn('status', static::blockingStatuses())
            ->when($record?->id, fn ($qq) => $qq->where('id', '!=', $record->id))
            ->where('starts_at', '<', $end)
            ->whereRaw(
                "COALESCE(ends_at, DATE_ADD(starts_at, INTERVAL COALESCE(duration_minutes, 60) MINUTE)) > ?",
                [$start->toDateTimeString()]
            );

        return $q->pluck('teacher_id')->unique()->values()->all();
    }

    protected static function teacherOptionsWithAvailability(?string $startsAt, ?int $durationMinutes, ?Lesson $record = null): array
    {
        $busy = static::busyTeacherIds($startsAt, $durationMinutes, $record);

        return Teacher::query()
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get()
            ->mapWithKeys(function (Teacher $t) use ($busy) {
                $name = trim(($t->last_name ?? '') . ' ' . ($t->first_name ?? ''));
                $label = $name !== '' ? $name : ('Docente #' . $t->id);

                $label .= in_array($t->id, $busy, true) ? ' — OCCUPATO' : ' — DISPONIBILE';

                return [$t->id => $label];
            })
            ->toArray();
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('lessons.manage') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->can('lessons.manage') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->can('lessons.manage') ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->can('lessons.manage') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Dettagli lezione')
                ->schema([
                    Forms\Components\Select::make('student_id')
                        ->label('Studente')
                        ->relationship('student', 'id')
                        ->getOptionLabelFromRecordUsing(fn ($record) =>
                            $record->full_name
                            ?? trim(($record->first_name ?? '') . ' ' . ($record->last_name ?? ''))
                            ?: ('ID ' . $record->id)
                        )
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\Select::make('course_id')
                        ->label('Corso')
                        ->relationship('course', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\Select::make('teacher_id')
                        ->label('Docente')
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->live()
                        ->options(fn (Get $get, ?Lesson $record) =>
                            static::teacherOptionsWithAvailability(
                                $get('starts_at'),
                                (int) ($get('duration_minutes') ?? 60),
                                $record
                            )
                        )
                        ->disableOptionWhen(fn (string|int $value, Get $get, ?Lesson $record) =>
                            in_array(
                                (int) $value,
                                static::busyTeacherIds(
                                    $get('starts_at'),
                                    (int) ($get('duration_minutes') ?? 60),
                                    $record
                                ),
                                true
                            )
                        )
                        ->helperText('I docenti occupati vengono disabilitati in base a Inizio + Durata.'),

                    Forms\Components\DateTimePicker::make('starts_at')
                        ->label('Inizio')
                        ->seconds(false)
                        ->native(false)
                        ->closeOnDateSelection()
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (Set $set) {
                            $set('teacher_id', null);
                        }),

                    Forms\Components\TextInput::make('duration_minutes')
                        ->label('Durata (minuti)')
                        ->numeric()
                        ->minValue(1)
                        ->default(60)
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (Set $set) {
                            $set('teacher_id', null);
                        }),

                    Forms\Components\TextInput::make('lesson_number')
                        ->label('N° lezione')
                        ->numeric()
                        ->required(),

                    Forms\Components\DateTimePicker::make('ends_at')
                        ->label('Fine')
                        ->nullable(),

                    Forms\Components\Select::make('status')
                        ->label('Stato')
                        ->required()
                        ->options([
                            Lesson::STATUS_SCHEDULED         => 'Programmato',
                            Lesson::STATUS_COMPLETED         => 'Completata',
                            Lesson::STATUS_CANCELLED_RECOVER => 'Annullata (da recuperare)',
                            Lesson::STATUS_CANCELLED_COUNTED => 'Annullata (conteggiata)',
                        ])
                        ->default(Lesson::STATUS_SCHEDULED),

                    Forms\Components\DateTimePicker::make('cancelled_at')
                        ->label('Annullata il')
                        ->nullable(),

                    Forms\Components\TextInput::make('cancel_reason')
                        ->label('Motivo annullamento')
                        ->maxLength(255)
                        ->nullable(),

                    Forms\Components\Textarea::make('notes')
                        ->label('Note')
                        ->columnSpanFull()
                        ->nullable(),
                ])
                ->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('starts_at', 'asc')
            ->columns([
                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Inizio')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('student_id')
                    ->label('Studente')
                    ->formatStateUsing(function ($state, Lesson $record) {
                        $s = $record->student;
                        if (! $s) return '—';
                        return $s->full_name
                            ?? trim(($s->first_name ?? '') . ' ' . ($s->last_name ?? ''))
                            ?: ($s->email ?? ('ID ' . $s->id));
                    })
                    ->searchable(),

                Tables\Columns\TextColumn::make('course.name')
                    ->label('Corso')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('teacher_id')
                    ->label('Docente')
                    ->formatStateUsing(function ($state, Lesson $record) {
                        $t = $record->teacher;
                        if (! $t) return '—';
                        return trim(($t->first_name ?? '') . ' ' . ($t->last_name ?? '')) ?: ('ID ' . $t->id);
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Stato')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => Lesson::statusLabel($state))
                    ->color(fn (?string $state) => Lesson::statusColor($state))
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn () => static::canCreate()),

                Tables\Actions\Action::make('cancelLesson')
                    ->label('Annulla')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Lesson $record) =>
                        (auth()->user()?->can('lessons.cancel') ?? false)
                        && $record->status === Lesson::STATUS_SCHEDULED
                    )
                    ->form([
                        Forms\Components\Textarea::make('cancel_reason')
                            ->label('Motivo annullamento')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\DateTimePicker::make('cancelled_at')
                            ->label('Data annullamento')
                            ->default(now())
                            ->required(),
                    ])
                    ->action(function (Lesson $record, array $data) {
                        $cancelledAt = Carbon::parse($data['cancelled_at'] ?? now());
                        $startsAt    = $record->starts_at ? Carbon::parse($record->starts_at) : null;

                        // true se annullo con più di 24h di anticipo rispetto a starts_at
                        $moreThan24h = $startsAt ? $cancelledAt->diffInHours($startsAt, false) > 24 : false;

                        $record->cancel_reason = $data['cancel_reason'] ?? null;
                        $record->cancelled_at  = $cancelledAt;

                        $record->status = $moreThan24h
                            ? Lesson::STATUS_CANCELLED_RECOVER
                            : Lesson::STATUS_CANCELLED_COUNTED;

                        $record->save();
                    })
                    ->requiresConfirmation(),

                Tables\Actions\Action::make('recoverLesson')
                    ->label('Recupera')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn (Lesson $record) =>
                        (auth()->user()?->can('lessons.manage') ?? false)
                        && $record->status === Lesson::STATUS_CANCELLED_RECOVER
                    )
                    ->form([
                        Forms\Components\DateTimePicker::make('new_starts_at')
                            ->label('Nuova data e ora')
                            ->required(),

                        Forms\Components\TextInput::make('duration_minutes')
                            ->label('Durata (minuti)')
                            ->numeric()
                            ->minValue(1)
                            ->default(fn (Lesson $record) => $record->duration_minutes ?? 60)
                            ->required(),
                    ])
                    ->action(function (Lesson $record, array $data) {
                        $oldStartsAt = $record->starts_at;
                        $cancelledAt = $record->cancelled_at;
                        $reason      = $record->cancel_reason;

                        $newStartsAt = Carbon::parse($data['new_starts_at']);
                        $duration    = (int) $data['duration_minutes'];

                        $record->starts_at = $newStartsAt;
                        $record->ends_at   = $newStartsAt->copy()->addMinutes($duration);
                        $record->status    = Lesson::STATUS_SCHEDULED;

                        $historyNote = "Lezione recuperata.\n"
                            . "Data originale: " . (optional($oldStartsAt)?->format('d/m/Y H:i') ?? '—') . "\n"
                            . "Annullata il: " . (optional($cancelledAt)?->format('d/m/Y H:i') ?? '—') . "\n"
                            . "Motivo: " . ($reason ?? '—');

                        $record->notes = trim(($record->notes ? $record->notes . "\n\n" : '') . $historyNote);

                        $record->save();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Programma recupero lezione')
                    ->modalSubmitActionLabel('Recupera lezione'),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListLessons::route('/'),
            'create' => Pages\CreateLesson::route('/create'),
            'edit'   => Pages\EditLesson::route('/{record}/edit'),
        ];
    }
}
