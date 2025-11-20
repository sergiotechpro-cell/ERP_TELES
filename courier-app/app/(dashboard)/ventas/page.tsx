'use client';

import { useEffect, useState } from 'react';
import { courierAPI } from '@/lib/api';
import type { Sale, SalesSummary } from '@/types';

export default function VentasPage() {
  const [sales, setSales] = useState<Sale[]>([]);
  const [summary, setSummary] = useState<SalesSummary | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [dateFilter, setDateFilter] = useState<'today' | 'week' | 'month' | 'all'>('today');

  useEffect(() => {
    loadSales();
  }, [dateFilter]);

  const getDateRange = () => {
    const now = new Date();
    let startDate: Date;
    const endDate = new Date(now);
    endDate.setHours(23, 59, 59, 999);

    switch (dateFilter) {
      case 'today':
        startDate = new Date(now);
        startDate.setHours(0, 0, 0, 0);
        break;
      case 'week':
        startDate = new Date(now);
        startDate.setDate(startDate.getDate() - 7);
        startDate.setHours(0, 0, 0, 0);
        break;
      case 'month':
        startDate = new Date(now);
        startDate.setMonth(startDate.getMonth() - 1);
        startDate.setHours(0, 0, 0, 0);
        break;
      default:
        startDate = new Date(0);
        endDate.setFullYear(2100); // Fecha futura para "todas"
    }

    return {
      start: startDate.toISOString(),
      end: endDate.toISOString(),
    };
  };

  const loadSales = async () => {
    try {
      setLoading(true);
      const { start, end } = getDateRange();
      const response = await courierAPI.getSales(start, end);
      setSales(response.data);
      setSummary(response.summary);
      setError('');
    } catch (err: any) {
      setError('Error al cargar ventas');
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const getPaymentMethodBadge = (formaPago: string) => {
    const colors: Record<string, string> = {
      efectivo: 'bg-green-100 text-green-800',
      tarjeta: 'bg-blue-100 text-blue-800',
      transferencia: 'bg-purple-100 text-purple-800',
    };

    const icons: Record<string, string> = {
      efectivo: '💵',
      tarjeta: '💳',
      transferencia: '🏦',
    };

    return (
      <span className={`px-3 py-1 rounded-full text-xs font-semibold flex items-center gap-1 ${
        colors[formaPago] || 'bg-gray-100 text-gray-800'
      }`}>
        <span>{icons[formaPago] || '💰'}</span>
        <span className="capitalize">{formaPago}</span>
      </span>
    );
  };

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('es-MX', {
      style: 'currency',
      currency: 'MXN',
    }).format(amount);
  };

  return (
    <div className="w-full">
      <div className="mb-6">
        <h1 className="text-2xl sm:text-3xl font-bold text-gray-900 mb-1">Ventas Brutas</h1>
        <p className="text-sm sm:text-base text-gray-600">Consulta el registro de todas las ventas realizadas</p>
      </div>

      {/* Filtros de fecha */}
      <div className="mb-6 bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <div className="flex flex-wrap gap-2">
          <button
            onClick={() => setDateFilter('today')}
            className={`px-4 py-2 rounded-lg text-sm font-medium transition ${
              dateFilter === 'today'
                ? 'bg-blue-600 text-white'
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
            }`}
          >
            Hoy
          </button>
          <button
            onClick={() => setDateFilter('week')}
            className={`px-4 py-2 rounded-lg text-sm font-medium transition ${
              dateFilter === 'week'
                ? 'bg-blue-600 text-white'
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
            }`}
          >
            Última semana
          </button>
          <button
            onClick={() => setDateFilter('month')}
            className={`px-4 py-2 rounded-lg text-sm font-medium transition ${
              dateFilter === 'month'
                ? 'bg-blue-600 text-white'
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
            }`}
          >
            Último mes
          </button>
          <button
            onClick={() => setDateFilter('all')}
            className={`px-4 py-2 rounded-lg text-sm font-medium transition ${
              dateFilter === 'all'
                ? 'bg-blue-600 text-white'
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
            }`}
          >
            Todas
          </button>
        </div>
      </div>

      {/* Resumen */}
      {summary && (
        <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4 mb-6">
          <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-3 sm:p-4">
            <div className="text-xs sm:text-sm text-gray-600 mb-1">Total Ventas</div>
            <div className="text-xl sm:text-2xl font-bold text-gray-900">{summary.total_ventas}</div>
          </div>
          <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-3 sm:p-4">
            <div className="text-xs sm:text-sm text-gray-600 mb-1">Total Bruto</div>
            <div className="text-lg sm:text-2xl font-bold text-green-600 truncate">{formatCurrency(summary.total_bruto)}</div>
          </div>
          <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-3 sm:p-4">
            <div className="text-xs sm:text-sm text-gray-600 mb-1">Efectivo</div>
            <div className="text-base sm:text-xl font-bold text-green-700 truncate">{formatCurrency(summary.total_efectivo)}</div>
          </div>
          <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-3 sm:p-4">
            <div className="text-xs sm:text-sm text-gray-600 mb-1">Tarjeta</div>
            <div className="text-base sm:text-xl font-bold text-blue-700 truncate">{formatCurrency(summary.total_tarjeta)}</div>
          </div>
          <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-3 sm:p-4 col-span-2 sm:col-span-1">
            <div className="text-xs sm:text-sm text-gray-600 mb-1">Transferencia</div>
            <div className="text-base sm:text-xl font-bold text-purple-700 truncate">{formatCurrency(summary.total_transferencia)}</div>
          </div>
        </div>
      )}

      {loading && (
        <div className="text-center py-12">
          <div className="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
          <p className="mt-4 text-gray-600">Cargando ventas...</p>
        </div>
      )}

      {error && (
        <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">
          {error}
        </div>
      )}

      {!loading && sales.length === 0 && (
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
          <div className="text-6xl mb-4">📊</div>
          <h3 className="text-xl font-semibold text-gray-900 mb-2">
            No hay ventas registradas
          </h3>
          <p className="text-gray-600">
            Las ventas aparecerán aquí cuando se registren en el sistema
          </p>
        </div>
      )}

      {!loading && sales.length > 0 && (
        <div className="grid gap-4">
          {sales.map((sale) => (
            <div
              key={sale.id}
              className="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden"
            >
              <div className="p-6">
                <div className="flex items-start justify-between mb-4">
                  <div className="flex-1">
                    <div className="flex items-center gap-2 mb-2">
                      <h3 className="text-xl font-bold text-gray-900">
                        Venta #{sale.id}
                      </h3>
                      {getPaymentMethodBadge(sale.forma_pago)}
                    </div>
                    <p className="text-sm text-gray-500">
                      {new Date(sale.fecha).toLocaleDateString('es-MX', {
                        day: 'numeric',
                        month: 'long',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                      })}
                    </p>
                    {sale.vendedor && (
                      <p className="text-sm text-gray-500 mt-1">
                        Vendedor: {sale.vendedor.name}
                      </p>
                    )}
                    {sale.cliente && (
                      <p className="text-sm text-gray-500 mt-1">
                        Cliente: {sale.cliente.nombre}
                      </p>
                    )}
                  </div>
                  <div className="text-right">
                    <div className="text-2xl font-bold text-green-600">
                      {formatCurrency(sale.total)}
                    </div>
                    {sale.envio > 0 && (
                      <div className="text-sm text-gray-500">
                        Envío: {formatCurrency(sale.envio)}
                      </div>
                    )}
                  </div>
                </div>

                <div className="border-t border-gray-200 pt-4 mt-4">
                  <h4 className="text-sm font-semibold text-gray-700 mb-2">Productos:</h4>
                  <div className="space-y-2">
                    {sale.items.map((item, idx) => (
                      <div key={idx} className="flex justify-between items-center text-sm">
                        <span className="text-gray-700">
                          {item.cantidad}x {item.producto}
                        </span>
                        <span className="font-medium text-gray-900">
                          {formatCurrency(item.subtotal)}
                        </span>
                      </div>
                    ))}
                  </div>
                  <div className="flex justify-between items-center mt-3 pt-3 border-t border-gray-100">
                    <span className="font-semibold text-gray-700">Subtotal:</span>
                    <span className="font-bold text-gray-900">{formatCurrency(sale.subtotal)}</span>
                  </div>
                </div>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}

