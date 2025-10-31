@extends('layouts.erp')
@section('title','Nuevo producto')

@section('content')
<x-flash />

<div class="container" style="max-width: 980px;">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <h3 class="fw-bold mb-0"><i class="bi bi-plus-square me-2"></i>Nuevo producto</h3>
    <div class="d-flex gap-2">
      <a href="{{ route('inventario.warehouses.create') }}" class="btn btn-light">
        <i class="bi bi-building-add"></i> Nueva bodega
      </a>
      <a href="{{ route('inventario.index') }}" class="btn btn-light">
        <i class="bi bi-arrow-left"></i> Volver
      </a>
    </div>
  </div>

  @if($bodegas->isEmpty())
    <div class="alert alert-warning d-flex align-items-center" role="alert">
      <i class="bi bi-exclamation-triangle-fill me-2"></i>
      <div>
        No hay bodegas aún. Crea al menos una bodega para cargar stock inicial. 
        <a href="{{ route('inventario.warehouses.create') }}" class="alert-link">Crear bodega</a>.
      </div>
    </div>
  @endif

  <div class="card border-0 shadow-sm" style="border-radius:16px;">
    <div class="card-body">
      <form method="POST" action="{{ route('inventario.store') }}" class="row g-3">
        @csrf

        <div class="col-12">
          <label class="form-label">Descripción</label>
          <input name="descripcion" class="form-control" required placeholder="TV 55'' 4K UHD">
        </div>

        <div class="col-md-4">
          <label class="form-label">Costo unitario</label>
          <input name="costo_unitario" type="number" step="0.01" class="form-control" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Precio de venta (menudeo)</label>
          <input name="precio_venta" type="number" step="0.01" class="form-control" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Precio mayoreo (opcional)</label>
          <input name="precio_mayoreo" type="number" step="0.01" class="form-control">
        </div>

        <div class="col-md-4">
          <label class="form-label">Lista de precios</label>
          <select name="price_tier" class="form-select">
            <option value="menudeo">Menudeo</option>
            <option value="mayoreo">Mayoreo</option>
          </select>
        </div>

        <div class="col-12">
          <hr>
          <div class="d-flex align-items-center justify-content-between mb-3">
            <h6 class="mb-0 fw-bold"><i class="bi bi-boxes me-2"></i>Inventario por almacén</h6>
            <button type="button" class="btn btn-sm btn-primary" id="addWarehouseRow">
              <i class="bi bi-plus-lg"></i> Agregar almacén
            </button>
          </div>
          
          <div id="warehousesContainer">
            <!-- Las filas de almacenes se agregarán aquí dinámicamente -->
          </div>
          
          <small class="text-secondary">
            <i class="bi bi-info-circle"></i> Puedes agregar inventario a múltiples almacenes. Si no agregas ningún almacén, el producto se creará sin stock inicial.
          </small>
        </div>

        <div class="col-12 d-flex justify-content-end gap-2">
          <a href="{{ route('inventario.index') }}" class="btn btn-light">Cancelar</a>
          <button class="btn btn-primary">
            <i class="bi bi-check2-circle"></i> Guardar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  const warehouses = @json($bodegas);
  const container = document.getElementById('warehousesContainer');
  
  function addWarehouseRow() {
    const div = document.createElement('div');
    div.className = 'row g-2 mb-2 align-items-end warehouse-row';
    div.innerHTML = `
      <div class="col-md-6">
        <label class="form-label small">Almacén</label>
        <select name="almacenes[][warehouse_id]" class="form-select" required>
          <option value="">Selecciona almacén...</option>
          ${warehouses.map(w => `<option value="${w.id}">${w.nombre}</option>`).join('')}
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label small">Cantidad</label>
        <input name="almacenes[][cantidad]" type="number" min="0" class="form-control" value="0" required>
      </div>
      <div class="col-md-2">
        <button type="button" class="btn btn-sm btn-outline-danger w-100 remove-warehouse">
          <i class="bi bi-trash"></i>
        </button>
      </div>
    `;
    container.appendChild(div);
    
    div.querySelector('.remove-warehouse').addEventListener('click', () => div.remove());
  }
  
  document.getElementById('addWarehouseRow').addEventListener('click', addWarehouseRow);
</script>
@endpush
