@php
    /** @var \App\Models\Course|null $course */
    /** @var \App\Models\Student|null $student */
    /** @var int|null $courseId */
    /** @var int|null $studentId */

    $teacherId = auth()->user()?->teacher?->id;

    $lessons = \App\Models\Lesson::query()
        ->when($teacherId, fn($q) => $q->where('teacher_id', $teacherId), fn($q) => $q->whereRaw('1=0'))
        ->when($courseId, fn($q) => $q->where('course_id', $courseId))
        ->when($studentId, fn($q) => $q->where('student_id', $studentId))
        ->orderBy('starts_at', 'asc')
        ->get();
@endphp

<div class="space-y-2">
    <div class="text-sm text-gray-600">
        <div><strong>Corso:</strong> {{ $course?->name ?? ('ID ' . ($courseId ?? '—')) }}</div>
        <div><strong>Studente:</strong>
            {{ $student?->full_name
                ?? trim(($student?->first_name ?? '').' '.($student?->last_name ?? ''))
                ?: ('ID ' . ($studentId ?? '—')) }}
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="border-b">
                    <th class="py-2 text-left">Inizio</th>
                    <th class="py-2 text-left">Stato</th>
                    <th class="py-2 text-left">Note</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($lessons as $l)
                    <tr class="border-b">
                        <td class="py-2">{{ optional($l->starts_at)->format('d/m/Y H:i') }}</td>
                        <td class="py-2">{{ $l->statusLabel() ?? $l->status }}</td>
                        <td class="py-2">{{ $l->notes ?: '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="py-3 text-gray-500">Nessuna lezione trovata.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
