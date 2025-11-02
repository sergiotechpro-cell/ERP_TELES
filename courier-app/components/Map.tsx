'use client';

import { useJsApiLoader, GoogleMap, Marker } from '@react-google-maps/api';
import { useMemo } from 'react';

interface MapProps {
  lat: number;
  lng: number;
}

const libraries: ('places' | 'drawing' | 'geometry' | 'visualization')[] = ['places'];

export default function MapComponent({ lat, lng }: MapProps) {
  const googleMapsApiKey = process.env.NEXT_PUBLIC_GOOGLE_MAPS_API_KEY || '';

  const { isLoaded } = useJsApiLoader({
    id: 'google-map-script',
    googleMapsApiKey: googleMapsApiKey,
    libraries: libraries,
  });

  const center = useMemo(() => ({ lat, lng }), [lat, lng]);

  if (!isLoaded) {
    return (
      <div className="w-full h-64 bg-gray-200 rounded-lg flex items-center justify-center">
        <div className="text-center">
          <div className="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mb-2"></div>
          <p className="text-gray-600 text-sm">Cargando mapa...</p>
        </div>
      </div>
    );
  }

  if (!googleMapsApiKey) {
    return (
      <div className="w-full h-64 bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg flex items-center justify-center border-2 border-blue-200">
        <div className="text-center p-6">
          <div className="text-5xl mb-4">🗺️</div>
          <p className="text-gray-700 font-semibold mb-1">Ubicación de Entrega</p>
          <p className="text-sm text-gray-600 mb-4">
            Usa el botón de navegación para abrir Google Maps
          </p>
          <a
            href={`https://www.google.com/maps?q=${lat},${lng}`}
            target="_blank"
            rel="noopener noreferrer"
            className="inline-flex items-center justify-center bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition font-semibold shadow-md"
          >
            <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
            </svg>
            Ver en Google Maps
          </a>
        </div>
      </div>
    );
  }

  return (
    <div className="w-full h-64 rounded-lg overflow-hidden">
      <GoogleMap
        mapContainerStyle={{ width: '100%', height: '100%' }}
        center={center}
        zoom={16}
        options={{
          disableDefaultUI: false,
          zoomControl: true,
          streetViewControl: false,
          mapTypeControl: false,
        }}
      >
        <Marker position={center} />
      </GoogleMap>
    </div>
  );
}

