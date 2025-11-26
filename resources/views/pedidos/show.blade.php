@extends('layouts.erp')
@section('title','Detalle de pedido')

@section('content')
<x-flash />

<div class="container-fluid">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <h3 class="fw-bold mb-0"><i class="bi bi-receipt me-2"></i> Pedido #{{ $pedido->id }}</h3>
    <a href="{{ route('pedidos.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i> Volver</a>
  </div>

  <div class="row g-3">
    <div class="col-12 col-lg-7">
      <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <div class="text-secondary">Dirección de entrega</div>
              <div class="fw-medium">{{ $pedido->direccion_entrega ?? '—' }}</div>
            </div>
            <div>
              <x-status-badge :status="$pedido->estado"/>
            </div>
          </div>
          <hr>
          
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="text-secondary mb-0"><i class="bi bi-list-check me-2"></i>Checklist de verificación</h6>
            @php
              $totalItems = $pedido->checklistItems->count();
              $completados = $pedido->checklistItems->where('completado', true)->count();
              $porcentaje = $totalItems > 0 ? ($completados / $totalItems) * 100 : 0;
            @endphp
            <small class="text-secondary">{{ $completados }}/{{ $totalItems }} completados</small>
          </div>
          
          @if($pedido->checklistItems->count() > 0)
            <div class="progress mb-3" style="height: 6px;">
              <div class="progress-bar {{ $porcentaje == 100 ? 'bg-success' : 'bg-primary' }}" 
                   role="progressbar" 
                   style="width: {{ $porcentaje }}%"></div>
            </div>
            
            <div class="list-group">
              @foreach($pedido->checklistItems as $item)
                <div class="list-group-item">
                  <form action="{{ route('pedidos.checklist.toggle', [$pedido, $item]) }}" 
                        method="POST" class="d-inline">
                    @csrf
                    <div class="form-check form-check-lg">
                      <input class="form-check-input" 
                             type="checkbox" 
                             {{ $item->completado ? 'checked' : '' }}
                             onchange="this.form.submit()">
                      <label class="form-check-label {{ $item->completado ? 'text-decoration-line-through text-secondary' : '' }}">
                        {{ $item->texto }}
                      </label>
                    </div>
                  </form>
                  @if($item->completado && $item->completed_at)
                    <small class="text-secondary ms-4">
                      <i class="bi bi-check-circle"></i> 
                      Completado {{ $item->completed_at->format('d M Y H:i') }}
                      @if($item->user)
                        por {{ $item->user->name }}
                      @endif
                    </small>
                  @endif
                </div>
              @endforeach
            </div>
          @else
            <div class="alert alert-info">
              <i class="bi bi-info-circle"></i> No hay checklist disponible para este pedido.
            </div>
          @endif

          <hr class="my-3">
          
          <h6 class="text-secondary mb-3">Productos</h6>
          <div class="table-responsive">
            <table class="table align-middle table-sm">
              <thead class="table-light">
                <tr>
                  <th>Producto</th><th>Bodega</th><th>Cant.</th><th>Precio</th><th>Subtotal</th>
                </tr>
              </thead>
              <tbody>
                @php $total=0; @endphp
                @foreach($pedido->items as $it)
                  @php $sub = $it->precio_unitario * $it->cantidad; $total+=$sub; @endphp
                  <tr>
                    <td>
                      <div class="fw-medium">{{ $it->product->descripcion ?? '—' }}</div>
                      @if(!empty($it->seriales) && is_array($it->seriales))
                        <div class="mt-2">
                          <small class="text-secondary d-block mb-1">
                            <i class="bi bi-upc-scan"></i> Números de Serie:
                          </small>
                          <div class="d-flex flex-wrap gap-1">
                            @foreach($it->seriales as $serial)
                              <span class="badge bg-primary-subtle text-primary-emphasis" style="font-family: 'Courier New', monospace; font-size: 0.8rem;">
                                <i class="bi bi-qr-code-scan"></i> {{ $serial }}
                              </span>
                            @endforeach
                          </div>
                        </div>
                      @endif
                    </td>
                    <td>{{ $it->warehouse->nombre ?? '—' }}</td>
                    <td>{{ $it->cantidad }}</td>
                    <td>${{ number_format($it->precio_unitario,2) }}</td>
                    <td>${{ number_format($sub,2) }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          <div class="d-flex justify-content-end">
            <div class="text-end">
              <div class="text-secondary">Envío</div>
              <div class="fw-bold">${{ number_format($pedido->costo_envio,2) }}</div>
              <div class="text-secondary mt-2">Total</div>
              <div class="fs-5 fw-bold">
                ${{ number_format($total + ($pedido->costo_envio ?? 0),2) }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-5">
      {{-- INFORMACIÓN DE PAGO --}}
      @if($pedido->payments->count() > 0)
      <div class="card border-0 shadow-sm mb-3" style="border-radius:16px;">
        <div class="card-body">
          <h6 class="mb-3"><i class="bi bi-cash-coin me-2"></i>Información de pago</h6>
          @foreach($pedido->payments as $payment)
            <div class="mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <div class="fw-semibold">Monto a cobrar</div>
                  <div class="display-6 fw-bold text-success">
                    ${{ number_format($payment->monto, 2) }}
                  </div>
                </div>
                <div>
                  <span class="badge rounded-pill fs-6 
                    @if($payment->estado==='depositado') text-bg-success
                    @elseif($payment->estado==='en_caja') text-bg-info
                    @else text-bg-warning
                    @endif">
                    {{ ucfirst($payment->estado ?? 'pendiente') }}
                  </span>
                </div>
              </div>
              <div class="row g-2 mt-2">
                <div class="col-12">
                  <div class="text-secondary small">Forma de pago</div>
                  <div class="fw-medium text-capitalize">{{ $payment->forma_pago }}</div>
                </div>
                <div class="col-12">
                  <div class="text-secondary small">Fecha de registro</div>
                  <div class="fw-medium">{{ $payment->created_at->format('d M Y H:i') }}</div>
                </div>
                @if($payment->reportado_at)
                <div class="col-12">
                  <div class="text-secondary small">Reportado</div>
                  <div class="fw-medium">{{ $payment->reportado_at->format('d M Y H:i') }}</div>
                </div>
                @endif
              </div>
            </div>
          @endforeach
        </div>
      </div>
      @endif

      <div class="card border-0 shadow-sm mb-3" style="border-radius:16px;">
        <div class="card-body">
          <h6 class="mb-3"><i class="bi bi-truck me-2"></i>Asignar chofer</h6>
          
          {{-- Mostrar chofer asignado si existe --}}
          @if($pedido->assignment)
            <div class="alert alert-info mb-3">
              <div class="d-flex align-items-center justify-content-between">
                <div>
                  <div class="fw-semibold">Chofer asignado</div>
                  <div class="mt-1">
                    <i class="bi bi-person-circle"></i> 
                    {{ $pedido->assignment->courier->name ?? '—' }}
                  </div>
                  <small class="text-secondary">
                    Asignado: {{ $pedido->assignment->asignado_at?->format('d M Y H:i') }}
                  </small>
                </div>
                <div>
                  <span class="badge rounded-pill 
                    @if($pedido->assignment->estado==='entregado') text-bg-success
                    @elseif($pedido->assignment->estado==='en_ruta') text-bg-info
                    @else text-bg-warning
                    @endif">
                    {{ ucfirst($pedido->assignment->estado ?? 'pendiente') }}
                  </span>
                </div>
              </div>
            </div>
          @endif
          
          {{-- Formulario para asignar chofer --}}
          <form method="POST" action="{{ route('logistica.assign') }}" class="row g-2" id="assignForm">
            @csrf
            <input type="hidden" name="order_id" value="{{ $pedido->id }}">
            <div class="col-12">
              <label class="form-label">Seleccionar chofer</label>
              <select name="courier_id" class="form-select" {{ !$pedido->assignment ? 'required' : '' }}>
                <option value="">Selecciona...</option>
                @foreach($empleados ?? [] as $e)
                  <option value="{{ $e->id }}" {{ ($pedido->assignment && $pedido->assignment->courier_id == $e->id) ? 'selected' : '' }}>
                    {{ $e->name }} ({{ $e->email }})
                  </option>
                @endforeach
              </select>
            </div>
            <div class="col-12 d-flex justify-content-end">
              <button type="submit" class="btn btn-primary">
                <i class="bi bi-check2"></i> {{ $pedido->assignment ? 'Reasignar' : 'Asignar' }}
              </button>
            </div>
          </form>
          
          <hr class="my-3">
          
          {{-- Programar entrega en calendario --}}
          <div class="mt-3">
            <h6 class="mb-3"><i class="bi bi-calendar-event me-2"></i>Programar entrega</h6>
            <form method="POST" action="{{ route('calendario.store') }}" class="row g-2">
              @csrf
              <input type="hidden" name="order_id" value="{{ $pedido->id }}">
              <div class="col-md-6">
                <label class="form-label">Fecha de entrega</label>
                <input type="date" name="fecha" class="form-select" required 
                       min="{{ now()->toDateString() }}" 
                       value="{{ old('fecha') }}">
              </div>
              <div class="col-md-4">
                <label class="form-label">Hora (opcional)</label>
                <input type="time" name="hora" class="form-select" value="{{ old('hora', '09:00') }}">
              </div>
              <div class="col-md-12">
                <label class="form-label">Chofer</label>
                <select name="courier_id" class="form-select" required>
                  <option value="">Selecciona...</option>
                  @foreach($empleados ?? [] as $e)
                    <option value="{{ $e->id }}" {{ ($pedido->assignment && $pedido->assignment->courier_id == $e->id) ? 'selected' : '' }}>
                      {{ $e->name }}
                    </option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-12">
                <label class="form-label">Título (opcional)</label>
                <input type="text" name="titulo" class="form-control" 
                       placeholder="Ej: Entrega urgente" value="{{ old('titulo') }}">
              </div>
              <div class="col-12 d-flex justify-content-end">
                <button type="submit" class="btn btn-success">
                  <i class="bi bi-calendar-plus"></i> Programar en Calendario
                </button>
              </div>
            </form>
          </div>

          <script>
            document.getElementById('assignForm').addEventListener('submit', function(e) {
              const select = this.querySelector('select[name="courier_id"]');
              if (!select.value) {
                e.preventDefault();
                alert('Por favor selecciona un chofer.');
                return false;
              }
            });
          </script>
        </div>
      </div>

      {{-- CAMBIAR ESTADO DEL PEDIDO --}}
      <div class="card border-0 shadow-sm mb-3" style="border-radius:16px;">
        <div class="card-body">
          <h6 class="mb-3"><i class="bi bi-arrow-repeat me-2"></i>Cambiar estado</h6>
          <form method="POST" action="{{ route('pedidos.update', $pedido) }}" class="row g-2">
            @csrf
            @method('PATCH')
            <div class="col-12">
              <label class="form-label">Estado actual</label>
              <select name="estado" class="form-select" required>
                <option value="capturado" {{ $pedido->estado === 'capturado' ? 'selected' : '' }}>Capturado</option>
                <option value="preparacion" {{ $pedido->estado === 'preparacion' ? 'selected' : '' }}>Preparación</option>
                <option value="asignado" {{ $pedido->estado === 'asignado' ? 'selected' : '' }}>Asignado</option>
                <option value="en_ruta" {{ $pedido->estado === 'en_ruta' ? 'selected' : '' }}>En ruta</option>
                <option value="entregado" {{ $pedido->estado === 'entregado' ? 'selected' : '' }}>Entregado</option>
                <option value="entregado_pendiente_pago" {{ $pedido->estado === 'entregado_pendiente_pago' ? 'selected' : '' }}>Entregado - Pendiente Pago</option>
                <option value="finalizado" {{ $pedido->estado === 'finalizado' ? 'selected' : '' }}>Finalizado</option>
                <option value="cancelado" {{ $pedido->estado === 'cancelado' ? 'selected' : '' }}>Cancelado</option>
              </select>
              <small class="text-secondary d-block mt-1">
                <i class="bi bi-info-circle"></i> Al cambiar a "Finalizado", el dinero se reflejará en finanzas automáticamente.
              </small>
            </div>
            <div class="col-12 d-flex justify-content-end">
              <button type="submit" class="btn btn-primary">
                <i class="bi bi-check2"></i> Actualizar Estado
              </button>
            </div>
          </form>
        </div>
      </div>

      <div class="card border-0 shadow-sm" style="border-radius:16px;">
        <div class="card-body">
          <h6 class="mb-3"><i class="bi bi-upc-scan me-2"></i>Escaneo de serie</h6>
          <form method="POST" action="{{ route('logistica.scan') }}" class="row g-2">
            @csrf
            <input type="hidden" name="order_id" value="{{ $pedido->id }}">
            <div class="col-12">
              <label class="form-label">Número de serie</label>
              <input name="numero_serie" class="form-control" placeholder="TV-XXXXXX..." required>
            </div>
            <div class="col-12">
              <label class="form-label">Tipo</label>
              <select name="tipo" class="form-select" required>
                <option value="salida_bodega">Salida de bodega</option>
                <option value="entrega_cliente">Entrega a cliente</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">Notas / Meta (opcional)</label>
              <input name="meta[nota]" class="form-control" placeholder="Modelo, evidencia, etc.">
            </div>
            <div class="col-12 d-flex justify-content-end">
              <button class="btn btn-outline-primary"><i class="bi bi-qr-code-scan"></i> Registrar</button>
            </div>
          </form>
        </div>
      </div>
    </div>

  </div>
</div>
@endsection
