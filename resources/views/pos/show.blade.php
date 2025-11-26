@extends('layouts.erp')
@section('title','Detalle de venta')

@section('content')
<x-flash />

<div class="container-fluid">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <h3 class="fw-bold mb-0"><i class="bi bi-receipt me-2"></i> Venta #{{ $pos->id }}</h3>
    <div class="d-flex gap-2">
      <form action="{{ route('pos.destroy', $pos) }}" method="POST" 
            onsubmit="return confirm('¿Eliminar esta venta? Esta acción no se puede deshacer.')">
        @csrf @method('DELETE')
        <button class="btn btn-outline-danger" type="submit">
          <i class="bi bi-trash"></i> Eliminar
        </button>
      </form>
      <a href="{{ route('pos.index') }}" class="btn btn-light">
        <i class="bi bi-arrow-left"></i> Volver
      </a>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-12 col-lg-8">
      <div class="card border-0 shadow-sm" style="border-radius:16px;">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
              <div class="text-secondary small">Fecha y hora</div>
              <div class="fw-medium">{{ $pos->created_at?->format('d M Y H:i') }}</div>
            </div>
            <div>
              <span class="badge rounded-pill 
                @if($pos->status==='pagada') text-bg-success 
                @elseif($pos->status==='cancelada') text-bg-danger 
                @else text-bg-warning 
                @endif fs-6">
                {{ ucfirst($pos->status) }}
              </span>
            </div>
          </div>
          
          <div class="mb-3">
            <div class="text-secondary small">Forma de pago</div>
            <div class="fw-medium text-capitalize">{{ $pos->forma_pago ?? '—' }}</div>
          </div>

          @if($pos->customer)
          <div class="mb-3">
            <div class="text-secondary small">Cliente</div>
            <div class="fw-medium">
              {{ $pos->customer->nombre }}
              @if($pos->customer->es_empresa)
                <span class="badge text-bg-primary ms-2">Empresa</span>
              @endif
            </div>
            @if($pos->customer->telefono || $pos->customer->email)
            <div class="text-secondary small mt-1">
              @if($pos->customer->telefono)
                <i class="bi bi-telephone"></i> {{ $pos->customer->telefono }}
              @endif
              @if($pos->customer->email)
                <span class="ms-2"><i class="bi bi-envelope"></i> {{ $pos->customer->email }}</span>
              @endif
            </div>
            @endif
          </div>
          @endif

          @if($pos->user)
          <div class="mb-3">
            <div class="text-secondary small">Atendido por</div>
            <div class="fw-medium">{{ $pos->user->name }}</div>
          </div>
          @endif

          <hr>

          <h6 class="text-secondary mb-3"><i class="bi bi-cart-plus me-2"></i> Productos</h6>
          <div class="table-responsive">
            <table class="table align-middle">
              <thead class="table-light">
                <tr>
                  <th>Producto</th>
                  <th>Cantidad</th>
                  <th>Precio unit.</th>
                  <th>Subtotal</th>
                </tr>
              </thead>
              <tbody>
                @php $total=0; @endphp
                @foreach($pos->items as $it)
                  @php 
                    $sub = $it->precio_unitario * $it->cantidad; 
                    $total+=$sub; 
                  @endphp
                  <tr>
                    <td>
                      <div class="fw-medium">{{ $it->product->descripcion ?? '—' }}</div>
                      @if(!empty($it->seriales) && is_array($it->seriales))
                        <div class="mt-2">
                          <small class="text-secondary d-block mb-1">
                            <i class="bi bi-upc-scan"></i> Números de Serie:
                          </small>
                          <div class="d-flex flex-wrap gap-1">
                            @foreach($it->seriales as $serial)
                              <span class="badge bg-success-subtle text-success-emphasis" style="font-family: 'Courier New', monospace; font-size: 0.8rem;">
                                <i class="bi bi-qr-code-scan"></i> {{ $serial }}
                              </span>
                            @endforeach
                          </div>
                        </div>
                      @endif
                    </td>
                    <td>{{ $it->cantidad }}</td>
                    <td>${{ number_format($it->precio_unitario,2) }}</td>
                    <td class="fw-semibold">${{ number_format($sub,2) }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          
          <div class="d-flex justify-content-end mt-4">
            <div class="text-end">
              <div class="text-secondary">Subtotal</div>
              <div class="fw-bold">${{ number_format($pos->subtotal ?? $total,2) }}</div>
              @if($pos->envio > 0)
              <div class="text-secondary mt-2">Envío</div>
              <div class="fw-bold">${{ number_format($pos->envio,2) }}</div>
              @endif
              <hr>
              <div class="text-secondary">Total</div>
              <div class="fs-4 fw-bold">
                ${{ number_format($pos->total,2) }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-4">
      <div class="card border-0 shadow-sm" style="border-radius:16px;">
        <div class="card-body">
          <h6 class="mb-3"><i class="bi bi-info-circle me-2"></i>Información adicional</h6>
          
          <div class="mb-3">
            <div class="text-secondary small">ID de venta</div>
            <div class="fw-medium">#{{ $pos->id }}</div>
          </div>

          <div class="mb-3">
            <div class="text-secondary small">Productos</div>
            <div class="fw-medium">{{ $pos->items->count() }} producto(s)</div>
          </div>

          @php
            $totalItems = $pos->items->sum('cantidad');
          @endphp
          <div class="mb-3">
            <div class="text-secondary small">Total de unidades</div>
            <div class="fw-medium">{{ $totalItems }}</div>
          </div>

          <div class="mb-3">
            <div class="text-secondary small">Creado</div>
            <div class="fw-medium">{{ $pos->created_at?->format('d/m/Y H:i') }}</div>
          </div>

          @if($pos->updated_at != $pos->created_at)
          <div class="mb-3">
            <div class="text-secondary small">Última actualización</div>
            <div class="fw-medium">{{ $pos->updated_at?->format('d/m/Y H:i') }}</div>
          </div>
          @endif

          <hr>

          <div class="alert alert-info">
            <i class="bi bi-lightbulb"></i>
            <small>Esta es una venta de mostrador. No incluye información de envío.</small>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

