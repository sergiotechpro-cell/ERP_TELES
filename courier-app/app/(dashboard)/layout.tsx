'use client';

import { useEffect, useState } from 'react';
import { useRouter, usePathname } from 'next/navigation';
import Link from 'next/link';

export default function DashboardLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  const router = useRouter();
  const pathname = usePathname();
  const [user, setUser] = useState<{ name?: string }>({});
  const [mounted, setMounted] = useState(false);

  useEffect(() => {
    setMounted(true);
    
    // Verificar si hay token
    const token = localStorage.getItem('courier_token');
    if (!token) {
      router.push('/login');
      return;
    }

    // Cargar usuario desde localStorage
    const userData = localStorage.getItem('courier_user');
    if (userData) {
      try {
        setUser(JSON.parse(userData));
      } catch (e) {
        console.error('Error parsing user data:', e);
      }
    }
  }, [router]);

  const handleLogout = (e: React.MouseEvent) => {
    e.preventDefault();
    e.stopPropagation();
    
    console.log('Logout clicked');
    
    // Limpiar localStorage
    localStorage.removeItem('courier_token');
    localStorage.removeItem('courier_user');
    
    // Redirigir al login
    router.push('/login');
    router.refresh();
  };

  return (
    <div className="min-h-screen bg-gray-50 pb-20">
      {/* Header móvil optimizado */}
      <nav className="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-50">
        <div className="px-4 py-3">
          {/* Primera fila: Logo y usuario */}
          <div className="flex justify-between items-center mb-3">
            <Link href="/pedidos" className="text-lg font-bold text-blue-600 flex items-center gap-2">
              <svg className="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
              </svg>
              <span className="hidden sm:inline">Chofer App</span>
              <span className="sm:hidden">App</span>
            </Link>
            {mounted && user?.name && (
              <span className="text-xs sm:text-sm text-gray-700 truncate max-w-[100px] sm:max-w-none font-medium">
                {user.name.split(' ')[0]}
              </span>
            )}
          </div>
          
          {/* Segunda fila: Navegación y Logout */}
          <div className="flex gap-2 border-t border-gray-100 pt-3">
            <Link
              href="/pedidos"
              className={`flex-1 px-3 py-2 rounded-lg text-sm font-medium text-center transition ${
                pathname === '/pedidos' || pathname?.startsWith('/pedidos/')
                  ? 'bg-blue-600 text-white shadow-sm'
                  : 'bg-gray-100 text-gray-700 hover:bg-gray-200 active:bg-gray-300'
              }`}
            >
              📦 Pedidos
            </Link>
            <Link
              href="/ventas"
              className={`flex-1 px-3 py-2 rounded-lg text-sm font-medium text-center transition ${
                pathname === '/ventas'
                  ? 'bg-blue-600 text-white shadow-sm'
                  : 'bg-gray-100 text-gray-700 hover:bg-gray-200 active:bg-gray-300'
              }`}
            >
              💰 Ventas
            </Link>
            <button
              type="button"
              onClick={handleLogout}
              className="flex items-center justify-center gap-1.5 px-3 py-2 text-sm font-medium text-white bg-red-600 border border-red-700 rounded-lg hover:bg-red-700 active:bg-red-800 transition shadow-sm min-w-[80px]"
              title="Cerrar sesión"
            >
              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
              </svg>
              <span>Salir</span>
            </button>
          </div>
        </div>
      </nav>

      {/* Contenido principal */}
      <main className="px-4 py-6 max-w-4xl mx-auto w-full">
        {children}
      </main>
    </div>
  );
}

