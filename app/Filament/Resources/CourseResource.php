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
            Forms\Components\Section::make('Dati corso')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nome corso')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\Select::make('subject_id')
                        ->label('Lingua')
                        ->relationship(
                            name: 'subject',
                            titleAttribute: 'name',
                            modifyQueryUsing: fn ($query) => $query->orderBy('name')
                        )
                        ->searchable()
                        ->preload()
                        ->required()
                        ->helperText('Lingua del corso (coerente con le lingue docenti).'),

                    Forms\Components\TextInput::make('lessons_count')
                        ->label('Numero lezioni')
                        ->numeric()
                        ->minValue(0)
                        ->required(),

                    Forms\Components\Textarea::make('description')
                        ->label('Descrizione')
                        ->rows(4)
                        ->columnSpanFull()
                        ->nullable(),
                ])
                ->columns(3),

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
                ])
                ->columns(2),
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
                Tables\Actions\EditAction::make()
                    ->visible(fn (Model $record) => static::canEdit($record)),
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
