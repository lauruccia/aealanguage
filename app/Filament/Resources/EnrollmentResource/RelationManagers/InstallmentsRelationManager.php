<?php

namespace App\Filament\Resources\EnrollmentResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class InstallmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'installments';

    protected static ?string $title = 'Rate';

    // Rinfresca la tabella al dispatch('refreshInstallments')
    protected $listeners = [
        'refreshInstallments' => '$refresh',
    ];

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('number')
                ->label('N°')
                ->numeric()
                ->required(),

            Forms\Components\DatePicker::make('due_date')
                ->label('Scadenza')
                ->required(),

Forms\Components\TextInput::make('amount_cents')
    ->label('Importo (€)')
    ->numeric()
    ->required()
    ->suffix('€')
    ->formatStateUsing(fn ($state) =>
        $state !== null ? number_format($state / 100, 2, '.', '') : null
    )
    ->dehydrateStateUsing(fn ($state) =>
        $state !== null ? (int) round(((float) str_replace(',', '.', $state)) * 100) : null
    ),

         Forms\Components\TextInput::make('paid_cents')
    ->label('Pagato (€)')
    ->numeric()
    ->suffix('€')
    ->formatStateUsing(fn ($state) =>
        $state !== null ? number_format($state / 100, 2, '.', '') : null
    )
    ->dehydrateStateUsing(fn ($state) =>
        $state !== null ? (int) round(((float) str_replace(',', '.', $state)) * 100) : null
    ),


            Forms\Components\Select::make('status')
                ->label('Stato')
                ->required()
                ->options([
                    'da_pagare' => 'Da pagare',
                    'parziale'  => 'Parziale',
                    'pagata'    => 'Pagata',
                ]),
        ])->columns(5);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('number')
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->label('N°')
                    ->sortable(),

                Tables\Columns\TextColumn::make('due_date')
                    ->label('Scadenza')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount_cents')
                    ->label('Importo')
                    ->formatStateUsing(fn ($state) => number_format(((int) $state) / 100, 2, ',', '.') . ' €')
                    ->sortable(),

                Tables\Columns\TextColumn::make('paid_cents')
                    ->label('Pagato')
                    ->formatStateUsing(fn ($state) => number_format(((int) $state) / 100, 2, ',', '.') . ' €')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Stato')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pagata'    => 'Pagata',
                        'parziale'  => 'Parziale',
                        'da_pagare' => 'Da pagare',
                        default     => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'pagata'    => 'success', // verde
                        'parziale'  => 'info',    // blu
                        'da_pagare' => 'warning', // giallo
                        default     => 'gray',
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
