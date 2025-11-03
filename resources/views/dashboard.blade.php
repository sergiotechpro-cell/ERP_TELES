@extends('layouts.erp')
@section('title','Dashboard')

@section('content')
<x-flash />

<div class="container-fluid">
  <!-- KPI cards -->
  <div class="row g-3 mb-4">
    <div class="col-12 col-md-3">
      <div class="card border-0 shadow-sm h-100" style="border-radius:16px; border-left: 4px solid #10b981;">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="text-secondary small mb-1">Ventas hoy</div>
              <div class="fs-3 fw-bold text-success">${{ number_format((float)($ventasHoy ?? 0), 2) }}</div>
            </div>
            <div class="display-6 text-success opacity-25"><i class="bi bi-cash-coin"></i></div>
          </div>
          <small class="text-secondary">Total de pagos válidos en el día</small>
        </div>
      </div>
    </div>

    <div class="col-12 col-md-3">
      <div class="card border-0 shadow-sm h-100" style="border-radius:16px; border-left: 4px solid #3b82f6;">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="text-secondary small mb-1">Pedidos</div>
              <div class="fs-3 fw-bold text-primary">{{ $pedidos ?? 0 }}</div>
              <div class="small text-secondary mt-1">
                <span class="badge bg-warning">{{ $pedidosPendientes ?? 0 }} pendientes</span>
                <span class="badge bg-success">{{ $pedidosEntregados ?? 0 }} entregados</span>
              </div>
            </div>
            <div class="display-6 text-primary opacity-25"><i class="bi bi-bag-check"></i></div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-md-3">
      <div class="card border-0 shadow-sm h-100" style="border-radius:16px; border-left: 4px solid #f59e0b;">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="text-secondary small mb-1">Productos</div>
              <div class="fs-3 fw-bold text-warning">{{ $inventario ?? 0 }}</div>
            </div>
            <div class="display-6 text-warning opacity-25"><i class="bi bi-box-seam"></i></div>
          </div>
          <small class="text-secondary">SKUs registrados</small>
        </div>
      </div>
    </div>

    <div class="col-12 col-md-3">
      <div class="card border-0 shadow-sm h-100" style="border-radius:16px; border-left: 4px solid #8b5cf6;">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="text-secondary small mb-1">Clientes</div>
              <div class="fs-3 fw-bold text-purple">{{ $clientes ?? 0 }}</div>
              <div class="small text-secondary mt-1">
                <span class="badge bg-info">{{ $empleados ?? 0 }} empleados</span>
              </div>
            </div>
            <div class="display-6 text-purple opacity-25"><i class="bi bi-people"></i></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3">
    <!-- Chart ventas mensuales -->
    <div class="col-12 col-md-8">
      <div class="card border-0 shadow-sm" style="border-radius:16px;">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="mb-0 fw-bold"><i class="bi bi-bar-chart me-2"></i> Ventas mensuales</h5>
          </div>
          @if($ventasMensuales && $ventasMensuales->count() > 0)
            <canvas id="ventasMensuales"></canvas>
          @else
            <div class="alert alert-info mb-0">
              <i class="bi bi-info-circle"></i> No hay datos de ventas mensuales para mostrar aún.
            </div>
          @endif
        </div>
      </div>
    </div>

    <!-- Pedidos por estado -->
    <div class="col-12 col-md-4">
      <div class="card border-0 shadow-sm" style="border-radius:16px;">
        <div class="card-body">
          <h5 class="mb-3 fw-bold"><i class="bi bi-pie-chart me-2"></i> Pedidos por estado</h5>
          @if($pedidosPorEstado && $pedidosPorEstado->count() > 0)
            <div class="list-group list-group-flush">
              @foreach($pedidosPorEstado as $estado => $cantidad)
                <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                  <span class="badge 
                    @if($estado === 'entregado') bg-success
                    @elseif($estado === 'en_ruta') bg-primary
                    @elseif($estado === 'asignado') bg-warning
                    @elseif($estado === 'cancelado') bg-danger
                    @else bg-secondary
                    @endif
                  ">{{ $estado }}</span>
                  <strong>{{ $cantidad }}</strong>
                </div>
              @endforeach
            </div>
          @else
            <div class="alert alert-info mb-0">
              <i class="bi bi-info-circle"></i> No hay pedidos registrados aún.
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>

  <!-- Actividad reciente -->
  <div class="row g-3 mt-3">
    <!-- Ventas recientes -->
    <div class="col-12 col-md-6">
      <div class="card border-0 shadow-sm" style="border-radius:16px;">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="mb-0 fw-bold"><i class="bi bi-cart-check me-2"></i> Ventas recientes</h5>
            <a href="{{ route('pos.index') }}" class="btn btn-sm btn-outline-primary">Ver todas</a>
          </div>
          @if($ventasRecientes && $ventasRecientes->count() > 0)
            <div class="table-responsive">
              <table class="table table-sm align-middle mb-0">
                <thead>
                  <tr>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Total</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($ventasRecientes as $venta)
                    <tr>
                      <td class="small">{{ $venta->created_at->format('d/m/Y H:i') }}</td>
                      <td>{{ $venta->customer->nombre ?? '—' }}</td>
                      <td class="fw-semibold">${{ number_format((float)$venta->total, 2) }}</td>
                      <td class="text-end">
                        <a href="{{ route('pos.show', $venta) }}" class="btn btn-sm btn-outline-primary">
                          <i class="bi bi-eye"></i>
                        </a>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @else
            <div class="alert alert-info mb-0">
              <i class="bi bi-info-circle"></i> No hay ventas recientes.
            </div>
          @endif
        </div>
      </div>
    </div>

    <!-- Pedidos recientes -->
    <div class="col-12 col-md-6">
      <div class="card border-0 shadow-sm" style="border-radius:16px;">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="mb-0 fw-bold"><i class="bi bi-bag me-2"></i> Pedidos recientes</h5>
            <a href="{{ route('pedidos.index') }}" class="btn btn-sm btn-outline-primary">Ver todos</a>
          </div>
          @if($pedidosRecientes && $pedidosRecientes->count() > 0)
            <div class="table-responsive">
              <table class="table table-sm align-middle mb-0">
                <thead>
                  <tr>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Estado</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($pedidosRecientes as $pedido)
                    <tr>
                      <td class="small">{{ $pedido->created_at->format('d/m/Y H:i') }}</td>
                      <td>{{ $pedido->customer->nombre ?? '—' }}</td>
                      <td>
                        <span class="badge 
                          @if($pedido->estado === 'entregado') bg-success
                          @elseif($pedido->estado === 'en_ruta') bg-primary
                          @elseif($pedido->estado === 'asignado') bg-warning
                          @elseif($pedido->estado === 'cancelado') bg-danger
                          @else bg-secondary
                          @endif
                        ">{{ $pedido->estado }}</span>
                      </td>
                      <td class="text-end">
                        <a href="{{ route('pedidos.show', $pedido) }}" class="btn btn-sm btn-outline-primary">
                          <i class="bi bi-eye"></i>
                        </a>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @else
            <div class="alert alert-info mb-0">
              <i class="bi bi-info-circle"></i> No hay pedidos recientes.
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
@if($ventasMensuales && $ventasMensuales->count() > 0)
@php
  $chartData = $ventasMensuales->map(function($r) {
    return [
      'label' => \Carbon\Carbon::parse($r->mes)->isoFormat('MMM YYYY'),
      'total' => (float)$r->total
    ];
  })->values();
@endphp
<script type="module">
  import Chart from 'https://cdn.jsdelivr.net/npm/chart.js/+esm'

  const rows = @json($chartData);

  const ctx = document.getElementById('ventasMensuales');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: rows.map(r => r.label),
      datasets: [{
        label: 'Ventas',
        data: rows.map(r => r.total),
        backgroundColor: 'rgba(59, 130, 246, 0.5)',
        borderColor: 'rgba(59, 130, 246, 1)',
        borderWidth: 2
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false }
      },
      scales: {
        y: { beginAtZero: true }
      }
    }
  });
</script>
@endif
@endpush
