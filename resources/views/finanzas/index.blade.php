@extends('layouts.erp')
@section('title','Finanzas')

@section('content')
<x-flash />

<div class="container-fluid">

  <div class="d-flex align-items-center justify-content-between mb-4">
    <h2 class="fw-bold mb-0"><i class="bi bi-graph-up-arrow me-2"></i> Finanzas</h2>
    <a href="{{ route('finanzas.cierre-diario') }}" class="btn btn-outline-primary">
      <i class="bi bi-journal-check"></i> Cierre diario
    </a>
  </div>

  {{-- KPI CARDS --}}
  <div class="row g-3 mb-3">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
        <div class="card-body">
          <div class="small text-secondary mb-1">
            <i class="bi bi-box-seam me-1"></i> Costo total de inventario
          </div>
          <div class="display-6 fw-bold">${{ number_format((float)($inventario ?? 0), 2) }}</div>
          @if(($inventario ?? 0) == 0)
          <div class="small text-secondary mt-2">
            <i class="bi bi-info-circle"></i> Sin productos en stock
          </div>
          @endif
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
        <div class="card-body">
          <div class="small text-secondary mb-1">
            <i class="bi bi-graph-up me-1"></i> Utilidad proyectada
          </div>
          <div class="display-6 fw-bold {{ ($utilidadProyectada ?? 0) > 0 ? 'text-success' : '' }}">${{ number_format((float)($utilidadProyectada ?? 0), 2) }}</div>
          @if(($utilidadProyectada ?? 0) == 0)
          <div class="small text-secondary mt-2">
            <i class="bi bi-info-circle"></i> Sin productos en stock
          </div>
          @endif
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm h-100" style="border-radius:16px; border-left: 4px solid #10b981;">
        <div class="card-body">
          <div class="small text-secondary">
            <i class="bi bi-cash-stack text-success me-1"></i> Efectivo hoy
          </div>
          <div class="display-6 fw-bold text-success">${{ number_format((float)($totalEfectivoHoy ?? 0), 2) }}</div>
          <div class="small text-secondary mt-2">
            {{ (int)($countEfectivoHoy ?? 0) }} pago(s) · 7 días: ${{ number_format((float)($totalEfectivo7Dias ?? 0), 2) }}
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm h-100" style="border-radius:16px; border-left: 4px solid #3b82f6;">
        <div class="card-body">
          <div class="small text-secondary">
            <i class="bi bi-bank text-primary me-1"></i> Transferencia hoy
          </div>
          <div class="display-6 fw-bold text-primary">${{ number_format((float)($totalTransferenciaHoy ?? 0), 2) }}</div>
          <div class="small text-secondary mt-2">
            {{ (int)($countTransferenciaHoy ?? 0) }} pago(s) · 7 días: ${{ number_format((float)($totalTransferencia7Dias ?? 0), 2) }}
          </div>
        </div>
      </div>
    </div>
  </div>
  
  {{-- Total General --}}
  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <div class="card border-0 shadow-sm h-100" style="border-radius:16px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="card-body text-white">
          <div class="small opacity-75">Total ingresos hoy</div>
          <div class="display-6 fw-bold">${{ number_format((float)($totalPagosHoy ?? 0), 2) }}</div>
          <div class="small opacity-75 mt-2">
            @if($totalPagosHoy > 0)
              <i class="bi bi-arrow-up"></i> {{ (int)($countEfectivoHoy ?? 0) + (int)($countTransferenciaHoy ?? 0) }} pago(s)
            @else
              Sin pagos hoy
            @endif
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm h-100" style="border-radius:16px; background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
        <div class="card-body text-white">
          <div class="small opacity-75">Últimos 7 días</div>
          <div class="display-6 fw-bold">${{ number_format((float)($totalPagos7Dias ?? 0), 2) }}</div>
          <div class="small opacity-75 mt-2">
            {{ (int)($pagosUltimos7 ?? 0) }} pago(s) registrado(s)
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm h-100" style="border-radius:16px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
        <div class="card-body text-white">
          <div class="small opacity-75">Este mes</div>
          <div class="display-6 fw-bold">${{ number_format((float)($totalPagosEsteMes ?? 0), 2) }}</div>
          <div class="small opacity-75 mt-2">
            {{ now()->isoFormat('MMMM YYYY') }}
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- GRÁFICA --}}
  <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
    <div class="card-body">
      <h6 class="fw-bold mb-3"><i class="bi bi-bar-chart-line me-2"></i>Ventas mensuales</h6>
      @if(($chartLabels && $chartLabels->count() > 0) || ($chartPOS && $chartPOS->count() > 0) || ($chartPedidos && $chartPedidos->count() > 0))
        <canvas id="ventasMensuales" height="120"></canvas>
        <div class="small text-secondary mt-2">
          Se muestran <strong>POS</strong> y <strong>Pedidos</strong>. Línea punteada: <em>utilidad POS</em>.
        </div>
      @else
        <div class="alert alert-info mb-0">
          <i class="bi bi-info-circle"></i> No hay datos de ventas mensuales para mostrar aún. Los datos aparecerán cuando se registren ventas POS o pedidos.
        </div>
      @endif
    </div>
  </div>

  <div class="row g-3">
    {{-- PAGOS RECIENTES --}}
    <div class="col-lg-7">
      <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
        <div class="card-body">
          <h6 class="fw-bold mb-3"><i class="bi bi-credit-card me-2"></i>Pagos recientes</h6>
          @if(($pagos->count() ?? 0) === 0)
            <x-empty icon="bi-receipt" title="Sin pagos" text="Aún no hay pagos registrados." />
          @else
            <div class="table-responsive">
              <table class="table align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th>#</th>
                    <th>Tipo</th>
                    <th>Referencia</th>
                    <th>Método</th>
                    <th>Monto</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($pagos as $p)
                    <tr>
                      <td>{{ $p->id }}</td>
                      <td>
                        @if($p->sale_id)
                          <span class="badge text-bg-info">Venta POS</span>
                        @elseif($p->order_id)
                          <span class="badge text-bg-primary">Pedido</span>
                        @else
                          <span class="badge text-bg-secondary">—</span>
                        @endif
                      </td>
                      <td>
                        @if($p->sale_id)
                          <a href="{{ route('pos.show', $p->sale_id) }}">Venta #{{ $p->sale_id }}</a>
                        @elseif($p->order_id)
                          <a href="{{ route('pedidos.show', $p->order_id) }}">Pedido #{{ $p->order_id }}</a>
                        @else
                          —
                        @endif
                      </td>
                      <td>
                        @if($p->forma_pago === 'transferencia')
                          <span class="badge text-bg-primary">
                            <i class="bi bi-bank me-1"></i> Transferencia
                          </span>
                        @elseif($p->forma_pago === 'efectivo')
                          <span class="badge text-bg-success">
                            <i class="bi bi-cash-stack me-1"></i> Efectivo
                          </span>
                        @elseif($p->forma_pago === 'tarjeta')
                          <span class="badge text-bg-info">
                            <i class="bi bi-credit-card me-1"></i> Tarjeta
                          </span>
                        @else
                          <span class="badge text-bg-secondary">{{ $p->forma_pago ?? '—' }}</span>
                        @endif
                      </td>
                      <td>
                        <strong class="@if($p->forma_pago === 'transferencia') text-primary @elseif($p->forma_pago === 'efectivo') text-success @endif">
                          ${{ number_format($p->monto ?? 0, 2) }}
                        </strong>
                      </td>
                      <td>
                        <span class="badge rounded-pill
                          @if($p->estado==='completado' || $p->estado==='depositado') text-bg-success
                          @elseif($p->estado==='en_caja') text-bg-info
                          @elseif($p->estado==='en_ruta') text-bg-warning
                          @else text-bg-secondary
                          @endif">
                          @if($p->estado === 'completado')
                            ✅ Completado
                          @elseif($p->estado === 'en_ruta')
                            🚚 En ruta
                          @else
                            {{ ucfirst($p->estado ?? '—') }}
                          @endif
                        </span>
                      </td>
                      <td>{{ $p->created_at?->format('d M Y H:i') }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            <div class="mt-3">
              {{ $pagos->withQueryString()->links() }}
            </div>
          @endif
        </div>
      </div>
    </div>

    {{-- VENTAS POS RECIENTES (para verificar datos) --}}
    <div class="col-lg-5">
      <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
        <div class="card-body">
          <h6 class="fw-bold mb-3"><i class="bi bi-shop me-2"></i>Últimas ventas POS</h6>
          @if(($ventasPosRecientes->count() ?? 0) === 0)
            <x-empty icon="bi-bag" title="Sin ventas POS" text="Registra una venta en Punto de Venta." />
          @else
            <ul class="list-group list-group-flush">
              @foreach($ventasPosRecientes as $s)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  <span>#{{ $s->id }} · {{ $s->created_at->format('d M Y H:i') }}</span>
                  <strong>${{ number_format($s->total,2) }}</strong>
                </li>
              @endforeach
            </ul>
          @endif
        </div>
      </div>
    </div>
  </div>

</div>
@endsection

@push('scripts')
@if(($chartLabels && $chartLabels->count() > 0) || ($chartPOS && $chartPOS->count() > 0) || ($chartPedidos && $chartPedidos->count() > 0))
<script type="module">
  import Chart from 'https://cdn.jsdelivr.net/npm/chart.js/+esm';

  const labels  = @json($chartLabels ?? []);
  const dsPOS   = @json($chartPOS ?? []);
  const dsPed   = @json($chartPedidos ?? []);
  const dsUtil  = @json($chartUtil ?? []);

  const ctx = document.getElementById('ventasMensuales');
  if (ctx) {
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels,
        datasets: [
          { label: 'POS',     data: dsPOS, backgroundColor: 'rgba(59, 130, 246, 0.5)', borderColor: 'rgba(59, 130, 246, 1)' },
          { label: 'Pedidos', data: dsPed, backgroundColor: 'rgba(16, 185, 129, 0.5)', borderColor: 'rgba(16, 185, 129, 1)' },
          { label: 'Utilidad POS', data: dsUtil, type:'line', borderDash:[6,6], tension:.3, borderColor: 'rgba(139, 92, 246, 1)' }
        ]
      },
      options: {
        responsive: true,
        plugins: { legend: { position: 'top' } },
        scales: { y: { beginAtZero: true } }
      }
    });
  }
</script>
@endif
@endpush
