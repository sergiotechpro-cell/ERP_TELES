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

  const handleLogout = () => {
    localStorage.removeItem('courier_token');
    localStorage.removeItem('courier_user');
    router.push('/login');
  };

  return (
    <div className="min-h-screen bg-gray-50 pb-20">
      {/* Header móvil optimizado */}
      <nav className="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-50">
        <div className="px-4 py-3">
          {/* Primera fila: Logo y usuario */}
          <div className="flex justify-between items-center mb-3">
            <Link href="/pedidos" className="text-lg font-bold text-blue-600 flex items-center">
              <span className="mr-2">🚚</span>
              <span className="hidden sm:inline">Chofer App</span>
              <span className="sm:hidden">App</span>
            </Link>
            <div className="flex items-center gap-2">
              {mounted && user?.name && (
                <span className="text-xs sm:text-sm text-gray-700 truncate max-w-[100px] sm:max-w-none">
                  {user.name.split(' ')[0]}
                </span>
              )}
              <button
                onClick={handleLogout}
                className="px-3 py-1.5 text-xs sm:text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 active:bg-gray-100 transition"
              >
                Salir
              </button>
            </div>
          </div>
          
          {/* Segunda fila: Navegación */}
          <nav className="flex gap-2 border-t border-gray-100 pt-3">
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
          </nav>
        </div>
      </nav>

      {/* Contenido principal */}
      <main className="px-4 py-6 max-w-4xl mx-auto w-full">
        {children}
      </main>
    </div>
  );
}

