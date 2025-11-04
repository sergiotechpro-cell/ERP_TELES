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

  {{-- Alertas de elementos faltantes --}}
  @if(!$canProceed || !$hasProductos || !$hasBodegas || !$hasChoferes)
  <div class="alert alert-warning border-0 shadow-sm mb-4" style="border-radius:16px;">
    <div class="d-flex align-items-start">
      <i class="bi bi-exclamation-triangle-fill fs-4 me-3 text-warning"></i>
      <div class="flex-grow-1">
        <h6 class="fw-bold mb-2">⚠️ Antes de crear pedidos, necesitas:</h6>
        <ul class="mb-2">
          @if(!$hasProductos)
          <li class="mb-1">
            <strong class="text-danger">Productos:</strong> No hay productos registrados
            <a href="{{ route('inventario.create') }}" class="btn btn-sm btn-outline-primary ms-2" target="_blank">
              <i class="bi bi-plus-circle"></i> Crear producto
            </a>
          </li>
          @endif
          @if(!$hasBodegas)
          <li class="mb-1">
            <strong class="text-danger">Bodegas:</strong> No hay bodegas registradas
            <a href="{{ route('bodegas.create') }}" class="btn btn-sm btn-outline-primary ms-2" target="_blank">
              <i class="bi bi-plus-circle"></i> Crear bodega
            </a>
          </li>
          @endif
          @if(!$hasChoferes)
          <li class="mb-1">
            <strong class="text-danger">Choferes:</strong> No hay choferes registrados (requerido para pedidos)
            <a href="{{ route('empleados.create') }}" class="btn btn-sm btn-outline-danger ms-2" target="_blank">
              <i class="bi bi-plus-circle"></i> Crear chofer
            </a>
          </li>
          @endif
          @if(!$hasClientes)
          <li class="mb-1">
            <strong class="text-info">Clientes:</strong> Recomendado registrar clientes
            <a href="{{ route('clientes.create') }}" class="btn btn-sm btn-outline-info ms-2" target="_blank">
              <i class="bi bi-plus-circle"></i> Crear cliente
            </a>
          </li>
          @endif
        </ul>
        @if(!$canProceed)
        <div class="alert alert-danger py-2 mb-0">
          <strong>No puedes crear pedidos hasta que crees los elementos requeridos (Productos, Bodegas y Choferes).</strong>
        </div>
        @endif
      </div>
    </div>
  </div>
  @endif

  <div class="card border-0 shadow-sm" style="border-radius:16px;">
    <div class="card-body">
      <form method="POST" action="{{ route('pedidos.store') }}" id="pedidoForm" @if(!$canProceed || !$hasChoferes) onsubmit="event.preventDefault(); alert('Debes crear al menos un producto, una bodega y un chofer antes de crear pedidos.'); return false;" @endif>
        @csrf

        <div class="mb-3">
          <label class="form-label">Cliente (opcional)</label>
          <select name="customer_id" id="customer_id" class="form-select">
            <option value="">Sin cliente</option>
            @foreach($clientes ?? [] as $cliente)
              <option value="{{ $cliente->id }}">
                {{ $cliente->nombre }}
                @if($cliente->telefono)
                  - {{ $cliente->telefono }}
                @endif
              </option>
            @endforeach
          </select>
          <small class="text-secondary">
            <a href="{{ route('clientes.create') }}" target="_blank" class="text-decoration-none">
              <i class="bi bi-plus-circle"></i> Crear nuevo cliente
            </a>
          </small>
        </div>

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Nombre de contacto <span class="text-danger" id="cliente_nombre_required" style="display:none;">*</span></label>
            <input name="cliente_nombre" id="cliente_nombre" class="form-control" placeholder="Nombre del cliente">
          </div>

          <div class="col-md-6">
            <label class="form-label">Teléfono <span class="text-danger" id="cliente_telefono_required" style="display:none;">*</span></label>
            <input name="cliente_telefono" id="cliente_telefono" type="tel" class="form-control" placeholder="Teléfono de contacto">
          </div>

          <div class="col-12">
            <label class="form-label">Dirección de entrega <span class="text-danger">*</span></label>
            <input name="direccion_entrega" id="direccion_entrega" class="form-control" required 
                   placeholder="Escribe la dirección y selecciona una opción">
            <input type="hidden" name="lat" id="address_lat">
            <input type="hidden" name="lng" id="address_lng">
            <div id="address_validation_info" class="mt-2"></div>
          </div>

          <div class="col-12">
            <label class="form-label">Chofer <span class="text-danger">*</span></label>
            <select name="courier_id" id="courier_id" class="form-select" required>
              <option value="">Selecciona un chofer...</option>
              @foreach($choferes ?? [] as $chofer)
                <option value="{{ $chofer->id }}">
                  {{ $chofer->name }}
                  @if($chofer->email)
                    - {{ $chofer->email }}
                  @endif
                </option>
              @endforeach
            </select>
            <small class="text-secondary">El chofer es obligatorio para crear el pedido.</small>
          </div>

          <div class="col-md-6">
            <label class="form-label">Km estimados</label>
            <input id="km" type="number" min="0" step="1" class="form-control" value="0">
            <small class="text-secondary">Banderazo 10km = $100; + $10 por km adicional.</small>
          </div>
          <div class="col-md-6">
            <label class="form-label">Costo de envío <span class="text-danger">*</span></label>
            <input name="costo_envio" id="costo_envio" type="number" step="0.01" class="form-control" required value="0" min="0">
          </div>
          <div class="col-12">
            <button type="button" class="btn btn-outline-primary w-100" id="btnCalcEnvio">
              <i class="bi bi-geo-alt"></i> Calcular envío automáticamente
            </button>
            <small class="text-secondary d-block mt-1 text-center">
              <i class="bi bi-info-circle"></i> Calcula desde la dirección o ingresa km manualmente
            </small>
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
  const clientes  = @json($cliRows ?? []);
  const mapsKey   = @json($mapsKey ?? null);
  const originLat = {{ $originLat ?? 19.432608 }};
  const originLng = {{ $originLng ?? -99.133209 }};

  const tbody     = document.querySelector('#itemsTable tbody');
  const addRowBtn = document.getElementById('addRow');
  const customerSelect = document.getElementById('customer_id');
  const clienteNombreInput = document.getElementById('cliente_nombre');
  const clienteTelefonoInput = document.getElementById('cliente_telefono');
  const direccionInput = document.getElementById('direccion_entrega');
  
  let autocomplete = null;
  let selectedPlace = null;
  
  // Función para actualizar campos requeridos según si hay cliente seleccionado
  function updateClienteFieldsRequired() {
    const clienteId = customerSelect?.value;
    const hasCliente = !!clienteId;
    
    if (clienteNombreInput) {
      clienteNombreInput.required = !hasCliente;
      document.getElementById('cliente_nombre_required').style.display = hasCliente ? 'none' : 'inline';
    }
    
    if (clienteTelefonoInput) {
      clienteTelefonoInput.required = !hasCliente;
      document.getElementById('cliente_telefono_required').style.display = hasCliente ? 'none' : 'inline';
    }
  }
  
  // Autocompletar campos cuando se selecciona un cliente
  if (customerSelect) {
    customerSelect.addEventListener('change', function() {
      const clienteId = this.value;
      updateClienteFieldsRequired();
      
      if (clienteId) {
        const cliente = clientes.find(c => c.id == clienteId);
        if (cliente) {
          // Autocompletar nombre y teléfono
          if (clienteNombreInput) {
            clienteNombreInput.value = cliente.nombre || '';
          }
          if (clienteTelefonoInput) {
            clienteTelefonoInput.value = cliente.telefono || '';
          }
          // Autocompletar dirección si existe
          if (cliente.direccion && direccionInput) {
            direccionInput.value = cliente.direccion;
            // Si hay API key, intentar validar y calcular distancia
            if (mapsKey && window.google && window.google.maps) {
              const loadMapsAndGeocode = function() {
                if (window.google && window.google.maps && window.google.maps.Geocoder) {
                  const geocoder = new google.maps.Geocoder();
                  geocoder.geocode({ address: cliente.direccion }, function(results, status) {
                    if (status === 'OK' && results[0]) {
                      const lat = results[0].geometry.location.lat();
                      const lng = results[0].geometry.location.lng();
                      document.getElementById('address_lat').value = lat;
                      document.getElementById('address_lng').value = lng;
                      calculateDistance(lat, lng, false);
                    }
                  });
                } else {
                  setTimeout(loadMapsAndGeocode, 100);
                }
              };
              setTimeout(loadMapsAndGeocode, 200);
            }
          }
        }
      } else {
        // Si no hay cliente seleccionado, limpiar campos
        if (clienteNombreInput) clienteNombreInput.value = '';
        if (clienteTelefonoInput) clienteTelefonoInput.value = '';
      }
    });
    
    // Inicializar estado de campos requeridos
    updateClienteFieldsRequired();
  }
  
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
      calculateDistance(lat, lng, false);
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
              const lat = results[0].geometry.location.lat();
              const lng = results[0].geometry.location.lng();
              calculateDistance(lat, lng, false);
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
    
    calculateDistance(lat, lng, false);
    
    validationInfo.innerHTML = `
      <div class="alert alert-info py-2 mb-0">
        <i class="bi bi-info-circle"></i> 
        <small>Dirección encontrada</small>
      </div>
    `;
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
  window.calculateDistance = function(destLat, destLng, restoreButton = true) {
    // No hacer nada si no hay API key
    return;
  };
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
          ${productos.map(p => `<option value="${p.id}" data-precio="${p.precio}" data-costo="${p.costo}" data-stock="${p.stock}">${p.descripcion} ${p.stock > 0 ? `(${p.stock} disponibles)` : '(sin stock)'}</option>`).join('')}
        </select>
      </td>
      <td>
        <select class="form-select bod" required>
          <option value="">Selecciona...</option>
          ${optionList(bodegas)}
        </select>
      </td>
      <td>
        <input type="number" class="form-control qty" min="1" value="1" required>
        <small class="stock-warning d-none"></small>
      </td>
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
    const qtyI    = tr.querySelector('.qty');
    const warning = tr.querySelector('.stock-warning');

    prodSel.addEventListener('change', () => {
      const opt = prodSel.selectedOptions[0];
      if (!opt) return;
      precio.value = opt.dataset.precio || 0;
      costo.value  = opt.dataset.costo  || 0;
      
      // Validar stock
      const stock = parseInt(opt.dataset.stock || 0);
      qtyI.max = stock;
      qtyI.min = stock > 0 ? 1 : 0;
      
      if (stock === 0) {
        qtyI.setAttribute('readonly', 'readonly');
        qtyI.classList.add('bg-warning');
        qtyI.value = 0;
        warning.className = 'stock-warning text-danger d-block mt-1';
        warning.innerHTML = '<i class="bi bi-exclamation-triangle"></i> Sin stock disponible';
      } else {
        qtyI.removeAttribute('readonly');
        qtyI.classList.remove('bg-warning');
        const currentQty = parseInt(qtyI.value || 1);
        qtyI.value = Math.min(Math.max(currentQty, 1), stock);
        if (stock < 10) {
          warning.className = 'stock-warning text-warning d-block mt-1';
          warning.innerHTML = `<i class="bi bi-exclamation-triangle"></i> Solo quedan ${stock} unidades disponibles`;
        } else {
          warning.className = 'stock-warning d-none';
          warning.innerHTML = '';
        }
      }
    });
    
    qtyI.addEventListener('input', function() {
      const opt = prodSel.selectedOptions[0];
      if (opt) {
        const stock = parseInt(opt.dataset.stock || 0);
        const qty = parseInt(this.value || 0);
        if (qty > stock) {
          this.value = stock;
          alert(`Solo hay ${stock} unidades disponibles.`);
        }
      }
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
  
  // Mejorar calculateDistance para aceptar restoreButton
  window.calculateDistance = function(destLat, destLng, restoreButton = true) {
    if (!mapsKey || !window.google) return;
    
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
        const distanceKm = Math.round(distance); // Redondear km a entero
        const duration = element.duration.text;
        
        distanceText.textContent = `Distancia: ${element.distance.text} | Tiempo: ${duration}`;
        distanceInfo.classList.remove('d-none');
        
        // Actualizar campo de km y calcular costo automáticamente
        document.getElementById('km').value = distanceKm;
        document.getElementById('costo_envio').value = calcularEnvio(distanceKm);
      } else {
        distanceInfo.classList.add('d-none');
      }
    });
  };
  
  document.getElementById('btnCalcEnvio').addEventListener('click', function() {
    const btn = this;
    const kmInput = document.getElementById('km');
    const latInput = document.getElementById('address_lat');
    const lngInput = document.getElementById('address_lng');
    const direccionText = direccionInput.value.trim();
    const lat = latInput.value;
    const lng = lngInput.value;
    const distanceInfo = document.getElementById('distance_info');
    const distanceText = document.getElementById('distance_text');
    
    // Deshabilitar botón mientras calcula
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Calculando...';
    
    // Si hay coordenadas, calcular directamente
    if (lat && lng && mapsKey && window.google && window.google.maps) {
      calculateDistance(parseFloat(lat), parseFloat(lng), true);
      btn.disabled = false;
      btn.innerHTML = '<i class="bi bi-geo-alt"></i> Calcular envío automáticamente';
      return;
    }
    
    // Si hay dirección escrita pero no coordenadas, intentar geocodificar
    if (direccionText && mapsKey && window.google && window.google.maps && window.google.maps.Geocoder) {
      const geocoder = new google.maps.Geocoder();
      geocoder.geocode({ address: direccionText }, function(results, status) {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-geo-alt"></i> Calcular envío automáticamente';
        
        if (status === 'OK' && results[0]) {
          const lat = results[0].geometry.location.lat();
          const lng = results[0].geometry.location.lng();
          latInput.value = lat;
          lngInput.value = lng;
          calculateDistance(lat, lng, true);
        } else {
          // Si falla la geocodificación, usar el km manual
          const kmManual = Math.round(parseFloat(kmInput.value) || 0);
          kmInput.value = kmManual;
          document.getElementById('costo_envio').value = calcularEnvio(kmManual);
          
          distanceInfo.classList.add('d-none');
          alert('No se pudo calcular la distancia automáticamente. Usa el campo "Km estimados" para ingresar la distancia manualmente.');
        }
      });
      return;
    }
    
    // Si no hay coordenadas ni dirección, usar km manual
    const kmManual = Math.round(parseFloat(kmInput.value) || 0);
    
    if (kmManual <= 0) {
      btn.disabled = false;
      btn.innerHTML = '<i class="bi bi-geo-alt"></i> Calcular envío automáticamente';
      alert('Ingresa la distancia en kilómetros o selecciona una dirección válida para calcular automáticamente.');
      kmInput.focus();
      return;
    }
    
    kmInput.value = kmManual;
    document.getElementById('costo_envio').value = calcularEnvio(kmManual);
    distanceInfo.classList.add('d-none');
    
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-geo-alt"></i> Calcular envío automáticamente';
  });
  
  // Calcular costo cuando cambia el km manualmente
  document.getElementById('km').addEventListener('input', function() {
    const kmValue = Math.round(parseFloat(this.value) || 0);
    this.value = kmValue;
    document.getElementById('costo_envio').value = calcularEnvio(kmValue);
  });

  // Serializa "productos[...]" al enviar
  document.getElementById('pedidoForm').addEventListener('submit', (e) => {
    // Validar campos obligatorios
    const lat = document.getElementById('address_lat')?.value;
    const lng = document.getElementById('address_lng')?.value;
    
    if (!lat || !lng) {
      e.preventDefault();
      alert('Debes seleccionar una dirección válida del autocompletado para obtener las coordenadas.');
      direccionInput?.focus();
      return false;
    }
    
    // Validar cliente si no hay customer_id
    const customerId = customerSelect?.value;
    if (!customerId) {
      if (!clienteNombreInput?.value?.trim()) {
        e.preventDefault();
        alert('El nombre del cliente es obligatorio cuando no seleccionas un cliente existente.');
        clienteNombreInput?.focus();
        return false;
      }
      if (!clienteTelefonoInput?.value?.trim()) {
        e.preventDefault();
        alert('El teléfono del cliente es obligatorio cuando no seleccionas un cliente existente.');
        clienteTelefonoInput?.focus();
        return false;
      }
    }
    
    // Validar chofer
    const courierId = document.getElementById('courier_id')?.value;
    if (!courierId) {
      e.preventDefault();
      alert('El chofer es obligatorio. Debes seleccionar un chofer para el pedido.');
      document.getElementById('courier_id')?.focus();
      return false;
    }
    
    // Validar stock antes de enviar
    let hasErrors = false;
    let errorMessages = [];
    
    [...tbody.querySelectorAll('tr')].forEach((tr, idx) => {
      const prodSel = tr.querySelector('.prod');
      const qtyI = tr.querySelector('.qty');
      
      if (prodSel.value) {
        const opt = prodSel.selectedOptions[0];
        if (opt) {
          const stock = parseInt(opt.dataset.stock || 0);
          const qty = parseInt(qtyI.value || 0);
          
          if (stock === 0) {
            hasErrors = true;
            errorMessages.push(`El producto "${opt.textContent.split('(')[0].trim()}" no tiene stock disponible.`);
          } else if (qty > stock) {
            hasErrors = true;
            errorMessages.push(`El producto "${opt.textContent.split('(')[0].trim()}" solo tiene ${stock} unidades disponibles.`);
          }
        }
      }
    });
    
    if (hasErrors) {
      e.preventDefault();
      alert('Error de stock:\n\n' + errorMessages.join('\n'));
      return;
    }
    
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
