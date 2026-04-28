@extends('layouts.admin')

@section('title', 'Calendrier des Réservations')
@section('page-title', 'Calendrier')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard', $tenant->slug) }}" class="hover:text-amber-500">Dashboard</a>
    <span class="mx-2">/</span>
    <a href="{{ route('admin.reservations.index', $tenant->slug) }}" class="hover:text-amber-500">Réservations</a>
    <span class="mx-2">/</span>
    <span>Calendrier</span>
@endsection

@push('head')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css">
<style>
    #reservation-calendar .fc-toolbar-title { font-size: 1rem; font-weight: 700; }
    #reservation-calendar .fc-button {
        background: #f59e0b; border-color: #f59e0b;
        font-size: 0.75rem; padding: 0.35rem 0.75rem;
    }
    #reservation-calendar .fc-button:hover { background: #d97706; border-color: #d97706; }
    #reservation-calendar .fc-button-active { background: #92400e !important; border-color: #92400e !important; }
    #reservation-calendar .fc-day-today { background: #fffbeb !important; }
    #reservation-calendar .fc-event { cursor: pointer; font-size: 0.7rem; }
</style>
@endpush

@section('content')
<div>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-5">
        <a href="{{ route('admin.reservations.index', $tenant->slug) }}"
           class="inline-flex items-center gap-2 px-4 py-2 border border-gray-200 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-50 transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0z"/>
            </svg>
            Vue liste
        </a>
        <a href="{{ route('admin.reservations.create', $tenant->slug) }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold rounded-xl transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Nouvelle réservation
        </a>
    </div>

    {{-- Légende --}}
    <div class="flex flex-wrap gap-3 mb-4">
        <div class="flex items-center gap-1.5 text-xs text-gray-600">
            <span class="w-3 h-3 rounded-full bg-yellow-400 inline-block"></span> En attente
        </div>
        <div class="flex items-center gap-1.5 text-xs text-gray-600">
            <span class="w-3 h-3 rounded-full bg-blue-400 inline-block"></span> Confirmée
        </div>
        <div class="flex items-center gap-1.5 text-xs text-gray-600">
            <span class="w-3 h-3 rounded-full bg-emerald-400 inline-block"></span> Installée
        </div>
        <div class="flex items-center gap-1.5 text-xs text-gray-600">
            <span class="w-3 h-3 rounded-full bg-gray-400 inline-block"></span> Terminée
        </div>
        <div class="flex items-center gap-1.5 text-xs text-gray-600">
            <span class="w-3 h-3 rounded-full bg-red-400 inline-block"></span> Annulée
        </div>
    </div>

    {{-- Calendar container --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
        <div id="reservation-calendar"></div>
    </div>

    {{-- Detail popup --}}
    <div id="event-popup"
         class="hidden fixed inset-0 z-50 flex items-center justify-center p-4"
         onclick="closePopup(event)">
        <div class="absolute inset-0 bg-black/40"></div>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 z-10">
            <button onclick="document.getElementById('event-popup').classList.add('hidden')"
                    class="absolute top-3 right-3 text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            <h3 class="font-bold text-gray-900 text-base mb-4" id="popup-title"></h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500">Statut</dt>
                    <dd id="popup-status" class="font-semibold"></dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Table</dt>
                    <dd id="popup-table" class="font-semibold"></dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Téléphone</dt>
                    <dd id="popup-phone" class="font-semibold"></dd>
                </div>
            </dl>
            <a id="popup-link" href="#"
               class="mt-5 block text-center py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl transition text-sm">
                Voir la réservation
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script>
const calendarEl = document.getElementById('reservation-calendar');
const calendar = new FullCalendar.Calendar(calendarEl, {
    locale: 'fr',
    initialView: window.innerWidth < 640 ? 'listWeek' : 'dayGridMonth',
    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,listWeek'
    },
    events: function(fetchInfo, successCallback, failureCallback) {
        fetch(`{{ route('admin.reservations.calendar', $tenant->slug) }}?start=${fetchInfo.startStr}&end=${fetchInfo.endStr}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => successCallback(data))
        .catch(() => failureCallback());
    },
    eventClick: function(info) {
        const props = info.event.extendedProps;
        document.getElementById('popup-title').textContent = info.event.title;
        document.getElementById('popup-status').textContent = props.status_label || props.status;
        document.getElementById('popup-table').textContent = props.table || '—';
        document.getElementById('popup-phone').textContent = props.phone || '—';
        document.getElementById('popup-link').href =
            '{{ url("/admin/" . $tenant->slug . "/reservations") }}/' + info.event.id;
        document.getElementById('event-popup').classList.remove('hidden');
    },
    height: 'auto',
});
calendar.render();

function closePopup(e) {
    if (e.target === document.getElementById('event-popup') || e.target.classList.contains('absolute')) {
        document.getElementById('event-popup').classList.add('hidden');
    }
}
</script>
@endpush
@endsection
