<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClosureDayResource\Pages;
use App\Models\ClosureDay;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ClosureDayResource extends Resource
{
    protected static ?string $model = ClosureDay::class;

    protected static ?string $navigationIcon = 'heroicon-o-no-symbol';
    protected static ?string $navigationLabel = 'Giorni di chiusura';
    protected static ?string $navigationGroup = 'Didattica';
    protected static ?int $navigationSort = 4;

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
        return $form
            ->schema([
                Forms\Components\DatePicker::make('date')
                    ->label('Data')
                    ->required()
                    ->unique(ignoreRecord: true),

                Forms\Components\TextInput::make('reason')
                    ->label('Motivo')
                    ->maxLength(255)
                    ->nullable()
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('reason')
                    ->label('Motivo')
                    ->wrap(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn (Model $record) => static::canEdit($record)),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn (Model $record) => static::canDelete($record)),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListClosureDays::route('/'),
            'create' => Pages\CreateClosureDay::route('/create'),
            'edit'   => Pages\EditClosureDay::route('/{record}/edit'),
        ];
    }
}
