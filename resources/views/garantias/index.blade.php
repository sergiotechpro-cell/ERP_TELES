@extends('layouts.erp')
@section('title','Garantías')

@section('content')
<x-flash />

<div class="d-flex align-items-center justify-content-between mb-4">
  <h3 class="fw-bold mb-0"><i class="bi bi-shield-check me-2"></i>Garantías</h3>
  <a href="{{ route('garantias.create') }}" class="btn btn-primary">
    <i class="bi bi-plus-lg"></i> Nueva garantía
  </a>
</div>

@if($claims->count()===0)
  <x-empty icon="bi-clipboard2-x" title="Sin garantías" text="Registra tu primera garantía."/>
@else
  <div class="card border-0 shadow-sm">
    <div class="table-responsive">
      <table class="table align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Pedido</th>
            <th>Producto</th>
            <th>N° de serie</th>
            <th>Motivo</th>
            <th>Estado</th>
            <th>Creado</th>
          </tr>
        </thead>
        <tbody>
          @foreach($claims as $c)
          <tr>
            <td>{{ $c->id }}</td>
            <td>#{{ $c->order_id }}</td>
            <td>{{ $c->product?->descripcion ?? '—' }}</td>
            <td>{{ $c->serialNumber?->numero_serie ?? '—' }}</td>
            <td class="text-truncate" style="max-width:320px">{{ $c->motivo }}</td>
            <td><x-status-badge :status="$c->status"/></td>
            <td>{{ $c->created_at?->format('Y-m-d H:i') }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <div class="card-body">
      {{ $claims->links() }}
    </div>
  </div>
@endif
@endsection
