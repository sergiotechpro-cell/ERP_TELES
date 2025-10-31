@extends('layouts.erp')
@section('title','Nuevo pedido')

@section('content')
<x-flash />

<div class="container" style="max-width: 1100px;">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <h3 class="fw-bold mb-0"><i class="bi bi-plus-square me-2"></i>Nuevo pedido</h3>
    <a href="{{ route('pedidos.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i> Volver</a>
  </div>

  @if ($errors->any())
    <div class="alert alert-danger">
      <div class="fw-semibold mb-2">Revisa los campos:</div>
      <ul class="mb-0">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="card border-0 shadow-sm" style="border-radius:16px;">
    <div class="card-body">
      <form method="POST" action="{{ route('pedidos.store') }}" id="pedidoForm">
        @csrf

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Nombre de contacto (opcional)</label>
            <input name="cliente_nombre" class="form-control" placeholder="Nombre del cliente">
          </div>

          <div class="col-md-6">
            <label class="form-label">Teléfono (opcional)</label>
            <input name="cliente_telefono" type="tel" class="form-control" placeholder="Teléfono de contacto">
          </div>

          <div class="col-12">
            <label class="form-label">Dirección de entrega <span class="text-danger">*</span></label>
            <input name="direccion_entrega" id="direccion_entrega" class="form-control" required 
                   placeholder="Escribe la dirección y selecciona una opción">
            <input type="hidden" name="lat" id="address_lat">
            <input type="hidden" name="lng" id="address_lng">
            <div id="address_validation_info" class="mt-2"></div>
          </div>

          <div class="col-md-4">
            <label class="form-label">Km estimados</label>
            <input id="km" type="number" min="0" step="0.1" class="form-control" value="0">
            <small class="text-secondary">Banderazo 10km = $100; + $10 por km adicional.</small>
          </div>
          <div class="col-md-4">
            <label class="form-label">Costo de envío</label>
            <input name="costo_envio" id="costo_envio" type="number" step="0.01" class="form-control" readonly value="0">
          </div>
          <div class="col-md-4 d-flex align-items-end">
            <button type="button" class="btn btn-outline-primary w-100" id="btnCalcEnvio">
              <i class="bi bi-geo-alt"></i> Calcular envío
            </button>
          </div>
          <div class="col-12">
            <div id="distance_info" class="alert alert-info d-none">
              <i class="bi bi-info-circle"></i> <span id="distance_text"></span>
            </div>
          </div>
        </div>

        <hr class="my-4">

        <div class="d-flex align-items-center justify-content-between mb-2">
          <h5 class="mb-0 fw-bold"><i class="bi bi-cart-plus me-2"></i>Productos</h5>
          <button class="btn btn-sm btn-primary" type="button" id="addRow">
            <i class="bi bi-plus-lg"></i> Agregar línea
          </button>
        </div>

        <div class="table-responsive">
          <table class="table align-middle" id="itemsTable">
            <thead class="table-light">
              <tr>
                <th style="width: 30%;">Producto</th>
                <th style="width: 20%;">Bodega</th>
                <th style="width: 12%;">Cantidad</th>
                <th style="width: 16%;">Precio unit.</th>
                <th style="width: 16%;">Costo unit.</th>
                <th style="width: 6%;" class="text-end">—</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-3">
          <a href="{{ route('pedidos.index') }}" class="btn btn-light">Cancelar</a>
          <button class="btn btn-primary"><i class="bi bi-check2-circle"></i> Guardar pedido</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
@if($mapsKey)
<script>
  // Hacer función global para callback de Google Maps
  window.initAddressAutocomplete = function() {
    initAddressAutocompleteCallback();
  };
</script>
<script async
  src="https://maps.googleapis.com/maps/api/js?key={{ $mapsKey }}&libraries=places,distance-matrix&v=weekly&callback=initAddressAutocomplete">
</script>
@endif
<script>
  // Arrays ya preparados en el controlador:
  const productos = @json($prodRows);
  const bodegas   = @json($bodRows);
  const mapsKey   = @json($mapsKey ?? null);
  const originLat = {{ $originLat ?? 19.432608 }};
  const originLng = {{ $originLng ?? -99.133209 }};

  const tbody     = document.querySelector('#itemsTable tbody');
  const addRowBtn = document.getElementById('addRow');
  
  let autocomplete = null;
  let selectedPlace = null;
  
  // Inicializar autocompletado de direcciones
  @if($mapsKey)
  function initAddressAutocompleteCallback() {
    const addressInput = document.getElementById('direccion_entrega');
    
    // Configurar autocompletado con restricciones de país (México)
    autocomplete = new google.maps.places.Autocomplete(addressInput, {
      componentRestrictions: { country: ['mx'] },
      fields: ['formatted_address', 'geometry', 'address_components', 'place_id'],
      types: ['address']
    });

    // Cuando se selecciona una dirección
    autocomplete.addListener('place_changed', function() {
      selectedPlace = autocomplete.getPlace();
      
      if (!selectedPlace.geometry) {
        console.warn('No se encontró geometría para el lugar seleccionado');
        return;
      }

      // Guardar coordenadas
      const lat = selectedPlace.geometry.location.lat();
      const lng = selectedPlace.geometry.location.lng();
      
      document.getElementById('address_lat').value = lat;
      document.getElementById('address_lng').value = lng;
      
      // Usar la dirección formateada oficial
      addressInput.value = selectedPlace.formatted_address;
      
      // Validar dirección con Address Validation API
      validateAddress(selectedPlace);
      
      // Calcular distancia automáticamente
      calculateDistance(lat, lng);
    });

    // Validar cuando el usuario termine de escribir (después de 1 segundo sin escribir)
    let validationTimeout;
    addressInput.addEventListener('input', function() {
      clearTimeout(validationTimeout);
      validationTimeout = setTimeout(() => {
        if (addressInput.value.length > 10 && !selectedPlace) {
          // Intentar validar si no hay lugar seleccionado
          const geocoder = new google.maps.Geocoder();
          geocoder.geocode({ address: addressInput.value }, function(results, status) {
            if (status === 'OK' && results[0]) {
              validateAddressInput(results[0]);
            }
          });
        }
      }, 1000);
    });
  }
  
  // Validar dirección usando Address Validation API
  async function validateAddress(place) {
    const validationInfo = document.getElementById('address_validation_info');
    
    if (!mapsKey) {
      validationInfo.innerHTML = '<small class="text-secondary">API key no configurada</small>';
      return;
    }

    try {
      const response = await fetch('https://addressvalidation.googleapis.com/v1:validateAddress?key=' + mapsKey, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          address: {
            addressLines: [place.formatted_address],
            regionCode: 'MX'
          }
        })
      });

      const data = await response.json();
      
      if (data.result && data.result.verdict) {
        const verdict = data.result.verdict;
        const address = data.result.address || {};
        
        if (verdict.validationGranularity === 'PREMISE' || verdict.validationGranularity === 'SUB_PREMISE') {
          validationInfo.innerHTML = `
            <div class="alert alert-success py-2 mb-0">
              <i class="bi bi-check-circle"></i> 
              <small>Dirección válida y completa</small>
            </div>
          `;
        } else if (verdict.validationGranularity === 'ROUTE' || verdict.validationGranularity === 'LOCALITY') {
          validationInfo.innerHTML = `
            <div class="alert alert-warning py-2 mb-0">
              <i class="bi bi-exclamation-triangle"></i> 
              <small>Dirección parcial - verifica el número de casa</small>
            </div>
          `;
        } else {
          validationInfo.innerHTML = `
            <div class="alert alert-warning py-2 mb-0">
              <i class="bi bi-info-circle"></i> 
              <small>Dirección validada en nivel: ${verdict.validationGranularity}</small>
            </div>
          `;
        }
        
        // Mostrar dirección formateada sugerida si existe
        if (address.formattedAddress && address.formattedAddress !== place.formatted_address) {
          validationInfo.innerHTML += `
            <div class="mt-1">
              <small class="text-secondary">Sugerencia: ${address.formattedAddress}</small>
            </div>
          `;
        }
      }
    } catch (error) {
      console.error('Error al validar dirección:', error);
      validationInfo.innerHTML = '<small class="text-secondary">No se pudo validar la dirección</small>';
    }
  }
  
  // Validar dirección desde input manual
  function validateAddressInput(result) {
    const validationInfo = document.getElementById('address_validation_info');
    const lat = result.geometry.location.lat();
    const lng = result.geometry.location.lng();
    
    document.getElementById('address_lat').value = lat;
    document.getElementById('address_lng').value = lng;
    
    calculateDistance(lat, lng);
    
    validationInfo.innerHTML = `
      <div class="alert alert-info py-2 mb-0">
        <i class="bi bi-info-circle"></i> 
        <small>Dirección encontrada</small>
      </div>
    `;
  }
  
  // Calcular distancia desde origen
  function calculateDistance(destLat, destLng) {
    if (!mapsKey) return;
    
    const distanceInfo = document.getElementById('distance_info');
    const distanceText = document.getElementById('distance_text');
    
    const origin = { lat: originLat, lng: originLng };
    const destination = { lat: destLat, lng: destLng };
    
    const service = new google.maps.DistanceMatrixService();
    
    service.getDistanceMatrix({
      origins: [origin],
      destinations: [destination],
      travelMode: google.maps.TravelMode.DRIVING,
      unitSystem: google.maps.UnitSystem.METRIC
    }, function(response, status) {
      if (status === 'OK' && response.rows[0].elements[0].status === 'OK') {
        const element = response.rows[0].elements[0];
        const distance = element.distance.value / 1000; // en km
        const duration = element.duration.text;
        
        distanceText.textContent = `Distancia: ${element.distance.text} | Tiempo: ${duration}`;
        distanceInfo.classList.remove('d-none');
        
        // Actualizar campo de km y calcular costo automáticamente
        document.getElementById('km').value = distance.toFixed(2);
        document.getElementById('costo_envio').value = calcularEnvio(distance);
      } else {
        distanceInfo.classList.add('d-none');
      }
    });
  }
  @else
  // Si no hay API key, función dummy
  function initAddressAutocomplete() {
    // API key no configurada
    const validationInfo = document.getElementById('address_validation_info');
    if (validationInfo) {
      validationInfo.innerHTML = `
        <div class="alert alert-warning py-2 mb-0">
          <i class="bi bi-exclamation-triangle"></i> 
          <small>Configure GOOGLE_MAPS_API_KEY para habilitar autocompletado de direcciones</small>
        </div>
      `;
    }
  }
  
  // Función dummy para calculateDistance cuando no hay API key
  function calculateDistance(destLat, destLng) {
    // No hacer nada si no hay API key
    return;
  }
  @endif

  function optionList(arr, value = 'id', label = 'nombre') {
    return arr.map(o => `<option value="${o[value]}">${o[label]}</option>`).join('');
  }

  function addRow(preset = null) {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>
        <select class="form-select prod" required>
          <option value="">Selecciona...</option>
          ${productos.map(p => `<option value="${p.id}" data-precio="${p.precio}" data-costo="${p.costo}">${p.descripcion}</option>`).join('')}
        </select>
      </td>
      <td>
        <select class="form-select bod" required>
          <option value="">Selecciona...</option>
          ${optionList(bodegas)}
        </select>
      </td>
      <td><input type="number" class="form-control qty" min="1" value="1" required></td>
      <td><input type="number" class="form-control precio" step="0.01" required></td>
      <td><input type="number" class="form-control costo" step="0.01" required></td>
      <td class="text-end">
        <button type="button" class="btn btn-sm btn-outline-danger rm"><i class="bi bi-x-lg"></i></button>
      </td>
    `;
    tbody.appendChild(tr);

    const prodSel = tr.querySelector('.prod');
    const precio  = tr.querySelector('.precio');
    const costo   = tr.querySelector('.costo');

    prodSel.addEventListener('change', () => {
      const opt = prodSel.selectedOptions[0];
      if (!opt) return;
      precio.value = opt.dataset.precio || 0;
      costo.value  = opt.dataset.costo  || 0;
    });

    tr.querySelector('.rm').addEventListener('click', () => tr.remove());

    if (preset) {
      prodSel.value = preset.id;
      precio.value  = preset.precio;
      costo.value   = preset.costo;
    }
  }

  addRowBtn.addEventListener('click', () => addRow());
  // Agrega una fila por defecto
  addRow();

  // Cálculo de envío: banderazo 10 km = $100; + $10 por km adicional
  function calcularEnvio(km) {
    km = Number(km || 0);
    if (km <= 10) return 100;
    return 100 + Math.ceil(km - 10) * 10;
  }
  
  document.getElementById('btnCalcEnvio').addEventListener('click', () => {
    const kmInput = document.getElementById('km');
    const lat = document.getElementById('address_lat').value;
    const lng = document.getElementById('address_lng').value;
    
    // Si hay coordenadas, recalcular distancia
    if (lat && lng && mapsKey) {
      calculateDistance(parseFloat(lat), parseFloat(lng));
    } else {
      // Calcular solo con el km manual
      document.getElementById('costo_envio').value = calcularEnvio(kmInput.value);
    }
  });
  
  // Calcular costo cuando cambia el km manualmente
  document.getElementById('km').addEventListener('input', function() {
    document.getElementById('costo_envio').value = calcularEnvio(this.value);
  });

  // Serializa "productos[...]" al enviar
  document.getElementById('pedidoForm').addEventListener('submit', (e) => {
    // limpia inputs ocultos previos
    document.querySelectorAll('input[name^="productos["]').forEach(n => n.remove());

    [...tbody.querySelectorAll('tr')].forEach((tr, idx) => {
      const prod = tr.querySelector('.prod').value;
      const bod  = tr.querySelector('.bod').value;
      const qty  = tr.querySelector('.qty').value;
      const pre  = tr.querySelector('.precio').value;
      const cos  = tr.querySelector('.costo').value;

      if (!prod || !bod) return;

      const f = e.target;
      [
        ['id', prod],
        ['bodega_id', bod],
        ['cantidad', qty],
        ['precio', pre],
        ['costo',  cos],
      ].forEach(([k, v]) => {
        const i = document.createElement('input');
        i.type  = 'hidden';
        i.name  = `productos[${idx}][${k}]`;
        i.value = v;
        f.appendChild(i);
      });
    });
  });
</script>
@endpush
