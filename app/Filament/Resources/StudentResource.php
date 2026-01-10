<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentResource\Pages;
use App\Filament\Resources\StudentResource\RelationManagers\EnrollmentsRelationManager;
use App\Models\Student;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Studenti';
    protected static ?string $modelLabel = 'Studente';
    protected static ?string $pluralModelLabel = 'Studenti';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Dati principali')
                ->schema([
                    Forms\Components\TextInput::make('first_name')
                        ->label('Nome')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('last_name')
                        ->label('Cognome')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->maxLength(255)
                        ->nullable(),

                    Forms\Components\TextInput::make('phone')
                        ->label('Telefono')
                        ->tel()
                        ->maxLength(64)
                        ->nullable(),

                    Forms\Components\DatePicker::make('birth_date')
                        ->label('Data di nascita')
                        ->nullable(),

                    Forms\Components\Toggle::make('is_minor')
                        ->label('Studente minorenne')
                        ->reactive()
                        ->default(false),
                ])
                ->columns(3),

            Forms\Components\Section::make('Nascita')
                ->schema([
                    Forms\Components\TextInput::make('birth_place')
                        ->label('Luogo di nascita')
                        ->maxLength(255)
                        ->nullable(),

                    Forms\Components\TextInput::make('birth_country')
                        ->label('Nazione di nascita')
                        ->maxLength(255)
                        ->nullable(),
                ])
                ->columns(2),

            Forms\Components\Section::make('Dati fiscali')
                ->schema([
                    Forms\Components\TextInput::make('vat_number')
                        ->label('Partita IVA')
                        ->maxLength(32)
                        ->nullable(),

                    Forms\Components\TextInput::make('tax_code')
                        ->label('Codice Fiscale')
                        ->maxLength(32)
                        ->nullable(),
                ])
                ->columns(2),

            Forms\Components\Section::make('Residenza')
                ->schema([
                    Forms\Components\TextInput::make('address_line')
                        ->label('Indirizzo')
                        ->maxLength(255)
                        ->nullable()
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('postal_code')
                        ->label('CAP')
                        ->maxLength(16)
                        ->nullable(),

                    Forms\Components\TextInput::make('city')
                        ->label('Città')
                        ->maxLength(255)
                        ->nullable(),

                    Forms\Components\TextInput::make('province')
                        ->label('Provincia')
                        ->maxLength(32)
                        ->nullable(),

                    Forms\Components\TextInput::make('country')
                        ->label('Nazione (residenza)')
                        ->maxLength(255)
                        ->nullable(),
                ])
                ->columns(4),

            Forms\Components\Section::make('Dati genitore / tutore')
                ->schema([
                    Forms\Components\Radio::make('guardian_role')
                        ->label('Il responsabile è')
                        ->options([
                            'padre' => 'Padre',
                            'madre' => 'Madre',
                            'altro' => 'Altro',
                        ])
                        ->inline()
                        ->required(fn (Forms\Get $get) => (bool) $get('is_minor'))
                        ->visible(fn (Forms\Get $get) => (bool) $get('is_minor')),

                    Forms\Components\TextInput::make('guardian_name')
                        ->label('Nome genitore / tutore')
                        ->maxLength(255)
                        ->required(fn (Forms\Get $get) => (bool) $get('is_minor'))
                        ->visible(fn (Forms\Get $get) => (bool) $get('is_minor')),

                    Forms\Components\TextInput::make('guardian_email')
                        ->label('Email genitore / tutore')
                        ->email()
                        ->maxLength(255)
                        ->visible(fn (Forms\Get $get) => (bool) $get('is_minor')),

                    Forms\Components\TextInput::make('guardian_phone')
                        ->label('Telefono genitore / tutore')
                        ->tel()
                        ->maxLength(64)
                        ->visible(fn (Forms\Get $get) => (bool) $get('is_minor')),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('last_name')
                ->label('Cognome')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('first_name')
                ->label('Nome')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('email')
                ->label('Email')
                ->searchable(),

            Tables\Columns\TextColumn::make('phone')
                ->label('Telefono')
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),

            Tables\Columns\IconColumn::make('is_minor')
                ->label('Minorenne')
                ->boolean()
                ->toggleable(isToggledHiddenByDefault: true),

            Tables\Columns\TextColumn::make('created_at')
                ->label('Creato')
                ->dateTime('d/m/Y H:i')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
        ]);
    }

    public static function getRelations(): array
    {
        return [

        \App\Filament\Resources\StudentResource\RelationManagers\InstallmentsRelationManager::class,

        ];


    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
        ];
    }
}
