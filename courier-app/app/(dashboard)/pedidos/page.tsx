'use client';

import { useEffect, useState } from 'react';
import Link from 'next/link';
import { courierAPI } from '@/lib/api';
import type { Assignment } from '@/types';
import { useLocationTracking } from '@/app/hooks/useLocationTracking';

export default function PedidosPage() {
  const [assignments, setAssignments] = useState<Assignment[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [optimizing, setOptimizing] = useState(false);
  const [trackingEnabled, setTrackingEnabled] = useState(false);

  // Determinar si hay pedidos activos (en ruta)
  const activeAssignment = assignments.find(a => a.estado === 'en_ruta');
  const shouldTrack = trackingEnabled && activeAssignment !== undefined;

  // Hook de tracking GPS
  const { currentLocation, isTracking, error: trackingError } = useLocationTracking({
    enabled: shouldTrack,
    orderId: activeAssignment?.pedido.id,
    interval: 15000, // Actualizar cada 15 segundos
    onLocationUpdate: (location) => {
      console.log('Ubicación actualizada:', location);
    },
    onError: (err) => {
      console.error('Error en tracking:', err);
    }
  });

  useEffect(() => {
    loadAssignments();
    
    // Activar tracking automáticamente si hay pedidos en ruta
    const hasActiveDelivery = assignments.some(a => a.estado === 'en_ruta');
    if (hasActiveDelivery) {
      setTrackingEnabled(true);
    }
  }, []);

  const loadAssignments = async () => {
    try {
      const response = await courierAPI.getAssignments();
      // Ordenar por fecha de asignación (más antiguas primero) como respaldo
      const sorted = [...response.data].sort((a, b) => {
        const dateA = new Date(a.asignado_at).getTime();
        const dateB = new Date(b.asignado_at).getTime();
        return dateA - dateB; // Ascendente: más antiguas primero
      });
      setAssignments(sorted);
      setError('');
    } catch (err: any) {
      setError('Error al cargar pedidos');
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const pendingAssignments = assignments.filter(a => a.estado !== 'entregado');
  const optimizableAssignments = pendingAssignments.filter(
    a => Number.isFinite(a.pedido.lat) && Number.isFinite(a.pedido.lng)
  );
  const canOptimize = optimizableAssignments.length > 2;

  const handleOptimizeRoutes = () => {
    if (!canOptimize || optimizing) return;
    setOptimizing(true);

    try {
      const sorted = [...optimizableAssignments].sort(
        (a, b) => new Date(a.asignado_at).getTime() - new Date(b.asignado_at).getTime()
      );

      const originPoint = sorted[0]?.origen;
      const stops = sorted.map(assignment => `${assignment.pedido.lat},${assignment.pedido.lng}`);

      if (stops.length < 2) {
        alert('Se necesitan al menos dos puntos con coordenadas para optimizar.');
        return;
      }

      const originParam = originPoint ? `${originPoint.lat},${originPoint.lng}` : '';
      const destination = stops[stops.length - 1];
      const waypointsList = stops.slice(0, -1);
      const waypointParam = waypointsList.length
        ? `optimize:true|${waypointsList.join('|')}`
        : '';

      let url = 'https://www.google.com/maps/dir/?api=1&travelmode=driving&dir_action=navigate';
      if (originParam) {
        url += `&origin=${encodeURIComponent(originParam)}`;
      }
      url += `&destination=${encodeURIComponent(destination)}`;
      if (waypointParam) {
        url += `&waypoints=${encodeURIComponent(waypointParam)}`;
      }

      window.open(url, '_blank');
    } finally {
      setTimeout(() => setOptimizing(false), 600);
    }
  };

  const getEstadoBadge = (estado: string) => {
    const colors = {
      pendiente: 'bg-yellow-100 text-yellow-800',
      en_ruta: 'bg-blue-100 text-blue-800',
      entregado: 'bg-green-100 text-green-800',
      devuelto: 'bg-red-100 text-red-800',
    };

    return (
      <span className={`px-3 py-1 rounded-full text-xs font-semibold ${
        colors[estado as keyof typeof colors] || 'bg-gray-100 text-gray-800'
      }`}>
        {estado.toUpperCase()}
      </span>
    );
  };

  const getTimeAgo = (dateString: string) => {
    const now = new Date();
    const date = new Date(dateString);
    const diffMs = now.getTime() - date.getTime();
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 60) {
      return { text: `hace ${diffMins} min`, color: 'text-green-600', bg: 'bg-green-50' };
    } else if (diffHours < 24) {
      return { text: `hace ${diffHours} ${diffHours === 1 ? 'hora' : 'horas'}`, color: diffHours >= 6 ? 'text-orange-600' : 'text-blue-600', bg: diffHours >= 6 ? 'bg-orange-50' : 'bg-blue-50' };
    } else {
      return { text: `hace ${diffDays} ${diffDays === 1 ? 'día' : 'días'}`, color: 'text-red-600', bg: 'bg-red-50' };
    }
  };

  return (
    <div className="w-full">
      <div className="mb-6">
        <div className="flex justify-between items-start">
          <div>
            <h1 className="text-2xl sm:text-3xl font-bold text-gray-900 mb-1">Mis Pedidos</h1>
            <p className="text-sm sm:text-base text-gray-600">Gestiona tus entregas asignadas</p>
          </div>
          
          {/* Indicador de tracking GPS */}
          {isTracking && (
            <div className="flex items-center gap-2 bg-green-50 text-green-700 px-4 py-2 rounded-lg border border-green-200">
              <div className="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
              <span className="text-sm font-medium">GPS Activo</span>
            </div>
          )}
          
          {trackingError && (
            <div className="flex items-center gap-2 bg-red-50 text-red-700 px-4 py-2 rounded-lg border border-red-200">
              <span className="text-sm font-medium">⚠️ Error GPS</span>
            </div>
          )}
        </div>
      </div>

      {loading && (
        <div className="text-center py-12">
          <div className="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
          <p className="mt-4 text-gray-600">Cargando pedidos...</p>
        </div>
      )}

      {error && (
        <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">
          {error}
        </div>
      )}

      {canOptimize && (
        <div className="bg-blue-50 border border-blue-200 rounded-xl p-4 sm:p-5 mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          <div>
            <p className="text-blue-900 font-semibold text-base sm:text-lg">Optimiza tus rutas</p>
            <p className="text-sm sm:text-base text-blue-700">
              Tienes {optimizableAssignments.length} destinos pendientes. Genera una sola ruta con optimización automática.
            </p>
          </div>
          <button
            onClick={handleOptimizeRoutes}
            disabled={optimizing}
            className="inline-flex items-center justify-center bg-blue-600 text-white px-4 sm:px-6 py-2.5 rounded-lg font-semibold text-sm sm:text-base hover:bg-blue-700 active:bg-blue-800 disabled:opacity-50 disabled:cursor-not-allowed transition"
          >
            {optimizing ? 'Optimizando...' : 'Optimizar rutas'}
          </button>
        </div>
      )}

      {!loading && assignments.length === 0 && (
        <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-8 sm:p-12 text-center">
          <div className="text-5xl sm:text-6xl mb-4">📦</div>
          <h3 className="text-lg sm:text-xl font-semibold text-gray-900 mb-2">
            No tienes pedidos asignados
          </h3>
          <p className="text-sm sm:text-base text-gray-600">
            Los pedidos asignados aparecerán aquí cuando estén disponibles
          </p>
        </div>
      )}

          {!loading && assignments.length > 0 && (
        <div className="grid gap-4">
          {assignments.map((assignment) => {
            const timeInfo = getTimeAgo(assignment.asignado_at);
            const isUrgent = timeInfo.color.includes('red') || timeInfo.color.includes('orange');
            const borderClass = isUrgent ? 'border-2 border-orange-400' : 'border border-gray-200';
            
            return (
            <Link
              key={assignment.id}
              href={`/pedidos/detalle?id=${assignment.id}`}
              className={`block bg-white rounded-xl shadow-md active:shadow-lg transition ${borderClass} overflow-hidden ${isUrgent ? 'ring-2 ring-orange-200' : ''}`}
            >
              <div className="p-4 sm:p-6">
                <div className="flex items-start justify-between mb-3 sm:mb-4 gap-2">
                  <div className="flex-1 min-w-0">
                    <div className="flex flex-wrap items-center gap-2 mb-2">
                      <h3 className="text-lg sm:text-2xl font-bold text-gray-900">
                        Pedido #{assignment.pedido.id}
                      </h3>
                      <span className={`px-2 py-1 rounded-full text-xs font-semibold whitespace-nowrap ${timeInfo.bg} ${timeInfo.color}`}>
                        ⏰ {timeInfo.text}
                      </span>
                    </div>
                    <p className="text-xs sm:text-sm text-gray-500">
                      {new Date(assignment.asignado_at).toLocaleDateString('es-MX', {
                        day: 'numeric',
                        month: 'short',
                        hour: '2-digit',
                        minute: '2-digit'
                      })}
                    </p>
                  </div>
                  <div className="flex-shrink-0">
                    {getEstadoBadge(assignment.estado)}
                  </div>
                </div>

                <div className="bg-gray-50 rounded-lg p-3 sm:p-4 mb-3 sm:mb-4">
                  <div className="flex items-start">
                    <span className="mr-2 sm:mr-3 text-xl sm:text-2xl flex-shrink-0">📍</span>
                    <span className="text-sm sm:text-base text-gray-800 font-medium break-words">{assignment.pedido.direccion_entrega}</span>
                  </div>
                </div>

                <div className="flex items-center justify-between">
                  <div className="flex items-center text-blue-600 font-semibold text-sm sm:text-base">
                    <span className="mr-1 sm:mr-2">📦</span>
                    <span>{assignment.pedido.productos.length} {assignment.pedido.productos.length === 1 ? 'producto' : 'productos'}</span>
                  </div>
                  
                  <div className="flex items-center">
                    {assignment.estado === 'pendiente' && (
                      <span className="text-green-600 font-bold text-base sm:text-lg">🚀 Iniciar</span>
                    )}
                    {assignment.estado === 'en_ruta' && (
                      <span className="text-blue-600 font-bold text-base sm:text-lg">✅ Completar</span>
                    )}
                    <svg className="w-5 h-5 sm:w-6 sm:h-6 text-gray-400 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                    </svg>
                  </div>
                </div>
              </div>
            </Link>
            );
          })}
        </div>
      )}
    </div>
  );
}

