@extends('layouts.erp')
@section('title','Módulo de Garantías')

@section('content')
<x-flash />

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-shield-check me-2"></i>Módulo de Garantías</h4>
        <p class="text-secondary mb-0">Escanea el ticket para conocer el estatus, crea nuevas garantías o cierra las existentes.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('garantias.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-list-ul"></i> Historial completo
        </a>
        <a href="{{ route('garantias.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nueva garantía
        </a>
    </div>
</div>

<div class="row g-3">
    {{-- Revisar status --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
            <div class="card-body">
                <h5 class="fw-bold mb-3"><i class="bi bi-upc-scan me-2"></i>Revisar status</h5>
                <form method="GET" action="{{ route('garantias.module') }}" class="row g-2">
                    <div class="col-9">
                        <label class="form-label">Escanea o captura el número de serie</label>
                        <input type="text" name="serial" value="{{ $serialQuery }}" class="form-control form-control-lg text-uppercase"
                               placeholder="Ej. SN-123ABC" autofocus>
                    </div>
                    <div class="col-3 d-flex align-items-end">
                        <button class="btn btn-dark w-100" type="submit">
                            <i class="bi bi-search"></i> Buscar
                        </button>
                    </div>
                </form>

                @if($serialResult)
                    <div class="mt-4">
                        @if($serialResult['found'])
                            @php
                                $serial = $serialResult['serial'];
                                $claim = $serialResult['claim'];
                            @endphp
                            <div class="alert alert-success border-0 shadow-sm">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="fw-bold mb-1">{{ $serial->numero_serie }}</h6>
                                        <p class="mb-0 text-secondary">
                                            Producto: {{ $serial->warehouseProduct?->product?->descripcion ?? 'N/A' }}<br>
                                            Estado actual: <strong class="text-capitalize">{{ $serial->estado }}</strong>
                                        </p>
                                    </div>
                                    <span class="badge text-bg-dark">{{ $serial->warehouseProduct?->product?->id ?? '—' }}</span>
                                </div>
                            </div>
                            @if($claim)
                                <div class="border rounded p-3 bg-light">
                                    <div class="d-flex justify-content-between mb-2">
                                        <strong>Garantía #{{ $claim->id }}</strong>
                                        <span class="badge text-bg-{{ $claim->status === 'cerrada' ? 'success' : 'warning' }}">
                                            {{ ucfirst($claim->status) }}
                                        </span>
                                    </div>
                                    <p class="mb-1 text-secondary">
                                        Motivo: {{ $claim->motivo }}<br>
                                        Producto: {{ $claim->product?->descripcion ?? 'N/A' }}
                                    </p>
                                    <small class="text-secondary">
                                        Registrada el {{ $claim->created_at?->format('d/m/Y H:i') }} por pedido #{{ $claim->order_id }}
                                    </small>
                                </div>
                            @else
                                <div class="alert alert-info mt-3 mb-0">
                                    No se han registrado garantías previas para este número de serie.
                                </div>
                            @endif
                        @else
                            <div class="alert alert-danger mt-4 mb-0">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                No encontramos el número de serie <strong>{{ $serialQuery }}</strong>. Verifica que esté correcto.
                            </div>
                        @endif
                    </div>
                @elseif($serialQuery !== '')
                    <div class="alert alert-warning mt-4 mb-0">
                        Ingresa un número de serie válido.
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Crear nueva garantía --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
            <div class="card-body d-flex flex-column">
                <h5 class="fw-bold mb-3"><i class="bi bi-file-earmark-plus me-2"></i>Crear nueva garantía</h5>
                <p class="text-secondary flex-grow-1">
                    Registra un nuevo ticket de garantía seleccionando el pedido, producto y número de serie involucrado.
                    Puedes complementar el detalle con fotos o notas adicionales después del registro.
                </p>
                <div class="d-flex gap-2">
                    <a href="{{ route('garantias.create') }}" class="btn btn-primary w-50">
                        <i class="bi bi-plus-circle"></i> Registrar
                    </a>
                    <a href="{{ route('garantias.index') }}" class="btn btn-outline-secondary w-50">
                        <i class="bi bi-clock-history"></i> Ver historial
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Cerrar garantías --}}
<div class="card border-0 shadow-sm mt-4" style="border-radius:16px;">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0"><i class="bi bi-x-circle me-2"></i>Cerrar garantía</h5>
            <small class="text-secondary">Mostrando las 10 más recientes abiertas / en revisión</small>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Garantía</th>
                        <th>Producto / Serie</th>
                        <th>Motivo</th>
                        <th>Status</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($openClaims as $claim)
                        <tr>
                            <td>
                                <div class="fw-semibold">#{{ $claim->id }}</div>
                                <small class="text-secondary">
                                    Pedido #{{ $claim->order_id }} · {{ $claim->created_at?->format('d/m/Y H:i') }}
                                </small>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $claim->product?->descripcion ?? 'N/A' }}</div>
                                <small class="text-secondary">
                                    Serie: {{ $claim->serialNumber?->numero_serie ?? 'No registrado' }}
                                </small>
                            </td>
                            <td>{{ $claim->motivo }}</td>
                            <td>
                                <span class="badge text-bg-{{ $claim->status === 'abierta' ? 'warning' : 'info' }}">
                                    {{ ucfirst($claim->status) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('garantias.close', $claim) }}"
                                      onsubmit="return confirm('¿Cerrar la garantía #{{ $claim->id }}?');">
                                    @csrf
                                    <button class="btn btn-sm btn-success">
                                        <i class="bi bi-check-lg"></i> Cerrar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-secondary py-4">
                                No hay garantías abiertas por el momento.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

