# App para Choferes - Instrucciones de Configuración

## 📋 Estado Actual

✅ **Backend API Listo**

- Controladores API creados (`AuthController` y `CourierController`)
- Rutas configuradas con autenticación Sanctum
- CORS habilitado
- Relaciones de modelos configuradas

## 🚀 Comandos para Crear la App Next.js

### 1. Crear el proyecto Next.js

Desde el directorio `C:\Users\User\Desktop\teleserp`, ejecuta:

```bash
cd ..
npx create-next-app@latest courier-app --typescript --tailwind --app --no-src --import-alias "@/*"
```

Cuando te pregunte si quieres crear el proyecto en el directorio `courier-app`, escribe **N** y confirma que quieres crear en el directorio actual.

O ejecuta directamente:

```bash
cd ..
mkdir courier-app
cd courier-app
npx create-next-app@latest . --typescript --tailwind --app --no-src --import-alias "@/*"
```

### 2. Instalar dependencias adicionales

```bash
cd courier-app
npm install axios @tanstack/react-query
npm install -D @types/node
```

### 3. Configurar variables de entorno

Crea un archivo `.env.local`:

```env
NEXT_PUBLIC_API_URL=http://localhost:8000/api
```

### 4. Estructura de archivos recomendada

```
courier-app/
├── app/
│   ├── (auth)/
│   │   ├── login/
│   │   │   └── page.tsx
│   ├── (dashboard)/
│   │   ├── layout.tsx
│   │   ├── page.tsx          # Lista de pedidos
│   │   ├── pedidos/
│   │   │   └── [id]/
│   │   │       └── page.tsx  # Detalle del pedido
│   │   └── profile/
│   │       └── page.tsx
│   └── layout.tsx
├── components/
│   ├── PedidoCard.tsx
│   ├── PedidoDetail.tsx
│   ├── ChecklistItem.tsx
│   └── GoogleMap.tsx
├── lib/
│   ├── api.ts
│   └── auth.ts
└── types/
    └── index.ts
```

## 🔌 Endpoints API Disponibles

### Autenticación

- `POST /api/courier/login` - Login del chofer
- `POST /api/courier/logout` - Logout
- `GET /api/courier/profile` - Perfil del chofer autenticado

### Pedidos

- `GET /api/courier/assignments` - Listar pedidos asignados
- `GET /api/courier/assignments/{id}` - Detalle de un pedido
- `POST /api/courier/assignments/{id}/start` - Iniciar entrega
- `POST /api/courier/assignments/{id}/complete` - Completar entrega

## 📝 Formato de Respuestas API

### Login Response

```json
{
  "token": "1|xxxxx...",
  "user": {
    "id": 1,
    "name": "Carlos Mendoza",
    "email": "carlos@teleserp.com",
    "telefono": "5559988776"
  }
}
```

### Assignments Response

```json
{
  "data": [
    {
      "id": 1,
      "estado": "pendiente",
      "asignado_at": "2025-11-02T...",
      "salida_at": null,
      "pedido": {
        "id": 1,
        "estado": "asignado",
        "direccion_entrega": "Av. Insurgentes...",
        "lat": 19.3617856,
        "lng": -99.1733642,
        "costo_envio": 150.00,
        "productos": [...],
        "checklist": [...]
      }
    }
  ]
}
```

## 🎨 Funcionalidades de la App

### 1. Login

- Campo email y password
- Validación de credenciales
- Guardar token en localStorage
- Redirigir a lista de pedidos

### 2. Lista de Pedidos

- Mostrar pedidos con estado "pendiente" y "en_ruta"
- Badge de estado
- Dirección
- Fecha de asignación
- Botón para ver detalles

### 3. Detalle del Pedido

- Información completa del pedido
- Lista de productos
- Checklist para marcar completado
- Mapa con la ubicación de entrega
- Botón "Iniciar ruta" (si estado es pendiente)
- Botón "Completar entrega" (si estado es en_ruta)

### 4. Perfil

- Información del chofer
- Botón para cerrar sesión

## 🗺️ Integración con Google Maps

Para mostrar el mapa de la ruta:

```bash
npm install @react-google-maps/api
```

Configura en `next.config.js`:

```js
const nextConfig = {
  env: {
    NEXT_PUBLIC_GOOGLE_MAPS_API_KEY: 'tu-api-key'
  }
}
```

## 🚢 Deployment en Vercel

1. Conecta tu repo de GitHub a Vercel
2. Configura las variables de entorno en Vercel:
   - `NEXT_PUBLIC_API_URL` = URL de tu API en producción
3. Deploy automático

## ✅ Próximos Pasos

1. Crear la app Next.js con los comandos de arriba
2. Configurar el cliente API en `lib/api.ts`
3. Implementar las páginas y componentes
4. Agregar integración con Google Maps
5. Configurar estilos responsivos
6. Probar flujo completo
7. Deploy a Vercel

## 🧪 Probar la API

Mientras creas la app, puedes probar la API con Postman o cURL:

```bash
# Login
curl -X POST http://localhost:8000/api/courier/login \
  -H "Content-Type: application/json" \
  -d '{"email":"carlos@teleserp.com","password":"12345678"}'

# Get assignments (reemplaza TOKEN)
curl http://localhost:8000/api/courier/assignments \
  -H "Authorization: Bearer TOKEN"
```

## 📚 Referencias

- Next.js Docs: https://nextjs.org/docs
- TanStack Query: https://tanstack.com/query/latest
- Google Maps API: https://developers.google.com/maps/documentation/javascript
