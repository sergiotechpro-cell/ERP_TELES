@extends('layouts.erp')
@section('title','Editar producto')

@section('content')
<x-flash />

<div class="container" style="max-width: 920px;">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <h3 class="fw-bold mb-0"><i class="bi bi-pencil-square me-2"></i>Editar producto</h3>
    <a href="{{ route('inventario.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i> Volver</a>
  </div>

  <div class="card border-0 shadow-sm" style="border-radius:16px;">
    <div class="card-body">
      <form method="POST" action="{{ route('inventario.update',$inventario) }}" class="row g-3">
        @csrf @method('PUT')

        <div class="col-12">
          <label class="form-label">Descripción</label>
          <input name="descripcion" class="form-control" required value="{{ old('descripcion',$inventario->descripcion) }}">
        </div>

        <div class="col-md-6">
          <label class="form-label">Costo unitario</label>
          <input name="costo_unitario" type="number" step="0.01" class="form-control"
                 value="{{ old('costo_unitario',$inventario->costo_unitario) }}" required>
        </div>

        <div class="col-md-6">
          <label class="form-label">Precio de venta</label>
          <input name="precio_venta" type="number" step="0.01" class="form-control"
                 value="{{ old('precio_venta',$inventario->precio_venta) }}" required>
        </div>

        <div class="col-12 d-flex justify-content-end gap-2">
          <a class="btn btn-light" href="{{ route('inventario.index') }}">Cancelar</a>
          <button class="btn btn-primary"><i class="bi bi-check2-circle"></i> Guardar cambios</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
