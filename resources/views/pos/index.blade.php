@extends('layouts.erp')
@section('title','Punto de Venta')

@section('content')
<x-flash />

<form method="POST" action="{{ route('pos.store') }}" id="posForm">
  @csrf

  <div class="row g-3">
    <div class="col-lg-8">
      <div class="card border-0 shadow-sm" style="border-radius:16px;">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="fw-bold mb-0"><i class="bi bi-shop me-2"></i> Ventas</h5>
            <button type="button" id="addRow" class="btn btn-sm btn-primary">
              <i class="bi bi-plus-lg"></i> Agregar línea
            </button>
          </div>

          <div class="table-responsive">
            <table class="table align-middle" id="itemsTable">
              <thead class="table-light">
                <tr>
                  <th style="width:42%;">Producto</th>
                  <th style="width:14%;">Cantidad</th>
                  <th style="width:18%;">Precio</th>
                  <th style="width:16%;">Subtotal</th>
                  <th style="width:10%;" class="text-end">—</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>

          <input type="hidden" name="subtotal" value="0">
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card border-0 shadow-sm" style="border-radius:16px;">
        <div class="card-body">
          <h6 class="fw-bold">Resumen</h6>
          <div class="d-flex justify-content-between mt-2">
            <span class="text-secondary">Subtotal</span>
            <strong id="subtotal">$0.00</strong>
          </div>
          <hr>
          <div class="d-flex justify-content-between">
            <span class="text-secondary">Total</span>
            <strong id="total">$0.00</strong>
          </div>

          <div class="mt-3">
            <label class="form-label">Cliente (opcional)</label>
            <select name="customer_id" class="form-select">
              <option value="">Público en general</option>
              @foreach($clientes as $c)
                <option value="{{ $c->id }}">{{ $c->nombre }}</option>
              @endforeach
            </select>
          </div>

          <div class="mt-3">
            <label class="form-label">Forma de pago</label>
            <select name="forma_pago" class="form-select" required>
              <option value="efectivo">Efectivo</option>
              <option value="tarjeta">Tarjeta</option>
              <option value="transferencia">Transferencia</option>
            </select>
          </div>

          <button class="btn btn-primary w-100 mt-3">
            <i class="bi bi-cash-coin"></i> Cobrar
          </button>
        </div>
      </div>
    </div>
  </div>
</form>

{{-- ================= VENTAS RECIENTES ================= --}}
<div class="mt-4">
  <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <h5 class="fw-bold mb-0"><i class="bi bi-receipt me-2"></i>Ventas recientes</h5>
    <div class="d-flex gap-3 small text-secondary">
      <span>Hoy: <strong class="text-dark">{{ $conteoHoy ?? 0 }}</strong> ventas</span>
      <span>Total hoy: <strong class="text-dark">
        {{ isset($totalesHoy) ? number_format($totalesHoy,2) : '0.00' }} MXN
      </strong></span>
    </div>
  </div>

  @if(($ventas->count() ?? 0) === 0)
    <x-empty icon="bi-cash-coin" title="Sin ventas registradas" text="Registra tu primera venta en el POS." />
  @else
    <div class="card border-0 shadow-sm" style="border-radius:16px;">
      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Cliente</th>
              <th>Forma de pago</th>
              <th>Status</th>
              <th>Total</th>
              <th>Fecha</th>
            </tr>
          </thead>
          <tbody>
            @foreach($ventas as $v)
              <tr>
                <td>{{ $v->id }}</td>
                <td>{{ $v->customer->nombre ?? 'Público en general' }}</td>
                <td class="text-capitalize">{{ $v->forma_pago }}</td>
                <td>
                  <span class="badge rounded-pill 
                    @if($v->status==='pagada') text-bg-success 
                    @elseif($v->status==='pendiente') text-bg-warning 
                    @else text-bg-secondary @endif">
                    {{ $v->status }}
                  </span>
                </td>
                <td><strong>${{ number_format($v->total, 2) }}</strong></td>
                <td>{{ $v->created_at?->format('d M Y H:i') }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div class="card-body">
        {{ $ventas->withQueryString()->links() }}
      </div>
    </div>
  @endif
</div>
@endsection

@push('scripts')
@php
  // PREPARA EL ARREGLO EN PHP (sin short closures) PARA EVITAR ParseError
  $prodRows = $productos->map(function($p){
      $precio = ($p->price_tier === 'mayoreo' && !empty($p->precio_mayoreo))
        ? $p->precio_mayoreo
        : $p->precio_venta;
      return [
        'id' => $p->id,
        'descripcion' => $p->descripcion,
        'precio' => (float) $precio,
        'costo'  => (float) $p->costo_unitario,
      ];
  })->values();
@endphp

<script>
  // Datos listos para JS
  const productos = @json($prodRows);

  const tbody     = document.querySelector('#itemsTable tbody');
  const addRowBtn = document.getElementById('addRow');
  const subtotalEl= document.getElementById('subtotal');
  const totalEl   = document.getElementById('total');

  function money(n){
    return new Intl.NumberFormat('es-MX',{style:'currency',currency:'MXN'}).format(Number(n||0));
  }

  function calc(){
    let sum = 0;
    [...tbody.querySelectorAll('tr')].forEach(tr=>{
      const qty   = Number(tr.querySelector('.qty').value||1);
      const price = Number(tr.querySelector('.price').value||0);
      const sub   = qty * price;
      sum += sub;
      tr.querySelector('.sub').innerText = money(sub);
    });
    subtotalEl.innerText = money(sum);
    totalEl.innerText    = money(sum);
    document.querySelector('input[name="subtotal"]').value = sum.toFixed(2);
  }

  function optionsProductos(){
    return productos.map(p => `<option value="${p.id}" data-price="${p.precio}">${p.descripcion}</option>`).join('');
  }

  function addRow(preset=null){
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>
        <select class="form-select prod">
          <option value="">Selecciona...</option>
          ${optionsProductos()}
        </select>
      </td>
      <td>
        <input type="number" class="form-control qty" min="1" value="${preset?.qty ?? 1}">
      </td>
      <td>
        <input type="number" class="form-control price" step="0.01" value="${preset?.price ?? 0}">
      </td>
      <td class="sub fw-semibold">$0.00</td>
      <td class="text-end">
        <button type="button" class="btn btn-sm btn-outline-danger rm"><i class="bi bi-x-lg"></i></button>
      </td>
    `;
    tbody.appendChild(tr);

    const prodSel = tr.querySelector('.prod');
    const qtyI    = tr.querySelector('.qty');
    const priceI  = tr.querySelector('.price');

    prodSel.addEventListener('change', ()=>{
      const opt = prodSel.selectedOptions[0];
      if(opt && opt.dataset.price){
        priceI.value = opt.dataset.price;
        calc();
      }
    });
    qtyI.addEventListener('input', calc);
    priceI.addEventListener('input', calc);
    tr.querySelector('.rm').addEventListener('click', ()=>{ tr.remove(); calc(); });

    if(preset?.id){
      prodSel.value = String(preset.id);
      priceI.value  = Number(preset.price||0);
    }
    calc();
  }

  addRowBtn.addEventListener('click', ()=>addRow());
  addRow(); // una fila por defecto

  // Serializa items al enviar
  document.getElementById('posForm').addEventListener('submit', (e)=>{
    // limpiar previos
    [...document.querySelectorAll('input[name^="items["]')].forEach(n=>n.remove());

    [...tbody.querySelectorAll('tr')].forEach((tr, idx)=>{
      const pid = tr.querySelector('.prod').value;
      const qty = tr.querySelector('.qty').value;
      const prc = tr.querySelector('.price').value;
      if(!pid) return;

      const f = e.target;
      [['product_id',pid],['cantidad',qty],['precio_unitario',prc]]
        .forEach(([k,v])=>{
          const i = document.createElement('input');
          i.type='hidden'; i.name=`items[${idx}][${k}]`; i.value=v;
          f.appendChild(i);
        });
    });
  });
</script>
@endpush
