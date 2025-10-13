@extends('layouts.erp')
@section('title','Logística')

@section('content')
<x-flash />
<div class="container-fluid">
  <h2 class="fw-bold mb-3"><i class="bi bi-truck me-2"></i>Logística</h2>
  <x-empty icon="bi-map" title="Pronto" text="En esta vista podrás monitorear rutas, asignaciones y escaneos en vivo." />
</div>
@endsection
