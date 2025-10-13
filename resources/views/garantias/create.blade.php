@extends('layouts.erp')
@section('title','Nueva garantía')

@section('content')
<x-flash />

<div class="container" style="max-width: 920px;">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <h3 class="fw-bold mb-0"><i class="bi bi-shield-check me-2"></i>Nueva garantía</h3>
    <a href="{{ route('garantias.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i> Volver</a>
  </div>

  <div class="card border-0 shadow-sm" style="border-radius:16px;">
    <div class="card-body">
      <form method="POST" action="{{ route('garantias.store') }}" id="claimForm" class="row g-3">
        @csrf

        <div class="col-md-6">
          <label class="form-label">Pedido</label>
          <select name="order_id" class="form-select" required>
            <option value="">Selecciona pedido...</option>
            @foreach($pedidos as $p)
              <option value="{{ $p->id }}" @selected(old('order_id')==$p->id)>
                #{{ $p->id }} — {{ $p->direccion_entrega }} — {{ $p->created_at->format('Y-m-d') }}
              </option>
            @endforeach
          </select>
          @error('order_id') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6">
          <label class="form-label">Producto</label>
          <select name="product_id" class="form-select" id="productSel" required>
            <option value="">Selecciona producto...</option>
            @foreach($productos as $pr)
              <option value="{{ $pr->id }}" @selected(old('product_id')==$pr->id)>{{ $pr->descripcion }}</option>
            @endforeach
          </select>
          @error('product_id') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6">
          <label class="form-label">Número de serie (ID)</label>
          {{-- Este select manda un ID numérico => evita errores de cast --}}
          <select name="serial_number_id" class="form-select" id="serialSel">
            <option value="">Selecciona número de serie...</option>
            {{-- Se poblará vía JS al elegir producto --}}
          </select>
          @error('serial_number_id') <div class="text-danger small">{{ $message }}</div> @enderror
          <div class="form-text">Si no lo tienes, puedes escribir el texto del número de serie abajo.</div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Número de serie (texto)</label>
          <input name="numero_serie" class="form-control" value="{{ old('numero_serie') }}" placeholder="TV-ABC123456">
          @error('numero_serie') <div class="text-danger small">{{ $message }}</div> @enderror
          <div class="form-text">Se usará solo si no seleccionas el ID arriba.</div>
        </div>

        <div class="col-md-4">
          <label class="form-label">Fecha de compra</label>
          <input type="date" name="fecha_compra"
                 class="form-control"
                 value="{{ old('fecha_compra', now()->toDateString()) }}" required>
          @error('fecha_compra') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-8">
          <label class="form-label">Motivo</label>
          <input name="motivo" class="form-control" value="{{ old('motivo') }}" required
                 placeholder="Falla de encendido, líneas en pantalla, etc.">
          @error('motivo') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

        <div class="col-12">
          <label class="form-label">Condición del producto</label>
          <input name="condicion" class="form-control" value="{{ old('condicion') }}"
                 placeholder="Display estrellado (no aplica), chasis coincide, etc.">
          @error('condicion') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

        <div class="col-12 d-flex justify-content-end gap-2">
          <a href="{{ route('garantias.index') }}" class="btn btn-light">Cancelar</a>
          <button class="btn btn-primary"><i class="bi bi-check2-circle"></i> Registrar garantía</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  // Carga perezosa de números de serie por producto (evita listas enormes)
  const serialSel  = document.getElementById('serialSel');
  const productSel = document.getElementById('productSel');

  productSel?.addEventListener('change', async (e) => {
    const pid = e.target.value;
    serialSel.innerHTML = `<option value="">Cargando...</option>`;
    if (!pid) { serialSel.innerHTML = `<option value="">Selecciona número de serie...</option>`; return; }

    try {
      // Endpoint sencillo: /api/seriales?product_id=XX
      const res = await fetch(`/api/seriales?product_id=${encodeURIComponent(pid)}`);
      const data = await res.json();

      const opts = ['<option value="">Selecciona número de serie...</option>']
        .concat(data.map(sn => `<option value="${sn.id}">${sn.numero_serie}</option>`));

      serialSel.innerHTML = opts.join('');
    } catch(err){
      serialSel.innerHTML = `<option value="">Error al cargar</option>`;
      console.error(err);
    }
  });
</script>
@endpush
