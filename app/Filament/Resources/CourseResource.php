<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseResource\Pages;
use App\Models\Course;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'Corsi';
    protected static ?string $modelLabel = 'Corso';
    protected static ?string $pluralLabel = 'Corsi';

    protected static ?string $navigationGroup = 'Didattica';
    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        $u = auth()->user();
        if (! $u) {
            return false;
        }

        // Visibile allo staff
        return $u->hasAnyRole(['superadmin', 'amministrazione', 'segreteria']);
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('courses.manage') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->can('courses.manage') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->can('courses.manage') ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->can('courses.manage') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Dati corso')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nome corso')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\Select::make('subject_id')
                        ->label('Lingua')
                        ->relationship('subject', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->helperText('Lingua del corso (coerente con le lingue docenti).'),

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

                Tables\Columns\TextColumn::make('subject.name')
                    ->label('Lingua')
                    ->sortable()
                    ->toggleable(),

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
                Tables\Actions\EditAction::make()
                    ->visible(fn () => static::canCreate()),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->visible(fn () => static::canDeleteAny()),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourse::route('/create'),
            'edit'   => Pages\EditCourse::route('/{record}/edit'),
        ];
    }
}
