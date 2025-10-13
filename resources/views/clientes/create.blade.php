@extends('layouts.erp')
@section('title','Nuevo cliente')

@section('content')
<x-flash />
<div class="container" style="max-width: 900px;">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <h3 class="fw-bold mb-0"><i class="bi bi-person-plus me-2"></i>Nuevo cliente</h3>
    <a href="{{ route('clientes.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i> Volver</a>
  </div>
  <div class="card border-0 shadow-sm" style="border-radius:16px;">
    <div class="card-body">
      <form method="POST" action="{{ route('clientes.store') }}" class="row g-3">
        @csrf
        <div class="col-12">
          <label class="form-label">Nombre / Razón social</label>
          <input name="nombre" class="form-control" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">¿Es empresa?</label>
          <select name="es_empresa" class="form-select">
            <option value="0">No</option><option value="1">Sí</option>
          </select>
        </div>
        <div class="col-md-4"><label class="form-label">Teléfono</label><input name="telefono" class="form-control"></div>
        <div class="col-md-4"><label class="form-label">Email</label><input name="email" type="email" class="form-control"></div>
        <div class="col-12"><label class="form-label">Dirección entrega</label><input name="direccion_entrega" class="form-control"></div>
        <div class="col-12 d-flex justify-content-end gap-2">
          <a class="btn btn-light" href="{{ route('clientes.index') }}">Cancelar</a>
          <button class="btn btn-primary"><i class="bi bi-check2-circle"></i> Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
