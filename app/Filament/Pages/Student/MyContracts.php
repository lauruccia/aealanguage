<?php

namespace App\Filament\Pages\Student;

use App\Models\Enrollment;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;

class MyContracts extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'I miei contratti';
    protected static ?string $navigationGroup = 'Studente';
    protected static string $view = 'filament.pages.student.my-contracts';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('student.contracts.view_own') ?? false;
    }

    protected function baseQuery(): Builder
    {
        $studentId = auth()->user()?->student?->id;

        return Enrollment::query()
            ->when(
                $studentId,
                fn (Builder $q) => $q->where('student_id', $studentId),
                fn (Builder $q) => $q->whereRaw('1=0')
            )
            ->with(['course.subject'])
            ->orderBy('created_at', 'desc');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->baseQuery())
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                Tables\Columns\TextColumn::make('course.name')
                    ->label('Corso')
                    ->searchable(),

                Tables\Columns\TextColumn::make('course.subject.name')
                    ->label('Lingua')
                    ->badge()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data iscrizione')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->actions([
                Action::make('print_contract')
                    ->label('Contratto')
                    ->icon('heroicon-o-printer')
                    ->url(fn ($record) => route('enrollments.contract.print', $record->id))
                    ->openUrlInNewTab(true),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
