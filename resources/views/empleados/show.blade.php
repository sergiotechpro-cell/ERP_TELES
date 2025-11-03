@extends('layouts.erp')
@section('title','Detalles del Empleado')

@section('content')
<x-flash />

<div class="container-fluid">
  <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <h2 class="fw-bold mb-0">
      <i class="bi bi-person-badge me-2"></i>{{ $empleado->user->name }}
    </h2>
    <div class="d-flex gap-2">
      <a href="{{ route('empleados.edit', $empleado) }}" class="btn btn-outline-primary">
        <i class="bi bi-pencil"></i> Editar
      </a>
      <a href="{{ route('empleados.index') }}" class="btn btn-light">
        <i class="bi bi-arrow-left"></i> Volver
      </a>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-md-8">
      <div class="card border-0 shadow-sm" style="border-radius:16px;">
        <div class="card-body">
          <h5 class="fw-bold mb-4">Información Personal</h5>
          
          <div class="mb-3">
            <label class="text-secondary small">Nombre completo</label>
            <div class="fw-semibold fs-5">{{ $empleado->user->name }}</div>
          </div>

          <div class="mb-3">
            <label class="text-secondary small">Teléfono</label>
            <div class="fw-semibold">{{ $empleado->telefono ?? '—' }}</div>
          </div>

          <div class="mb-3">
            <label class="text-secondary small">Dirección</label>
            <div class="fw-semibold">{{ $empleado->direccion ?? '—' }}</div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card border-0 shadow-sm" style="border-radius:16px; border-left: 4px solid #10b981;">
        <div class="card-body">
          <h5 class="fw-bold mb-4">
            <i class="bi bi-phone me-2"></i>Credenciales para App
          </h5>
          
          <div class="mb-4">
            <label class="text-secondary small mb-2 d-block">📧 Email</label>
            <div class="input-group">
              <input type="text" class="form-control font-monospace" 
                     value="{{ $empleado->user->email }}" 
                     id="emailInput" readonly>
              <button class="btn btn-outline-secondary" type="button" 
                      onclick="copyToClipboard('emailInput', this)">
                <i class="bi bi-copy"></i>
              </button>
            </div>
          </div>

          <div class="mb-4">
            <label class="text-secondary small mb-2 d-block">🔑 Contraseña</label>
            <div class="input-group">
              <input type="text" class="form-control font-monospace" 
                     value="12345678" 
                     id="passwordInput" readonly>
              <button class="btn btn-outline-secondary" type="button" 
                      onclick="copyToClipboard('passwordInput', this)">
                <i class="bi bi-copy"></i>
              </button>
            </div>
            <small class="text-secondary mt-2 d-block">
              <i class="bi bi-info-circle"></i> Contraseña por defecto para acceso a la app móvil
            </small>
          </div>

          <div class="alert alert-info py-2 mb-0">
            <i class="bi bi-exclamation-circle me-2"></i>
            <small><strong>Nota:</strong> El chofer usará estas credenciales para acceder a la app móvil en <code>http://localhost:3000</code></small>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
function copyToClipboard(inputId, button) {
  const input = document.getElementById(inputId);
  input.select();
  input.setSelectionRange(0, 99999); // Para móviles
  
  navigator.clipboard.writeText(input.value).then(function() {
    const icon = button.querySelector('i');
    const originalClass = icon.className;
    icon.className = 'bi bi-check';
    button.classList.add('btn-success');
    button.classList.remove('btn-outline-secondary');
    
    setTimeout(() => {
      icon.className = originalClass;
      button.classList.remove('btn-success');
      button.classList.add('btn-outline-secondary');
    }, 2000);
  });
}
</script>
@endpush
@endsection

