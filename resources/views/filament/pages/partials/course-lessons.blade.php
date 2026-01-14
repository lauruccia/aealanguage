@php
    $teacherId = auth()->user()?->teacher?->id;

    $lessons = \App\Models\Lesson::query()
        ->where('course_id', $course->id)
        ->when(
            $teacherId,
            fn($q) => $q->where('teacher_id', $teacherId),
            fn($q) => $q->whereRaw('1=0')
        )
        ->with('student')
        ->orderBy('starts_at', 'asc')
        ->get();
@endphp

<div class="space-y-2">
    <div class="text-sm text-gray-600">
        Totale lezioni: <strong>{{ $lessons->count() }}</strong>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="text-left border-b">
                    <th class="py-2 pr-4">Data/ora</th>
                    <th class="py-2 pr-4">Studente</th>
                    <th class="py-2 pr-4">Stato</th>
                    <th class="py-2 pr-4">Note</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lessons as $l)
                    <tr class="border-b">
                        <td class="py-2 pr-4">{{ optional($l->starts_at)->format('d/m/Y H:i') }}</td>
                        <td class="py-2 pr-4">
                            @php
                                $s = $l->student;
                                $name = $s?->full_name
                                    ?? trim(($s->first_name ?? '').' '.($s->last_name ?? ''))
                                    ?: ($s->email ?? ('ID '.$s->id));
                            @endphp
                            {{ $name }}
                        </td>
                        <td class="py-2 pr-4">{{ $l->getStatusLabel() }}</td>
                        <td class="py-2 pr-4">{{ $l->notes ?: '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td class="py-3" colspan="4">Nessuna lezione trovata.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
