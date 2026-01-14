<?php

namespace App\Filament\Pages\Reports;

use App\Models\Enrollment;
use App\Models\Lesson;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class StudentHoursReport extends Page implements Tables\Contracts\HasTable, Forms\Contracts\HasForms
{
    use Tables\Concerns\InteractsWithTable;
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'Report';
    protected static ?string $navigationLabel = 'Lezioni studenti';
    protected static string $view = 'filament.pages.reports.student-hours-report';

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
            'from' => now()->startOfMonth()->toDateString(),
            'to'   => now()->endOfMonth()->toDateString(),
        ]);
    }

    public function getTableRecordKey($record): string
    {
        return (string) $record->enrollment_id;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Filtri')
                    ->columns(3)
                    ->schema([
                        Forms\Components\DatePicker::make('from')->label('Da')->live(),
                        Forms\Components\DatePicker::make('to')->label('A')->live(),

                        Forms\Components\TextInput::make('student_id')
                            ->label('ID Studente')
                            ->numeric()
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
                Tables\Columns\TextColumn::make('studente')
                    ->label('Studente')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('corso')
                    ->label('Corso')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('purchased_lessons')
                    ->label('Lezioni acquistate')
                    ->alignRight()
                    ->sortable(),

                Tables\Columns\TextColumn::make('attended_lessons')
                    ->label('Lezioni fruite')
                    ->alignRight()
                    ->sortable(),

                Tables\Columns\TextColumn::make('remaining_lessons')
                    ->label('Lezioni residue')
                    ->alignRight()
                    ->sortable(),

                Tables\Columns\TextColumn::make('to_recover_lessons')
                    ->label('Da recuperare')
                    ->alignRight()
                    ->sortable(),
            ])
            ->defaultSort('studente')
            ->paginated([25, 50, 100]);
    }

    protected function baseQuery(): Builder
    {
        $from = $this->data['from'] ?? null;
        $to   = $this->data['to'] ?? null;
        $studentId = $this->data['student_id'] ?? null;

        $q = Enrollment::query()
            ->select([
                'enrollments.id as enrollment_id',

                DB::raw("COALESCE(CONCAT(students.first_name, ' ', students.last_name), CONCAT('Studente #', enrollments.student_id)) as studente"),
                DB::raw("COALESCE(courses.name, CONCAT('Corso #', enrollments.course_id)) as corso"),

                DB::raw("COALESCE(courses.lessons_count, 0) as purchased_lessons"),

                DB::raw("SUM(CASE WHEN lessons.status IN ('" . Lesson::STATUS_COMPLETED . "','" . Lesson::STATUS_CANCELLED_COUNTED . "') THEN 1 ELSE 0 END) as attended_lessons"),

                DB::raw("SUM(CASE WHEN lessons.status = '" . Lesson::STATUS_CANCELLED_RECOVER . "' THEN 1 ELSE 0 END) as to_recover_lessons"),

                DB::raw("(COALESCE(courses.lessons_count, 0) - SUM(CASE WHEN lessons.status IN ('" . Lesson::STATUS_COMPLETED . "','" . Lesson::STATUS_CANCELLED_COUNTED . "') THEN 1 ELSE 0 END)) as remaining_lessons"),
            ])
            ->join('students', 'students.id', '=', 'enrollments.student_id')
            ->join('courses', 'courses.id', '=', 'enrollments.course_id')
            ->leftJoin('lessons', function ($join) use ($from, $to) {
                $join->on('lessons.enrollment_id', '=', 'enrollments.id');

                if ($from) $join->whereDate('lessons.starts_at', '>=', $from);
                if ($to)   $join->whereDate('lessons.starts_at', '<=', $to);
            })
            ->groupBy(
                'enrollments.id',
                'students.first_name',
                'students.last_name',
                'enrollments.student_id',
                'courses.name',
                'enrollments.course_id',
                'courses.lessons_count'
            );

        if ($studentId) {
            $q->where('enrollments.student_id', $studentId);
        }

        return $q;
    }

    public function updatedData(): void
    {
        $this->resetTable();
    }
}
