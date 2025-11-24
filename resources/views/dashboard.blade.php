@extends('layouts.erp')
@section('title','Dashboard')

@section('content')
<x-flash />

<div class="container-fluid">
  <div class="row g-3 mb-4">
    <div class="col-12 col-md-3">
      <div class="card border-0 shadow-sm h-100" style="border-radius:16px; border-left: 4px solid #10b981;">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="text-secondary small mb-1">Ingresos combinados hoy</div>
              <div class="fs-3 fw-bold text-success">${{ number_format((float)($ventasHoy ?? 0), 2) }}</div>
              <small class="text-secondary">POS + pedidos del día</small>
            </div>
            <div class="display-6 text-success opacity-25"><i class="bi bi-cash-stack"></i></div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-3">
      <div class="card border-0 shadow-sm h-100" style="border-radius:16px; border-left: 4px solid #3b82f6;">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="text-secondary small mb-1">Ventas POS hoy</div>
              <div class="fs-3 fw-bold text-primary">${{ number_format((float)($ventasPosHoy ?? 0), 2) }}</div>
            </div>
            <div class="display-6 text-primary opacity-25"><i class="bi bi-shop"></i></div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-3">
      <div class="card border-0 shadow-sm h-100" style="border-radius:16px; border-left: 4px solid #0ea5e9;">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="text-secondary small mb-1">Ventas pedidos hoy</div>
              <div class="fs-3 fw-bold text-info">${{ number_format((float)($ventasPedidosHoy ?? 0), 2) }}</div>
            </div>
            <div class="display-6 text-info opacity-25"><i class="bi bi-truck"></i></div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-3">
      <div class="card border-0 shadow-sm h-100" style="border-radius:16px; border-left: 4px solid #f97316;">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="text-secondary small mb-1">Pagos reportados hoy</div>
              <div class="fs-3 fw-bold text-warning">${{ number_format((float)($ingresosPagosHoy ?? 0), 2) }}</div>
              <small class="text-secondary">Estados en caja/depositados</small>
            </div>
            <div class="display-6 text-warning opacity-25"><i class="bi bi-receipt"></i></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3 mb-4">
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
              <small class="text-secondary">SKUs registrados</small>
            </div>
            <div class="display-6 text-warning opacity-25"><i class="bi bi-box-seam"></i></div>
          </div>
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
    <div class="col-12 col-md-3">
      <div class="card border-0 shadow-sm h-100" style="border-radius:16px; border-left: 4px solid #22c55e;">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="text-secondary small mb-1">Valor inventario</div>
              <div class="fs-3 fw-bold text-success">${{ number_format((float)($valorInventario ?? 0), 2) }}</div>
              <small class="text-secondary">Utilidad potencial: ${{ number_format((float)($utilidadInventario ?? 0), 2) }}</small>
            </div>
            <div class="display-6 text-success opacity-25"><i class="bi bi-kanban"></i></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
              <div class="text-secondary small mb-1">Garantías activas</div>
              <div class="fs-3 fw-bold text-danger">{{ $garantiasAbiertas ?? 0 }}</div>
            </div>
            <div class="text-end">
              <small class="text-secondary">Cerradas este mes</small>
              <div class="fw-bold text-success">{{ $garantiasCerradasMes ?? 0 }}</div>
            </div>
          </div>
          <a href="{{ route('garantias.module') }}" class="btn btn-outline-danger w-100 btn-sm">Ir al módulo de garantías</a>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
        <div class="card-body">
          <div class="text-secondary small mb-2">Estado de números de serie</div>
          <div class="d-flex justify-content-between">
            <div>
              <div class="fw-bold">{{ $serialDisponibles ?? 0 }}</div>
              <small class="text-secondary">Disponibles</small>
            </div>
            <div>
              <div class="fw-bold">{{ $serialApartados ?? 0 }}</div>
              <small class="text-secondary">Apartados</small>
            </div>
            <div>
              <div class="fw-bold">{{ $serialEntregados ?? 0 }}</div>
              <small class="text-secondary">Entregados</small>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
        <div class="card-body">
          <div class="text-secondary small mb-1">Seguimiento financiero</div>
          <p class="mb-1 text-secondary">Pagos reportados hoy: <strong>${{ number_format((float)($ingresosPagosHoy ?? 0),2) }}</strong></p>
          <a href="{{ route('finanzas.index') }}" class="btn btn-outline-primary w-100 btn-sm">Ver panel financiero</a>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-12 col-md-8">
      <div class="card border-0 shadow-sm" style="border-radius:16px;">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="mb-0 fw-bold"><i class="bi bi-bar-chart me-2"></i> Ventas mensuales</h5>
          </div>
          @if($chartLabels && $chartLabels->count() > 0)
            <canvas id="ventasMensuales"></canvas>
          @else
            <div class="alert alert-info mb-0">
              <i class="bi bi-info-circle"></i> No hay datos de ventas mensuales para mostrar aún.
            </div>
          @endif
        </div>
      </div>
    </div>
    <div class="col-12 col-md-4">
      <div class="card border-0 shadow-sm" style="border-radius:16px;">
        <div class="card-body">
          <h5 class="mb-3 fw-bold"><i class="bi bi-pie-chart me-2"></i>Pedidos por estado</h5>
          @if($pedidosPorEstado && $pedidosPorEstado->count() > 0)
            <div class="list-group list-group-flush">
              @foreach($pedidosPorEstado as $estado => $cantidad)
                <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                  <span class="badge 
                    @if($estado === 'entregado' || $estado === 'finalizado') bg-success
                    @elseif($estado === 'entregado_pendiente_pago') bg-warning
                    @elseif($estado === 'en_ruta') bg-primary
                    @elseif($estado === 'asignado') bg-info
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

  <div class="row g-3 mt-3">
    <div class="col-12 col-md-6">
      <div class="card border-0 shadow-sm" style="border-radius:16px;">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="mb-0 fw-bold"><i class="bi bi-cart-check me-2"></i>Ventas recientes</h5>
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
    <div class="col-12 col-md-6">
      <div class="card border-0 shadow-sm" style="border-radius:16px;">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="mb-0 fw-bold"><i class="bi bi-bag me-2"></i>Pedidos recientes</h5>
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
                        <x-status-badge :status="$pedido->estado" />
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

  <div class="row g-3 mt-3">
    <div class="col-12 col-md-6">
      <div class="card border-0 shadow-sm" style="border-radius:16px;">
        <div class="card-body">
          <div class="d-flex alignments-center justify-content-between mb-3">
            <h5 class="mb-0 fw-bold"><i class="bi bi-star me-2"></i>Top productos POS</h5>
            <a href="{{ route('inventario.index') }}" class="btn btn-sm btn-outline-secondary">Inventario</a>
          </div>
          @if($topProductos && $topProductos->count() > 0)
            <div class="table-responsive">
              <table class="table table-sm align-middle mb-0">
                <thead>
                  <tr>
                    <th>Producto</th>
                    <th class="text-center">Unidades</th>
                    <th class="text-end">Monto</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($topProductos as $prod)
                    <tr>
                      <td>{{ $prod->product?->descripcion ?? '—' }}</td>
                      <td class="text-center"><span class="badge text-bg-primary">{{ $prod->unidades }}</span></td>
                      <td class="text-end fw-semibold">${{ number_format((float)$prod->monto, 2) }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @else
            <div class="alert alert-info mb-0">
              <i class="bi bi-info-circle"></i> No hay ventas suficientes para generar el ranking.
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
@if($chartLabels && $chartLabels->count() > 0)
<script type="module">
  import Chart from 'https://cdn.jsdelivr.net/npm/chart.js/+esm'

  const labels = @json($chartLabels);
  const posData = @json($chartPos);
  const pedidosData = @json($chartPedidos);

  const ctx = document.getElementById('ventasMensuales');
  if (ctx) {
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels,
        datasets: [
          {
            label: 'POS',
            data: posData,
            backgroundColor: 'rgba(59, 130, 246, 0.6)',
            borderColor: 'rgba(59, 130, 246, 1)',
            borderWidth: 1.5
          },
          {
            label: 'Pedidos',
            data: pedidosData,
            backgroundColor: 'rgba(16, 185, 129, 0.6)',
            borderColor: 'rgba(16, 185, 129, 1)',
            borderWidth: 1.5
          }
        ]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { position: 'top' }
        },
        scales: {
          y: { beginAtZero: true }
        }
      }
    });
  }
</script>
@endif
@endpush

