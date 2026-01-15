<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InstallmentResource\Pages;
use App\Models\Installment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class InstallmentResource extends Resource
{
    protected static ?string $model = Installment::class;

    protected static ?string $modelLabel = 'Rata';
    protected static ?string $pluralLabel = 'Rate';

    protected static ?string $navigationGroup = 'Studenti';
    protected static ?string $navigationLabel = 'Scadenze e pagamenti';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    /**
     * Staff con controllo completo.
     */
    protected static function staffCanManage(): bool
    {
        $u = auth()->user();
        if (! $u) {
            return false;
        }

        return $u->hasAnyRole(['superadmin', 'amministrazione', 'segreteria']);
    }

    public static function canViewAny(): bool
    {
        return static::staffCanManage();
    }

    public static function canCreate(): bool
    {
        return static::staffCanManage();
    }

    public static function canEdit(Model $record): bool
    {
        return static::staffCanManage();
    }

    public static function canDelete(Model $record): bool
    {
        return static::staffCanManage();
    }

    public static function canDeleteAny(): bool
    {
        return static::staffCanManage();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Rata')
                ->schema([
                    Forms\Components\TextInput::make('enrollment_id')
                        ->label('ID Iscrizione')
                        ->disabled()
                        ->dehydrated(false),

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

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('due_date', 'asc')
            ->columns([
                Tables\Columns\TextColumn::make('enrollment.student.last_name')
                    ->label('Studente')
                    ->formatStateUsing(function ($state, $record) {
                        $s = $record->enrollment?->student;
                        return $s ? ($s->last_name . ' ' . $s->first_name) : '-';
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('enrollment.student', function (Builder $q) use ($search) {
                            $q->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                    }),

                Tables\Columns\TextColumn::make('enrollment.course.name')
                    ->label('Corso')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),

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
                    ->label('In scadenza oggi')
                    ->query(fn (Builder $query) => $query->whereDate('due_date', now()->toDateString())),

                Filter::make('due_next_7_days')
                    ->label('In scadenza 7 giorni')
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
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn (Model $record) => static::canEdit($record)),

                Action::make('markPaid')
                    ->label('Segna pagata')
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => static::staffCanManage() && $record->status !== 'pagata')
                    ->action(function ($record) {
                        $record->paid_cents = (int) $record->amount_cents;
                        $record->status = 'pagata';
                        $record->save();
                    }),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListInstallments::route('/'),
            'create' => Pages\CreateInstallment::route('/create'),
            'edit'   => Pages\EditInstallment::route('/{record}/edit'),
        ];
    }
}
