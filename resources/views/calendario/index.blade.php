@extends('layouts.erp')
@section('title','Calendario de Entregas')

@section('content')
<x-flash/>

<div class="container-fluid">
  <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <h2 class="fw-bold mb-0">
      <i class="bi bi-calendar3 me-2"></i> Calendario de Entregas
    </h2>
    
    <div class="d-flex gap-2 flex-wrap">
      <a href="{{ route('pedidos.index') }}" class="btn btn-sm btn-outline-primary">
        <i class="bi bi-bag-check"></i> Ver Pedidos
      </a>
      <select id="filterChofer" class="form-select form-select-sm" style="width: auto;">
        <option value="">Todos los choferes</option>
        @foreach($choferes ?? [] as $c)
          <option value="{{ $c->id }}">{{ $c->name }}</option>
        @endforeach
      </select>
      
      <button id="btnToday" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-calendar-event"></i> Hoy
      </button>
    </div>
  </div>
  
  <div class="alert alert-info mb-3" style="border-radius:16px;">
    <div class="d-flex align-items-start">
      <i class="bi bi-info-circle me-2 mt-1"></i>
      <div>
        <strong>¿Cómo programar entregas?</strong>
        <ul class="mb-0 mt-2 small">
          <li><strong>Automático:</strong> Al asignar un chofer a un pedido, aparece automáticamente en el calendario</li>
          <li><strong>Manual:</strong> Ve al detalle de un pedido y usa el formulario "Programar entrega" para fijar fecha y hora específica</li>
        </ul>
      </div>
    </div>
  </div>

  <div class="card border-0 shadow-sm" style="border-radius:16px;">
    <div class="card-body p-4">
      <div id="calendar"></div>
    </div>
  </div>

  {{-- Leyenda --}}
  <div class="card border-0 shadow-sm mt-3" style="border-radius:16px;">
    <div class="card-body">
      <h6 class="fw-bold mb-3">Leyenda</h6>
      <div class="row g-3">
        <div class="col-md-3">
          <div class="d-flex align-items-center">
            <div class="rounded me-2" style="width:20px;height:20px;background:#f59e0b;"></div>
            <span class="small">Pendiente</span>
          </div>
        </div>
        <div class="col-md-3">
          <div class="d-flex align-items-center">
            <div class="rounded me-2" style="width:20px;height:20px;background:#10b981;"></div>
            <span class="small">En Ruta</span>
          </div>
        </div>
        <div class="col-md-3">
          <div class="d-flex align-items-center">
            <div class="rounded me-2" style="width:20px;height:20px;background:#3b82f6;"></div>
            <span class="small">Programado</span>
          </div>
        </div>
        <div class="col-md-3">
          <div class="d-flex align-items-center">
            <div class="rounded me-2" style="width:20px;height:20px;background:#6b7280;"></div>
            <span class="small">Completado</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/main.min.css" rel="stylesheet">
<style>
  .fc-event-title {
    font-weight: 600;
    font-size: 0.875rem;
  }
  .fc-event-time {
    font-weight: 500;
  }
  #calendar {
    min-height: 600px;
  }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const calendarEl = document.getElementById('calendar');
  
  const calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    locale: 'es',
    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: 'dayGridMonth,timeGridWeek,timeGridDay'
    },
    height: 'auto',
    events: @json($events),
    eventClick: function(info) {
      if (info.event.url) {
        window.location.href = info.event.url;
        return false;
      }
    },
    eventDidMount: function(info) {
      // Agregar tooltip con información adicional
      const props = info.event.extendedProps;
      if (props.direccion) {
        info.el.setAttribute('title', 
          `Chofer: ${props.courier}\nDirección: ${props.direccion}\nEstado: ${props.estado || 'Programado'}`
        );
      }
    },
    eventDisplay: 'block',
    eventTextColor: '#ffffff',
    navLinks: true,
    dayMaxEvents: true,
    moreLinkClick: 'popover',
  });
  
  calendar.render();

  // Filtro por chofer
  document.getElementById('filterChofer')?.addEventListener('change', function(e) {
    const choferId = e.target.value;
    const events = calendar.getEvents();
    
    events.forEach(event => {
      const props = event.extendedProps;
      if (!choferId) {
        event.setProp('display', 'auto');
      } else {
        // Filtrar por chofer (necesitaríamos el ID del chofer en extendedProps)
        // Por ahora, mostrar todos si no hay filtro específico
        event.setProp('display', 'auto');
      }
    });
    
    // Refrescar eventos desde el servidor con filtro
    if (choferId) {
      // TODO: Implementar filtro del lado del servidor si es necesario
    }
  });

  // Botón "Hoy"
  document.getElementById('btnToday')?.addEventListener('click', function() {
    calendar.today();
  });
});
</script>
@endpush

