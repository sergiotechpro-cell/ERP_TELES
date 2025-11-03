@extends('layouts.erp')
@section('title','Nuevo empleado')

@section('content')
<x-flash />
<div class="container" style="max-width: 900px;">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <h3 class="fw-bold mb-0"><i class="bi bi-person-plus me-2"></i>Nuevo empleado</h3>
    <a href="{{ route('empleados.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i> Volver</a>
  </div>
  <div class="card border-0 shadow-sm" style="border-radius:16px;">
    <div class="card-body">
      <form method="POST" action="{{ route('empleados.store') }}" class="row g-3">
        @csrf
        <div class="col-md-6">
          <label class="form-label">Nombre <span class="text-danger">*</span></label>
          <input name="nombre" class="form-control @error('nombre') is-invalid @enderror" 
                 value="{{ old('nombre') }}" required>
          @error('nombre')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        <div class="col-md-6">
          <label class="form-label">Email <span class="text-danger">*</span></label>
          <input name="email" type="email" class="form-control @error('email') is-invalid @enderror" 
                 value="{{ old('email') }}" required>
          @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        <div class="col-md-6">
          <label class="form-label">Teléfono</label>
          <input name="telefono" class="form-control @error('telefono') is-invalid @enderror" 
                 value="{{ old('telefono') }}">
          @error('telefono')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        <div class="col-md-6">
          <label class="form-label">Dirección</label>
          <input name="direccion" class="form-control @error('direccion') is-invalid @enderror" 
                 value="{{ old('direccion') }}">
          @error('direccion')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-12 d-flex justify-content-end gap-2">
          <a class="btn btn-light" href="{{ route('empleados.index') }}">Cancelar</a>
          <button class="btn btn-primary"><i class="bi bi-check2-circle"></i> Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
