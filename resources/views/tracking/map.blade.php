@extends('layouts.erp')

@section('title', 'Tracking en Tiempo Real')

@section('content')
@php
    $apiKey = config('services.google.maps_key');
@endphp

@if(!$apiKey)
<div class="container-fluid py-4">
    <div class="alert alert-warning">
        <h4><i class="bi bi-exclamation-triangle"></i> Google Maps API Key no configurada</h4>
        <p>Para usar el tracking en tiempo real, necesitas configurar tu Google Maps API Key.</p>
        <p>Agrega <code>GOOGLE_MAPS_API_KEY</code> o <code>GOOGLE_ROUTES_API_KEY</code> a tu archivo <code>.env</code></p>
        <hr>
        <p><strong>APIs requeridas en Google Cloud Console:</strong></p>
        <ul>
            <li>Maps JavaScript API</li>
            <li>Geolocation API</li>
            <li>Places API (opcional)</li>
        </ul>
    </div>
</div>
@else
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">
                    <i class="bi bi-geo-alt-fill text-primary"></i>
                    Tracking en Tiempo Real
                </h2>
                <div class="d-flex gap-2">
                    <span class="badge bg-success" id="online-drivers">0 choferes activos</span>
                    <button class="btn btn-sm btn-outline-primary" onclick="centerMap()">
                        <i class="bi bi-bullseye"></i> Centrar mapa
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" onclick="toggleTraffic()">
                        <i class="bi bi-stoplights"></i> Tráfico
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Mapa principal -->
        <div class="col-lg-9">
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div id="map" style="height: calc(100vh - 200px); min-height: 500px;"></div>
                </div>
            </div>
        </div>

        <!-- Panel lateral con lista de choferes -->
        <div class="col-lg-3">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-people-fill"></i>
                        Choferes Activos
                    </h5>
                </div>
                <div class="card-body p-0" style="max-height: calc(100vh - 200px); overflow-y: auto;">
                    <div id="drivers-list" class="list-group list-group-flush">
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-hourglass-split fs-3"></i>
                            <p class="mb-0 mt-2">Esperando datos...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .driver-card {
        cursor: pointer;
        transition: all 0.2s;
        border-left: 4px solid transparent;
    }
    
    .driver-card:hover {
        background-color: #f8f9fa;
        border-left-color: #0d6efd;
    }
    
    .driver-card.active {
        background-color: #e7f1ff;
        border-left-color: #0d6efd;
    }
    
    .driver-status {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 5px;
    }
    
    .driver-status.moving {
        background-color: #28a745;
        animation: pulse 2s infinite;
    }
    
    .driver-status.stopped {
        background-color: #ffc107;
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
    
    .info-window {
        font-family: Arial, sans-serif;
    }
    
    .info-window h6 {
        margin: 0 0 8px 0;
        color: #0d6efd;
    }
    
    .info-window p {
        margin: 4px 0;
        font-size: 13px;
    }
</style>

<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_key') }}&libraries=places"></script>
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.js"></script>

<script>
let map;
let markers = {};
let infoWindows = {};
let trafficLayer;
let selectedDriver = null;
let echo;
let routeLines = {}; // Líneas de ruta por chofer
let completedLines = {}; // Líneas de ruta completada
let originMarkers = {}; // Marcadores de origen
let destinationMarkers = {}; // Marcadores de destino
let driverPaths = {}; // Historial de ubicaciones por chofer

// Inicializar el mapa
function initMap() {
    // Centro en México (puedes cambiar esto a tu ubicación)
    const center = { lat: 19.4326, lng: -99.1332 };
    
    map = new google.maps.Map(document.getElementById('map'), {
        zoom: 12,
        center: center,
        mapTypeControl: true,
        streetViewControl: false,
        fullscreenControl: true,
        styles: [
            {
                featureType: "poi",
                elementType: "labels",
                stylers: [{ visibility: "off" }]
            }
        ]
    });
    
    // Capa de tráfico
    trafficLayer = new google.maps.TrafficLayer();
    
    // Cargar ubicaciones iniciales
    loadInitialLocations();
    
    // Configurar Pusher y Laravel Echo
    setupRealtimeUpdates();
}

// Cargar ubicaciones iniciales
async function loadInitialLocations() {
    try {
        const response = await fetch('/tracking/drivers', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const result = await response.json();
        
        if (result.success && result.data.length > 0) {
            // Cargar historial de ubicaciones para cada chofer
            for (const location of result.data) {
                await loadDriverHistory(location.driver_id);
                updateDriverMarker(location);
            }
            
            // Centrar el mapa en el primer chofer
            const firstDriver = result.data[0];
            map.setCenter({ lat: firstDriver.latitude, lng: firstDriver.longitude });
        }
        
        updateDriversList(result.data);
    } catch (error) {
        console.error('Error cargando ubicaciones:', error);
    }
}

// Cargar historial de ubicaciones de un chofer
async function loadDriverHistory(driverId) {
    try {
        const response = await fetch(`/tracking/drivers/${driverId}/history`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (response.ok) {
            const result = await response.json();
            if (result.success && result.data.length > 0) {
                // Inicializar el historial con todas las ubicaciones previas
                driverPaths[driverId] = result.data.map(loc => ({
                    lat: loc.latitude,
                    lng: loc.longitude
                }));
            }
        }
    } catch (error) {
        console.error('Error cargando historial:', error);
    }
}

// Configurar actualizaciones en tiempo real
function setupRealtimeUpdates() {
    const pusherKey = '{{ config('broadcasting.connections.pusher.key') }}';
    const pusherCluster = '{{ config('broadcasting.connections.pusher.options.cluster', 'mt1') }}';
    
    if (pusherKey && pusherKey !== '') {
        // Usar Pusher si está configurado
        try {
            echo = new Echo({
                broadcaster: 'pusher',
                key: pusherKey,
                cluster: pusherCluster,
                forceTLS: true
            });
            
            // Escuchar el canal de tracking
            echo.channel('driver-tracking')
                .listen('.location.updated', (data) => {
                    console.log('Nueva ubicación recibida vía Pusher:', data);
                    updateDriverMarker(data);
                    refreshDriversList();
                });
            
            console.log('✅ Pusher conectado - Actualizaciones en tiempo real activas');
        } catch (error) {
            console.error('Error conectando Pusher:', error);
            setupPolling();
        }
    } else {
        // Fallback: Usar polling si no hay Pusher
        console.log('⚠️ Pusher no configurado - Usando polling cada 3 segundos');
        setupPolling();
    }
}

// Configurar polling automático (alternativa a Pusher)
function setupPolling() {
    // Actualizar cada 3 segundos
    setInterval(async () => {
        await refreshDriversList();
    }, 3000);
    
    console.log('🔄 Polling automático activado (cada 3 segundos)');
}

// Actualizar o crear marcador de chofer
function updateDriverMarker(location) {
    const position = { lat: location.latitude, lng: location.longitude };
    const driverId = location.driver_id;
    
    // Inicializar historial de ubicaciones si no existe
    if (!driverPaths[driverId]) {
        driverPaths[driverId] = [];
    }
    
    // Agregar posición actual al historial
    driverPaths[driverId].push(position);
    
    // Si el marcador ya existe, actualizarlo con animación suave
    if (markers[driverId]) {
        const oldPosition = markers[driverId].getPosition();
        
        // Animar el movimiento suavemente
        animateMarkerMovement(markers[driverId], oldPosition, position, 2000); // 2 segundos
        
        // Actualizar rotación
        animateMarker(markers[driverId], position);
    } else {
        // Crear nuevo marcador con icono de carrito/delivery
        const marker = new google.maps.Marker({
            position: position,
            map: map,
            title: location.driver_name,
            icon: {
                path: 'M17.402,0H5.643C2.526,0,0,3.467,0,6.584v34.804c0,3.116,2.526,5.644,5.643,5.644h11.759c3.116,0,5.644-2.527,5.644-5.644 V6.584C23.044,3.467,20.518,0,17.402,0z M22.057,14.188v11.665l-2.729,0.351v-4.806L22.057,14.188z M20.625,10.773 c-1.016,3.9-2.219,8.51-2.219,8.51H4.638l-2.222-8.51C2.417,10.773,11.3,7.755,20.625,10.773z M3.748,21.713v4.492l-2.73-0.349 V14.502L3.748,21.713z M1.018,37.938V27.579l2.73,0.343v8.196L1.018,37.938z M2.575,40.882l2.218-3.336h13.771l2.219,3.336H2.575z M19.328,35.805v-7.872l2.729-0.355v10.048L19.328,35.805z',
                fillColor: '#0d6efd',
                fillOpacity: 1,
                strokeColor: '#ffffff',
                strokeWeight: 2,
                scale: 0.8,
                anchor: new google.maps.Point(11.5, 46),
                rotation: location.heading || 0
            },
            animation: google.maps.Animation.DROP
        });
        
        // Crear ventana de información
        const infoWindow = new google.maps.InfoWindow({
            content: getInfoWindowContent(location)
        });
        
        // Evento click en el marcador
        marker.addListener('click', () => {
            closeAllInfoWindows();
            infoWindow.open(map, marker);
            selectDriver(driverId);
        });
        
        markers[driverId] = marker;
        infoWindows[driverId] = infoWindow;
    }
    
    // Actualizar contenido de la ventana de información
    if (infoWindows[driverId]) {
        infoWindows[driverId].setContent(getInfoWindowContent(location));
    }
    
    // Actualizar la ruta recorrida
    updateDriverRoute(driverId, location);
}

// Actualizar la ruta del chofer
function updateDriverRoute(driverId, location) {
    // Si hay un pedido con coordenadas, mostrar origen y destino
    if (location.order_id && location.order_lat && location.order_lng) {
        const destinationPos = { lat: location.order_lat, lng: location.order_lng };
        
        // Crear marcador de origen (bodega) si no existe y es el primer punto
        if (!originMarkers[driverId] && driverPaths[driverId].length === 1) {
            const firstPos = driverPaths[driverId][0];
            originMarkers[driverId] = new google.maps.Marker({
                position: firstPos,
                map: map,
                title: 'Origen - Bodega',
                icon: {
                    path: 'M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z',
                    fillColor: '#28a745',
                    fillOpacity: 1,
                    strokeColor: '#ffffff',
                    strokeWeight: 2,
                    scale: 1.5,
                    anchor: new google.maps.Point(12, 22)
                },
                zIndex: 100
            });
            
            // Info window para origen
            const originInfo = new google.maps.InfoWindow({
                content: '<div class="info-window"><h6>🏢 Origen - Bodega</h6><p>Punto de partida</p></div>'
            });
            
            originMarkers[driverId].addListener('click', () => {
                originInfo.open(map, originMarkers[driverId]);
            });
        }
        
        // Crear marcador de destino si no existe
        if (!destinationMarkers[driverId]) {
            destinationMarkers[driverId] = new google.maps.Marker({
                position: destinationPos,
                map: map,
                title: 'Destino - Pedido #' + location.order_id,
                icon: {
                    path: 'M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z',
                    fillColor: '#dc3545',
                    fillOpacity: 1,
                    strokeColor: '#ffffff',
                    strokeWeight: 2,
                    scale: 1.8,
                    anchor: new google.maps.Point(12, 22)
                },
                zIndex: 100
            });
            
            // Info window para destino
            const destInfo = new google.maps.InfoWindow({
                content: `<div class="info-window">
                    <h6>📍 Destino</h6>
                    <p><strong>Pedido:</strong> #${location.order_id}</p>
                    <p><strong>Dirección:</strong><br>${location.order_address || 'N/A'}</p>
                </div>`
            });
            
            destinationMarkers[driverId].addListener('click', () => {
                destInfo.open(map, destinationMarkers[driverId]);
            });
            
            // Dibujar línea punteada desde posición actual hasta destino (ruta pendiente)
            if (!routeLines[driverId]) {
                routeLines[driverId] = new google.maps.Polyline({
                    path: [{ lat: location.latitude, lng: location.longitude }, destinationPos],
                    geodesic: true,
                    strokeColor: '#0d6efd',
                    strokeOpacity: 0.6,
                    strokeWeight: 3,
                    icons: [{
                        icon: {
                            path: 'M 0,-1 0,1',
                            strokeOpacity: 1,
                            scale: 3
                        },
                        offset: '0',
                        repeat: '15px'
                    }],
                    map: map,
                    zIndex: 50
                });
            }
        } else {
            // Actualizar línea punteada hacia el destino
            if (routeLines[driverId]) {
                routeLines[driverId].setPath([
                    { lat: location.latitude, lng: location.longitude },
                    destinationPos
                ]);
            }
        }
    }
    
    // Dibujar línea de ruta recorrida (verde)
    if (driverPaths[driverId] && driverPaths[driverId].length > 1) {
        // Eliminar línea anterior si existe
        if (completedLines[driverId]) {
            completedLines[driverId].setMap(null);
        }
        
        // Crear nueva línea con todo el recorrido
        completedLines[driverId] = new google.maps.Polyline({
            path: driverPaths[driverId],
            geodesic: true,
            strokeColor: '#28a745',
            strokeOpacity: 0.8,
            strokeWeight: 4,
            map: map
        });
    }
}

// Obtener contenido de la ventana de información
function getInfoWindowContent(location) {
    const speed = location.speed ? `${Math.round(location.speed)} km/h` : 'N/A';
    const orderInfo = location.order_id 
        ? `<p><strong>Pedido:</strong> #${location.order_id}</p>` 
        : '<p><em>Sin pedido asignado</em></p>';
    
    return `
        <div class="info-window">
            <h6><i class="bi bi-person-circle"></i> ${location.driver_name}</h6>
            ${orderInfo}
            <p><strong>Velocidad:</strong> ${speed}</p>
            <p><strong>Última actualización:</strong><br>${formatTimestamp(location.timestamp)}</p>
        </div>
    `;
}

// Animar movimiento suave del marcador
function animateMarkerMovement(marker, startPos, endPos, duration) {
    const start = Date.now();
    
    function animate() {
        const now = Date.now();
        const elapsed = now - start;
        const progress = Math.min(elapsed / duration, 1);
        
        // Interpolación lineal entre posiciones
        const lat = startPos.lat() + (endPos.lat - startPos.lat()) * progress;
        const lng = startPos.lng() + (endPos.lng - startPos.lng()) * progress;
        
        marker.setPosition({ lat, lng });
        
        if (progress < 1) {
            requestAnimationFrame(animate);
        }
    }
    
    animate();
}

// Animar marcador (rotación)
function animateMarker(marker, newPosition) {
    const currentPosition = marker.getPosition();
    
    // Calcular el ángulo de rotación hacia la nueva posición
    const heading = google.maps.geometry.spherical.computeHeading(currentPosition, newPosition);
    
    // Actualizar ícono con rotación hacia la dirección de movimiento
    const icon = marker.getIcon();
    if (icon && typeof icon === 'object') {
        icon.rotation = heading;
        marker.setIcon(icon);
    }
}

// Actualizar lista de choferes
async function refreshDriversList() {
    try {
        const response = await fetch('/tracking/drivers', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Actualizar marcadores en el mapa
            result.data.forEach(location => {
                updateDriverMarker(location);
            });
            
            // Actualizar lista en el panel
            updateDriversList(result.data);
        }
    } catch (error) {
        console.error('Error actualizando lista:', error);
    }
}

// Actualizar lista de choferes en el panel
function updateDriversList(drivers) {
    const listContainer = document.getElementById('drivers-list');
    
    if (drivers.length === 0) {
        listContainer.innerHTML = `
            <div class="text-center py-4 text-muted">
                <i class="bi bi-inbox fs-3"></i>
                <p class="mb-0 mt-2">No hay choferes activos</p>
            </div>
        `;
        document.getElementById('online-drivers').textContent = '0 choferes activos';
        return;
    }
    
    document.getElementById('online-drivers').textContent = `${drivers.length} chofer${drivers.length > 1 ? 'es' : ''} activo${drivers.length > 1 ? 's' : ''}`;
    
    listContainer.innerHTML = drivers.map(driver => {
        const isMoving = driver.speed && driver.speed > 5;
        const statusClass = isMoving ? 'moving' : 'stopped';
        const statusText = isMoving ? 'En movimiento' : 'Detenido';
        const orderBadge = driver.order_id 
            ? `<span class="badge bg-primary">Pedido #${driver.order_id}</span>` 
            : '<span class="badge bg-secondary">Sin pedido</span>';
        
        return `
            <div class="driver-card list-group-item list-group-item-action ${selectedDriver === driver.driver_id ? 'active' : ''}" 
                 onclick="focusDriver(${driver.driver_id}, ${driver.latitude}, ${driver.longitude})">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="mb-1">
                            <span class="driver-status ${statusClass}"></span>
                            ${driver.driver_name}
                        </h6>
                        <small class="text-muted">${statusText}</small>
                        <div class="mt-1">${orderBadge}</div>
                    </div>
                    <small class="text-muted">${formatTimestamp(driver.timestamp, true)}</small>
                </div>
            </div>
        `;
    }).join('');
}

// Enfocar en un chofer
function focusDriver(driverId, lat, lng) {
    selectedDriver = driverId;
    map.setCenter({ lat, lng });
    map.setZoom(15);
    
    closeAllInfoWindows();
    if (infoWindows[driverId]) {
        infoWindows[driverId].open(map, markers[driverId]);
    }
    
    // Actualizar UI
    document.querySelectorAll('.driver-card').forEach(card => {
        card.classList.remove('active');
    });
    event.currentTarget.classList.add('active');
}

// Seleccionar chofer
function selectDriver(driverId) {
    selectedDriver = driverId;
    document.querySelectorAll('.driver-card').forEach(card => {
        card.classList.remove('active');
    });
}

// Cerrar todas las ventanas de información
function closeAllInfoWindows() {
    Object.values(infoWindows).forEach(infoWindow => {
        infoWindow.close();
    });
}

// Centrar mapa
function centerMap() {
    const bounds = new google.maps.LatLngBounds();
    let hasMarkers = false;
    
    Object.values(markers).forEach(marker => {
        bounds.extend(marker.getPosition());
        hasMarkers = true;
    });
    
    if (hasMarkers) {
        map.fitBounds(bounds);
    }
}

// Toggle capa de tráfico
function toggleTraffic() {
    if (trafficLayer.getMap()) {
        trafficLayer.setMap(null);
    } else {
        trafficLayer.setMap(map);
    }
}

// Formatear timestamp
function formatTimestamp(timestamp, short = false) {
    const date = new Date(timestamp);
    const now = new Date();
    const diff = Math.floor((now - date) / 1000); // segundos
    
    if (short) {
        if (diff < 60) return 'Ahora';
        if (diff < 3600) return `${Math.floor(diff / 60)}m`;
        if (diff < 86400) return `${Math.floor(diff / 3600)}h`;
        return date.toLocaleDateString();
    }
    
    if (diff < 60) return 'Hace unos segundos';
    if (diff < 3600) return `Hace ${Math.floor(diff / 60)} minuto${Math.floor(diff / 60) > 1 ? 's' : ''}`;
    if (diff < 86400) return `Hace ${Math.floor(diff / 3600)} hora${Math.floor(diff / 3600) > 1 ? 's' : ''}`;
    
    return date.toLocaleString();
}

// Inicializar cuando cargue la página
document.addEventListener('DOMContentLoaded', initMap);

// Limpiar al salir
window.addEventListener('beforeunload', () => {
    if (echo) {
        echo.disconnect();
    }
});
</script>
@endif
@endsection

