@extends('layouts.erp')
@section('title','Calendario de entregas')
@section('content')
<x-flash/>
<h3 class="fw-bold mb-3"><i class="bi bi-calendar3 me-2"></i>Calendario de entregas</h3>
<div id="calendar"></div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/main.min.css" rel="stylesheet">
@endpush
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const calendarEl = document.getElementById('calendar');
  const calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    height: 'auto',
    events: @json($events)
  });
  calendar.render();
});
</script>
@endpush
