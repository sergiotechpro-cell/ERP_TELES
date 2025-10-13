@extends('layouts.erp')
@section('title','Detalle de pedido')

@section('content')
<x-flash />

<div class="container-fluid">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <h3 class="fw-bold mb-0"><i class="bi bi-receipt me-2"></i> Pedido #{{ $pedido->id }}</h3>
    <a href="{{ route('pedidos.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i> Volver</a>
  </div>

  <div class="row g-3">
    <div class="col-12 col-lg-7">
      <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <div class="text-secondary">Cliente</div>
              <div class="fw-bold">{{ $pedido->customer->nombre ?? '—' }}</div>
              <div class="text-secondary mt-2">Dirección</div>
              <div class="fw-medium">{{ $pedido->direccion_entrega }}</div>
            </div>
            <div>
              <x-status-badge :status="$pedido->estado"/>
            </div>
          </div>
          <hr>
          <h6 class="text-secondary">Productos</h6>
          <div class="table-responsive">
            <table class="table align-middle">
              <thead class="table-light">
                <tr>
                  <th>Producto</th><th>Bodega</th><th>Cant.</th><th>Precio</th><th>Subtotal</th>
                </tr>
              </thead>
              <tbody>
                @php $total=0; @endphp
                @foreach($pedido->items as $it)
                  @php $sub = $it->precio_unitario * $it->cantidad; $total+=$sub; @endphp
                  <tr>
                    <td>{{ $it->product->descripcion ?? '—' }}</td>
                    <td>{{ $it->warehouse->nombre ?? '—' }}</td>
                    <td>{{ $it->cantidad }}</td>
                    <td>${{ number_format($it->precio_unitario,2) }}</td>
                    <td>${{ number_format($sub,2) }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          <div class="d-flex justify-content-end">
            <div class="text-end">
              <div class="text-secondary">Envío</div>
              <div class="fw-bold">${{ number_format($pedido->costo_envio,2) }}</div>
              <div class="text-secondary mt-2">Total</div>
              <div class="fs-5 fw-bold">
                ${{ number_format($total + ($pedido->costo_envio ?? 0),2) }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-5">
      <div class="card border-0 shadow-sm mb-3" style="border-radius:16px;">
        <div class="card-body">
          <h6 class="mb-3"><i class="bi bi-truck me-2"></i>Asignar repartidor</h6>
          <form method="POST" action="{{ route('logistica.assign') }}" class="row g-2">
            @csrf
            <input type="hidden" name="order_id" value="{{ $pedido->id }}">
            <div class="col-12">
              <label class="form-label">Usuario (courier)</label>
              <select name="courier_id" class="form-select" required>
                <option value="">Selecciona...</option>
                @foreach(\App\Models\User::orderBy('name')->get() as $u)
                  <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                @endforeach
              </select>
            </div>
            <div class="col-12 d-flex justify-content-end">
              <button class="btn btn-primary"><i class="bi bi-check2"></i> Asignar</button>
            </div>
          </form>
        </div>
      </div>

      <div class="card border-0 shadow-sm" style="border-radius:16px;">
        <div class="card-body">
          <h6 class="mb-3"><i class="bi bi-upc-scan me-2"></i>Escaneo de serie</h6>
          <form method="POST" action="{{ route('logistica.scan') }}" class="row g-2">
            @csrf
            <input type="hidden" name="order_id" value="{{ $pedido->id }}">
            <div class="col-12">
              <label class="form-label">Número de serie</label>
              <input name="numero_serie" class="form-control" placeholder="TV-XXXXXX..." required>
            </div>
            <div class="col-12">
              <label class="form-label">Tipo</label>
              <select name="tipo" class="form-select" required>
                <option value="salida_bodega">Salida de bodega</option>
                <option value="entrega_cliente">Entrega a cliente</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">Notas / Meta (opcional)</label>
              <input name="meta[nota]" class="form-control" placeholder="Modelo, evidencia, etc.">
            </div>
            <div class="col-12 d-flex justify-content-end">
              <button class="btn btn-outline-primary"><i class="bi bi-qr-code-scan"></i> Registrar</button>
            </div>
          </form>
        </div>
      </div>
    </div>

  </div>
</div>
@endsection
