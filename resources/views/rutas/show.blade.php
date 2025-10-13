@extends('layouts.erp')
@section('title','Ruta de entrega')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3 class="fw-bold"><i class="bi bi-geo-alt me-2"></i>Ruta del pedido #{{ $pedido->id }}</h3>
  <a href="{{ route('pedidos.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

<div class="row g-3">
  <div class="col-lg-8">
    <div id="map" style="height: 520px; border-radius: 12px; overflow:hidden" class="shadow-sm"></div>
  </div>
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <h6 class="fw-bold mb-2">Detalles</h6>
        <div class="small text-secondary mb-2">Cliente</div>
        <div class="fw-semibold">{{ $pedido->customer?->nombre }}</div>
        <div class="small text-secondary mt-3 mb-2">Dirección entrega</div>
        <div>{{ $dest['address'] }}</div>
        <hr>
        <div id="routeInfo" class="small"></div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script async
  src="https://maps.googleapis.com/maps/api/js?key={{ $mapsKey }}&libraries=marker&v=weekly&callback=initMap">
</script>
<script>
  function initMap(){
    const origin = {lat: {{ $origin['lat'] }}, lng: {{ $origin['lng'] }}};
    const dest   = {lat: {{ $dest['lat'] }},   lng: {{ $dest['lng'] }}};

    const map = new google.maps.Map(document.getElementById('map'), {
      center: origin, zoom: 11, mapTypeControl:false, streetViewControl:false
    });

    const directionsService = new google.maps.DirectionsService();
    const directionsRenderer = new google.maps.DirectionsRenderer({ map });

    directionsService.route({
      origin, destination: dest, travelMode: google.maps.TravelMode.DRIVING,
      provideRouteAlternatives: true
    }, (res, status) => {
      if (status === 'OK') {
        directionsRenderer.setDirections(res);
        const leg = res.routes[0].legs[0];
        document.getElementById('routeInfo').innerHTML =
          `<div><strong>Distancia:</strong> ${leg.distance.text}</div>
           <div><strong>Tiempo:</strong> ${leg.duration.text}</div>`;
      } else {
        document.getElementById('routeInfo').innerHTML = 'No fue posible calcular la ruta.';
      }
    });
  }
</script>
@endpush
