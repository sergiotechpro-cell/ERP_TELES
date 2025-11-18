@extends('layouts.erp')
@section('title','Punto de Venta')

@section('content')
<x-flash />

{{-- Alertas de elementos faltantes --}}
@if(!$canProceed || !$hasProductos || !$hasBodegas || !$hasChoferes || !$hasClientes)
<div class="alert alert-warning border-0 shadow-sm mb-4" style="border-radius:16px;">
  <div class="d-flex align-items-start">
    <i class="bi bi-exclamation-triangle-fill fs-4 me-3 text-warning"></i>
    <div class="flex-grow-1">
      <h6 class="fw-bold mb-2">⚠️ Antes de realizar ventas, necesitas:</h6>
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
          <strong class="text-warning">Choferes:</strong> No hay choferes registrados (requerido para entregas)
          <a href="{{ route('empleados.create') }}" class="btn btn-sm btn-outline-warning ms-2" target="_blank">
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
        <strong>No puedes realizar ventas hasta que crees los elementos requeridos (Productos y Bodegas).</strong>
      </div>
      @endif
    </div>
  </div>
</div>
@endif

<form method="POST" action="{{ route('pos.store') }}" id="posForm" @if(!$canProceed) onsubmit="event.preventDefault(); alert('Debes crear al menos un producto y una bodega antes de realizar ventas.'); return false;" @endif>
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
              <option value="tarjeta">Tarjeta</option>
              <option value="transferencia">Transferencia</option>
            </select>
          </div>

          {{-- Campos para entrega a domicilio --}}
          <div id="campos_entrega" class="mt-3 d-none">
            <hr>
            <h6 class="fw-bold mb-3">Información de entrega</h6>
            
            <div class="mb-3">
              <label class="form-label">Nombre de contacto (opcional)</label>
              <input name="cliente_nombre" id="cliente_nombre_pos" class="form-control" placeholder="Nombre del cliente">
            </div>

            <div class="mb-3">
              <label class="form-label">Teléfono (opcional)</label>
              <input name="cliente_telefono" id="cliente_telefono_pos" type="tel" class="form-control" placeholder="Teléfono de contacto">
            </div>

            <div class="mb-3">
              <label class="form-label">Dirección de entrega <span class="text-danger">*</span></label>
              <input name="direccion_entrega" id="direccion_entrega_pos" class="form-control" 
                     placeholder="Escribe la dirección y selecciona una opción">
              <input type="hidden" name="lat" id="address_lat_pos">
              <input type="hidden" name="lng" id="address_lng_pos">
              <div id="address_validation_info_pos" class="mt-2"></div>
            </div>

            <div class="mb-3">
              <label class="form-label">Chofer <span class="text-danger">*</span></label>
              <select name="courier_id" id="courier_id_pos" class="form-select" required>
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
              <small class="text-secondary">El chofer es obligatorio para entregas a domicilio.</small>
            </div>

            <div class="row g-2 mb-3">
              <div class="col-md-6">
                <label class="form-label">Km estimados</label>
                <input id="km_pos" type="number" min="0" step="1" class="form-control" value="0">
                <small class="text-secondary">Banderazo 10km = $100; + $10 por km adicional.</small>
              </div>
              <div class="col-md-6">
                <label class="form-label">Costo de envío</label>
                <input name="costo_envio" id="costo_envio_pos" type="number" step="0.01" class="form-control" required value="0" min="0">
              </div>
              <div class="col-12">
                <button type="button" class="btn btn-outline-primary w-100" id="btnCalcEnvioPos">
                  <i class="bi bi-geo-alt"></i> Calcular envío automáticamente
                </button>
                <small class="text-secondary d-block mt-1 text-center">
                  <i class="bi bi-info-circle"></i> Calcula desde la dirección o ingresa km manualmente
                </small>
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
        'stock'  => (int) ($p->stock_total ?? 0),
      ];
  })->values();
  
  $bodRows = $bodegas->map(function($b) {
      return ['id' => $b->id, 'nombre' => $b->nombre];
  })->values();
  
  $clientRows = $clientes->map(function($c) {
      return [
        'id' => $c->id,
        'nombre' => $c->nombre ?? '',
        'telefono' => $c->telefono ?? '',
        'direccion' => $c->direccion_entrega ?? '',
      ];
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
  const clientes  = @json($clientRows);
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
  const customerSelect = document.getElementById('customer_id');
  const clienteNombreInput = document.getElementById('cliente_nombre_pos');
  const clienteTelefonoInput = document.getElementById('cliente_telefono_pos');
  
  let autocomplete = null;
  let selectedPlace = null;
  
  // Autocompletar campos cuando se selecciona un cliente
  customerSelect.addEventListener('change', function() {
    const clienteId = this.value;
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
        // Autocompletar dirección si existe y está en modo entrega
        if (cliente.direccion && tipoVentaSelect.value === 'entrega' && direccionInput) {
          direccionInput.value = cliente.direccion;
          // Si hay API key, intentar validar y calcular distancia (esperar a que Google Maps esté cargado)
          if (mapsKey) {
            const loadMapsAndGeocode = function() {
              if (window.google && window.google.maps && window.google.maps.Geocoder) {
                const geocoder = new google.maps.Geocoder();
                geocoder.geocode({ address: cliente.direccion }, function(results, status) {
                  if (status === 'OK' && results[0]) {
                    const lat = results[0].geometry.location.lat();
                    const lng = results[0].geometry.location.lng();
                    document.getElementById('address_lat_pos').value = lat;
                    document.getElementById('address_lng_pos').value = lng;
                    if (window.calculateDistancePos) {
                      window.calculateDistancePos(lat, lng, false);
                    }
                    if (window.validateAddressPos) {
                      const place = {
                        formatted_address: cliente.direccion,
                        geometry: results[0].geometry
                      };
                      validateAddressPos(place);
                    }
                  }
                });
              } else {
                // Esperar un poco más si Google Maps aún no está cargado
                setTimeout(loadMapsAndGeocode, 500);
              }
            };
            loadMapsAndGeocode();
          }
        }
      }
    } else {
      // Limpiar campos si no hay cliente seleccionado
      if (clienteNombreInput) clienteNombreInput.value = '';
      if (clienteTelefonoInput) clienteTelefonoInput.value = '';
    }
  });

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
    return productos.map(p => `<option value="${p.id}" data-price="${p.precio}" data-stock="${p.stock}">${p.descripcion} ${p.stock > 0 ? `(${p.stock} disponibles)` : '(sin stock)'}</option>`).join('');
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
        <small class="stock-warning d-none"></small>
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
        const stock = parseInt(opt.dataset.stock || 0);
        qtyI.max = stock;
        qtyI.min = stock > 0 ? 1 : 0;
        
        // Mostrar advertencia si no hay stock
        const warning = tr.querySelector('.stock-warning');
        if (stock === 0) {
          qtyI.setAttribute('readonly', 'readonly');
          qtyI.classList.add('bg-warning');
          qtyI.value = 0;
          if (warning) {
            warning.className = 'stock-warning text-danger d-block mt-1';
            warning.innerHTML = '<i class="bi bi-exclamation-triangle"></i> Sin stock disponible';
          }
        } else {
          qtyI.removeAttribute('readonly');
          qtyI.classList.remove('bg-warning');
          const currentQty = parseInt(qtyI.value || 1);
          qtyI.value = Math.min(Math.max(currentQty, 1), stock);
          if (warning) {
            warning.className = 'stock-warning d-none';
            warning.innerHTML = '';
          }
        }
        
        calc();
      }
    });
    qtyI.addEventListener('input', function(){
      const opt = prodSel.selectedOptions[0];
      if (opt) {
        const stock = parseInt(opt.dataset.stock || 0);
        const qty = parseInt(this.value || 0);
        
        if (qty > stock) {
          this.value = stock;
          alert(`Stock disponible: ${stock} unidades. Se ajustó la cantidad al máximo disponible.`);
        }
        
        // Mostrar advertencia si la cantidad está cerca del límite
        const warning = tr.querySelector('.stock-warning');
        if (qty > 0 && qty <= stock && stock > 0) {
          if (warning) {
            if (qty === stock) {
              warning.className = 'stock-warning text-warning d-block mt-1';
              warning.innerHTML = `<i class="bi bi-exclamation-triangle"></i> Cantidad máxima disponible`;
            } else if (qty > stock * 0.8) {
              warning.className = 'stock-warning text-warning d-block mt-1';
              warning.innerHTML = `<i class="bi bi-info-circle"></i> Solo quedan ${stock} unidades disponibles`;
            } else {
              warning.className = 'stock-warning d-none';
              warning.innerHTML = '';
            }
          }
        } else if (warning && !warning.classList.contains('text-danger')) {
          warning.className = 'stock-warning d-none';
          warning.innerHTML = '';
        }
      }
      calc();
    });
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
    const courierSelect = document.getElementById('courier_id_pos');
    
    if (this.value === 'entrega') {
      camposEntrega.classList.remove('d-none');
      direccionInput.setAttribute('required', 'required');
      if (courierSelect) courierSelect.setAttribute('required', 'required');
      btnText.textContent = 'Crear pedido';
      
      // Si hay un cliente seleccionado, autocompletar dirección también
      const clienteId = customerSelect.value;
      if (clienteId) {
        const cliente = clientes.find(c => c.id == clienteId);
        if (cliente && cliente.direccion && direccionInput) {
          direccionInput.value = cliente.direccion;
          // Si hay API key, validar y calcular distancia (esperar a que Google Maps esté cargado)
          if (mapsKey) {
            const loadMapsAndGeocode = function() {
              if (window.google && window.google.maps && window.google.maps.Geocoder) {
                const geocoder = new google.maps.Geocoder();
                geocoder.geocode({ address: cliente.direccion }, function(results, status) {
                  if (status === 'OK' && results[0]) {
                    const lat = results[0].geometry.location.lat();
                    const lng = results[0].geometry.location.lng();
                    document.getElementById('address_lat_pos').value = lat;
                    document.getElementById('address_lng_pos').value = lng;
                    if (window.calculateDistancePos) {
                      window.calculateDistancePos(lat, lng, false);
                    }
                    if (window.validateAddressPos) {
                      const place = {
                        formatted_address: cliente.direccion,
                        geometry: results[0].geometry
                      };
                      validateAddressPos(place);
                    }
                  }
                });
              } else {
                // Esperar un poco más si Google Maps aún no está cargado
                setTimeout(loadMapsAndGeocode, 500);
              }
            };
            loadMapsAndGeocode();
          }
        }
      }
    } else {
      camposEntrega.classList.add('d-none');
      direccionInput.removeAttribute('required');
      const courierSelect = document.getElementById('courier_id_pos');
      if (courierSelect) courierSelect.removeAttribute('required');
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
      calculateDistancePos(lat, lng, false);
    });
  }

  // Hacer función global para validar dirección
  window.validateAddressPos = async function(place) {
    const validationInfo = document.getElementById('address_validation_info_pos');
    
    if (!mapsKey) {
      validationInfo.innerHTML = `
        <div class="alert alert-info py-2 mb-0">
          <i class="bi bi-info-circle"></i> 
          <small>API key no configurada. No se puede validar la dirección.</small>
        </div>
      `;
      return;
    }

    if (!place || !place.formatted_address) {
      validationInfo.innerHTML = '';
      return;
    }

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

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

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
        
        // Mostrar dirección formateada sugerida si existe y es diferente
        if (address.formattedAddress && address.formattedAddress !== place.formatted_address) {
          validationInfo.innerHTML += `
            <div class="mt-1">
              <small class="text-secondary">Sugerencia: ${address.formattedAddress}</small>
            </div>
          `;
        }
      } else {
        validationInfo.innerHTML = `
          <div class="alert alert-info py-2 mb-0">
            <i class="bi bi-info-circle"></i> 
            <small>Dirección registrada (no se pudo validar completamente)</small>
          </div>
        `;
      }
    } catch (error) {
      console.error('Error al validar dirección:', error);
      validationInfo.innerHTML = `
        <div class="alert alert-warning py-2 mb-0">
          <i class="bi bi-exclamation-triangle"></i> 
          <small>No se pudo validar la dirección. Verifica que sea correcta.</small>
        </div>
      `;
    }
  };
  
  // Alias para compatibilidad
  async function validateAddressPos(place) {
    return window.validateAddressPos(place);
  }

  // Hacer función global para poder llamarla desde el evento de cliente
  window.calculateDistancePos = function(destLat, destLng, restoreButton = true) {
    if (!mapsKey || !window.google || !window.google.maps) {
      if (restoreButton) {
        const btn = document.getElementById('btnCalcEnvioPos');
        if (btn) {
          btn.disabled = false;
          btn.innerHTML = '<i class="bi bi-geo-alt"></i> Calcular envío automáticamente';
        }
      }
      return;
    }
    
    const distanceInfo = document.getElementById('distance_info_pos');
    const distanceText = document.getElementById('distance_text_pos');
    const btn = document.getElementById('btnCalcEnvioPos');
    
    const origin = { lat: originLat, lng: originLng };
    const destination = { lat: destLat, lng: destLng };
    
    const service = new google.maps.DistanceMatrixService();
    
    service.getDistanceMatrix({
      origins: [origin],
      destinations: [destination],
      travelMode: google.maps.TravelMode.DRIVING,
      unitSystem: google.maps.UnitSystem.METRIC
    }, function(response, status) {
      if (restoreButton && btn) {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-geo-alt"></i> Calcular envío automáticamente';
      }
      
      if (status === 'OK' && response.rows[0].elements[0].status === 'OK') {
        const element = response.rows[0].elements[0];
        const distance = element.distance.value / 1000; // en km
        const distanceKm = Math.round(distance); // Redondear km a entero
        const duration = element.duration.text;
        
        distanceText.textContent = `Distancia: ${element.distance.text} | Tiempo: ${duration}`;
        distanceInfo.classList.remove('d-none');
        
        document.getElementById('km_pos').value = distanceKm;
        document.getElementById('costo_envio_pos').value = calcularEnvioPos(distanceKm);
      } else {
        distanceInfo.classList.add('d-none');
        if (status !== 'OK') {
          alert('No se pudo calcular la distancia. Verifica la dirección o ingresa los km manualmente.');
        }
      }
    });
  };
  
  // Calcular distancia desde origen (alias para compatibilidad)
  function calculateDistancePos(destLat, destLng) {
    window.calculateDistancePos(destLat, destLng);
  }

  // Cálculo de envío: banderazo 10 km = $100; + $10 por km adicional
  function calcularEnvioPos(km) {
    km = Number(km || 0);
    if (km <= 10) return 100;
    return 100 + Math.ceil(km - 10) * 10;
  }

  document.getElementById('btnCalcEnvioPos').addEventListener('click', function() {
    const btn = this;
    const kmInput = document.getElementById('km_pos');
    const latInput = document.getElementById('address_lat_pos');
    const lngInput = document.getElementById('address_lng_pos');
    const direccionText = direccionInput.value.trim();
    const lat = latInput.value;
    const lng = lngInput.value;
    const distanceInfo = document.getElementById('distance_info_pos');
    const distanceText = document.getElementById('distance_text_pos');
    
    // Deshabilitar botón mientras calcula
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Calculando...';
    
    // Si hay coordenadas, calcular directamente
    if (lat && lng && mapsKey && window.google && window.google.maps) {
      calculateDistancePos(parseFloat(lat), parseFloat(lng), true);
      return;
    }
    
    // Si hay dirección escrita pero no coordenadas, intentar geocodificar
    if (direccionText && mapsKey && window.google && window.google.maps && window.google.maps.Geocoder) {
      const geocoder = new google.maps.Geocoder();
      geocoder.geocode({ address: direccionText }, function(results, status) {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-geo-alt"></i> Calcular envío';
        
        if (status === 'OK' && results[0]) {
          const lat = results[0].geometry.location.lat();
          const lng = results[0].geometry.location.lng();
          latInput.value = lat;
          lngInput.value = lng;
          calculateDistancePos(lat, lng, true);
        } else {
          // Si falla la geocodificación, usar el km manual
          const kmManual = Math.round(parseFloat(kmInput.value) || 0);
          kmInput.value = kmManual;
          document.getElementById('costo_envio_pos').value = calcularEnvioPos(kmManual);
          
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
      btn.innerHTML = '<i class="bi bi-geo-alt"></i> Calcular envío';
      alert('Ingresa la distancia en kilómetros o selecciona una dirección válida para calcular automáticamente.');
      kmInput.focus();
      return;
    }
    
    kmInput.value = kmManual;
    document.getElementById('costo_envio_pos').value = calcularEnvioPos(kmManual);
    distanceInfo.classList.add('d-none');
    
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-geo-alt"></i> Calcular envío';
  });

  document.getElementById('km_pos').addEventListener('input', function() {
    const kmValue = Math.round(parseFloat(this.value) || 0);
    this.value = kmValue;
    document.getElementById('costo_envio_pos').value = calcularEnvioPos(kmValue);
  });
  @endif

  // Serializa items al enviar
  document.getElementById('posForm').addEventListener('submit', (e)=>{
    // Si es venta con entrega, validar campos obligatorios
    if (tipoVentaSelect?.value === 'entrega') {
      // Validar coordenadas
      const lat = document.getElementById('address_lat_pos')?.value;
      const lng = document.getElementById('address_lng_pos')?.value;
      
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
      const courierId = document.getElementById('courier_id_pos')?.value;
      if (!courierId) {
        e.preventDefault();
        alert('El chofer es obligatorio para entregas a domicilio.');
        document.getElementById('courier_id_pos')?.focus();
        return false;
      }
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
