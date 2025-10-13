@extends('layouts.erp')
@section('title','Dashboard')

@section('content')
<x-flash />

<div class="container-fluid">
  <!-- KPI cards -->
  <div class="row g-3">
    <div class="col-12 col-md-4">
      <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="text-secondary">Ventas hoy</div>
              <div class="fs-3 fw-bold">${{ number_format($ventasHoy ?? 0, 2) }}</div>
            </div>
            <div class="display-6 text-primary"><i class="bi bi-cash-coin"></i></div>
          </div>
          <small class="text-secondary">Total de pagos registrados en el día</small>
        </div>
      </div>
    </div>

    <div class="col-12 col-md-4">
      <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="text-secondary">Pedidos</div>
              <div class="fs-3 fw-bold">{{ $pedidos ?? 0 }}</div>
            </div>
            <div class="display-6 text-primary"><i class="bi bi-bag-check"></i></div>
          </div>
          <small class="text-secondary">Pedidos totales</small>
        </div>
      </div>
    </div>

    <div class="col-12 col-md-4">
      <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="text-secondary">Productos</div>
              <div class="fs-3 fw-bold">{{ $inventario ?? 0 }}</div>
            </div>
            <div class="display-6 text-primary"><i class="bi bi-box-seam"></i></div>
          </div>
          <small class="text-secondary">SKUs registrados</small>
        </div>
      </div>
    </div>
  </div>

  <!-- Chart ventas mensuales -->
  <div class="card border-0 shadow-sm mt-4" style="border-radius:16px;">
    <div class="card-body">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="mb-0 fw-bold"><i class="bi bi-bar-chart me-2"></i> Ventas mensuales</h5>
      </div>
      <canvas id="ventasMensuales"></canvas>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script type="module">
  import Chart from 'https://cdn.jsdelivr.net/npm/chart.js/+esm'

  const rows = @json(($ventasMensuales ?? collect())->map(fn($r)=>[
    'label'=>\Carbon\Carbon::parse($r->mes)->isoFormat('MMM YYYY'),
    'total'=>(float)$r->total
  ]));

  const ctx = document.getElementById('ventasMensuales');
  new Chart(ctx,{
    type:'bar',
    data:{
      labels: rows.map(r=>r.label),
      datasets:[{label:'Ventas', data: rows.map(r=>r.total)}]
    },
    options:{
      responsive:true,
      plugins:{ legend:{ display:false }},
      scales:{ y:{ beginAtZero:true } }
    }
  });
</script>
@endpush
