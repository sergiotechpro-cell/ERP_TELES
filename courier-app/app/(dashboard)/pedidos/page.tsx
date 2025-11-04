'use client';

import { useEffect, useState } from 'react';
import Link from 'next/link';
import { courierAPI } from '@/lib/api';
import type { Assignment } from '@/types';

export default function PedidosPage() {
  const [assignments, setAssignments] = useState<Assignment[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    loadAssignments();
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
    <div>
      <div className="mb-8">
        <h1 className="text-3xl font-bold text-gray-900 mb-2">Mis Pedidos</h1>
        <p className="text-gray-600">Gestiona tus entregas asignadas</p>
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

      {!loading && assignments.length === 0 && (
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
          <div className="text-6xl mb-4">📦</div>
          <h3 className="text-xl font-semibold text-gray-900 mb-2">
            No tienes pedidos asignados
          </h3>
          <p className="text-gray-600">
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
              href={`/pedidos/${assignment.id}`}
              className={`block bg-white rounded-xl shadow-md hover:shadow-lg transition ${borderClass} overflow-hidden ${isUrgent ? 'ring-2 ring-orange-200' : ''}`}
            >
              <div className="p-6">
                <div className="flex items-start justify-between mb-4">
                  <div className="flex-1">
                    <div className="flex items-center gap-2 mb-2">
                      <h3 className="text-2xl font-bold text-gray-900">
                        Pedido #{assignment.pedido.id}
                      </h3>
                      <span className={`px-2 py-1 rounded-full text-xs font-semibold ${timeInfo.bg} ${timeInfo.color}`}>
                        ⏰ {timeInfo.text}
                      </span>
                    </div>
                    <p className="text-sm text-gray-500">
                      Asignado: {new Date(assignment.asignado_at).toLocaleDateString('es-MX', {
                        day: 'numeric',
                        month: 'short',
                        hour: '2-digit',
                        minute: '2-digit'
                      })}
                    </p>
                  </div>
                  <div className="ml-4">
                    {getEstadoBadge(assignment.estado)}
                  </div>
                </div>

                <div className="bg-gray-50 rounded-lg p-4 mb-4">
                  <div className="flex items-start text-base">
                    <span className="mr-3 text-2xl">📍</span>
                    <span className="text-gray-800 font-medium">{assignment.pedido.direccion_entrega}</span>
                  </div>
                </div>

                <div className="flex items-center justify-between">
                  <div className="flex items-center text-blue-600 font-semibold">
                    <span className="mr-2">📦</span>
                    <span>{assignment.pedido.productos.length} productos</span>
                  </div>
                  
                  <div className="flex items-center">
                    {assignment.estado === 'pendiente' && (
                      <span className="text-green-600 font-bold text-lg">🚀 Iniciar</span>
                    )}
                    {assignment.estado === 'en_ruta' && (
                      <span className="text-blue-600 font-bold text-lg">✅ Completar</span>
                    )}
                    <svg className="w-6 h-6 text-gray-400 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

