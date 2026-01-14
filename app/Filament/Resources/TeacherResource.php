<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeacherResource\Pages;
use App\Models\Teacher;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class TeacherResource extends Resource
{
    protected static ?string $model = Teacher::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'Docenti';
    protected static ?string $modelLabel = 'Docente';
    protected static ?string $pluralModelLabel = 'Docenti';

    protected static ?string $navigationGroup = 'Risorse Umane';
    protected static ?int $navigationSort = 1;

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
        return auth()->user()?->can('teachers.manage') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->can('teachers.manage') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->can('teachers.manage') ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->can('teachers.manage') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Dati docente')
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
                        ->maxLength(255)
                        ->nullable(),

                    Forms\Components\DatePicker::make('birth_date')
                        ->label('Data di nascita')
                        ->nullable(),

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
                        ->maxLength(50)
                        ->nullable(),

                    Forms\Components\TextInput::make('tax_code')
                        ->label('Codice Fiscale')
                        ->maxLength(50)
                        ->nullable(),
                ])
                ->columns(2),

            Forms\Components\Section::make('Residenza')
                ->schema([
                    Forms\Components\TextInput::make('address')
                        ->label('Indirizzo')
                        ->maxLength(255)
                        ->nullable()
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('postal_code')
                        ->label('CAP')
                        ->maxLength(10)
                        ->nullable(),

                    Forms\Components\TextInput::make('city')
                        ->label('Città')
                        ->maxLength(255)
                        ->nullable(),

                    Forms\Components\TextInput::make('province')
                        ->label('Provincia')
                        ->maxLength(10)
                        ->nullable(),

                    Forms\Components\TextInput::make('residence_country')
                        ->label('Nazione (residenza)')
                        ->maxLength(255)
                        ->nullable(),
                ])
                ->columns(2),

            Forms\Components\Section::make('Contratto e materie')
                ->schema([
                    Forms\Components\TextInput::make('contract_type')
                        ->label('Tipo di contratto')
                        ->maxLength(255)
                        ->nullable(),

                    Forms\Components\TextInput::make('gross_hourly_rate')
                        ->label('Tariffa oraria lorda (€)')
                        ->numeric()
                        ->minValue(0)
                        ->step(0.01)
                        ->nullable(),

                    Forms\Components\Select::make('subjects')
                        ->label('Materie insegnate')
                        ->relationship('subjects', 'name')
                        ->multiple()
                        ->preload()
                        ->searchable()
                        ->required()
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Forms\Components\Section::make('Fatturazione')
                ->schema([
                    Forms\Components\Select::make('billing_mode')
                        ->label('Modalità di fatturazione')
                        ->options([
                            'WITHHOLDING_20' => "Ritenuta d'acconto del 20%",
                            'NO_VAT' => 'Fatturazione senza IVA',
                            'WITH_VAT' => 'Fatturazione con IVA',
                            'NONE' => 'Nessuna dei precedenti',
                        ])
                        ->required()
                        ->live(),

                    Forms\Components\TextInput::make('vat_percentage')
                        ->label('IVA (%)')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100)
                        ->step(0.01)
                        ->visible(fn (Forms\Get $get) => $get('billing_mode') === 'WITH_VAT')
                        ->required(fn (Forms\Get $get) => $get('billing_mode') === 'WITH_VAT')
                        ->nullable(),
                ])
                ->columns(2),

            Forms\Components\Section::make('Dati amministrativi')
                ->schema([
                    Forms\Components\TextInput::make('pec')
                        ->label('Indirizzo PEC')
                        ->email()
                        ->maxLength(255)
                        ->nullable(),

                    Forms\Components\TextInput::make('iban')
                        ->label('IBAN')
                        ->maxLength(34)
                        ->nullable(),
                ])
                ->columns(2),

            Forms\Components\Section::make('Documenti')
                ->schema([
                    Forms\Components\FileUpload::make('cv_path')
                        ->label('CV Insegnante')
                        ->disk('public')
                        ->directory('teachers/cv')
                        ->preserveFilenames()
                        ->acceptedFileTypes([
                            'application/pdf',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        ])
                        ->maxSize(10240)
                        ->nullable(),

                    Forms\Components\FileUpload::make('id_document_path')
                        ->label('Documento di identità')
                        ->disk('public')
                        ->directory('teachers/id-documents')
                        ->preserveFilenames()
                        ->acceptedFileTypes([
                            'application/pdf',
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                        ])
                        ->maxSize(10240)
                        ->nullable(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('last_name')
            ->columns([
                Tables\Columns\TextColumn::make('last_name')
                    ->label('Cognome')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('first_name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('subjects.name')
                    ->label('Materie')
                    ->badge()
                    ->separator(', ')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('gross_hourly_rate')
                    ->label('€/h lordo')
                    ->money('EUR')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('billing_mode')
                    ->label('Fatturazione')
                    ->formatStateUsing(function (?string $state): string {
                        return match ($state) {
                            'WITHHOLDING_20' => "Ritenuta 20%",
                            'NO_VAT' => 'Senza IVA',
                            'WITH_VAT' => 'Con IVA',
                            'NONE' => 'Nessuna',
                            default => $state ?? '-',
                        };
                    })
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('vat_percentage')
                    ->label('IVA %')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('tax_code')
                    ->label('Cod. Fiscale')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('vat_number')
                    ->label('Partita IVA')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('city')
                    ->label('Città')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('province')
                    ->label('Prov.')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Telefono')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creato')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('subject')
                    ->label('Materia')
                    ->relationship('subjects', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('billing_mode')
                    ->label('Fatturazione')
                    ->options([
                        'WITHHOLDING_20' => "Ritenuta d'acconto 20%",
                        'NO_VAT' => 'Senza IVA',
                        'WITH_VAT' => 'Con IVA',
                        'NONE' => 'Nessuna',
                    ]),

                Tables\Filters\SelectFilter::make('province')
                    ->label('Provincia')
                    ->options(function () {
                        return Teacher::query()
                            ->whereNotNull('province')
                            ->where('province', '!=', '')
                            ->distinct()
                            ->orderBy('province')
                            ->pluck('province', 'province')
                            ->toArray();
                    })
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\Action::make('download_cv')
                    ->label('CV')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(fn (Teacher $record) => $record->cv_path ? url('storage/' . $record->cv_path) : null)
                    ->openUrlInNewTab()
                    ->visible(fn (Teacher $record) => ! empty($record->cv_path)),

                Tables\Actions\Action::make('download_id')
                    ->label('Doc ID')
                    ->icon('heroicon-o-identification')
                    ->url(fn (Teacher $record) => $record->id_document_path ? url('storage/' . $record->id_document_path) : null)
                    ->openUrlInNewTab()
                    ->visible(fn (Teacher $record) => ! empty($record->id_document_path)),

                Tables\Actions\EditAction::make()
                    ->visible(fn () => static::canCreate()),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => static::canDeleteAny()),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->visible(fn () => static::canDeleteAny()),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTeachers::route('/'),
            'create' => Pages\CreateTeacher::route('/create'),
            'edit'   => Pages\EditTeacher::route('/{record}/edit'),
        ];
    }
}
