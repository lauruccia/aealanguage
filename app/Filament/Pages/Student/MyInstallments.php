<?php

namespace App\Filament\Pages\Student;

use App\Models\Installment;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class MyInstallments extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Le mie rate';
    protected static ?string $navigationGroup = 'Studente';
    protected static string $view = 'filament.pages.student.my-installments';

    protected static bool $shouldRegisterNavigation = true;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('studente') ?? false;
    }

    /**
     * Stato "effettivo" lato UI:
     * - se è da_pagare e la scadenza è passata => scaduta
     */
    protected function uiStatus(Installment $record): ?string
    {
        $status = $record->status;

        if (! $record->due_date) {
            return $status;
        }

        $due = $record->due_date instanceof Carbon
            ? $record->due_date
            : Carbon::parse($record->due_date);

        if ($status === 'da_pagare' && $due->startOfDay()->lt(now()->startOfDay())) {
            return 'scaduta';
        }

        return $status;
    }

    protected function statusLabel(?string $status): string
    {
        return match ($status) {
            'da_pagare' => 'Da pagare',
            'pagata' => 'Pagata',
            'scaduta' => 'Scaduta',
            'annullata' => 'Annullata',
            'parziale' => 'Parzialmente pagata',
            default => $status ?: '—',
        };
    }

    protected function statusColor(?string $status): string
    {
        return match ($status) {
            'pagata' => 'success',
            'da_pagare' => 'warning',
            'parziale' => 'info',
            'scaduta' => 'danger',
            'annullata' => 'gray',
            default => 'gray',
        };
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
            ->searchable(false)
            ->columns([
                Tables\Columns\TextColumn::make('enrollment.course.name')
                    ->label('Corso')
                    ->wrap()
                    ->placeholder('—'),

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
                    ->badge()
                    ->color(fn ($state, Installment $record) => $this->statusColor($this->uiStatus($record)))
                    ->formatStateUsing(fn ($state, Installment $record) => $this->statusLabel($this->uiStatus($record)))
                    ->sortable(),
            ])
            ->filters([
                /**
                 * ✅ filtro coerente con la UI (scaduta è calcolata)
                 */
                Tables\Filters\SelectFilter::make('ui_status')
                    ->label('Stato rata')
                    ->options([
                        'da_pagare' => 'Da pagare',
                        'scaduta' => 'Scaduta',
                        'pagata' => 'Pagata',
                        'parziale' => 'Parzialmente pagata',
                        'annullata' => 'Annullata',
                    ])
                    ->query(function (Builder $query, array $data) {
                        $value = $data['value'] ?? null;
                        if (! $value) return $query;

                        return match ($value) {
                            // Da pagare (NON scadute)
                            'da_pagare' => $query
                                ->where('status', 'da_pagare')
                                ->where(function (Builder $q) {
                                    $q->whereNull('due_date')
                                      ->orWhereDate('due_date', '>=', now()->toDateString());
                                }),

                            // Scadute (calcolate)
                            'scaduta' => $query
                                ->where('status', 'da_pagare')
                                ->whereDate('due_date', '<', now()->toDateString()),

                            // Stati reali DB
                            'pagata' => $query->where('status', 'pagata'),
                            'parziale' => $query->where('status', 'parziale'),
                            'annullata' => $query->where('status', 'annullata'),

                            default => $query,
                        };
                    })
                    ->placeholder('Tutti'),
            ])
            ->defaultSort('due_date', 'asc');
    }
}
