import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  reactCompiler: true,
  // Configuración para Capacitor (export estático)
  output: 'export',
  images: {
    unoptimized: true, // Necesario para export estático
  },
  // Base path para Capacitor (opcional, ajustar según necesidad)
  // basePath: '',
  // trailingSlash: true,
};

export default nextConfig;
