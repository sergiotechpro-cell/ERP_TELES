@extends('layouts.erp')
@section('title','Nuevo producto')

@section('content')
<x-flash />

<div class="container" style="max-width: 980px;">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <h3 class="fw-bold mb-0"><i class="bi bi-plus-square me-2"></i>Nuevo producto</h3>
    <a href="{{ route('inventario.index') }}" class="btn btn-light">
      <i class="bi bi-arrow-left"></i> Volver
    </a>
  </div>

  <div class="alert alert-info border-0 shadow-sm d-flex align-items-center" role="alert" style="border-radius:12px;">
    <i class="bi bi-info-circle me-2"></i>
    <div class="flex-fill">
      <strong>Tip:</strong> Puedes crear el producto sin stock inicial y agregarlo después, o asignar stock a múltiples almacenes desde aquí.
    </div>
  </div>

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
          <label class="form-label">Precio mayoreo <span class="text-danger">*</span></label>
          <input name="precio_mayoreo" type="number" step="0.01" class="form-control" required>
        </div>

        <div class="col-md-4">
          <label class="form-label">Lista de precios <span class="text-danger">*</span></label>
          <select name="price_tier" class="form-select" required>
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

        <div class="col-12 d-flex justify-content-between align-items-center">
          <div>
            <label class="form-check-label d-flex align-items-center">
              <input type="checkbox" name="crear_otro" value="1" class="form-check-input me-2">
              <span class="small text-secondary">Crear otro producto después</span>
            </label>
          </div>
          <div class="d-flex gap-2">
            <a href="{{ route('inventario.index') }}" class="btn btn-light">Cancelar</a>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check2-circle"></i> Guardar producto
            </button>
          </div>
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
  let rowIndex = 0;
  
  function addWarehouseRow() {
    if (warehouses.length === 0) {
      alert('No hay almacenes disponibles. Ve al módulo de Bodegas para crear uno.');
      return;
    }
    
    const div = document.createElement('div');
    div.className = 'row g-2 mb-2 align-items-end warehouse-row';
    div.setAttribute('data-row-index', rowIndex++);
    div.innerHTML = `
      <div class="col-md-6">
        <label class="form-label small">Almacén</label>
        <select name="almacenes[${div.getAttribute('data-row-index')}][warehouse_id]" class="form-select warehouse-select">
          <option value="">Selecciona almacén...</option>
          ${warehouses.map(w => `<option value="${w.id}">${w.nombre}</option>`).join('')}
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label small">Cantidad</label>
        <input name="almacenes[${div.getAttribute('data-row-index')}][cantidad]" type="number" min="1" class="form-control warehouse-qty" placeholder="0">
      </div>
      <div class="col-md-2">
        <button type="button" class="btn btn-sm btn-outline-danger w-100 remove-warehouse" title="Eliminar">
          <i class="bi bi-trash"></i>
        </button>
      </div>
    `;
    container.appendChild(div);
    
    div.querySelector('.remove-warehouse').addEventListener('click', () => div.remove());
  }
  
  document.getElementById('addWarehouseRow').addEventListener('click', addWarehouseRow);
  
  // Filtrar filas vacías antes de enviar el formulario
  document.querySelector('form').addEventListener('submit', function(e) {
    const rows = container.querySelectorAll('.warehouse-row');
    let hasValidRow = false;
    
    rows.forEach(row => {
      const select = row.querySelector('.warehouse-select');
      const qty = row.querySelector('.warehouse-qty');
      const warehouseId = select?.value;
      const cantidad = qty?.value;
      
      // Si la fila está vacía o incompleta, eliminarla
      if (!warehouseId || !cantidad || parseInt(cantidad) <= 0) {
        row.remove();
      } else {
        hasValidRow = true;
      }
    });
    
    // Si no hay bodegas pero el usuario quiere crear el producto sin stock, está bien
    // No bloqueamos el envío del formulario
  });
</script>
@endpush
