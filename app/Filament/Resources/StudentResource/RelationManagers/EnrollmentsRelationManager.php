<?php

namespace App\Filament\Resources\StudentResource\RelationManagers;

use App\Models\Course;
use App\Services\InstallmentGenerator;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class EnrollmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'enrollments';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('course_id')
                ->label('Corso')
                ->required()
                ->searchable()
                ->preload()
                ->reactive()
                ->options(fn () => Course::query()->orderBy('name')->pluck('name', 'id'))
                ->afterStateUpdated(function ($state, callable $set) {
                    if (!$state) return;
                    $course = Course::find($state);
                    if (!$course) return;

                    $set('course_price_eur', $course->prezzo);
                    $set('registration_fee_eur', $course->tassa_iscrizione);
                }),

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

            Forms\Components\Radio::make('payment_plan')
                ->label('Pagamento')
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

            Forms\Components\DatePicker::make('enrolled_at')
                ->label('Data ammissione')
                ->default(now())
                ->required(),

            Forms\Components\DatePicker::make('starts_at')
                ->label('Data inizio')
                ->nullable(),

            Forms\Components\DatePicker::make('ends_at')
                ->label('Data fine')
                ->nullable(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('course.name')
                    ->label('Corso')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_plan')
                    ->label('Pagamento')
                    ->formatStateUsing(fn ($s) => $s === 'single' ? 'Unico' : 'Rate')
                    ->badge(),

                Tables\Columns\TextColumn::make('installments_count')
                    ->label('Rate')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Stato')
                    ->badge(),

                Tables\Columns\TextColumn::make('enrolled_at')
                    ->label('Ammissione')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->after(function ($record) {
                        app(InstallmentGenerator::class)->generateForEnrollment($record);
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->after(function ($record) {
                        app(InstallmentGenerator::class)->generateForEnrollment($record);
                    }),
            ]);
    }
}
