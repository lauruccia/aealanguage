<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseResource\Pages;
use App\Models\Course;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'Corsi';
    protected static ?string $modelLabel = 'Corso';
    protected static ?string $pluralLabel = 'Corsi';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Dati corso')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nome corso')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\Textarea::make('description')
                        ->label('Descrizione')
                        ->rows(4)
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('lessons_count')
                        ->label('Numero lezioni')
                        ->numeric()
                        ->minValue(0)
                        ->required(),
                ])
                ->columns(2),

            Forms\Components\Section::make('Prezzi')
                ->schema([
                    Forms\Components\TextInput::make('prezzo')
                        ->label('Prezzo corso (€)')
                        ->numeric()
                        ->minValue(0)
                        ->required(),

                    Forms\Components\TextInput::make('tassa_iscrizione')
                        ->label('Tassa iscrizione (€)')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->required(),

                    // Campo "price" presente in tabella: lo mostro disabilitato per non creare confusione
                    Forms\Components\TextInput::make('price')
                        ->label('price (campo legacy)')
                        ->disabled()
                        ->dehydrated(false)
                        ->helperText('Campo presente nel DB, ma non usato. Valuteremo se rimuoverlo.'),
                ])
                ->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Corso')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('lessons_count')
                    ->label('Lezioni')
                    ->sortable(),

                Tables\Columns\TextColumn::make('prezzo')
                    ->label('Prezzo (€)')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tassa_iscrizione')
                    ->label('Iscrizione (€)')
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Aggiornato')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourse::route('/create'),
            'edit' => Pages\EditCourse::route('/{record}/edit'),
        ];
    }
}
