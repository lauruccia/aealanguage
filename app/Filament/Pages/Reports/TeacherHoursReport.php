<?php

namespace App\Filament\Pages\Reports;

use App\Models\Teacher;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TeacherHoursReport extends Page implements Tables\Contracts\HasTable, Forms\Contracts\HasForms
{
    use Tables\Concerns\InteractsWithTable;
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'Report';
    protected static ?string $navigationLabel = 'Report lezioni docenti';
    protected static string $view = 'filament.pages.reports.teacher-hours-report';

    protected static bool $shouldRegisterNavigation = true;

    public static function canAccess(): bool
    {
        $u = auth()->user();
        if (! $u) {
            return false;
        }

        // SOLO: superadmin, amministrazione, segreteria
        return $u->hasAnyRole(['superadmin', 'amministrazione', 'segreteria']);
    }

    /** Stato del form (Filament) */
    public array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'from' => now()->startOfMonth(),
            'to'   => now()->endOfMonth(),
            'teacher_id' => null,
        ]);
    }

    public function getTitle(): string
    {
        return 'Report lezioni docenti';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Filtri')
                    ->columns(3)
                    ->schema([
                        Forms\Components\DatePicker::make('from')
                            ->label('Da')
                            ->native(false)
                            ->closeOnDateSelection()
                            ->live(),

                        Forms\Components\DatePicker::make('to')
                            ->label('A')
                            ->native(false)
                            ->closeOnDateSelection()
                            ->live(),

                        Forms\Components\Select::make('teacher_id')
                            ->label('Docente')
                            ->searchable()
                            ->preload()
                            ->options(
                                Teacher::query()
                                    ->orderBy('last_name')
                                    ->orderBy('first_name')
                                    ->get()
                                    ->mapWithKeys(fn ($t) => [
                                        $t->id => trim(($t->last_name ?? '') . ' ' . ($t->first_name ?? '')),
                                    ])
                                    ->toArray()
                            )
                            ->placeholder('Tutti')
                            ->live(),
                    ]),
            ])
            ->statePath('data');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => $this->baseQuery())
            ->columns([
                Tables\Columns\TextColumn::make('docente')
                    ->label('Docente')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('worked_in_period')
                    ->label('Lavorate nel periodo')
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('scheduled_in_period')
                    ->label('Programmate nel periodo')
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_in_period')
                    ->label('Totale periodo')
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('scheduled_future_from_today')
                    ->label('Future (da oggi)')
                    ->alignCenter()
                    ->sortable(),
            ])
            ->defaultSort('docente')
            ->paginated([25, 50, 100]);
    }

    protected function baseQuery(): Builder
    {
        $from = $this->data['from'] ?? null;
        $to   = $this->data['to'] ?? null;
        $teacherId = $this->data['teacher_id'] ?? null;

        $fromDt = $from ? Carbon::parse($from)->startOfDay() : null;
        $toDt   = $to   ? Carbon::parse($to)->endOfDay() : null;

        $q = Teacher::query()
            ->select([
                'teachers.id',
                DB::raw("TRIM(CONCAT(COALESCE(teachers.last_name,''),' ',COALESCE(teachers.first_name,''))) AS docente"),

                DB::raw("SUM(CASE WHEN lessons.status IN ('completed','cancelled_counted') THEN 1 ELSE 0 END) AS worked_in_period"),
                DB::raw("SUM(CASE WHEN lessons.status = 'scheduled' THEN 1 ELSE 0 END) AS scheduled_in_period"),
                DB::raw("SUM(CASE WHEN lessons.status IN ('completed','cancelled_counted','scheduled') THEN 1 ELSE 0 END) AS total_in_period"),
            ])
            ->leftJoin('lessons', function ($join) use ($fromDt, $toDt) {
                $join->on('lessons.teacher_id', '=', 'teachers.id');

                if ($fromDt) $join->where('lessons.starts_at', '>=', $fromDt);
                if ($toDt)   $join->where('lessons.starts_at', '<=', $toDt);
            })
            ->groupBy('teachers.id', 'teachers.last_name', 'teachers.first_name');

        if (! empty($teacherId)) {
            $q->where('teachers.id', (int) $teacherId);
        }

        $q->addSelect([
            DB::raw("(
                SELECT COUNT(*)
                FROM lessons l2
                WHERE l2.teacher_id = teachers.id
                  AND l2.status = 'scheduled'
                  AND l2.starts_at >= CURDATE()
            ) AS scheduled_future_from_today"),
        ]);

        return $q;
    }

    public function updatedData(): void
    {
        $this->resetTable();
    }

    public function updatedDataFrom(): void
    {
        $this->resetTable();
    }

    public function updatedDataTo(): void
    {
        $this->resetTable();
    }

    public function updatedDataTeacherId(): void
    {
        $this->resetTable();
    }
}
