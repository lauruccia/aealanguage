<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use Illuminate\Http\Request;

class CalendarLessonsController extends Controller
{
    public function __invoke(Request $request)
    {
        $start = $request->string('start')->toString();
        $end   = $request->string('end')->toString();

        $q = Lesson::query()
            ->whereBetween('starts_at', [$start, $end])
            ->with(['student', 'course', 'teacher']);

        if ($request->filled('student_id')) {
            $q->where('student_id', $request->integer('student_id'));
        }

        if ($request->filled('teacher_id')) {
            $q->where('teacher_id', $request->integer('teacher_id'));
        }

        if ($request->filled('course_id')) {
            $q->where('course_id', $request->integer('course_id'));
        }

        return $q->get()->map(function (Lesson $lesson) {
            $student = $lesson->student ? ($lesson->student->first_name . ' ' . $lesson->student->last_name) : 'Studente';
            $course  = $lesson->course?->name ?? 'Corso';
            $teacher = $lesson->teacher ? ($lesson->teacher->first_name . ' ' . $lesson->teacher->last_name) : null;

            return [
                'id'    => $lesson->id,
                'title' => $teacher
                    ? "{$student} • {$course} • {$teacher}"
                    : "{$student} • {$course}",
                'start' => $lesson->starts_at?->toIso8601String(),
                'end'   => ($lesson->ends_at ?? $lesson->starts_at->copy()->addMinutes($lesson->duration_minutes))->toIso8601String(),
            ];
        });
    }
}
