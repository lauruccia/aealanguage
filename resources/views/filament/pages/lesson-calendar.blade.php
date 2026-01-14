@php
    $eventsUrl = route('admin.calendar.lessons');

    $students = \App\Models\Student::query()
        ->orderBy('last_name')
        ->orderBy('first_name')
        ->get()
        ->map(fn($s) => ['id' => $s->id, 'name' => $s->last_name.' '.$s->first_name]);

    $teachers = \App\Models\Teacher::query()
        ->orderBy('last_name')
        ->orderBy('first_name')
        ->get()
        ->map(fn($t) => ['id' => $t->id, 'name' => $t->last_name.' '.$t->first_name]);

    $courses = \App\Models\Course::query()
        ->orderBy('name')
        ->get()
        ->map(fn($c) => ['id' => $c->id, 'name' => $c->name]);
@endphp

<x-filament-panels::page>
    <div class="rounded-xl border bg-white p-4 space-y-4">
        {{-- FILTRI --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div>
                <label class="text-sm text-gray-600">Studente</label>
                <select id="filter-student" class="w-full rounded-lg border-gray-300">
                    <option value="">Tutti</option>
                    @foreach ($students as $s)
                        <option value="{{ $s['id'] }}">{{ $s['name'] }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-sm text-gray-600">Docente</label>
                <select id="filter-teacher" class="w-full rounded-lg border-gray-300">
                    <option value="">Tutti</option>
                    @foreach ($teachers as $t)
                        <option value="{{ $t['id'] }}">{{ $t['name'] }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-sm text-gray-600">Corso</label>
                <select id="filter-course" class="w-full rounded-lg border-gray-300">
                    <option value="">Tutti</option>
                    @foreach ($courses as $c)
                        <option value="{{ $c['id'] }}">{{ $c['name'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- CALENDARIO --}}
        <div wire:ignore>
            <div id="calendar"></div>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const calendarEl = document.getElementById('calendar');

            const studentEl = document.getElementById('filter-student');
            const teacherEl = document.getElementById('filter-teacher');
            const courseEl  = document.getElementById('filter-course');

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'timeGridWeek',
                firstDay: 1,
                nowIndicator: true,
                height: 'auto',
                locale: 'it',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
                },
                events: function(fetchInfo, successCallback, failureCallback) {
                    const url = new URL(@json($eventsUrl));
                    url.searchParams.set('start', fetchInfo.startStr);
                    url.searchParams.set('end', fetchInfo.endStr);

                    const studentId = studentEl.value;
                    const teacherId = teacherEl.value;
                    const courseId  = courseEl.value;

                    if (studentId) url.searchParams.set('student_id', studentId);
                    if (teacherId) url.searchParams.set('teacher_id', teacherId);
                    if (courseId)  url.searchParams.set('course_id', courseId);

                    fetch(url)
                        .then(r => r.json())
                        .then(data => successCallback(data))
                        .catch(err => failureCallback(err));
                },
                eventClick: (info) => {
                    info.jsEvent.preventDefault();
                    const url = `{{ url('/admin/lessons') }}/${info.event.id}/edit`;
                    window.open(url, '_blank');
                },
            });

            calendar.render();

            // quando cambia un filtro, ricarica eventi
            [studentEl, teacherEl, courseEl].forEach(el => {
                el.addEventListener('change', () => calendar.refetchEvents());
            });
        });
    </script>
</x-filament-panels::page>
