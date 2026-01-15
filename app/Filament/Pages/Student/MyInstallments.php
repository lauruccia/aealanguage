<?php

namespace App\Filament\Pages\Student;

use App\Models\Installment;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Database\Eloquent\Builder;

class MyInstallments extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Le mie rate';
    protected static ?string $navigationGroup = 'Studente';
    protected static string $view = 'filament.pages.student.my-installments';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('studente') ?? false;
    }

    protected function baseQuery(): Builder
    {
        $studentId = auth()->user()?->student?->id;

        return Installment::query()
            ->when(
                $studentId,
                fn (Builder $q) => $q->whereHas('enrollment', fn (Builder $e) => $e->where('student_id', $studentId)),
                fn (Builder $q) => $q->whereRaw('1=0')
            )
            ->with(['enrollment.course'])
            ->orderBy('due_date', 'asc');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->baseQuery())
            ->columns([
                Tables\Columns\TextColumn::make('enrollment.course.name')
                    ->label('Corso')
                    ->searchable(),

                Tables\Columns\TextColumn::make('due_date')
                    ->label('Scadenza')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Importo')
                    ->money('EUR')
                    ->alignRight()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Stato')
                    ->badge(),
            ])
            ->defaultSort('due_date', 'asc');
    }
}
