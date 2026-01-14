@php
    /** @var \App\Models\Student $student */
    $studentId = $student->id;
    $eventsUrl = route('admin.calendar.lessons');
@endphp

<div class="rounded-xl border bg-white p-3">
    <div class="flex items-center gap-3 mb-3">
        <div class="text-sm text-gray-600">
            Calendario lezioni di: <b>{{ $student->first_name }} {{ $student->last_name }}</b>
        </div>
    </div>

    {{-- IMPORTANTISSIMO: evita conflitti con Livewire --}}
    <div wire:ignore>
        <div id="student-calendar-{{ $studentId }}"></div>
    </div>
</div>

@once
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
@endonce

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const studentId = @json($studentId);
    const eventsUrl = @json($eventsUrl);

    const el = document.getElementById(`student-calendar-${studentId}`);
    if (!el) return;

    // evita doppie inizializzazioni se Livewire ricarica pezzi di pagina
    if (el.dataset.initialized === '1') return;
    el.dataset.initialized = '1';

    const calendar = new FullCalendar.Calendar(el, {
        initialView: 'timeGridWeek',
        locale: 'it',
        firstDay: 1,
        nowIndicator: true,
        height: 'auto',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: (info, success, failure) => {
            const url =
                `${eventsUrl}?student_id=${studentId}` +
                `&start=${encodeURIComponent(info.startStr)}` +
                `&end=${encodeURIComponent(info.endStr)}`;

            fetch(url)
                .then(r => r.json())
                .then(data => success(data))
                .catch(err => failure(err));
        },
        eventClick: (info) => {
            info.jsEvent.preventDefault();

            // apre in nuova scheda e NON fa “flash” di Livewire
            const url = `{{ url('/admin/lessons') }}/${info.event.id}/edit`;
            window.open(url, '_blank');
        },
    });

    calendar.render();
});
</script>
@endpush
