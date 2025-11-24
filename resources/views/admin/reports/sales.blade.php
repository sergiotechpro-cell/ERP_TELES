@extends('layouts.erp')
@section('title','Administración - Reporte de Ventas y Stock')

@section('content')
<x-flash />

<div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="bi bi-clipboard-data me-2"></i>
            Reporte de ventas y salidas de stock
        </h4>
        <p class="text-secondary mb-0">Consulta el historial de ventas, números de serie y stock restante.</p>
    </div>
    <div>
        <small class="text-muted">Periodo: {{ $filters['from'] }} al {{ $filters['to'] }}</small>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
    <div class="card-body">
        <form class="row g-3 align-items-end" method="GET">
            <div class="col-md-3">
                <label class="form-label">Desde</label>
                <input type="date" class="form-control" name="from" value="{{ $filters['from'] }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Hasta</label>
                <input type="date" class="form-control" name="to" value="{{ $filters['to'] }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Producto</label>
                <select name="product_id" class="form-select">
                    <option value="">Todos</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" @selected($filters['product_id'] == $product->id)>
                            {{ $product->descripcion }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Forma de pago</label>
                <select name="forma_pago" class="form-select">
                    <option value="">Todas</option>
                    <option value="efectivo" @selected($filters['forma_pago'] === 'efectivo')>Efectivo</option>
                    <option value="tarjeta" @selected($filters['forma_pago'] === 'tarjeta')>Tarjeta</option>
                    <option value="transferencia" @selected($filters['forma_pago'] === 'transferencia')>Transferencia</option>
                </select>
            </div>
            <div class="col-md-1 text-end">
                <button class="btn btn-primary w-100" type="submit">
                    <i class="bi bi-funnel"></i>
                </button>
            </div>
        </form>
    </div>
</div>

@php
    $ventas = (int)($summary->total_ventas ?? 0);
    $totalMonto = (float)($summary->total_monto ?? 0);
    $totalUnidades = (int)($summary->total_unidades ?? 0);
    $ticketPromedio = $ventas > 0 ? $totalMonto / $ventas : 0;
@endphp

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
            <div class="card-body">
                <p class="text-secondary mb-1">Ingresos del periodo</p>
                <h3 class="fw-bold">${{ number_format($totalMonto, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
            <div class="card-body">
                <p class="text-secondary mb-1">Ventas registradas</p>
                <h3 class="fw-bold">{{ number_format($ventas) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
            <div class="card-body">
                <p class="text-secondary mb-1">Unidades vendidas</p>
                <h3 class="fw-bold">{{ number_format($totalUnidades) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
            <div class="card-body">
                <p class="text-secondary mb-1">Ticket promedio</p>
                <h3 class="fw-bold">${{ number_format($ticketPromedio, 2) }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm" style="border-radius:16px;">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0"><i class="bi bi-clock-history me-2"></i>Historial del periodo</h5>
            <small class="text-secondary">{{ $saleItems->total() }} registros encontrados</small>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Venta</th>
                        <th>Producto</th>
                        <th class="text-center">Cantidad</th>
                        <th>Seriales</th>
                        <th class="text-center">Stock actual</th>
                        <th>Forma de pago</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($saleItems as $item)
                        @php
                            $venta = $item->sale;
                            $cliente = $venta?->customer?->nombre ?? 'Mostrador';
                            $seriales = collect($item->seriales ?? []);
                            $stockRestante = $item->product?->warehouses->sum(fn($w) => $w->pivot->stock ?? 0) ?? 0;
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-semibold">#{{ $venta?->id ?? 'N/A' }}</div>
                                <small class="text-secondary d-block">{{ $venta?->created_at?->format('d/m/Y H:i') }}</small>
                                <small class="text-secondary d-block"><i class="bi bi-person"></i> {{ $cliente }}</small>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $item->product?->descripcion ?? 'Producto eliminado' }}</div>
                                <small class="text-secondary">Vendedor: {{ $venta?->user?->name ?? 'N/A' }}</small>
                            </td>
                            <td class="text-center">
                                <span class="badge text-bg-primary">{{ $item->cantidad }}</span>
                            </td>
                            <td>
                                @if($seriales->isEmpty())
                                    <span class="text-secondary">—</span>
                                @else
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($seriales as $serial)
                                            <span class="badge rounded-pill text-bg-light border">{{ $serial }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge text-bg-info">{{ $stockRestante }}</span>
                            </td>
                            <td class="text-capitalize">{{ $venta?->forma_pago ?? 'N/A' }}</td>
                            <td>
                                <strong>${{ number_format($item->cantidad * $item->precio_unitario, 2) }}</strong>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-secondary py-4">
                                <i class="bi bi-inboxes fs-1 d-block mb-2"></i>
                                No se encontraron movimientos con los filtros seleccionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $saleItems->links() }}
        </div>
    </div>
</div>
@endsection

