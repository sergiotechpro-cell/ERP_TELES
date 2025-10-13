@extends('layouts.erp')
@section('title','Nuevo traspaso')
@section('content')
<x-flash/>
<div class="d-flex justify-content-between mb-3">
  <h3 class="fw-bold"><i class="bi bi-arrow-left-right me-2"></i>Nuevo traspaso</h3>
  <a href="{{ route('traspasos.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i> Volver</a>
</div>
<div class="card border-0 shadow-sm">
  <div class="card-body">
    <form method="POST" action="{{ route('traspasos.store') }}" id="formTraspaso">
      @csrf
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Bodega origen</label>
          <select name="bodega_origen" class="form-select" required>
            <option value="">Selecciona...</option>
            @foreach($bodegas as $b)<option value="{{ $b->id }}">{{ $b->nombre }}</option>@endforeach
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Bodega destino</label>
          <select name="bodega_destino" class="form-select" required>
            <option value="">Selecciona...</option>
            @foreach($bodegas as $b)<option value="{{ $b->id }}">{{ $b->nombre }}</option>@endforeach
          </select>
        </div>
        <div class="col-12">
          <label class="form-label">Nota</label>
          <input name="nota" class="form-control" placeholder="Observaciones">
        </div>
      </div>

      <hr>
      <div class="d-flex justify-content-between mb-2">
        <h5 class="fw-bold">Productos</h5>
        <button type="button" class="btn btn-sm btn-primary" id="addRow"><i class="bi bi-plus-lg"></i> Agregar</button>
      </div>
      <div class="table-responsive">
        <table class="table align-middle" id="itemsTable">
          <thead class="table-light"><tr><th>Producto</th><th>Cantidad</th><th>Seriales (opcional, comma)</th><th class="text-end">—</th></tr></thead>
          <tbody></tbody>
        </table>
      </div>

      <div class="d-flex justify-content-end gap-2">
        <a class="btn btn-light" href="{{ route('traspasos.index') }}">Cancelar</a>
        <button class="btn btn-primary">Enviar traspaso</button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
const productos = @json($productos->map(fn($p)=>['id'=>$p->id,'descripcion'=>$p->descripcion]));
const tbody = document.querySelector('#itemsTable tbody');
function addRow(){
  const tr = document.createElement('tr');
  tr.innerHTML = `
    <td>
      <select class="form-select prod">
        <option value="">Selecciona...</option>
        ${productos.map(p=>`<option value="${p.id}">${p.descripcion}</option>`).join('')}
      </select>
    </td>
    <td><input type="number" class="form-control qty" min="1" value="1"></td>
    <td><input type="text" class="form-control ser" placeholder="TV-ABC,TV-DEF"></td>
    <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger rm"><i class="bi bi-x"></i></button></td>
  `;
  tbody.appendChild(tr);
  tr.querySelector('.rm').onclick = ()=>tr.remove();
}
document.getElementById('addRow').onclick = addRow; addRow();

document.getElementById('formTraspaso').addEventListener('submit', e=>{
  // serializar items
  document.querySelectorAll('input[name^="items["]').forEach(n=>n.remove());
  [...tbody.querySelectorAll('tr')].forEach((tr, idx)=>{
    const p = tr.querySelector('.prod').value;
    const q = tr.querySelector('.qty').value || 1;
    const s = tr.querySelector('.ser').value.split(',').map(x=>x.trim()).filter(Boolean);
    [['product_id',p],['cantidad',q]].forEach(([k,v])=>{
      const i=document.createElement('input');i.type='hidden';i.name=`items[${idx}][${k}]`;i.value=v; e.target.appendChild(i);
    });
    if(s.length){
      const i=document.createElement('input');i.type='hidden';i.name=`items[${idx}][seriales]`;i.value=JSON.stringify(s); e.target.appendChild(i);
    }
  });
});
</script>
@endpush
