@extends('layouts.erp')
@section('title','Cierre diario')

@section('content')
<x-flash />
<div class="container" style="max-width: 920px;">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <h3 class="fw-bold mb-0"><i class="bi bi-calendar-check me-2"></i> Cierre diario</h3>
    <a href="{{ route('finanzas.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i> Volver</a>
  </div>

  <div class="card border-0 shadow-sm" style="border-radius:16px;">
    <div class="card-body">
      <form method="POST" action="{{ route('finanzas.dailyClose') }}" class="row g-3">
        @csrf
        <div class="col-md-6">
          <label class="form-label">Fecha</label>
          <input type="date" class="form-control" name="fecha" value="{{ now()->toDateString() }}">
        </div>
        <div class="col-md-6 d-flex align-items-end justify-content-end">
          <button class="btn btn-primary"><i class="bi bi-cash-coin"></i> Generar cierre</button>
        </div>
      </form>
    </div>
  </div>

  @isset($closure)
  <div class="row g-3 mt-3">
    <div class="col-md-4">
      <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
        <div class="card-body">
          <div class="text-secondary">Efectivo</div>
          <div class="fs-4 fw-bold">${{ number_format($closure->total_efectivo,2) }}</div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
        <div class="card-body">
          <div class="text-secondary">Tarjeta</div>
          <div class="fs-4 fw-bold">${{ number_format($closure->total_tarjeta,2) }}</div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
        <div class="card-body">
          <div class="text-secondary">Transferencia</div>
          <div class="fs-4 fw-bold">${{ number_format($closure->total_transferencia,2) }}</div>
        </div>
      </div>
    </div>
  </div>
  @endisset
</div>
@endsection
