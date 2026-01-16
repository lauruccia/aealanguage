<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';
    protected static ?string $navigationLabel = 'Personale';
    protected static ?string $modelLabel = 'Personale';
    protected static ?string $pluralModelLabel = 'Personale';

    protected static ?string $navigationGroup = 'Amministrazione';
    protected static ?int $navigationSort = 1;

    private static function canManageStaff(): bool
    {
        $u = auth()->user();
        if (! $u) return false;

        // ✅ solo superadmin e amministrazione possono gestire il personale
        return $u->hasAnyRole(['superadmin', 'amministrazione']);
    }

    public static function canViewAny(): bool
    {
        return static::canManageStaff();
    }

    public static function canCreate(): bool
    {
        return static::canManageStaff();
    }

    public static function canEdit(Model $record): bool
    {
        return static::canManageStaff();
    }

    public static function canDelete(Model $record): bool
    {
        // ✅ cancellazione: solo superadmin
        return auth()->user()?->hasRole('superadmin') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Dati personale')
                ->schema([
                    Forms\Components\TextInput::make('first_name')
                        ->label('Nome')
                        ->maxLength(255)
                        ->required(),

                    Forms\Components\TextInput::make('last_name')
                        ->label('Cognome')
                        ->maxLength(255)
                        ->required(),

                    Forms\Components\TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true),

                    Forms\Components\TextInput::make('phone')
                        ->label('Telefono')
                        ->tel()
                        ->maxLength(50)
                        ->nullable(),

                    Forms\Components\TextInput::make('address')
                        ->label('Indirizzo')
                        ->maxLength(255)
                        ->nullable(),

                    Forms\Components\Select::make('staff_role')
    ->label('Ruolo')
    ->required()
    ->options([
        'amministrazione' => 'Amministrazione',
        'segreteria'      => 'Segreteria',
    ])
    ->helperText('Qui si gestisce solo lo staff. Docenti e studenti si creano dalle rispettive sezioni.')
    ->dehydrated(false)
    ->afterStateHydrated(function ($state, callable $set, $record) {
        if (! $record) return;
        $role = $record->roles->pluck('name')->first();
        if (in_array($role, ['amministrazione', 'segreteria'], true)) {
            $set('staff_role', $role);
        }
    }),
                    Forms\Components\TextInput::make('password')
                        ->label('Password (lascia vuoto per default)')
                        ->password()
                        ->revealable()
                        ->dehydrated(fn ($state) => filled($state))
                        ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                        ->helperText('Se vuota, verrà impostata Password123!')
                        ->nullable(),

                    Forms\Components\Toggle::make('must_change_password')
                        ->label('Obbliga cambio password al prossimo accesso')
                        ->default(true),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('first_name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('last_name')
                    ->label('Cognome')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Ruolo')
                    ->badge()
                    ->separator(', ')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Modifica'),

                Tables\Actions\Action::make('resetPasswordDefault')
                    ->label('Reset password')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (User $record) {
                        $record->password = Hash::make('Password123!');
                        $record->must_change_password = true;
                        $record->save();

                        \Filament\Notifications\Notification::make()
                            ->title('Password resettata')
                            ->body('Impostata a Password123! e obbligato cambio al prossimo accesso.')
                            ->success()
                            ->send();
                    }),
            ])
            ->modifyQueryUsing(function ($query) {
                // ✅ mostra SOLO staff (amministrazione/segreteria). Esclude docenti/studenti.
                return $query->whereHas('roles', function ($q) {
                    $q->whereIn('name', ['amministrazione', 'segreteria']);
                });
            });
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
