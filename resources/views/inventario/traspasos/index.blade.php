@extends('layouts.erp')
@section('title','Traspasos')
@section('content')
<x-flash/>
<div class="d-flex justify-content-between mb-3">
  <h3 class="fw-bold"><i class="bi bi-arrow-left-right me-2"></i>Traspasos</h3>
  <a href="{{ route('traspasos.create') }}" class="btn btn-primary"><i class="bi bi-plus"></i> Nuevo</a>
</div>
<div class="card border-0 shadow-sm">
  <div class="table-responsive">
    <table class="table align-middle">
      <thead class="table-light"><tr><th>#</th><th>Origen</th><th>Destino</th><th>Estado</th><th>Fechas</th><th class="text-end">Acciones</th></tr></thead>
      <tbody>
        @foreach($traspasos as $t)
          <tr>
            <td>{{ $t->id }}</td>
            <td>{{ $t->from->nombre }}</td>
            <td>{{ $t->to->nombre }}</td>
            <td><span class="badge text-bg-light">{{ $t->estado }}</span></td>
            <td>
              <small>Enviado: {{ optional($t->enviado_at)->format('d/m H:i') ?? '—' }}</small><br>
              <small>Recibido: {{ optional($t->recibido_at)->format('d/m H:i') ?? '—' }}</small>
            </td>
            <td class="text-end">
              @if($t->estado==='en_transito')
              <form method="POST" action="{{ route('traspasos.receive',$t) }}">
                @csrf
                <button class="btn btn-sm btn-success"><i class="bi bi-box-arrow-in-down"></i> Recibir</button>
              </form>
              @else
              <span class="text-secondary">—</span>
              @endif
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  <div class="card-body">{{ $traspasos->links() }}</div>
</div>
@endsection
