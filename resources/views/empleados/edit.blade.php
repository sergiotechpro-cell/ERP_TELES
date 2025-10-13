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
        <div class="col-md-6"><label class="form-label">Nombre</label><input name="nombre" class="form-control" value="{{ $empleado->user->name }}" required></div>
        <div class="col-md-6"><label class="form-label">Email</label><input name="email" type="email" class="form-control" value="{{ $empleado->user->email }}" required></div>
        <div class="col-md-6"><label class="form-label">Teléfono</label><input name="telefono" class="form-control" value="{{ $empleado->telefono }}"></div>
        <div class="col-md-6"><label class="form-label">Dirección</label><input name="direccion" class="form-control" value="{{ $empleado->direccion }}"></div>

        <div class="col-12 d-flex justify-content-end gap-2">
          <a class="btn btn-light" href="{{ route('empleados.index') }}">Cancelar</a>
          <button class="btn btn-primary"><i class="bi bi-check2-circle"></i> Guardar cambios</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
