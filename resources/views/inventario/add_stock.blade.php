@extends('layouts.erp')
@section('title','Agregar unidades al producto')

@section('content')
<x-flash />

<div class="container" style="max-width: 980px;">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <h3 class="fw-bold mb-0">
      <i class="bi bi-plus-square me-2"></i>Agregar unidades a: {{ $inventario->descripcion }}
    </h3>
    <a href="{{ route('inventario.show', $inventario) }}" class="btn btn-light">
      <i class="bi bi-arrow-left"></i> Volver
    </a>
  </div>

  @if($bodegas->isEmpty())
    <div class="alert alert-warning d-flex align-items-center" role="alert">
      <i class="bi bi-exclamation-triangle-fill me-2"></i>
      <div>
        No hay bodegas disponibles. <a href="{{ route('bodegas.create') }}" class="alert-link">Crear bodega</a>.
      </div>
    </div>
  @endif

  <div class="card border-0 shadow-sm" style="border-radius:16px;">
    <div class="card-body">
      <form method="POST" action="{{ route('inventario.store-add-stock', $inventario) }}" class="row g-3">
        @csrf

        <div class="col-md-6">
          <label class="form-label">Almacén <span class="text-danger">*</span></label>
          <select name="warehouse_id" class="form-select" required @if($bodegas->isEmpty()) disabled @endif>
            <option value="">Selecciona almacén...</option>
            @foreach($bodegas as $b)
              <option value="{{ $b->id }}">{{ $b->nombre }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-6">
          <label class="form-label">Cantidad <span class="text-danger">*</span></label>
          <input name="cantidad" type="number" min="1" class="form-control" required 
                 @if($bodegas->isEmpty()) disabled @endif>
          <small class="text-secondary">Se crearán números de serie automáticamente si no especificas manualmente.</small>
        </div>

        <div class="col-12">
          <label class="form-label">Números de serie (opcional)</label>
          <textarea name="seriales" class="form-control" rows="5" 
                    placeholder="Ingresa un número de serie por línea, o deja vacío para generarlos automáticamente"></textarea>
          <small class="text-secondary">
            <i class="bi bi-info-circle"></i> Si especificas números de serie manualmente, asegúrate de ingresar la misma cantidad que especificaste arriba.
          </small>
        </div>

        <div class="col-12 d-flex justify-content-end gap-2">
          <a href="{{ route('inventario.show', $inventario) }}" class="btn btn-light">Cancelar</a>
          <button class="btn btn-primary" @if($bodegas->isEmpty()) disabled @endif>
            <i class="bi bi-check2-circle"></i> Agregar unidades
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

