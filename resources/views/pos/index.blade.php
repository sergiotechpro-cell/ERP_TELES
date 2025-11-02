@extends('layouts.erp')
@section('title','Punto de Venta')

@section('content')
<x-flash />

<form method="POST" action="{{ route('pos.store') }}" id="posForm">
  @csrf

  <div class="row g-3">
    <div class="col-lg-8">
      <div class="card border-0 shadow-sm" style="border-radius:16px;">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="fw-bold mb-0"><i class="bi bi-shop me-2"></i> Ventas</h5>
            <button type="button" id="addRow" class="btn btn-sm btn-primary">
              <i class="bi bi-plus-lg"></i> Agregar línea
            </button>
          </div>

          <div class="table-responsive">
            <table class="table align-middle" id="itemsTable">
              <thead class="table-light">
                <tr>
                  <th style="width:42%;">Producto</th>
                  <th style="width:14%;">Cantidad</th>
                  <th style="width:18%;">Precio</th>
                  <th style="width:16%;">Subtotal</th>
                  <th style="width:10%;" class="text-end">—</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>

          <input type="hidden" name="subtotal" value="0">
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card border-0 shadow-sm" style="border-radius:16px;">
        <div class="card-body">
          <h6 class="fw-bold">Resumen</h6>
          <div class="d-flex justify-content-between mt-2">
            <span class="text-secondary">Subtotal</span>
            <strong id="subtotal">$0.00</strong>
          </div>
          <hr>
          <div class="d-flex justify-content-between">
            <span class="text-secondary">Total</span>
            <strong id="total">$0.00</strong>
          </div>

          <div class="mt-3">
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

          <div class="mt-3">
            <label class="form-label">Tipo de venta <span class="text-danger">*</span></label>
            <select name="tipo_venta" id="tipo_venta" class="form-select" required>
              <option value="">Selecciona...</option>
              <option value="mostrador">Mostrador</option>
              <option value="entrega">Entrega a domicilio</option>
            </select>
          </div>

          <div class="mt-3">
            <label class="form-label">Forma de pago <span class="text-danger">*</span></label>
            <select name="forma_pago" class="form-select" required>
              <option value="">Selecciona...</option>
              <option value="efectivo">Efectivo</option>
              <option value="transferencia">Transferencia</option>
            </select>
          </div>

          {{-- Campos para entrega a domicilio --}}
          <div id="campos_entrega" class="mt-3 d-none">
            <hr>
            <h6 class="fw-bold mb-3">Información de entrega</h6>
            
            <div class="mb-3">
              <label class="form-label">Nombre de contacto (opcional)</label>
              <input name="cliente_nombre" class="form-control" placeholder="Nombre del cliente">
            </div>

            <div class="mb-3">
              <label class="form-label">Teléfono (opcional)</label>
              <input name="cliente_telefono" type="tel" class="form-control" placeholder="Teléfono de contacto">
            </div>

            <div class="mb-3">
              <label class="form-label">Dirección de entrega <span class="text-danger">*</span></label>
              <input name="direccion_entrega" id="direccion_entrega_pos" class="form-control" 
                     placeholder="Escribe la dirección y selecciona una opción">
              <input type="hidden" name="lat" id="address_lat_pos">
              <input type="hidden" name="lng" id="address_lng_pos">
              <div id="address_validation_info_pos" class="mt-2"></div>
            </div>

            <div class="row g-2 mb-3">
              <div class="col-md-6">
                <label class="form-label">Km estimados</label>
                <input id="km_pos" type="number" min="0" step="0.1" class="form-control" value="0">
                <small class="text-secondary">Banderazo 10km = $100; + $10 por km adicional.</small>
              </div>
              <div class="col-md-6">
                <label class="form-label">Costo de envío</label>
                <input name="costo_envio" id="costo_envio_pos" type="number" step="0.01" class="form-control" readonly value="0">
              </div>
              <div class="col-12">
                <button type="button" class="btn btn-outline-primary w-100" id="btnCalcEnvioPos">
                  <i class="bi bi-geo-alt"></i> Calcular envío
                </button>
              </div>
              <div class="col-12">
                <div id="distance_info_pos" class="alert alert-info d-none">
                  <i class="bi bi-info-circle"></i> <span id="distance_text_pos"></span>
                </div>
              </div>
            </div>
          </div>

          <button class="btn btn-primary w-100 mt-3" type="submit">
            <i class="bi bi-cash-coin"></i> <span id="btnText">Cobrar</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</form>

{{-- ================= VENTAS RECIENTES ================= --}}
<div class="mt-4">
  <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <h5 class="fw-bold mb-0"><i class="bi bi-receipt me-2"></i>Ventas recientes</h5>
    <div class="d-flex gap-3 small text-secondary">
      <span>Hoy: <strong class="text-dark">{{ $conteoHoy ?? 0 }}</strong> ventas</span>
      <span>Total hoy: <strong class="text-dark">
        {{ isset($totalesHoy) ? number_format($totalesHoy,2) : '0.00' }} MXN
      </strong></span>
    </div>
  </div>

  @if(($ventas->count() ?? 0) === 0)
    <x-empty icon="bi-cash-coin" title="Sin ventas registradas" text="Registra tu primera venta en el POS." />
  @else
    <div class="card border-0 shadow-sm" style="border-radius:16px;">
      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Cliente</th>
              <th>Forma de pago</th>
              <th>Status</th>
              <th>Total</th>
              <th>Fecha</th>
              <th class="text-end">Acciones</th>
            </tr>
          </thead>
          <tbody>
            @foreach($ventas as $v)
              <tr>
                <td>{{ $v->id }}</td>
                <td>
                  @if($v->customer)
                    <div class="fw-medium">{{ $v->customer->nombre }}</div>
                    <small class="text-secondary">{{ $v->customer->telefono ?? '' }}</small>
                  @else
                    <span class="text-secondary">Sin cliente</span>
                  @endif
                </td>
                <td class="text-capitalize">{{ $v->forma_pago }}</td>
                <td>
                  <span class="badge rounded-pill 
                    @if($v->status==='pagada') text-bg-success 
                    @elseif($v->status==='pendiente') text-bg-warning 
                    @else text-bg-secondary @endif">
                    {{ $v->status }}
                  </span>
                </td>
                <td><strong>${{ number_format($v->total, 2) }}</strong></td>
                <td>{{ $v->created_at?->format('d M Y H:i') }}</td>
                <td class="text-end">
                  <div class="btn-group" role="group">
                    <a href="{{ route('pos.show', $v) }}" 
                       class="btn btn-sm btn-outline-secondary" 
                       title="Ver detalles">
                      <i class="bi bi-eye"></i>
                    </a>
                    <form action="{{ route('pos.destroy', $v) }}" 
                          method="POST" 
                          class="d-inline"
                          onsubmit="return confirm('¿Eliminar venta #{{ $v->id }}?')">
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
      <div class="card-body">
        {{ $ventas->withQueryString()->links() }}
      </div>
    </div>
  @endif
</div>

{{-- ================= PEDIDOS DE ENTREGA ================= --}}
@if(isset($pedidosEntrega) && $pedidosEntrega->count() > 0)
<div class="mt-4">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-truck me-2"></i>Pedidos de entrega a domicilio</h5>
    <a href="{{ route('rutas.index') }}" class="btn btn-sm btn-outline-primary">
      <i class="bi bi-map"></i> Ver todas las rutas
    </a>
  </div>
  
  <div class="card border-0 shadow-sm" style="border-radius:16px;">
    <div class="table-responsive">
      <table class="table align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Dirección</th>
            <th>Estado</th>
            <th>Costo envío</th>
            <th>Fecha</th>
            <th class="text-end">Acciones</th>
          </tr>
        </thead>
        <tbody>
          @foreach($pedidosEntrega as $p)
            <tr>
              <td class="fw-semibold">#{{ $p->id }}</td>
              <td class="text-truncate" style="max-width: 300px">
                {{ $p->direccion_entrega ?? '—' }}
              </td>
              <td>
                <span class="badge rounded-pill 
                  @if($p->estado==='entregado') text-bg-success 
                  @elseif($p->estado==='en_ruta') text-bg-info 
                  @elseif($p->estado==='cancelado') text-bg-danger 
                  @else text-bg-warning 
                  @endif">
                  {{ ucfirst($p->estado) }}
                </span>
              </td>
              <td>${{ number_format($p->costo_envio ?? 0, 2) }}</td>
              <td>{{ $p->created_at?->format('d M Y H:i') }}</td>
              <td class="text-end">
                <div class="btn-group" role="group">
                  <a href="{{ route('pedidos.show', $p) }}" 
                     class="btn btn-sm btn-outline-secondary" 
                     title="Ver detalles">
                    <i class="bi bi-eye"></i>
                  </a>
                  @if($p->lat && $p->lng)
                  <a href="{{ route('pedidos.ruta', $p) }}" 
                     class="btn btn-sm btn-outline-primary" 
                     title="Ver ruta">
                    <i class="bi bi-map"></i>
                  </a>
                  @endif
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
@endif
@endsection

@push('scripts')
@php
  // PREPARA EL ARREGLO EN PHP (sin short closures) PARA EVITAR ParseError
  $prodRows = $productos->map(function($p){
      $precio = ($p->price_tier === 'mayoreo' && !empty($p->precio_mayoreo))
        ? $p->precio_mayoreo
        : $p->precio_venta;
      return [
        'id' => $p->id,
        'descripcion' => $p->descripcion,
        'precio' => (float) $precio,
        'costo'  => (float) $p->costo_unitario,
      ];
  })->values();
  
  $bodRows = $bodegas->map(function($b) {
      return ['id' => $b->id, 'nombre' => $b->nombre];
  })->values();
@endphp

@if($mapsKey)
<script>
  // Función global para callback de Google Maps
  window.initPosAddressAutocomplete = function() {
    initPosAddressAutocompleteCallback();
  };
</script>
<script async
  src="https://maps.googleapis.com/maps/api/js?key={{ $mapsKey }}&libraries=places,distance-matrix&v=weekly&callback=initPosAddressAutocomplete">
</script>
@endif

<script>
  // Datos listos para JS
  const productos = @json($prodRows);
  const bodegas   = @json($bodRows);
  const mapsKey   = @json($mapsKey ?? null);
  const originLat = {{ $originLat ?? 19.432608 }};
  const originLng = {{ $originLng ?? -99.133209 }};

  const tbody     = document.querySelector('#itemsTable tbody');
  const addRowBtn = document.getElementById('addRow');
  const subtotalEl= document.getElementById('subtotal');
  const totalEl   = document.getElementById('total');
  const tipoVentaSelect = document.getElementById('tipo_venta');
  const camposEntrega = document.getElementById('campos_entrega');
  const btnText = document.getElementById('btnText');
  const direccionInput = document.getElementById('direccion_entrega_pos');
  
  let autocomplete = null;
  let selectedPlace = null;

  function money(n){
    return new Intl.NumberFormat('es-MX',{style:'currency',currency:'MXN'}).format(Number(n||0));
  }

  function calc(){
    let sum = 0;
    [...tbody.querySelectorAll('tr')].forEach(tr=>{
      const qty   = Number(tr.querySelector('.qty').value||1);
      const price = Number(tr.querySelector('.price').value||0);
      const sub   = qty * price;
      sum += sub;
      tr.querySelector('.sub').innerText = money(sub);
    });
    subtotalEl.innerText = money(sum);
    totalEl.innerText    = money(sum);
    document.querySelector('input[name="subtotal"]').value = sum.toFixed(2);
  }

  function optionsProductos(){
    return productos.map(p => `<option value="${p.id}" data-price="${p.precio}">${p.descripcion}</option>`).join('');
  }

  function addRow(preset=null){
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>
        <select class="form-select prod">
          <option value="">Selecciona...</option>
          ${optionsProductos()}
        </select>
      </td>
      <td>
        <input type="number" class="form-control qty" min="1" value="${preset?.qty ?? 1}">
      </td>
      <td>
        <input type="number" class="form-control price" step="0.01" value="${preset?.price ?? 0}">
      </td>
      <td class="sub fw-semibold">$0.00</td>
      <td class="text-end">
        <button type="button" class="btn btn-sm btn-outline-danger rm"><i class="bi bi-x-lg"></i></button>
      </td>
    `;
    tbody.appendChild(tr);

    const prodSel = tr.querySelector('.prod');
    const qtyI    = tr.querySelector('.qty');
    const priceI  = tr.querySelector('.price');

    prodSel.addEventListener('change', ()=>{
      const opt = prodSel.selectedOptions[0];
      if(opt && opt.dataset.price){
        priceI.value = opt.dataset.price;
        calc();
      }
    });
    qtyI.addEventListener('input', calc);
    priceI.addEventListener('input', calc);
    tr.querySelector('.rm').addEventListener('click', ()=>{ tr.remove(); calc(); });

    if(preset?.id){
      prodSel.value = String(preset.id);
      priceI.value  = Number(preset.price||0);
    }
    calc();
  }

  addRowBtn.addEventListener('click', ()=>addRow());
  addRow(); // una fila por defecto

  // Manejar cambio de tipo de venta
  tipoVentaSelect.addEventListener('change', function() {
    if (this.value === 'entrega') {
      camposEntrega.classList.remove('d-none');
      direccionInput.setAttribute('required', 'required');
      btnText.textContent = 'Crear pedido';
    } else {
      camposEntrega.classList.add('d-none');
      direccionInput.removeAttribute('required');
      btnText.textContent = 'Cobrar';
    }
  });

  // Inicializar autocompletado de direcciones (si hay API key)
  @if($mapsKey)
  function initPosAddressAutocompleteCallback() {
    if (!direccionInput) return;
    
    autocomplete = new google.maps.places.Autocomplete(direccionInput, {
      componentRestrictions: { country: ['mx'] },
      fields: ['formatted_address', 'geometry', 'address_components', 'place_id'],
      types: ['address']
    });

    autocomplete.addListener('place_changed', function() {
      selectedPlace = autocomplete.getPlace();
      
      if (!selectedPlace.geometry) {
        console.warn('No se encontró geometría para el lugar seleccionado');
        return;
      }

      const lat = selectedPlace.geometry.location.lat();
      const lng = selectedPlace.geometry.location.lng();
      
      document.getElementById('address_lat_pos').value = lat;
      document.getElementById('address_lng_pos').value = lng;
      direccionInput.value = selectedPlace.formatted_address;
      
      validateAddressPos(selectedPlace);
      calculateDistancePos(lat, lng);
    });
  }

  // Validar dirección usando Address Validation API
  async function validateAddressPos(place) {
    const validationInfo = document.getElementById('address_validation_info_pos');
    
    if (!mapsKey) return;

    try {
      const response = await fetch('https://addressvalidation.googleapis.com/v1:validateAddress?key=' + mapsKey, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
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
        
        if (verdict.validationGranularity === 'PREMISE' || verdict.validationGranularity === 'SUB_PREMISE') {
          validationInfo.innerHTML = `
            <div class="alert alert-success py-2 mb-0">
              <i class="bi bi-check-circle"></i> 
              <small>Dirección válida y completa</small>
            </div>
          `;
        } else {
          validationInfo.innerHTML = `
            <div class="alert alert-warning py-2 mb-0">
              <i class="bi bi-exclamation-triangle"></i> 
              <small>Dirección parcial - verifica el número de casa</small>
            </div>
          `;
        }
      }
    } catch (error) {
      console.error('Error al validar dirección:', error);
    }
  }

  // Calcular distancia desde origen
  function calculateDistancePos(destLat, destLng) {
    if (!mapsKey) return;
    
    const distanceInfo = document.getElementById('distance_info_pos');
    const distanceText = document.getElementById('distance_text_pos');
    
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
        
        document.getElementById('km_pos').value = distance.toFixed(2);
        document.getElementById('costo_envio_pos').value = calcularEnvioPos(distance);
      } else {
        distanceInfo.classList.add('d-none');
      }
    });
  }

  // Cálculo de envío: banderazo 10 km = $100; + $10 por km adicional
  function calcularEnvioPos(km) {
    km = Number(km || 0);
    if (km <= 10) return 100;
    return 100 + Math.ceil(km - 10) * 10;
  }

  document.getElementById('btnCalcEnvioPos').addEventListener('click', function() {
    const kmInput = document.getElementById('km_pos');
    const lat = document.getElementById('address_lat_pos').value;
    const lng = document.getElementById('address_lng_pos').value;
    
    if (lat && lng && mapsKey) {
      calculateDistancePos(parseFloat(lat), parseFloat(lng));
    } else {
      document.getElementById('costo_envio_pos').value = calcularEnvioPos(kmInput.value);
    }
  });

  document.getElementById('km_pos').addEventListener('input', function() {
    document.getElementById('costo_envio_pos').value = calcularEnvioPos(this.value);
  });
  @endif

  // Serializa items al enviar
  document.getElementById('posForm').addEventListener('submit', (e)=>{
    // Validar si es entrega y tiene dirección
    if (tipoVentaSelect.value === 'entrega') {
      if (!direccionInput.value.trim()) {
        e.preventDefault();
        alert('La dirección de entrega es requerida para entregas a domicilio.');
        direccionInput.focus();
        return;
      }
    }

    // limpiar previos
    [...document.querySelectorAll('input[name^="items["]')].forEach(n=>n.remove());

    [...tbody.querySelectorAll('tr')].forEach((tr, idx)=>{
      const pid = tr.querySelector('.prod').value;
      const qty = tr.querySelector('.qty').value;
      const prc = tr.querySelector('.price').value;
      if(!pid) return;

      const f = e.target;
      [['product_id',pid],['cantidad',qty],['precio_unitario',prc]]
        .forEach(([k,v])=>{
          const i = document.createElement('input');
          i.type='hidden'; i.name=`items[${idx}][${k}]`; i.value=v;
          f.appendChild(i);
        });
    });
  });
</script>
@endpush
