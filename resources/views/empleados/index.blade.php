@extends('layouts.erp')
@section('title','Empleados')

@section('content')
<x-flash />

{{-- Mostrar credenciales si se acaba de crear un empleado --}}
@if(session('credenciales'))
<div class="alert alert-success border-0 shadow-sm mb-4" style="border-radius:16px; border-left: 4px solid #10b981;">
  <div class="d-flex align-items-start">
    <i class="bi bi-check-circle-fill fs-4 me-3 text-success"></i>
    <div class="flex-grow-1">
      <h6 class="fw-bold mb-3">✅ Empleado creado exitosamente</h6>
      <div class="bg-white rounded-lg p-4 mb-3" style="border: 2px dashed #10b981;">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="text-secondary small mb-1">📧 Email para login:</label>
            <div class="fw-bold text-dark fs-6">{{ session('credenciales.email') }}</div>
          </div>
          <div class="col-md-6">
            <label class="text-secondary small mb-1">🔑 Contraseña:</label>
            <div class="fw-bold text-dark fs-6">{{ session('credenciales.password') }}</div>
          </div>
        </div>
      </div>
      <div class="alert alert-warning py-2 mb-0">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <strong>Importante:</strong> Guarda estas credenciales. El chofer las usará para acceder a la app móvil en <strong>http://localhost:3000</strong>
      </div>
    </div>
    <button type="button" class="btn-close" onclick="this.closest('.alert').remove()"></button>
  </div>
</div>
@endif

<div class="container-fluid">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <h2 class="fw-bold mb-0"><i class="bi bi-person-badge me-2"></i> Empleados</h2>
    <a href="{{ route('empleados.create') }}" class="btn btn-primary"><i class="bi bi-plus"></i> Nuevo</a>
  </div>

  @if($empleados->count()===0)
    <x-empty icon="bi-person" title="Sin empleados" text="Crea el primer empleado." />
  @else
    <div class="card border-0 shadow-sm" style="border-radius:16px;">
      <div class="table-responsive">
        <table class="table align-middle">
          <thead class="table-light">
            <tr>
              <th>Nombre</th>
              <th>Email</th>
              <th>Contraseña</th>
              <th>Teléfono</th>
              <th>Dirección</th>
              <th class="text-end">Acciones</th>
            </tr>
          </thead>
          <tbody>
            @foreach($empleados as $e)
              <tr>
                <td class="fw-semibold">{{ $e->user->name }}</td>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-envelope text-primary"></i>
                    <code class="text-primary">{{ $e->user->email }}</code>
                  </div>
                </td>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-key text-success"></i>
                    <code class="text-success">12345678</code>
                    <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none" 
                            onclick="navigator.clipboard.writeText('12345678'); this.innerHTML='<i class=\'bi bi-check\'></i>'; setTimeout(()=>this.innerHTML='<i class=\'bi bi-copy\'></i>', 2000);">
                      <i class="bi bi-copy text-secondary" style="font-size: 0.75rem;"></i>
                    </button>
                  </div>
                  <small class="text-secondary d-block">Para app móvil</small>
                </td>
                <td>{{ $e->telefono ?? '—' }}</td>
                <td class="text-truncate" style="max-width:300px">{{ $e->direccion ?? '—' }}</td>
                <td class="text-end">
                  <div class="btn-group" role="group">
                    <a href="{{ route('empleados.show',$e) }}" class="btn btn-sm btn-outline-info" title="Ver detalles">
                      <i class="bi bi-eye"></i>
                    </a>
                    <a href="{{ route('empleados.edit',$e) }}" class="btn btn-sm btn-outline-primary" title="Editar">
                      <i class="bi bi-pencil"></i>
                    </a>
                    <form action="{{ route('empleados.destroy',$e) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar empleado?')">
                      @csrf @method('DELETE')
                      <button class="btn btn-sm btn-outline-danger" title="Eliminar">
                        <i class="bi bi-trash"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div class="card-body">{{ $empleados->links() }}</div>
    </div>
  @endif
</div>
@endsection
