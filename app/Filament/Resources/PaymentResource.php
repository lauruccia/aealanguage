<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PaymentResource extends Resource
{
    // Non mostrare nel menu: lo gestiamo indirettamente (rate/pagamenti)
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
        // visibile allo staff (anche se non in menu)
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
            Forms\Components\TextInput::make('enrollment_id')
                ->required()
                ->numeric(),

            Forms\Components\TextInput::make('installment_id')
                ->numeric()
                ->nullable(),

            Forms\Components\DatePicker::make('paid_at')
                ->required(),

            Forms\Components\TextInput::make('amount_cents')
                ->required()
                ->numeric(),

            Forms\Components\TextInput::make('kind')
                ->required(),

            Forms\Components\TextInput::make('method')
                ->maxLength(255)
                ->nullable(),

            Forms\Components\TextInput::make('reference')
                ->maxLength(255)
                ->nullable(),

            Forms\Components\Textarea::make('notes')
                ->columnSpanFull()
                ->nullable(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('enrollment_id')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('installment_id')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('paid_at')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount_cents')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('kind'),

                Tables\Columns\TextColumn::make('method')
                    ->searchable(),

                Tables\Columns\TextColumn::make('reference')
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn (Model $record) => static::canEdit($record)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => static::canDeleteAny()),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit'   => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
