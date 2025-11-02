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
    <div className="min-h-screen bg-gray-50">
      <nav className="bg-white shadow-sm border-b border-gray-200">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between items-center h-16">
            <div className="flex items-center">
              <Link href="/pedidos" className="text-xl font-bold text-blue-600">
                🚚 Chofer App
              </Link>
              <nav className="ml-8 space-x-4">
                <Link
                  href="/pedidos"
                  className={`px-3 py-2 rounded-md text-sm font-medium ${
                    pathname === '/pedidos'
                      ? 'bg-blue-100 text-blue-700'
                      : 'text-gray-600 hover:bg-gray-100'
                  }`}
                >
                  Mis Pedidos
                </Link>
              </nav>
            </div>
            <div className="flex items-center space-x-4">
              {mounted && user?.name && (
                <span className="text-sm text-gray-700">
                  👤 {user.name}
                </span>
              )}
              <button
                onClick={handleLogout}
                className="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
              >
                Cerrar Sesión
              </button>
            </div>
          </div>
        </div>
      </nav>

      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {children}
      </main>
    </div>
  );
}

