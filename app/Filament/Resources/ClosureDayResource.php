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

    public static function canViewAny(): bool
    {
        $u = auth()->user();
        if (! $u) {
            return false;
        }

        return $u->hasAnyRole(['superadmin', 'amministrazione', 'segreteria']);
    }

    public static function canCreate(): bool
    {
        // superadmin passa comunque se hai Gate::before
        return auth()->user()?->can('closure_days.manage') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->can('closure_days.manage') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->can('closure_days.manage') ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->can('closure_days.manage') ?? false;
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
                Tables\Actions\EditAction::make()->visible(fn () => static::canCreate()),
                Tables\Actions\DeleteAction::make()->visible(fn () => static::canDeleteAny()),
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
