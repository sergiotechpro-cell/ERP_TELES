@extends('layouts.erp')
@section('title','Nueva garantía')

@section('content')
<x-flash />

<div class="container" style="max-width: 1000px;">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <h3 class="fw-bold mb-0"><i class="bi bi-shield-check me-2"></i>Nueva garantía</h3>
    <a href="{{ route('garantias.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i> Volver</a>
  </div>

  <div class="card border-0 shadow-sm" style="border-radius:16px;">
    <div class="card-body">
      <form method="POST" action="{{ route('garantias.store') }}" id="claimForm" class="row g-3">
        @csrf

        {{-- Paso 1: Seleccionar Pedido --}}
        <div class="col-12">
          <div class="alert alert-info d-flex align-items-center" style="border-radius: 12px;">
            <i class="bi bi-info-circle-fill me-2" style="font-size: 1.5rem;"></i>
            <div>
              <strong>Paso 1:</strong> Selecciona el pedido para ver los productos y números de serie disponibles
            </div>
          </div>
        </div>

        <div class="col-md-12">
          <label class="form-label fw-semibold">
            <i class="bi bi-receipt me-2"></i>Pedido
          </label>
          <select name="order_id" class="form-select form-select-lg" id="orderSel" required>
            <option value="">Selecciona un pedido...</option>
            @foreach($pedidos as $p)
              <option value="{{ $p->id }}" @selected(old('order_id')==$p->id)>
                #{{ $p->id }} — {{ Str::limit($p->direccion_entrega, 60) }} — {{ $p->created_at->format('d/m/Y') }}
              </option>
            @endforeach
          </select>
          @error('order_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>

        {{-- Paso 2: Contenedor de productos del pedido --}}
        <div class="col-12" id="orderItemsContainer" style="display: none;">
          <hr class="my-4">
          <div class="alert alert-success d-flex align-items-center" style="border-radius: 12px;">
            <i class="bi bi-check-circle-fill me-2" style="font-size: 1.5rem;"></i>
            <div>
              <strong>Paso 2:</strong> Selecciona el producto y número de serie del pedido
            </div>
          </div>
          
          <div id="orderItemsList" class="row g-3">
            <!-- Se llenará dinámicamente con JavaScript -->
          </div>
        </div>

        {{-- Campos ocultos para product_id y serial_number_id --}}
        <input type="hidden" name="product_id" id="productIdInput">
        <input type="hidden" name="serial_number_id" id="serialNumberIdInput">
        <input type="hidden" name="numero_serie" id="numeroSerieInput">

        {{-- Paso 3: Detalles de la garantía --}}
        <div class="col-12" id="warrantyDetailsContainer" style="display: none;">
          <hr class="my-4">
          <div class="alert alert-primary d-flex align-items-center" style="border-radius: 12px;">
            <i class="bi bi-clipboard-check me-2" style="font-size: 1.5rem;"></i>
            <div>
              <strong>Paso 3:</strong> Proporciona los detalles de la garantía
            </div>
          </div>

          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label fw-semibold">
                <i class="bi bi-calendar-event me-2"></i>Fecha de compra
              </label>
              <input type="date" name="fecha_compra"
                     class="form-control"
                     value="{{ old('fecha_compra', now()->toDateString()) }}" required>
              @error('fecha_compra') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-8">
              <label class="form-label fw-semibold">
                <i class="bi bi-exclamation-triangle me-2"></i>Motivo de la garantía
              </label>
              <input name="motivo" class="form-control" value="{{ old('motivo') }}" required
                     placeholder="Ej: Falla de encendido, líneas en pantalla, no carga...">
              @error('motivo') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="col-12">
              <label class="form-label fw-semibold">
                <i class="bi bi-clipboard-data me-2"></i>Condición del producto
              </label>
              <textarea name="condicion" class="form-control" rows="3"
                        placeholder="Describe el estado físico del producto (rayones, golpes, pantalla, accesorios incluidos, etc.)">{{ old('condicion') }}</textarea>
              @error('condicion') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
              <div class="form-text">
                <i class="bi bi-info-circle"></i> Opcional: Describe el estado físico del producto
              </div>
            </div>
          </div>
        </div>

        <div class="col-12 d-flex justify-content-end gap-2 mt-4">
          <a href="{{ route('garantias.index') }}" class="btn btn-light btn-lg">
            <i class="bi bi-x-circle"></i> Cancelar
          </a>
          <button class="btn btn-primary btn-lg" id="submitBtn" disabled>
            <i class="bi bi-check2-circle"></i> Registrar garantía
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  // Mejorar UX: Carga de items del pedido con números de serie
  const orderSel = document.getElementById('orderSel');
  const orderItemsContainer = document.getElementById('orderItemsContainer');
  const orderItemsList = document.getElementById('orderItemsList');
  const warrantyDetailsContainer = document.getElementById('warrantyDetailsContainer');
  const submitBtn = document.getElementById('submitBtn');
  const productIdInput = document.getElementById('productIdInput');
  const serialNumberIdInput = document.getElementById('serialNumberIdInput');
  const numeroSerieInput = document.getElementById('numeroSerieInput');

  let selectedItem = null;

  // Al seleccionar un pedido, cargar sus items
  orderSel?.addEventListener('change', async (e) => {
    const orderId = e.target.value;
    
    // Reset
    orderItemsContainer.style.display = 'none';
    warrantyDetailsContainer.style.display = 'none';
    submitBtn.disabled = true;
    productIdInput.value = '';
    serialNumberIdInput.value = '';
    numeroSerieInput.value = '';
    selectedItem = null;
    
    if (!orderId) return;

    try {
      orderItemsList.innerHTML = `
        <div class="col-12 text-center py-4">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando...</span>
          </div>
          <p class="mt-2 text-secondary">Cargando productos del pedido...</p>
        </div>
      `;
      orderItemsContainer.style.display = 'block';

      const res = await fetch(`/api/pedidos/${orderId}/items`);
      const items = await res.json();

      if (!items || items.length === 0) {
        orderItemsList.innerHTML = `
          <div class="col-12">
            <div class="alert alert-warning">
              <i class="bi bi-exclamation-triangle"></i> Este pedido no tiene productos con números de serie.
            </div>
          </div>
        `;
        return;
      }

      // Renderizar los items del pedido
      orderItemsList.innerHTML = items.map((item, idx) => {
        const hasSerials = item.seriales && item.seriales.length > 0;
        
        return `
          <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-radius: 12px; transition: all 0.3s ease;">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                  <div>
                    <h6 class="mb-1 fw-bold">
                      <i class="bi bi-box-seam text-primary"></i> ${item.product_name}
                    </h6>
                    <small class="text-secondary">
                      <i class="bi bi-stack"></i> Cantidad: ${item.cantidad} unidad(es)
                    </small>
                  </div>
                  ${hasSerials ? `
                    <span class="badge bg-success-subtle text-success-emphasis">
                      <i class="bi bi-check-circle"></i> ${item.seriales.length} número(s) de serie
                    </span>
                  ` : `
                    <span class="badge bg-warning-subtle text-warning-emphasis">
                      <i class="bi bi-exclamation-circle"></i> Sin números de serie
                    </span>
                  `}
                </div>

                ${hasSerials ? `
                  <div class="mt-3">
                    <label class="form-label fw-semibold small text-uppercase text-secondary">
                      <i class="bi bi-qr-code-scan"></i> Selecciona el Número de Serie:
                    </label>
                    <div class="d-flex flex-wrap gap-2">
                      ${item.seriales.map((serial, serialIdx) => `
                        <button type="button" 
                                class="btn btn-outline-primary serial-btn" 
                                data-item-idx="${idx}"
                                data-product-id="${item.product_id}"
                                data-serial="${serial}"
                                style="font-family: 'Courier New', monospace; font-weight: 500; border-radius: 8px; transition: all 0.2s ease;">
                          <i class="bi bi-qr-code"></i> ${serial}
                        </button>
                      `).join('')}
                    </div>
                    <small class="text-secondary d-block mt-2">
                      <i class="bi bi-hand-index"></i> Haz clic en el número de serie para seleccionarlo
                    </small>
                  </div>
                ` : `
                  <div class="mt-3">
                    <div class="alert alert-info mb-3">
                      <i class="bi bi-info-circle"></i> Este producto no tiene números de serie en el pedido. Puedes ingresar uno manualmente.
                    </div>
                    <div class="d-flex gap-2 align-items-end">
                      <div class="flex-grow-1">
                        <label class="form-label small fw-semibold">
                          <i class="bi bi-keyboard"></i> Ingresa el número de serie:
                        </label>
                        <input type="text" 
                               class="form-control manual-serial-input" 
                               data-product-id="${item.product_id}"
                               placeholder="Ej: TV-ABC123456"
                               style="font-family: 'Courier New', monospace;">
                      </div>
                      <button type="button" 
                              class="btn btn-primary manual-serial-btn"
                              data-product-id="${item.product_id}"
                              style="white-space: nowrap;">
                        <i class="bi bi-check-circle"></i> Usar este serial
                      </button>
                    </div>
                  </div>
                `}
              </div>
            </div>
          </div>
        `;
      }).join('');

      // Agregar event listeners a los botones de números de serie del pedido
      document.querySelectorAll('.serial-btn').forEach(btn => {
        btn.addEventListener('click', function() {
          selectSerial(this.dataset.productId, this.dataset.serial, this);
        });
      });

      // Agregar event listeners para los botones de serial manual
      document.querySelectorAll('.manual-serial-btn').forEach(btn => {
        btn.addEventListener('click', function() {
          const productId = this.dataset.productId;
          const input = this.closest('.d-flex').querySelector('.manual-serial-input');
          const serial = input.value.trim();
          
          if (!serial) {
            alert('Por favor, ingresa un número de serie');
            input.focus();
            return;
          }
          
          selectSerial(productId, serial, this);
        });
      });

      // Función para seleccionar un serial
      function selectSerial(productId, serial, button) {
        // Remover selección anterior
        document.querySelectorAll('.serial-btn, .manual-serial-btn').forEach(b => {
          b.classList.remove('btn-primary', 'active');
          if (b.classList.contains('serial-btn')) {
            b.classList.add('btn-outline-primary');
          }
        });
        
        // Marcar como seleccionado
        if (button.classList.contains('serial-btn')) {
          button.classList.remove('btn-outline-primary');
          button.classList.add('btn-primary', 'active');
        } else {
          button.classList.add('active');
        }
        
        // Actualizar campos ocultos
        productIdInput.value = productId;
        numeroSerieInput.value = serial;
        
        // Buscar el serial_number_id desde la API
        fetchSerialNumberId(productId, serial);
        
        // Mostrar sección de detalles
        warrantyDetailsContainer.style.display = 'block';
        submitBtn.disabled = false;
        
        // Scroll suave a la sección de detalles
        setTimeout(() => {
          warrantyDetailsContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }, 100);
      }

    } catch(err){
      console.error(err);
      orderItemsList.innerHTML = `
        <div class="col-12">
          <div class="alert alert-danger">
            <i class="bi bi-x-circle"></i> Error al cargar los productos del pedido. Por favor, intenta de nuevo.
          </div>
        </div>
      `;
    }
  });

  // Función para buscar el ID del número de serie
  async function fetchSerialNumberId(productId, serialText) {
    try {
      const res = await fetch(`/api/seriales?product_id=${productId}`);
      const serials = await res.json();
      const found = serials.find(s => s.numero_serie === serialText);
      if (found) {
        serialNumberIdInput.value = found.id;
      }
    } catch(err) {
      console.error('Error fetching serial number ID:', err);
    }
  }
</script>

@push('styles')
<style>
  .serial-btn.active {
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.4);
    transform: translateY(-2px);
  }
  
  .serial-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  }
  
  .card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12) !important;
  }
</style>
@endpush
@endpush
