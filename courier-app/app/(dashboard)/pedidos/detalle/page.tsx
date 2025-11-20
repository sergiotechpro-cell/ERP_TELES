'use client';

import { useEffect, useState, Suspense } from 'react';
import { useRouter, useSearchParams } from 'next/navigation';
import { courierAPI } from '@/lib/api';
import type { Assignment } from '@/types';
import MapComponent from '@/components/Map';

function PedidoDetailContent() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const id = parseInt(searchParams.get('id') || '0');

  const [assignment, setAssignment] = useState<Assignment | null>(null);
  const [loading, setLoading] = useState(true);
  const [actionLoading, setActionLoading] = useState(false);
  const [error, setError] = useState('');

  useEffect(() => {
    if (id) {
      loadAssignment();
    } else {
      setError('ID de pedido no válido');
      setLoading(false);
    }
  }, [id]);

  const loadAssignment = async () => {
    try {
      const data = await courierAPI.getAssignment(id);
      setAssignment(data);
      setError('');
    } catch (err: any) {
      setError('Error al cargar el pedido');
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const handleStart = async () => {
    if (!assignment) return;
    
    setActionLoading(true);
    try {
      await courierAPI.startAssignment(id);
      await loadAssignment();
    } catch (err: any) {
      setError('Error al iniciar la entrega');
    } finally {
      setActionLoading(false);
    }
  };

  const handleComplete = async () => {
    if (!assignment) return;
    
    setActionLoading(true);
    try {
      await courierAPI.completeAssignment(id);
      alert('✅ ¡Entrega completada!');
      router.push('/pedidos');
    } catch (err: any) {
      setError('Error al completar la entrega');
      setActionLoading(false);
    }
  };

  if (loading) {
    return (
      <div className="text-center py-12">
        <div className="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        <p className="mt-4 text-gray-600">Cargando pedido...</p>
      </div>
    );
  }

  if (error || !assignment) {
    return (
      <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
        {error || 'Pedido no encontrado'}
      </div>
    );
  }

  return (
    <div className="w-full max-w-4xl mx-auto">
      <div className="mb-4 sm:mb-6">
        <button
          onClick={() => router.back()}
          className="flex items-center text-gray-600 hover:text-gray-900 active:text-gray-900 text-sm sm:text-base"
        >
          <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
          </svg>
          Volver
        </button>
      </div>

      <div className="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-4 sm:mb-6">
        <div className="p-4 sm:p-6 border-b border-gray-200">
          <div className="flex items-center justify-between gap-2">
            <h1 className="text-xl sm:text-2xl font-bold text-gray-900">
              Pedido #{assignment.pedido.id}
            </h1>
            <span className={`px-2 sm:px-3 py-1 rounded-full text-xs font-semibold whitespace-nowrap ${
              assignment.estado === 'pendiente' ? 'bg-yellow-100 text-yellow-800' :
              assignment.estado === 'en_ruta' ? 'bg-blue-100 text-blue-800' :
              'bg-green-100 text-green-800'
            }`}>
              {assignment.estado.toUpperCase()}
            </span>
          </div>
        </div>

        <div className="p-4 sm:p-6 space-y-4 sm:space-y-6">
          {/* Mapa con ruta completa */}
          {assignment.pedido.lat && assignment.pedido.lng && (
            <div className="mb-6">
              <div className="mb-2">
                <h3 className="text-sm font-semibold text-gray-900 mb-2 flex items-center">
                  <span className="mr-2">🗺️</span>
                  Ruta de Entrega
                </h3>
                {assignment.origen && (
                  <div className="text-xs text-gray-600 mb-2">
                    Desde: <strong>{assignment.origen.name || 'Bodega Principal'}</strong>
                    {assignment.origen.lat && assignment.origen.lng && (
                      <span className="text-gray-500 ml-2">
                        ({assignment.origen.lat.toFixed(4)}, {assignment.origen.lng.toFixed(4)})
                      </span>
                    )}
                  </div>
                )}
                {!assignment.origen && (
                  <div className="text-xs text-yellow-600 mb-2">
                    ⚠️ No se encontró información del origen (bodega)
                  </div>
                )}
              </div>
              <MapComponent 
                lat={assignment.pedido.lat} 
                lng={assignment.pedido.lng}
                origen={assignment.origen || undefined}
              />
              <div className="mt-4 text-center">
                <a
                  href={`https://www.google.com/maps/dir/?api=1&destination=${assignment.pedido.lat},${assignment.pedido.lng}`}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="inline-flex items-center justify-center bg-blue-600 text-white px-4 sm:px-6 py-2.5 sm:py-3 rounded-lg hover:bg-blue-700 active:bg-blue-800 transition font-semibold text-sm sm:text-base w-full sm:w-auto"
                >
                  <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                  </svg>
                  Abrir en Google Maps
                </a>
              </div>
            </div>
          )}

          {/* Dirección */}
          <div className="bg-blue-50 border border-blue-200 rounded-lg p-3 sm:p-4">
            <h3 className="text-sm font-semibold text-gray-900 mb-2 flex items-center">
              <span className="mr-2">📍</span>
              Dirección de Entrega
            </h3>
            <p className="text-sm sm:text-base text-gray-800 font-medium break-words">{assignment.pedido.direccion_entrega}</p>
          </div>

          {/* Productos */}
          <div>
            <h3 className="text-base sm:text-lg font-bold text-gray-900 mb-3 flex items-center">
              <span className="mr-2">📦</span>
              Productos
            </h3>
            <div className="space-y-2">
              {assignment.pedido.productos.map((producto, idx) => (
                <div
                  key={idx}
                  className="flex justify-between items-center bg-gray-50 rounded-lg p-3 sm:p-4"
                >
                  <div className="flex-1 min-w-0">
                    <p className="text-sm sm:text-base text-gray-900 font-semibold break-words">{producto.producto}</p>
                    <p className="text-xs sm:text-sm text-gray-600">Cantidad: {producto.cantidad}</p>
                  </div>
                </div>
              ))}
            </div>
          </div>

          {/* Acciones */}
          <div className="pt-4 border-t border-gray-200">
            {assignment.estado === 'pendiente' && (
              <button
                onClick={handleStart}
                disabled={actionLoading}
                className="w-full bg-blue-600 text-white py-3.5 sm:py-4 px-6 rounded-xl font-bold text-base sm:text-lg hover:bg-blue-700 active:bg-blue-800 disabled:opacity-50 disabled:cursor-not-allowed transition shadow-lg"
              >
                {actionLoading ? '⏳ Iniciando...' : '🚀 Iniciar Ruta'}
              </button>
            )}

            {assignment.estado === 'en_ruta' && (
              <button
                onClick={handleComplete}
                disabled={actionLoading}
                className="w-full bg-green-600 text-white py-3.5 sm:py-4 px-6 rounded-xl font-bold text-base sm:text-lg hover:bg-green-700 active:bg-green-800 disabled:opacity-50 disabled:cursor-not-allowed transition shadow-lg"
              >
                {actionLoading ? '⏳ Completando...' : '✅ Completar Entrega'}
              </button>
            )}

            {assignment.estado === 'entregado' && (
              <div className="bg-green-50 border border-green-200 text-green-800 px-4 py-4 rounded-lg text-center">
                <p className="text-2xl mb-2">🎉</p>
                <p className="font-semibold text-sm sm:text-base">Pedido Entregado</p>
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}

export default function PedidoDetailPage() {
  return (
    <Suspense fallback={
      <div className="text-center py-12">
        <div className="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        <p className="mt-4 text-gray-600">Cargando...</p>
      </div>
    }>
      <PedidoDetailContent />
    </Suspense>
  );
}

