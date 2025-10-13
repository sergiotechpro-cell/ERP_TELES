@props(['icon' => 'bi-emoji-smile', 'title' => 'Sin datos', 'text' => 'No hay información para mostrar.'])
<div class="text-center py-5">
  <div class="display-5 mb-3 text-primary"><i class="bi {{ $icon }}"></i></div>
  <h5 class="fw-bold mb-2">{{ $title }}</h5>
  <p class="text-secondary mb-0">{{ $text }}</p>
</div>
