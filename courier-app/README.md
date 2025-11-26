# 🚚 App para Choferes - SRDigitalPro

Aplicación web responsiva para choferes de entrega, desarrollada con Next.js 14 y conectada a la API de Laravel.

## 📱 Características

- ✅ Login con email y contraseña
- 📦 Lista de pedidos asignados
- 🗺️ **NUEVO: Optimización inteligente de múltiples rutas**
- 🎯 **NUEVO: Ordenamiento por prioridad (fecha de asignación)**
- 🚀 Iniciar ruta con un click
- ✅ Completar entrega con un click
- 📱 Diseño responsivo optimizado para móviles
- 🧭 Integración directa con Google Maps Navigation
- 🎨 UI moderna con gradientes y feedback visual

## ✨ Optimización de Rutas

La app ahora incluye una funcionalidad avanzada de optimización de rutas:

- **Genera una sola ruta optimizada** para múltiples pedidos pendientes
- **Ordena por prioridad**: Los pedidos más antiguos (asignados primero) tienen mayor prioridad
- **Vista previa**: Muestra la lista de pedidos ordenados antes de abrir Google Maps
- **Optimización automática**: Google Maps calcula la ruta más eficiente respetando el orden
- **Mínimo 2 pedidos**: Se activa automáticamente cuando hay 2 o más destinos con coordenadas

## 📖 Cómo Usar la Optimización de Rutas

1. **Inicia sesión** en la app con tu cuenta de chofer
2. Ve a la sección **"Mis Pedidos"**
3. Si tienes **2 o más pedidos pendientes** con coordenadas, verás el botón **"Optimizar Rutas"**
4. Haz clic en el botón y confirma el orden de entregas
5. La app abrirá **Google Maps** con la ruta optimizada lista para navegar
6. Sigue las instrucciones de Google Maps para completar todas las entregas

### 🎯 Prioridad de Entregas

Los pedidos se ordenan automáticamente por:
1. **Fecha de asignación** (más antiguos primero)
2. **Hora de asignación** (si tienen la misma fecha)

Esto asegura que los pedidos urgentes se entreguen primero.

---

## 🚀 Inicio Rápido

### Variables de Entorno

Crea un archivo `.env.local` en la raíz del proyecto:

```env
# URL de la API de Laravel (local o producción)
NEXT_PUBLIC_API_URL=http://localhost:8000/api

# API Key de Google Maps (opcional, para ver el mapa)
NEXT_PUBLIC_GOOGLE_MAPS_API_KEY=tu_google_maps_api_key
```

### Instalación

```bash
# Instalar dependencias
npm install

# Ejecutar en desarrollo
npm run dev

# Build para producción
npm run build

# Ejecutar en producción
npm start
```

La app estará disponible en `http://localhost:3000`

## 📂 Estructura

```
courier-app/
├── app/
│   ├── (auth)/
│   │   ├── login/
│   │   │   └── page.tsx          # Login
│   │   └── layout.tsx
│   ├── (dashboard)/
│   │   ├── layout.tsx            # Layout con navegación
│   │   ├── pedidos/
│   │   │   ├── page.tsx          # Lista de pedidos
│   │   │   └── [id]/
│   │   │       └── page.tsx      # Detalle del pedido
│   │   └── page.tsx              # Redirección
│   ├── layout.tsx                # Layout principal
│   └── page.tsx                  # Home (redirección)
├── components/
│   └── Map.tsx                   # Componente de Google Maps
├── lib/
│   └── api.ts                    # Cliente API con axios
├── types/
│   └── index.ts                  # Tipos TypeScript
└── .env.local                    # Variables de entorno
```

## 🔐 Autenticación

Los choferes se autentican con su email y contraseña (configurados en el ERP).

El token de autenticación se guarda en `localStorage` y se envía automáticamente en todas las peticiones.

## 📡 API Endpoints

- `POST /api/courier/login` - Login
- `GET /api/courier/assignments` - Listar pedidos asignados
- `GET /api/courier/assignments/{id}` - Detalle de pedido
- `POST /api/courier/assignments/{id}/start` - Iniciar ruta
- `POST /api/courier/assignments/{id}/complete` - Completar entrega
- `POST /api/courier/logout` - Logout

## 🗺️ Google Maps

La app usa Google Maps para:
- Visualizar la ubicación de entrega
- Iniciar navegación con Google Maps app

**Nota**: Si no tienes API Key de Google Maps, la app funcionará igual pero mostrará un botón para abrir la navegación directamente.

## 🚢 Deployment

### Vercel

1. Conecta tu repo de GitHub a Vercel
2. Configura las variables de entorno:
   - `NEXT_PUBLIC_API_URL` - URL de tu API en producción
   - `NEXT_PUBLIC_GOOGLE_MAPS_API_KEY` - Tu Google Maps API Key
3. Deploy

### Build Manual

```bash
npm run build
npm start
```

## 🎨 Tecnologías

- Next.js 14 (App Router)
- TypeScript
- Tailwind CSS
- Axios
- Google Maps API
- React

## 📱 Responsive Design

La app está optimizada para móviles y tablets, ideal para uso de choferes en ruta.

## 🔗 Enlaces

- [Documentación Next.js](https://nextjs.org/docs)
- [Google Maps API](https://developers.google.com/maps/documentation)
- [Backend API](../README.md)
