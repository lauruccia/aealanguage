<?php

namespace App\Filament\Resources\EnrollmentResource\RelationManagers;

use App\Models\Lesson;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LessonsRelationManager extends RelationManager
{
    protected static string $relationship = 'lessons';

    protected static ?string $title = 'Lezioni';

    protected $listeners = [
        'refreshLessons' => '$refresh',
    ];

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('lesson_number')
                ->label('N°')
                ->numeric()
                ->required()
                ->disabled(),

            Forms\Components\DateTimePicker::make('starts_at')
                ->label('Inizio')
                ->seconds(false)
                ->required(),

            Forms\Components\TextInput::make('duration_minutes')
                ->label('Durata (min)')
                ->numeric()
                ->required()
                ->minValue(15)
                ->maxValue(240)
                ->default(60),

            Forms\Components\Select::make('teacher_id')
                ->label('Docente (per questa lezione)')
                ->searchable()
                ->preload()
                ->nullable()
                ->relationship('teacher', 'last_name')
                ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name),

            Forms\Components\Select::make('status')
                ->label('Stato')
                ->required()
                ->options([
                    Lesson::STATUS_SCHEDULED         => 'Programm. (scheduled)',
                    Lesson::STATUS_COMPLETED         => 'Svolta (completed)',
                    Lesson::STATUS_CANCELLED_RECOVER => 'Annullata - da recuperare',
                    Lesson::STATUS_CANCELLED_COUNTED => 'Annullata - conteggiata',
                ])
                ->default(Lesson::STATUS_SCHEDULED),

            Forms\Components\TextInput::make('cancel_reason')
                ->label('Motivo annullamento')
                ->maxLength(255)
                ->nullable(),

            Forms\Components\Textarea::make('notes')
                ->label('Note')
                ->rows(3)
                ->nullable(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->orderBy('lesson_number'))
            ->columns([
                Tables\Columns\TextColumn::make('lesson_number')->label('N°')->sortable(),

                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Inizio')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('teacher.full_name')
                    ->label('Docente')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Stato')
                    ->badge(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Modifica'),
                Tables\Actions\DeleteAction::make()->label('Elimina'),
            ]);
    }
}
