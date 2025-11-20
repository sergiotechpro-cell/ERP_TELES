'use client';

import { useState } from 'react';
import { useRouter } from 'next/navigation';
import { authAPI } from '@/lib/api';

export default function LoginPage() {
  const router = useRouter();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    setLoading(true);

    try {
      await authAPI.login(email, password);
      router.push('/pedidos');
    } catch (err: any) {
      console.error('Login error:', err);
      
      // Manejar diferentes tipos de errores
      let errorMessage = 'Error al iniciar sesión';
      
      if (err.response) {
        // Error de respuesta del servidor
        const data = err.response.data;
        if (data?.message) {
          errorMessage = data.message;
        } else if (data?.errors?.email) {
          errorMessage = Array.isArray(data.errors.email) 
            ? data.errors.email[0] 
            : data.errors.email;
        } else if (err.response.status === 422) {
          errorMessage = 'Las credenciales son incorrectas';
        } else if (err.response.status === 401) {
          errorMessage = 'No autorizado. Verifica tus credenciales';
        } else if (err.response.status === 500) {
          errorMessage = 'Error del servidor. Intenta más tarde';
        }
      } else if (err.request) {
        // Error de red (sin respuesta del servidor)
        errorMessage = 'No se pudo conectar al servidor. Verifica tu conexión a internet';
      } else {
        // Otro tipo de error
        errorMessage = err.message || 'Error desconocido';
      }
      
      setError(errorMessage);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-500 to-blue-700 px-4 py-8">
      <div className="max-w-md w-full bg-white rounded-2xl shadow-2xl p-6 sm:p-8">
        <div className="text-center mb-6 sm:mb-8">
          <div className="inline-flex items-center justify-center w-14 h-14 sm:w-16 sm:h-16 bg-blue-600 rounded-full mb-3 sm:mb-4">
            <svg className="w-7 h-7 sm:w-8 sm:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
            </svg>
          </div>
          <h1 className="text-2xl sm:text-3xl font-bold text-gray-900">GRC ELECTRONICS</h1>
          <p className="text-sm sm:text-base text-gray-600 mt-2">Inicia sesión para comenzar</p>
        </div>

        <form onSubmit={handleSubmit} className="space-y-5 sm:space-y-6">
          {error && (
            <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
              {error}
            </div>
          )}

          <div>
            <label htmlFor="email" className="block text-sm font-medium text-gray-700 mb-2">
              Email
            </label>
            <input
              id="email"
              type="email"
              required
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              className="w-full px-4 py-3 text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition"
              placeholder="tu@email.com"
              autoComplete="email"
            />
          </div>

          <div>
            <label htmlFor="password" className="block text-sm font-medium text-gray-700 mb-2">
              Contraseña
            </label>
            <input
              id="password"
              type="password"
              required
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              className="w-full px-4 py-3 text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition"
              placeholder="••••••••"
              autoComplete="current-password"
            />
          </div>

          <button
            type="submit"
            disabled={loading}
            className="w-full bg-blue-600 text-white py-3.5 rounded-lg font-semibold text-base hover:bg-blue-700 active:bg-blue-800 disabled:opacity-50 disabled:cursor-not-allowed transition shadow-lg"
          >
            {loading ? 'Iniciando sesión...' : 'Iniciar Sesión'}
          </button>
        </form>
      </div>
    </div>
  );
}

