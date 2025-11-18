@extends('layouts.erp')
@section('title','Detalle de producto')

@section('content')
<x-flash />

<div class="container-fluid">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <h3 class="fw-bold mb-0"><i class="bi bi-box-seam me-2"></i> {{ $producto->descripcion }}</h3>
    <div class="d-flex gap-2">
      <a href="{{ route('inventario.add-stock', $producto) }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Agregar Unidades
      </a>
      <a href="{{ route('inventario.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-12 col-lg-6">
      <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
        <div class="card-body">
          <h6 class="text-secondary">Información</h6>
          <div class="row mt-2">
            <div class="col-6">
              <div class="text-secondary">Costo unitario</div>
              <div class="fw-bold">${{ number_format($producto->costo_unitario,2) }}</div>
            </div>
            <div class="col-6">
              <div class="text-secondary">Precio venta</div>
              <div class="fw-bold">${{ number_format($producto->precio_venta,2) }}</div>
            </div>
          </div>
          <hr>
          <h6 class="text-secondary">Stock por bodega</h6>
          @forelse($producto->warehouses as $w)
            <div class="d-flex justify-content-between py-1">
              <span>{{ $w->nombre }}</span>
              <span class="badge text-bg-light">{{ $w->pivot->stock }}</span>
            </div>
          @empty
            <p class="text-secondary mb-0">Sin stock registrado.</p>
          @endforelse
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-6">
      <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
        <div class="card-body">
          <h6 class="text-secondary">Números de serie</h6>
          @php
            $seriales = $producto->warehouseProducts()->with('serials')->get()->flatMap->serials;
          @endphp
          @if($seriales->isEmpty())
            <x-empty icon="bi-upc-scan" title="Sin números de serie" text="Registra entradas para generar series." />
          @else
            <div class="mb-3">
              <button type="button" class="btn btn-sm btn-outline-primary" onclick="printAllSerials()">
                <i class="bi bi-printer"></i> Imprimir todos
              </button>
              <button type="button" class="btn btn-sm btn-outline-secondary" onclick="printSelectedSerials()">
                <i class="bi bi-printer-fill"></i> Imprimir seleccionados
              </button>
            </div>
            <div class="list-group list-group-flush">
              @foreach($seriales as $sn)
                <div class="list-group-item d-flex align-items-center justify-content-between">
                  <div class="form-check">
                    <input class="form-check-input serial-checkbox" type="checkbox" value="{{ $sn->id }}" id="serial_{{ $sn->id }}">
                    <label class="form-check-label" for="serial_{{ $sn->id }}">
                      <span class="fw-medium">{{ $sn->numero_serie }}</span>
                      <small class="text-secondary ms-2">({{ $sn->estado }})</small>
                    </label>
                  </div>
                  <div class="d-flex gap-2 align-items-center">
                    <a href="{{ route('inventario.print-serial', $sn) }}" target="_blank" class="btn btn-sm btn-outline-primary" title="Imprimir ticket">
                      <i class="bi bi-printer"></i>
                    </a>
                    <span class="badge text-bg-light">ID: {{ $sn->id }}</span>
                  </div>
                </div>
              @endforeach
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function printAllSerials() {
  const checkboxes = document.querySelectorAll('.serial-checkbox');
  checkboxes.forEach(cb => cb.checked = true);
  printSelectedSerials();
}

function printSelectedSerials() {
  const selected = Array.from(document.querySelectorAll('.serial-checkbox:checked'))
    .map(cb => cb.value);
  
  if (selected.length === 0) {
    alert('Selecciona al menos un número de serie para imprimir');
    return;
  }
  
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = '{{ route("inventario.print-serials") }}';
  form.target = '_blank';
  
  const csrf = document.createElement('input');
  csrf.type = 'hidden';
  csrf.name = '_token';
  csrf.value = '{{ csrf_token() }}';
  form.appendChild(csrf);
  
  selected.forEach(id => {
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'serial_ids[]';
    input.value = id;
    form.appendChild(input);
  });
  
  document.body.appendChild(form);
  form.submit();
  document.body.removeChild(form);
}
</script>
@endpush
