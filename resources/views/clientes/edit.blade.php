@extends('layouts.erp')
@section('title','Editar cliente')

@section('content')
<x-flash />
<div class="container" style="max-width: 900px;">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <h3 class="fw-bold mb-0"><i class="bi bi-pencil-square me-2"></i>Editar cliente</h3>
    <a href="{{ route('clientes.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i> Volver</a>
  </div>
  <div class="card border-0 shadow-sm" style="border-radius:16px;">
    <div class="card-body">
      <form method="POST" action="{{ route('clientes.update',$cliente) }}" class="row g-3">
        @csrf @method('PUT')
        <div class="col-12"><label class="form-label">Nombre</label><input name="nombre" class="form-control" value="{{ $cliente->nombre }}" required></div>
        <div class="col-md-4">
          <label class="form-label">¿Es empresa?</label>
          <select name="es_empresa" class="form-select">
            <option value="0" @selected(!$cliente->es_empresa)>No</option>
            <option value="1" @selected($cliente->es_empresa)>Sí</option>
          </select>
        </div>
        <div class="col-md-4"><label class="form-label">Teléfono <span class="text-danger">*</span></label><input name="telefono" class="form-control" value="{{ $cliente->telefono }}" required></div>
        <div class="col-md-4"><label class="form-label">Email <span class="text-danger">*</span></label><input name="email" type="email" class="form-control" value="{{ $cliente->email }}" required></div>
        <div class="col-12"><label class="form-label">Dirección entrega <span class="text-danger">*</span></label><input name="direccion_entrega" class="form-control" value="{{ $cliente->direccion_entrega }}" required></div>
        <div class="col-12 d-flex justify-content-end gap-2">
          <a class="btn btn-light" href="{{ route('clientes.index') }}">Cancelar</a>
          <button class="btn btn-primary"><i class="bi bi-check2-circle"></i> Guardar cambios</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
