@extends('layouts.erp')
@section('title','Editar empleado')

@section('content')
<x-flash />
<div class="container" style="max-width: 900px;">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <h3 class="fw-bold mb-0"><i class="bi bi-pencil-square me-2"></i>Editar empleado</h3>
    <a href="{{ route('empleados.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i> Volver</a>
  </div>
  <div class="card border-0 shadow-sm" style="border-radius:16px;">
    <div class="card-body">
      <form method="POST" action="{{ route('empleados.update',$empleado) }}" class="row g-3">
        @csrf @method('PUT')
        <div class="col-md-6">
          <label class="form-label">Nombre <span class="text-danger">*</span></label>
          <input name="nombre" class="form-control @error('nombre') is-invalid @enderror" 
                 value="{{ old('nombre', $empleado->user->name) }}" required>
          @error('nombre')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        <div class="col-md-6">
          <label class="form-label">Email <span class="text-danger">*</span></label>
          <input name="email" type="email" class="form-control @error('email') is-invalid @enderror" 
                 value="{{ old('email', $empleado->user->email) }}" required>
          @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        <div class="col-md-6">
          <label class="form-label">Teléfono <span class="text-danger">*</span></label>
          <input name="telefono" class="form-control @error('telefono') is-invalid @enderror" 
                 value="{{ old('telefono', $empleado->telefono) }}" required>
          @error('telefono')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        <div class="col-md-6">
          <label class="form-label">Dirección <span class="text-danger">*</span></label>
          <input name="direccion" class="form-control @error('direccion') is-invalid @enderror" 
                 value="{{ old('direccion', $empleado->direccion) }}" required>
          @error('direccion')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-12 d-flex justify-content-end gap-2">
          <a class="btn btn-light" href="{{ route('empleados.index') }}">Cancelar</a>
          <button class="btn btn-primary"><i class="bi bi-check2-circle"></i> Guardar cambios</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
