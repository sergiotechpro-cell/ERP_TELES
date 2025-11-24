import { useState, useEffect, useRef } from 'react';
import { Geolocation, Position } from '@capacitor/geolocation';

interface LocationData {
  latitude: number;
  longitude: number;
  speed: number | null;
  heading: number | null;
  accuracy: number | null;
}

interface UseLocationTrackingOptions {
  enabled: boolean;
  orderId?: number | null;
  interval?: number; // en milisegundos
  onLocationUpdate?: (location: LocationData) => void;
  onError?: (error: any) => void;
}

export function useLocationTracking({
  enabled,
  orderId,
  interval = 15000, // 15 segundos por defecto
  onLocationUpdate,
  onError
}: UseLocationTrackingOptions) {
  const [currentLocation, setCurrentLocation] = useState<LocationData | null>(null);
  const [isTracking, setIsTracking] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const intervalRef = useRef<NodeJS.Timeout | null>(null);
  const watchIdRef = useRef<string | null>(null);

  // Función para obtener la ubicación actual
  const getCurrentLocation = async (): Promise<LocationData | null> => {
    try {
      const position: Position = await Geolocation.getCurrentPosition({
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 0
      });

      const locationData: LocationData = {
        latitude: position.coords.latitude,
        longitude: position.coords.longitude,
        speed: position.coords.speed,
        heading: position.coords.heading,
        accuracy: position.coords.accuracy
      };

      return locationData;
    } catch (err: any) {
      console.error('Error obteniendo ubicación:', err);
      setError(err.message || 'Error al obtener ubicación');
      if (onError) onError(err);
      return null;
    }
  };

  // Función para enviar la ubicación al backend
  const sendLocationToBackend = async (location: LocationData) => {
    try {
      const token = localStorage.getItem('token');
      if (!token) {
        console.error('No hay token de autenticación');
        return;
      }

      const response = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/courier/tracking/update`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          latitude: location.latitude,
          longitude: location.longitude,
          speed: location.speed,
          heading: location.heading,
          accuracy: location.accuracy,
          order_id: orderId
        })
      });

      if (!response.ok) {
        throw new Error('Error al enviar ubicación al servidor');
      }

      const result = await response.json();
      console.log('Ubicación enviada correctamente:', result);
    } catch (err: any) {
      console.error('Error enviando ubicación:', err);
      if (onError) onError(err);
    }
  };

  // Función para actualizar la ubicación
  const updateLocation = async () => {
    const location = await getCurrentLocation();
    if (location) {
      setCurrentLocation(location);
      
      // Enviar al backend
      await sendLocationToBackend(location);
      
      // Callback opcional
      if (onLocationUpdate) {
        onLocationUpdate(location);
      }
    }
  };

  // Iniciar tracking
  const startTracking = async () => {
    try {
      // Solicitar permisos
      const permission = await Geolocation.checkPermissions();
      
      if (permission.location !== 'granted') {
        const requestPermission = await Geolocation.requestPermissions();
        if (requestPermission.location !== 'granted') {
          throw new Error('Permisos de ubicación denegados');
        }
      }

      setIsTracking(true);
      setError(null);

      // Obtener ubicación inicial inmediatamente
      await updateLocation();

      // Configurar intervalo para actualizaciones periódicas
      intervalRef.current = setInterval(async () => {
        await updateLocation();
      }, interval);

      console.log(`Tracking iniciado (intervalo: ${interval}ms)`);
    } catch (err: any) {
      console.error('Error iniciando tracking:', err);
      setError(err.message || 'Error al iniciar tracking');
      setIsTracking(false);
      if (onError) onError(err);
    }
  };

  // Detener tracking
  const stopTracking = async () => {
    try {
      // Limpiar intervalo
      if (intervalRef.current) {
        clearInterval(intervalRef.current);
        intervalRef.current = null;
      }

      // Limpiar watch si existe
      if (watchIdRef.current) {
        await Geolocation.clearWatch({ id: watchIdRef.current });
        watchIdRef.current = null;
      }

      // Notificar al backend que se detuvo el tracking
      const token = localStorage.getItem('token');
      if (token) {
        await fetch(`${process.env.NEXT_PUBLIC_API_URL}/courier/tracking/stop`, {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
          }
        });
      }

      setIsTracking(false);
      setCurrentLocation(null);
      console.log('Tracking detenido');
    } catch (err: any) {
      console.error('Error deteniendo tracking:', err);
      if (onError) onError(err);
    }
  };

  // Effect para iniciar/detener tracking basado en 'enabled'
  useEffect(() => {
    if (enabled && !isTracking) {
      startTracking();
    } else if (!enabled && isTracking) {
      stopTracking();
    }

    // Cleanup al desmontar
    return () => {
      if (intervalRef.current) {
        clearInterval(intervalRef.current);
      }
      if (watchIdRef.current) {
        Geolocation.clearWatch({ id: watchIdRef.current });
      }
    };
  }, [enabled]);

  // Effect para actualizar el order_id cuando cambie
  useEffect(() => {
    if (isTracking && currentLocation) {
      // Si cambia el order_id, enviar actualización inmediata
      sendLocationToBackend(currentLocation);
    }
  }, [orderId]);

  return {
    currentLocation,
    isTracking,
    error,
    startTracking,
    stopTracking,
    updateLocation
  };
}

