<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnrollmentResource\Pages;
use App\Models\Course;
use App\Models\Enrollment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Model;

class EnrollmentResource extends Resource
{
    protected static ?string $model = Enrollment::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';
    protected static ?string $navigationLabel = 'Contratti';
    protected static ?string $modelLabel = 'Contratto';
    protected static ?string $pluralModelLabel = 'Contratti';

    protected static ?string $navigationGroup = 'Studenti';
    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool
    {
        $u = auth()->user();
        if (! $u) {
            return false;
        }

        // visibile allo staff
        return $u->hasAnyRole(['superadmin', 'amministrazione', 'segreteria']);
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('enrollments.manage') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->can('enrollments.manage') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->can('enrollments.manage') ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->can('enrollments.manage') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Ammissione')
                ->schema([
                    Forms\Components\Select::make('student_id')
                        ->label('Studente')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->relationship('student', 'id')
                        ->getOptionLabelFromRecordUsing(function ($record) {
                            $label = $record->full_name
                                ?? trim(($record->first_name ?? '') . ' ' . ($record->last_name ?? ''));
                            return $label !== '' ? $label : ($record->email ?? ('ID ' . $record->id));
                        }),

                    Forms\Components\Select::make('course_id')
                        ->label('Corso')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->reactive()
                        ->options(fn () => Course::query()->orderBy('name')->pluck('name', 'id'))
                        ->afterStateUpdated(function ($state, callable $set) {
                            if (! $state) {
                                $set('course_subject_label', null);
                                return;
                            }

                            $course = Course::with('subject')->find($state);
                            if (! $course) {
                                return;
                            }

                            $set('course_price_eur', $course->prezzo);
                            $set('registration_fee_eur', $course->tassa_iscrizione);
                            $set('deposit', 0);

                            // Lingua derivata dal corso
                            $set('course_subject_label', $course->subject?->name);
                        }),

                    Forms\Components\Placeholder::make('course_subject_label')
                        ->label('Lingua')
                        ->content(fn (Forms\Get $get) => $get('course_subject_label') ?: '—')
                        ->dehydrated(false),

                    Forms\Components\Select::make('status')
                        ->label('Stato contratto')
                        ->required()
                        ->default('attivo')
                        ->options([
                            'attivo'    => 'Attivo',
                            'concluso'  => 'Concluso',
                            'annullato' => 'Annullato',
                            'sospeso'   => 'Sospeso',
                        ]),
                ])
                ->columns(4),

            Forms\Components\Section::make('Prezzi concordati')
                ->schema([
                    Forms\Components\TextInput::make('course_price_eur')
                        ->label('Prezzo corso (€)')
                        ->numeric()
                        ->required()
                        ->minValue(0)
                        ->reactive(),

                    Forms\Components\TextInput::make('registration_fee_eur')
                        ->label('Tassa iscrizione (€)')
                        ->numeric()
                        ->required()
                        ->minValue(0)
                        ->default(0),

                    Forms\Components\TextInput::make('deposit')
                        ->label('Acconto (€)')
                        ->helperText('Importo già versato sul prezzo del corso. Può essere 0.')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->reactive()
                        ->rule(function (Forms\Get $get) {
                            $price = (float) ($get('course_price_eur') ?? 0);
                            return 'lte:' . $price;
                        }),

                    Forms\Components\TextInput::make('remaining_to_pay')
                        ->label('Residuo da rateizzare (€)')
                        ->disabled()
                        ->dehydrated(false)
                        ->formatStateUsing(function ($state, Forms\Get $get) {
                            $price = (float) ($get('course_price_eur') ?? 0);
                            $deposit = (float) ($get('deposit') ?? 0);
                            $remaining = max(0, $price - $deposit);
                            return number_format($remaining, 2, '.', '');
                        }),
                ])
                ->columns(2),

            Forms\Components\Section::make('Pianificazione lezioni')
                ->schema([
                    Forms\Components\Select::make('default_teacher_id')
                        ->label('Docente (default)')
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->relationship('defaultTeacher', 'last_name')
                        ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name),

                    Forms\Components\Select::make('weekly_day')
                        ->label('Giorno della settimana')
                        ->options([
                            1 => 'Lunedì',
                            2 => 'Martedì',
                            3 => 'Mercoledì',
                            4 => 'Giovedì',
                            5 => 'Venerdì',
                            6 => 'Sabato',
                            7 => 'Domenica',
                        ])
                        ->nullable()
                        ->reactive(),

                    Forms\Components\TextInput::make('weekly_time')
                        ->label('Ora lezione (HH:MM)')
                        ->placeholder('17:00')
                        ->helperText('Formato 24h, es. 17:00')
                        ->regex('/^\d{2}:\d{2}$/')
                        ->nullable()
                        ->reactive(),

                    Forms\Components\Hidden::make('lesson_duration_minutes')
                        ->default(60)
                        ->dehydrated(true),
                ])
                ->columns(3),

            Forms\Components\Section::make('Piano pagamento')
                ->schema([
                    Forms\Components\Radio::make('payment_plan')
                        ->label('Modalità pagamento')
                        ->options([
                            'single'  => 'Unico importo',
                            'monthly' => 'Rate mensili',
                        ])
                        ->default('single')
                        ->required()
                        ->inline()
                        ->reactive(),

                    Forms\Components\TextInput::make('installments_count')
                        ->label('Numero rate')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(24)
                        ->default(1)
                        ->required()
                        ->visible(fn (Forms\Get $get) => $get('payment_plan') === 'monthly'),

                    Forms\Components\DatePicker::make('first_installment_due_date')
                        ->label('Prima scadenza rata (opzionale)')
                        ->nullable()
                        ->visible(fn (Forms\Get $get) => $get('payment_plan') === 'monthly'),
                ])
                ->columns(3),

            Forms\Components\Section::make('Date')
                ->schema([
                    Forms\Components\DatePicker::make('enrolled_at')
                        ->label('Data ammissione')
                        ->default(now())
                        ->required(),

                    Forms\Components\DatePicker::make('starts_at')
                        ->label('Inizio corso')
                        ->nullable(),

                    Forms\Components\DatePicker::make('ends_at')
                        ->label('Fine corso')
                        ->nullable(),
                ])
                ->columns(3),
        ]);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\EnrollmentResource\RelationManagers\InstallmentsRelationManager::class,
            \App\Filament\Resources\EnrollmentResource\RelationManagers\LessonsRelationManager::class,
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student_id')
                    ->label('Studente')
                    ->formatStateUsing(function ($state, Enrollment $record) {
                        $s = $record->student;
                        if (! $s) return '—';
                        return $s->full_name
                            ?? trim(($s->first_name ?? '') . ' ' . ($s->last_name ?? ''))
                            ?: ($s->name ?? $s->email ?? ('ID ' . $s->id));
                    })
                    ->searchable(),

                Tables\Columns\TextColumn::make('course.subject.name')
                    ->label('Lingua')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('course.name')
                    ->label('Corso')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Stato')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'attivo'    => 'Attivo',
                        'concluso'  => 'Concluso',
                        'annullato' => 'Annullato',
                        'sospeso'   => 'Sospeso',
                        default     => $state ?? '—',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('enrolled_at')
                    ->label('Iscritto il')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->actions([
                Action::make('printContract')
                    ->label('Stampa contratto')
                    ->icon('heroicon-o-printer')
                    ->url(fn ($record) => route('enrollments.contract.print', $record))
                    ->openUrlInNewTab(),

                Tables\Actions\ViewAction::make()->label('Vedi'),
                Tables\Actions\EditAction::make()->label('Modifica'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListEnrollments::route('/'),
            'create' => Pages\CreateEnrollment::route('/create'),
            'edit'   => Pages\EditEnrollment::route('/{record}/edit'),
        ];
    }
}
