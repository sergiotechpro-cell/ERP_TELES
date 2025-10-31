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
        <div class="small text-secondary mb-2">Origen</div>
        <div class="fw-semibold">{{ $origin['name'] ?? 'Bodega Principal' }}</div>
        @if(isset($origin['address']))
          <div class="small text-secondary">{{ $origin['address'] }}</div>
        @endif
        <div class="small text-secondary mt-3 mb-2">Pedido</div>
        <div class="fw-semibold">#{{ $pedido->id }}</div>
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
@if($mapsKey)
<script async
  src="https://maps.googleapis.com/maps/api/js?key={{ $mapsKey }}&libraries=places,directions&v=weekly&callback=initMap">
</script>
<script>
  function initMap(){
    const origin = {lat: {{ $origin['lat'] }}, lng: {{ $origin['lng'] }}};
    const destLat = {{ $dest['lat'] }};
    const destLng = {{ $dest['lng'] }};
    const destAddress = @json($dest['address']);

    const map = new google.maps.Map(document.getElementById('map'), {
      center: origin, 
      zoom: 11, 
      mapTypeControl: false, 
      streetViewControl: false
    });

    // Si no hay coordenadas pero sí dirección, usar geocodificación
    let destination = null;
    
    if (destLat !== 0 && destLng !== 0) {
      destination = {lat: destLat, lng: destLng};
    } else if (destAddress) {
      // Usar geocoding para obtener coordenadas de la dirección
      const geocoder = new google.maps.Geocoder();
      geocoder.geocode({ address: destAddress }, (results, status) => {
        if (status === 'OK' && results[0]) {
          destination = {
            lat: results[0].geometry.location.lat(),
            lng: results[0].geometry.location.lng()
          };
          calculateRoute(origin, destination);
        } else {
          document.getElementById('routeInfo').innerHTML = 
            'No se pudo encontrar la ubicación. Verifica que la dirección sea correcta.';
          // Mostrar al menos el origen en el mapa
          new google.maps.Marker({
            position: origin,
            map: map,
            title: 'Origen (Bodega)'
          });
        }
      });
    } else {
      document.getElementById('routeInfo').innerHTML = 
        'No hay coordenadas ni dirección disponible para calcular la ruta.';
      // Mostrar al menos el origen
      new google.maps.Marker({
        position: origin,
        map: map,
        title: 'Origen (Bodega)'
      });
      return;
    }

    // Si ya tenemos coordenadas, calcular ruta directamente
    if (destination && destLat !== 0 && destLng !== 0) {
      calculateRoute(origin, destination);
    }

    function calculateRoute(origin, destination) {
      const directionsService = new google.maps.DirectionsService();
      const directionsRenderer = new google.maps.DirectionsRenderer({ map });

      directionsService.route({
        origin: origin,
        destination: destination,
        travelMode: google.maps.TravelMode.DRIVING,
        provideRouteAlternatives: true
      }, (res, status) => {
        if (status === 'OK') {
          directionsRenderer.setDirections(res);
          const leg = res.routes[0].legs[0];
          document.getElementById('routeInfo').innerHTML =
            `<div class="mb-2"><strong>Distancia:</strong> ${leg.distance.text}</div>
             <div><strong>Tiempo estimado:</strong> ${leg.duration.text}</div>`;
        } else {
          document.getElementById('routeInfo').innerHTML = 
            'No fue posible calcular la ruta. Verifica que la dirección sea accesible.';
          // Mostrar marcadores sin ruta
          new google.maps.Marker({ position: origin, map: map, title: 'Origen' });
          new google.maps.Marker({ position: destination, map: map, title: 'Destino' });
          map.setCenter(origin);
        }
      });
    }
  }
</script>
@else
<script>
  document.getElementById('routeInfo').innerHTML = 
    '<div class="alert alert-warning">La API key de Google Maps no está configurada.</div>';
</script>
@endif
@endpush
