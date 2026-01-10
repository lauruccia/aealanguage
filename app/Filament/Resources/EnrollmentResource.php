<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnrollmentResource\Pages;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EnrollmentResource extends Resource
{
    protected static ?string $model = Enrollment::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';
    protected static ?string $navigationLabel = 'Iscrizioni';

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
                    ->relationship('student', 'id') // se vuoi: cambia col campo giusto
                    ->getOptionLabelFromRecordUsing(function ($record) {
                        $label = $record->full_name
                            ?? trim(($record->first_name ?? '') . ' ' . ($record->last_name ?? ''));
                        return $label !== '' ? $label : ($record->email ?? ('ID '.$record->id));
                    }),

                Forms\Components\Select::make('course_id')
                    ->label('Corso')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->options(fn () => \App\Models\Course::query()->orderBy('name')->pluck('name', 'id'))
                    ->afterStateUpdated(function ($state, callable $set) {
                        if (!$state) return;
                        $course = \App\Models\Course::find($state);
                        if (!$course) return;

                        // autocompila prezzi (modificabili)
                        $set('course_price_eur', $course->prezzo);
                        $set('registration_fee_eur', $course->tassa_iscrizione);
                    }),

                Forms\Components\Select::make('status')
                    ->label('Stato')
                    ->required()
                    ->default('attiva')
                    ->options([
                        'attiva' => 'Attiva',
                        'conclusa' => 'Conclusa',
                        'annullata' => 'Annullata',
                        'sospesa' => 'Sospesa',
                    ]),
            ])->columns(3),

        Forms\Components\Section::make('Prezzi concordati')
            ->schema([
                Forms\Components\TextInput::make('course_price_eur')
                    ->label('Prezzo corso (€)')
                    ->numeric()
                    ->required()
                    ->minValue(0),

                Forms\Components\TextInput::make('registration_fee_eur')
                    ->label('Tassa iscrizione (€)')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->default(0),
            ])->columns(2),

        Forms\Components\Section::make('Piano pagamento')
            ->schema([
                Forms\Components\Radio::make('payment_plan')
                    ->label('Modalità pagamento')
                    ->options([
                        'single' => 'Unico importo',
                        'monthly' => 'Rate mensili',
                    ])
                    ->default('monthly')
                    ->required()
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
            ])->columns(3),

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
            ])->columns(3),
    ]);
}

public static function getRelations(): array
{
    return [
        \App\Filament\Resources\EnrollmentResource\RelationManagers\InstallmentsRelationManager::class,
    ];
}

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('student_id')
                ->label('Studente')
                ->formatStateUsing(function ($state, Enrollment $record) {
                    $s = $record->student;
                    if (!$s) return '—';
                    return $s->full_name
                        ?? trim(($s->first_name ?? '') . ' ' . ($s->last_name ?? ''))
                        ?: ($s->name ?? $s->email ?? ('ID ' . $s->id));
                })
                ->searchable(),

            Tables\Columns\TextColumn::make('course.name')
                ->label('Corso')
                ->sortable()
                ->searchable(),

            Tables\Columns\TextColumn::make('status')
                ->label('Stato')
                ->sortable(),

            Tables\Columns\TextColumn::make('enrolled_at')
                ->label('Iscritto il')
                ->date('d/m/Y')
                ->sortable(),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEnrollments::route('/'),
            'create' => Pages\CreateEnrollment::route('/create'),
            'edit' => Pages\EditEnrollment::route('/{record}/edit'),
        ];
    }
}
