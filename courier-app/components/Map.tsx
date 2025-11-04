'use client';

import { useEffect, useRef, useState } from 'react';

interface Origin {
  lat: number;
  lng: number;
  name?: string;
  address?: string;
}

interface MapProps {
  lat: number;
  lng: number;
  origen?: Origin;
}

export default function MapComponent({ lat, lng, origen }: MapProps) {
  const mapRef = useRef<HTMLDivElement>(null);
  const [mapLoaded, setMapLoaded] = useState(false);
  const [routeInfo, setRouteInfo] = useState<{ distance?: string; duration?: string } | null>(null);
  const [error, setError] = useState<string | null>(null);
  const mapInstanceRef = useRef<google.maps.Map | null>(null);
  const directionsRendererRef = useRef<google.maps.DirectionsRenderer | null>(null);
  
  // Obtener API key al inicio del componente
  const apiKey = process.env.NEXT_PUBLIC_GOOGLE_MAPS_API_KEY || '';

  useEffect(() => {
    // Si no hay origen, no podemos mostrar la ruta, usar iframe simple
    if (!origen || !origen.lat || !origen.lng) {
      console.warn('MapComponent: No hay origen válido, usando iframe simple');
      console.warn('MapComponent: Origen recibido:', origen);
      return;
    }

    // Si no hay API key, usar iframe simple
    if (!apiKey) {
      console.warn('MapComponent: No hay API key de Google Maps configurada, usando iframe simple');
      console.warn('MapComponent: Verifica que NEXT_PUBLIC_GOOGLE_MAPS_API_KEY esté en .env.local');
      console.warn('MapComponent: Después de agregar la API key, REINICIA el servidor Next.js');
      return;
    }
    
    console.log('MapComponent: ✅ API key detectada:', apiKey ? `${apiKey.substring(0, 15)}...` : 'NO HAY');
    console.log('MapComponent: ✅ Origen válido:', { lat: origen.lat, lng: origen.lng, name: origen.name });
    console.log('MapComponent: ✅ Destino (raw):', { lat, lng });
    
    // Convertir destino a números si vienen como strings
    const destLatNum = typeof lat === 'string' ? parseFloat(lat) : lat;
    const destLngNum = typeof lng === 'string' ? parseFloat(lng) : lng;
    console.log('MapComponent: ✅ Destino (converted):', { lat: destLatNum, lng: destLngNum });

    // Cargar Google Maps API
    if (typeof window === 'undefined') return;
    
    console.log('MapComponent: Inicializando mapa con origen:', origen);

    // Generar un ID único para el callback
    const callbackName = `initMap_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
    
    // Convertir coordenadas a números explícitamente
    const originLat = parseFloat(String(origen.lat));
    const originLng = parseFloat(String(origen.lng));
    // Usar los valores convertidos del destino (pueden venir como strings desde la API)
    const destLat = typeof lat === 'string' ? parseFloat(lat) : parseFloat(String(lat));
    const destLng = typeof lng === 'string' ? parseFloat(lng) : parseFloat(String(lng));
    
    // Validar que las coordenadas sean válidas
    if (isNaN(originLat) || isNaN(originLng) || isNaN(destLat) || isNaN(destLng)) {
      console.error('MapComponent: Coordenadas inválidas', { originLat, originLng, destLat, destLng });
      setError('Las coordenadas no son válidas');
      return;
    }
    
    // Función que realmente inicializa el mapa
    const doInitializeMap = () => {
      if (!mapRef.current || !window.google || !window.google.maps) {
        console.error('MapComponent: Precondiciones no cumplidas para doInitializeMap');
        return;
      }
      
      if (!window.google.maps.DirectionsService || !window.google.maps.DirectionsRenderer) {
        console.error('MapComponent: Librerías directions no disponibles en doInitializeMap');
        return;
      }

      // Asegurarse de que las coordenadas sean números válidos (usar objetos literales como en el ERP)
      const origin = { lat: originLat, lng: originLng };
      const destination = { lat: destLat, lng: destLng };
      
      console.log('MapComponent: Calculando ruta desde', origin, 'hasta', destination);

      // Crear el mapa (igual configuración que en el ERP)
      const map = new window.google.maps.Map(mapRef.current, {
        center: origin,
        zoom: 11,
        mapTypeControl: false,
        streetViewControl: false,
      });
      
      mapInstanceRef.current = map;

      // Crear servicios de direcciones (igual que en el ERP)
      const directionsService = new window.google.maps.DirectionsService();
      // Configurar DirectionsRenderer exactamente como en el ERP
      // suppressMarkers: false hace que muestre automáticamente marcadores A y B
      const directionsRenderer = new window.google.maps.DirectionsRenderer({ 
        map: map,
        suppressMarkers: false, // Mostrar marcadores automáticos A y B (igual que en el ERP)
      });
      
      directionsRendererRef.current = directionsRenderer;
      
      console.log('MapComponent: DirectionsService y DirectionsRenderer creados exitosamente');

      // Calcular y mostrar la ruta (igual que en el ERP)
      directionsService.route(
        {
          origin: origin, // Usar LatLng object
          destination: destination, // Usar LatLng object
          travelMode: window.google.maps.TravelMode.DRIVING,
          provideRouteAlternatives: false, // false para mostrar solo una ruta como en el ERP
        },
        (result, status) => {
          if (status === 'OK' && result) {
            console.log('MapComponent: Ruta calculada exitosamente', result);
            // Dibujar la ruta completa - esto muestra automáticamente:
            // - La línea azul de la ruta
            // - Marcador A en el origen
            // - Marcador B en el destino
            directionsRenderer.setDirections(result);
            
            const leg = result.routes[0].legs[0];
            setRouteInfo({
              distance: leg.distance.text,
              duration: leg.duration.text,
            });
            setMapLoaded(true);
          } else {
            console.error('MapComponent: Error al calcular la ruta:', status, result);
            setError(`No se pudo calcular la ruta: ${status}`);
            
            // Si falla, mostrar al menos los marcadores básicos
            new window.google.maps.Marker({
              position: origin,
              map: map,
              title: 'Origen (Bodega)'
            });
            
            new window.google.maps.Marker({
              position: destination,
              map: map,
              title: 'Destino'
            });
            
            map.setCenter(origin);
            setMapLoaded(true);
          }
        }
      );
    };

    // CRÍTICO: Crear la función callback que captura todas las variables necesarias
    // Esta función será llamada por Google Maps cuando el script termine de cargar
    const callbackFunction = function() {
      console.log('MapComponent: Callback ejecutado por Google Maps');
      
      // Esperar un momento para asegurar que todo esté disponible
      setTimeout(() => {
        if (!mapRef.current) {
          console.error('MapComponent: mapRef.current no está disponible en callback');
          return;
        }
        
        if (!window.google || !window.google.maps) {
          console.error('MapComponent: window.google.maps no está disponible en callback');
          return;
        }
        
        // Esperar a que las librerías estén disponibles
        const checkLibraries = setInterval(() => {
          if (window.google && window.google.maps && 
              window.google.maps.DirectionsService && 
              window.google.maps.DirectionsRenderer) {
            clearInterval(checkLibraries);
            console.log('MapComponent: ✅ Todas las librerías están disponibles, inicializando mapa...');
            doInitializeMap();
          }
        }, 50);
        
        // Timeout después de 5 segundos
        setTimeout(() => {
          clearInterval(checkLibraries);
          if (!window.google?.maps?.DirectionsService || !window.google?.maps?.DirectionsRenderer) {
            console.error('MapComponent: La librería directions no se cargó después de 5 segundos');
            setError('La librería directions de Google Maps no está disponible. Verifica que tu API key tenga habilitada "Maps JavaScript API" y "Directions API".');
          }
        }, 5000);
      }, 100);
    };

    // Asignar la función directamente a window ANTES de crear el script
    // Esto debe hacerse de forma simple para que Google Maps pueda encontrarla
    (window as any)[callbackName] = callbackFunction;
    
    console.log('MapComponent: Callback registrado en window:', callbackName);
    console.log('MapComponent: Verificando callback:', typeof (window as any)[callbackName]);
    console.log('MapComponent: Callback es función?', typeof (window as any)[callbackName] === 'function');

    // Verificar si Google Maps ya está cargado
    if (window.google && window.google.maps && window.google.maps.DirectionsService) {
      console.log('MapComponent: Google Maps ya está cargado, inicializando directamente...');
      doInitializeMap();
      return () => {
        delete (window as any)[callbackName];
      };
    }

    // Verificar si el script ya está cargado o en proceso
    const existingScript = document.querySelector('script[src*="maps.googleapis.com/maps/api/js"]');
    
    if (existingScript) {
      // Si ya existe el script, verificar si está listo
      if (window.google && window.google.maps && window.google.maps.DirectionsService) {
        // Llamar directamente a doInitializeMap si las librerías están listas
        if (window.google.maps.DirectionsService && window.google.maps.DirectionsRenderer) {
          doInitializeMap();
        } else {
          callbackFunction();
        }
        return () => {
          delete (window as any)[callbackName];
        };
      }
      
      // Esperar a que el script existente termine de cargar
      const checkWhenReady = setInterval(() => {
        if (window.google && window.google.maps && window.google.maps.DirectionsService) {
          clearInterval(checkWhenReady);
          // Llamar directamente a doInitializeMap si las librerías están listas
          if (window.google.maps.DirectionsService && window.google.maps.DirectionsRenderer) {
            doInitializeMap();
          } else {
            callbackFunction();
          }
        }
      }, 100);
      
      setTimeout(() => {
        clearInterval(checkWhenReady);
      }, 10000);
      
      return () => {
        clearInterval(checkWhenReady);
        delete (window as any)[callbackName];
      };
    }

    // Crear y cargar el script con callback (igual que en el ERP)
    // IMPORTANTE: El callback se ejecuta DESPUÉS de que todas las librerías están cargadas
    const script = document.createElement('script');
    // DirectionsService y DirectionsRenderer están incluidos en la librería principal, no necesitan librería separada
    script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&libraries=places&v=weekly&callback=${callbackName}`;
    script.async = true;
    script.defer = false; // NO usar defer - necesitamos que el callback esté disponible inmediatamente
    script.id = 'google-maps-script';
    
    console.log('MapComponent: Cargando script de Google Maps con callback:', callbackName);
    console.log('MapComponent: URL:', script.src.replace(apiKey, 'API_KEY_HIDDEN'));
    
    // Verificar una última vez que el callback esté disponible
    if (typeof (window as any)[callbackName] !== 'function') {
      console.error('MapComponent: CRÍTICO - El callback NO está disponible como función!');
      setError('Error interno: el callback no se registró correctamente');
      return;
    }

    script.onerror = () => {
      console.error('MapComponent: Error al cargar el script de Google Maps');
      setError('Error al cargar Google Maps API. Verifica tu API key.');
      delete (window as any)[callbackName];
    };

    document.head.appendChild(script);

    return () => {
      // Cleanup
      if (directionsRendererRef.current) {
        directionsRendererRef.current.setMap(null);
      }
      delete (window as any)[callbackName];
    };
  }, [lat, lng, origen, apiKey]);

  // Si no hay origen válido o no hay API key, usar iframe simple
  if (!origen || !origen.lat || !origen.lng || !apiKey) {
    const embedUrl = `https://www.google.com/maps?q=${lat},${lng}&output=embed&z=16`;
    
    return (
      <div className="w-full h-64 md:h-96 lg:h-[520px] rounded-lg overflow-hidden border border-gray-200 shadow-sm">
        {!apiKey && (
          <div className="bg-yellow-50 border-b border-yellow-200 px-4 py-2 text-xs text-yellow-800">
            ⚠️ API key de Google Maps no detectada. Configura NEXT_PUBLIC_GOOGLE_MAPS_API_KEY en .env.local y reinicia el servidor.
          </div>
        )}
        <iframe
          width="100%"
          height="100%"
          style={{ border: 0 }}
          loading="lazy"
          allowFullScreen
          referrerPolicy="no-referrer-when-downgrade"
          src={embedUrl}
          title="Ubicación de Entrega"
        />
      </div>
    );
  }

  if (error) {
    return (
      <div className="w-full h-64 rounded-lg border border-yellow-200 bg-yellow-50 flex items-center justify-center">
        <div className="text-center text-yellow-800">
          <p className="font-semibold mb-1">⚠️ {error}</p>
          <p className="text-sm">Mostrando ubicación simple</p>
        </div>
      </div>
    );
  }

  return (
    <div className="w-full space-y-2">
      {/* Mapa con altura más grande como en el ERP (520px en el ERP, usamos h-96 = 384px para móvil) */}
      <div 
        ref={mapRef}
        className="w-full h-64 md:h-96 lg:h-[520px] rounded-lg overflow-hidden border border-gray-200 shadow-sm"
        style={{ minHeight: '256px' }}
      />
      {routeInfo && (
        <div className="bg-blue-50 border border-blue-200 rounded-lg p-3">
          <div className="space-y-1 text-sm">
            <div>
              <span className="font-semibold text-gray-700">Distancia:</span>
              <span className="ml-2 text-gray-900">{routeInfo.distance}</span>
            </div>
            <div>
              <span className="font-semibold text-gray-700">Tiempo estimado:</span>
              <span className="ml-2 text-gray-900">{routeInfo.duration}</span>
            </div>
          </div>
        </div>
      )}
      {!mapLoaded && !error && (
        <div className="bg-gray-50 border border-gray-200 rounded-lg p-3 text-center text-gray-600 text-sm">
          Cargando ruta...
        </div>
      )}
    </div>
  );
}
