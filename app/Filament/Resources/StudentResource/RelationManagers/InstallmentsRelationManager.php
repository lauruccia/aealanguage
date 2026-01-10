<?php

namespace App\Filament\Resources\StudentResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class InstallmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'installments';

    protected static ?string $title = 'Rate';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('due_date', 'asc')
            ->columns([
                Tables\Columns\TextColumn::make('enrollment.course.name')
                    ->label('Corso')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

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
            ->filters([
                SelectFilter::make('status')
                    ->label('Stato')
                    ->options([
                        'da_pagare' => 'Da pagare',
                        'parziale'  => 'Parziale',
                        'pagata'    => 'Pagata',
                        'scaduta'   => 'Scaduta',
                    ]),

                Filter::make('due_today')
                    ->label('Oggi')
                    ->query(fn (Builder $query) => $query->whereDate('due_date', now()->toDateString())),

                Filter::make('due_next_7_days')
                    ->label('7 giorni')
                    ->query(fn (Builder $query) => $query->whereBetween('due_date', [
                        now()->toDateString(),
                        now()->addDays(7)->toDateString(),
                    ])),

                Filter::make('overdue')
                    ->label('Scadute')
                    ->query(fn (Builder $query) => $query->where('status', 'scaduta')),

                Filter::make('to_pay')
                    ->label('Da pagare')
                    ->query(fn (Builder $query) => $query->whereIn('status', ['da_pagare', 'parziale', 'scaduta'])),
            ])
            ->headerActions([]) // solo sistema
            ->actions([
                Action::make('markPaid')
                    ->label('Segna pagata')
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status !== 'pagata')
                    ->action(function ($record) {
                        $record->paid_cents = (int) $record->amount_cents;
                        $record->status = 'pagata';
                        $record->save();
                    }),
            ])
            ->bulkActions([]);
    }
}
