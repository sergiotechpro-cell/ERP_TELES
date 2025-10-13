@extends('layouts.erp')
@section('title','Nuevo pedido')

@section('content')
<x-flash />

<div class="container" style="max-width: 1100px;">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <h3 class="fw-bold mb-0"><i class="bi bi-plus-square me-2"></i>Nuevo pedido</h3>
    <a href="{{ route('pedidos.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i> Volver</a>
  </div>

  @if ($errors->any())
    <div class="alert alert-danger">
      <div class="fw-semibold mb-2">Revisa los campos:</div>
      <ul class="mb-0">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="card border-0 shadow-sm" style="border-radius:16px;">
    <div class="card-body">
      <form method="POST" action="{{ route('pedidos.store') }}" id="pedidoForm">
        @csrf

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Cliente</label>
            <select name="cliente_id" class="form-select" required>
              <option value="">Selecciona...</option>
              @foreach($clientes as $c)
                <option value="{{ $c->id }}">
                  {{ $c->nombre }} @if($c->email) ({{ $c->email }}) @endif
                </option>
              @endforeach
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label">Dirección de entrega</label>
            <input name="direccion_entrega" class="form-control" required placeholder="Calle, número, colonia, ciudad">
          </div>

          <div class="col-md-4">
            <label class="form-label">Km estimados</label>
            <input id="km" type="number" min="0" step="0.1" class="form-control" value="0">
            <small class="text-secondary">Banderazo 10km = $100; + $10 por km adicional.</small>
          </div>
          <div class="col-md-4">
            <label class="form-label">Costo de envío</label>
            <input name="costo_envio" id="costo_envio" type="number" step="0.01" class="form-control" readonly value="0">
          </div>
          <div class="col-md-4 d-flex align-items-end">
            <button type="button" class="btn btn-outline-primary w-100" id="btnCalcEnvio">
              <i class="bi bi-geo-alt"></i> Calcular envío
            </button>
          </div>
        </div>

        <hr class="my-4">

        <div class="d-flex align-items-center justify-content-between mb-2">
          <h5 class="mb-0 fw-bold"><i class="bi bi-cart-plus me-2"></i>Productos</h5>
          <button class="btn btn-sm btn-primary" type="button" id="addRow">
            <i class="bi bi-plus-lg"></i> Agregar línea
          </button>
        </div>

        <div class="table-responsive">
          <table class="table align-middle" id="itemsTable">
            <thead class="table-light">
              <tr>
                <th style="width: 30%;">Producto</th>
                <th style="width: 20%;">Bodega</th>
                <th style="width: 12%;">Cantidad</th>
                <th style="width: 16%;">Precio unit.</th>
                <th style="width: 16%;">Costo unit.</th>
                <th style="width: 6%;" class="text-end">—</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-3">
          <a href="{{ route('pedidos.index') }}" class="btn btn-light">Cancelar</a>
          <button class="btn btn-primary"><i class="bi bi-check2-circle"></i> Guardar pedido</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  // Arrays ya preparados en el controlador:
  const productos = @json($prodRows);
  const bodegas   = @json($bodRows);

  const tbody     = document.querySelector('#itemsTable tbody');
  const addRowBtn = document.getElementById('addRow');

  function optionList(arr, value = 'id', label = 'nombre') {
    return arr.map(o => `<option value="${o[value]}">${o[label]}</option>`).join('');
  }

  function addRow(preset = null) {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>
        <select class="form-select prod" required>
          <option value="">Selecciona...</option>
          ${productos.map(p => `<option value="${p.id}" data-precio="${p.precio}" data-costo="${p.costo}">${p.descripcion}</option>`).join('')}
        </select>
      </td>
      <td>
        <select class="form-select bod" required>
          <option value="">Selecciona...</option>
          ${optionList(bodegas)}
        </select>
      </td>
      <td><input type="number" class="form-control qty" min="1" value="1" required></td>
      <td><input type="number" class="form-control precio" step="0.01" required></td>
      <td><input type="number" class="form-control costo" step="0.01" required></td>
      <td class="text-end">
        <button type="button" class="btn btn-sm btn-outline-danger rm"><i class="bi bi-x-lg"></i></button>
      </td>
    `;
    tbody.appendChild(tr);

    const prodSel = tr.querySelector('.prod');
    const precio  = tr.querySelector('.precio');
    const costo   = tr.querySelector('.costo');

    prodSel.addEventListener('change', () => {
      const opt = prodSel.selectedOptions[0];
      if (!opt) return;
      precio.value = opt.dataset.precio || 0;
      costo.value  = opt.dataset.costo  || 0;
    });

    tr.querySelector('.rm').addEventListener('click', () => tr.remove());

    if (preset) {
      prodSel.value = preset.id;
      precio.value  = preset.precio;
      costo.value   = preset.costo;
    }
  }

  addRowBtn.addEventListener('click', () => addRow());
  // Agrega una fila por defecto
  addRow();

  // Cálculo de envío: banderazo 10 km = $100; + $10 por km adicional
  function calcularEnvio(km) {
    km = Number(km || 0);
    if (km <= 10) return 100;
    return 100 + Math.ceil(km - 10) * 10;
  }
  document.getElementById('btnCalcEnvio').addEventListener('click', () => {
    document.getElementById('costo_envio').value =
      calcularEnvio(document.getElementById('km').value);
  });

  // Serializa "productos[...]" al enviar
  document.getElementById('pedidoForm').addEventListener('submit', (e) => {
    // limpia inputs ocultos previos
    document.querySelectorAll('input[name^="productos["]').forEach(n => n.remove());

    [...tbody.querySelectorAll('tr')].forEach((tr, idx) => {
      const prod = tr.querySelector('.prod').value;
      const bod  = tr.querySelector('.bod').value;
      const qty  = tr.querySelector('.qty').value;
      const pre  = tr.querySelector('.precio').value;
      const cos  = tr.querySelector('.costo').value;

      if (!prod || !bod) return;

      const f = e.target;
      [
        ['id', prod],
        ['bodega_id', bod],
        ['cantidad', qty],
        ['precio', pre],
        ['costo',  cos],
      ].forEach(([k, v]) => {
        const i = document.createElement('input');
        i.type  = 'hidden';
        i.name  = `productos[${idx}][${k}]`;
        i.value = v;
        f.appendChild(i);
      });
    });
  });
</script>
@endpush
