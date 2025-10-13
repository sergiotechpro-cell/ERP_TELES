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
    <div class="col-md-4">
      <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
        <div class="card-body">
          <div class="small text-secondary">Costo total de inventario</div>
          <div class="display-6 fw-bold">${{ number_format($inventario ?? 0, 2) }}</div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
        <div class="card-body">
          <div class="small text-secondary">Utilidad proyectada</div>
          <div class="display-6 fw-bold">${{ number_format($utilidadProyectada ?? 0, 2) }}</div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
        <div class="card-body">
          <div class="small text-secondary">Pagos registrados (últimos 7 días)</div>
          <div class="display-6 fw-bold">{{ $pagosUltimos7 ?? 0 }}</div>
        </div>
      </div>
    </div>
  </div>

  {{-- GRÁFICA --}}
  <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
    <div class="card-body">
      <h6 class="fw-bold mb-3"><i class="bi bi-bar-chart-line me-2"></i>Ventas mensuales</h6>
      <canvas id="ventasMensuales" height="120"></canvas>
      <div class="small text-secondary mt-2">
        Se muestran <strong>POS</strong> y <strong>Pedidos</strong>. Línea punteada: <em>utilidad POS</em>.
      </div>
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
                    <th>Pedido</th>
                    <th>Método</th>
                    <th>Monto</th>
                    <th>Fecha</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($pagos as $p)
                    <tr>
                      <td>{{ $p->id }}</td>
                      <td>#{{ $p->order_id }}</td>
                      <td class="text-capitalize">{{ $p->metodo_pago ?? '—' }}</td>
                      <td><strong>${{ number_format($p->monto ?? 0, 2) }}</strong></td>
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
<script type="module">
  import Chart from 'https://cdn.jsdelivr.net/npm/chart.js/+esm';

  const labels  = @json($chartLabels ?? []);
  const dsPOS   = @json($chartPOS ?? []);
  const dsPed   = @json($chartPedidos ?? []);
  const dsUtil  = @json($chartUtil ?? []);

  const ctx = document.getElementById('ventasMensuales');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels,
      datasets: [
        { label: 'POS',     data: dsPOS },
        { label: 'Pedidos', data: dsPed },
        { label: 'Utilidad POS', data: dsUtil, type:'line', borderDash:[6,6], tension:.3 }
      ]
    },
    options: {
      responsive: true,
      plugins: { legend: { position: 'top' } },
      scales: { y: { beginAtZero: true } }
    }
  });
</script>
@endpush
