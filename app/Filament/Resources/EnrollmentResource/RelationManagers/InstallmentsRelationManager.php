<?php

namespace App\Filament\Resources\EnrollmentResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class InstallmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'installments';

    protected static ?string $title = 'Rate';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Rata')
                ->schema([
                    Forms\Components\TextInput::make('number')
                        ->label('Numero')
                        ->disabled()
                        ->dehydrated(false),

                    Forms\Components\DatePicker::make('due_date')
                        ->label('Scadenza')
                        ->required(),

                    Forms\Components\TextInput::make('amount_cents')
                        ->label('Importo (€)')
                        ->numeric()
                        ->required()
                        ->formatStateUsing(fn ($state) => is_null($state) ? null : ((int) $state) / 100)
                        ->dehydrateStateUsing(fn ($state) => (int) round(((float) str_replace(',', '.', (string) $state)) * 100)),

                    Forms\Components\TextInput::make('paid_cents')
                        ->label('Pagato (€)')
                        ->numeric()
                        ->default(0)
                        ->formatStateUsing(fn ($state) => is_null($state) ? null : ((int) $state) / 100)
                        ->dehydrateStateUsing(fn ($state) => (int) round(((float) str_replace(',', '.', (string) $state)) * 100)),

                    Forms\Components\Select::make('status')
                        ->label('Stato')
                        ->required()
                        ->options([
                            'da_pagare' => 'Da pagare',
                            'parziale'  => 'Parziale',
                            'pagata'    => 'Pagata',
                            'scaduta'   => 'Scaduta',
                        ])
                        ->default('da_pagare'),
                ])
                ->columns(3),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('number', 'asc')
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->label('N°')
                    ->badge()
                    ->formatStateUsing(fn ($state) => ((int) $state) === 0 ? 'TASSA' : (string) $state),

                Tables\Columns\TextColumn::make('due_date')
                    ->label('Scadenza')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount_cents')
                    ->label('Importo')
                    ->alignRight()
                    ->formatStateUsing(fn ($state) => number_format(((int) $state) / 100, 2, ',', '.') . ' €')
                    ->sortable(),

                Tables\Columns\TextColumn::make('paid_cents')
                    ->label('Pagato')
                    ->alignRight()
                    ->formatStateUsing(fn ($state) => number_format(((int) $state) / 100, 2, ',', '.') . ' €')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Stato')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pagata'   => 'success',
                        'parziale' => 'warning',
                        'scaduta'  => 'danger',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pagata'   => 'Pagata',
                        'parziale' => 'Parziale',
                        'scaduta'  => 'Scaduta',
                        default    => 'Da pagare',
                    })
                    ->sortable(),
            ])
            ->headerActions([
                // di default: niente Create, perché le rate le genera il sistema dall'iscrizione
                // Se vuoi poter aggiungere rate manualmente, dimmelo e abilitiamo CreateAction
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }
}
