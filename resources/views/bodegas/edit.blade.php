@extends('layouts.erp')
@section('title','Editar bodega')

@section('content')
<x-flash />

<div class="container" style="max-width: 820px;">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <h3 class="fw-bold mb-0">
      <i class="bi bi-pencil-square me-2"></i>Editar bodega: {{ $bodega->nombre }}
    </h3>
    <a href="{{ route('bodegas.index') }}" class="btn btn-light">
      <i class="bi bi-arrow-left"></i> Volver
    </a>
  </div>

  <div class="card border-0 shadow-sm" style="border-radius:16px;">
    <div class="card-body">
      <form method="POST" action="{{ route('bodegas.update', $bodega) }}" class="row g-3">
        @csrf @method('PUT')

        <div class="col-md-6">
          <label class="form-label">Nombre de la bodega</label>
          <input name="nombre" class="form-control" required 
                 value="{{ old('nombre', $bodega->nombre) }}" 
                 placeholder="Bodega Centro">
        </div>

        <div class="col-md-6">
          <label class="form-label">Tipo de bodega</label>
          <select name="parent_warehouse_id" id="parent_warehouse_id" class="form-select">
            <option value="">Bodega principal</option>
            @foreach($bodegas ?? [] as $bodegaOption)
              <option value="{{ $bodegaOption->id }}" 
                      {{ old('parent_warehouse_id', $bodega->parent_warehouse_id) == $bodegaOption->id ? 'selected' : '' }}>
                Sub bodega de: {{ $bodegaOption->nombre }}
              </option>
            @endforeach
          </select>
          <small class="text-secondary">
            <i class="bi bi-info-circle"></i> 
            Selecciona "Bodega principal" o elige una bodega padre para convertir en <strong>Sub bodega</strong>.
          </small>
          <div id="sub_bodega_info" class="alert alert-info mt-2 {{ $bodega->parent_warehouse_id ? '' : 'd-none' }}">
            <i class="bi bi-diagram-2"></i> 
            <strong>Esta es una Sub bodega.</strong> Está vinculada a la bodega principal seleccionada.
          </div>
        </div>

        <div class="col-12">
          <label class="form-label">Dirección de la bodega <span class="text-danger">*</span></label>
          <input name="direccion" id="direccion_bodega" class="form-control" required
                 value="{{ old('direccion', $bodega->direccion) }}"
                 placeholder="Escribe la dirección y selecciona una opción del autocompletado">
          <input type="hidden" name="lat" id="bodega_lat" required value="{{ old('lat', $bodega->lat) }}">
          <input type="hidden" name="lng" id="bodega_lng" required value="{{ old('lng', $bodega->lng) }}">
          <div id="bodega_validation_info" class="mt-2">
            @if($bodega->lat && $bodega->lng)
              <div class="alert alert-success py-2 mb-0">
                <i class="bi bi-check-circle"></i> 
                <small>Coordenadas actuales: {{ number_format($bodega->lat, 6) }}, {{ number_format($bodega->lng, 6) }}</small>
              </div>
            @endif
          </div>
          <small class="text-secondary">
            <i class="bi bi-info-circle"></i> <strong>Importante:</strong> Escribe y selecciona una dirección del autocompletado para actualizar las coordenadas. Esto es necesario para calcular rutas de entrega.
          </small>
        </div>

        <div class="col-12">
          <div class="alert alert-info d-none" id="coordenadas_requeridas">
            <i class="bi bi-exclamation-triangle"></i>
            <strong>Importante:</strong> Debes seleccionar una dirección del autocompletado para capturar las coordenadas antes de guardar.
          </div>
        </div>

        <div class="col-12 d-flex justify-content-end gap-2">
          <a href="{{ route('bodegas.index') }}" class="btn btn-light">Cancelar</a>
          <button type="submit" class="btn btn-primary" id="btnGuardarBodega">
            <i class="bi bi-check2-circle"></i> Guardar cambios
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
@if($mapsKey ?? null)
<script>
  // Hacer función global para callback de Google Maps
  window.initWarehouseAutocomplete = function() {
    initWarehouseAutocompleteCallback();
  };
</script>
<script async
  src="https://maps.googleapis.com/maps/api/js?key={{ $mapsKey }}&libraries=places&v=weekly&callback=initWarehouseAutocomplete">
</script>
@endif
<script>
  const mapsKey = @json($mapsKey ?? null);
  const currentLat = {{ $bodega->lat ?? 'null' }};
  const currentLng = {{ $bodega->lng ?? 'null' }};
  
  @if($mapsKey)
  function initWarehouseAutocompleteCallback() {
    const addressInput = document.getElementById('direccion_bodega');
    
    if (!addressInput) return;
    
    // Configurar autocompletado con restricciones de país (México)
    const autocomplete = new google.maps.places.Autocomplete(addressInput, {
      componentRestrictions: { country: ['mx'] },
      fields: ['formatted_address', 'geometry', 'address_components'],
      types: ['address']
    });

    // Cuando se selecciona una dirección
    autocomplete.addListener('place_changed', function() {
      const place = autocomplete.getPlace();
      
      if (!place.geometry) {
        console.warn('No se encontró geometría para el lugar seleccionado');
        return;
      }

      // Guardar coordenadas
      const lat = place.geometry.location.lat();
      const lng = place.geometry.location.lng();
      
      document.getElementById('bodega_lat').value = lat;
      document.getElementById('bodega_lng').value = lng;
      
      // Usar la dirección formateada oficial
      addressInput.value = place.formatted_address;
      
      // Mostrar confirmación
      const validationInfo = document.getElementById('bodega_validation_info');
      const latInput = document.getElementById('bodega_lat');
      const lngInput = document.getElementById('bodega_lng');
      
      // Marcar campos como válidos para HTML5 validation
      if (latInput && lngInput) {
        latInput.setAttribute('value', lat);
        lngInput.setAttribute('value', lng);
      }
      
      if (validationInfo) {
        validationInfo.innerHTML = `
          <div class="alert alert-success py-2 mb-0">
            <i class="bi bi-check-circle"></i> 
            <small>Coordenadas actualizadas: ${lat.toFixed(6)}, ${lng.toFixed(6)}</small>
          </div>
        `;
      }
    });

    // Validar cuando el usuario termine de escribir
    let validationTimeout;
    addressInput.addEventListener('input', function() {
      clearTimeout(validationTimeout);
      validationTimeout = setTimeout(() => {
        if (addressInput.value.length > 10) {
          const geocoder = new google.maps.Geocoder();
          geocoder.geocode({ address: addressInput.value }, function(results, status) {
            if (status === 'OK' && results[0]) {
              const lat = results[0].geometry.location.lat();
              const lng = results[0].geometry.location.lng();
              
              const latInput = document.getElementById('bodega_lat');
              const lngInput = document.getElementById('bodega_lng');
              
              if (latInput && lngInput) {
                latInput.value = lat;
                lngInput.value = lng;
                latInput.setAttribute('value', lat);
                lngInput.setAttribute('value', lng);
              }
              
              const validationInfo = document.getElementById('bodega_validation_info');
              if (validationInfo) {
                validationInfo.innerHTML = `
                  <div class="alert alert-success py-2 mb-0">
                    <i class="bi bi-check-circle"></i> 
                    <small>Coordenadas encontradas: ${lat.toFixed(6)}, ${lng.toFixed(6)}</small>
                  </div>
                `;
              }
            }
          });
        }
      }, 1000);
    });
  }
  @else
  function initWarehouseAutocomplete() {
    const validationInfo = document.getElementById('bodega_validation_info');
    if (validationInfo) {
      validationInfo.innerHTML = `
        <div class="alert alert-warning py-2 mb-0">
          <i class="bi bi-exclamation-triangle"></i> 
          <small>Configure GOOGLE_MAPS_API_KEY para habilitar autocompletado de direcciones.</small>
        </div>
      `;
    }
  }
  @endif
  
  // Mostrar/ocultar información de sub-bodega
  const parentSelect = document.getElementById('parent_warehouse_id');
  const subBodegaInfo = document.getElementById('sub_bodega_info');
  
  if (parentSelect && subBodegaInfo) {
    parentSelect.addEventListener('change', function() {
      if (this.value) {
        subBodegaInfo.classList.remove('d-none');
      } else {
        subBodegaInfo.classList.add('d-none');
      }
    });
  }
  
  // Validar antes de enviar el formulario
  document.querySelector('form').addEventListener('submit', function(e) {
    const lat = document.getElementById('bodega_lat').value;
    const lng = document.getElementById('bodega_lng').value;
    
    if (!lat || !lng) {
      e.preventDefault();
      document.getElementById('coordenadas_requeridas').classList.remove('d-none');
      document.getElementById('direccion_bodega').focus();
      alert('Debes seleccionar una dirección válida del autocompletado para capturar las coordenadas.');
      return false;
    }
  });
</script>
@endpush

