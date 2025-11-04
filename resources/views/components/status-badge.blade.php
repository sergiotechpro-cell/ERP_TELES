@props(['status'])
@php
  $map = [
    'capturado'   => ['text' => 'Capturado', 'class' => 'text-bg-secondary'],
    'preparacion' => ['text' => 'Preparación', 'class' => 'text-bg-info'],
    'asignado'    => ['text' => 'Asignado', 'class' => 'text-bg-primary'],
    'en_ruta'     => ['text' => 'En ruta', 'class' => 'text-bg-warning'],
    'entregado'   => ['text' => 'Entregado', 'class' => 'text-bg-success'],
    'entregado_pendiente_pago' => ['text' => 'Entregado - Pendiente Pago', 'class' => 'text-bg-warning'],
    'finalizado'  => ['text' => 'Finalizado', 'class' => 'text-bg-success'],
    'cancelado'   => ['text' => 'Cancelado', 'class' => 'text-bg-danger'],
  ];
  $cfg = $map[$status] ?? ['text'=>$status,'class'=>'text-bg-light'];
@endphp
<span class="badge {{ $cfg['class'] }} rounded-pill">{{ $cfg['text'] }}</span>
