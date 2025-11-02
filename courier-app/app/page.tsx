'use client';

import { useEffect } from 'react';
import { useRouter } from 'next/navigation';

export default function Home() {
  const router = useRouter();

  useEffect(() => {
    // Redirigir a pedidos si está autenticado, sino a login
    const token = localStorage.getItem('courier_token');
    if (token) {
      router.push('/pedidos');
    } else {
      router.push('/login');
    }
  }, [router]);

  return (
    <div className="flex min-h-screen items-center justify-center">
      <div className="text-center">
        <div className="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        <p className="mt-4 text-gray-600">Cargando...</p>
      </div>
    </div>
  );
}
