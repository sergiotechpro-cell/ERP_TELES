# 🧪 Cómo Probar la App del Chofer

## 📋 Requisitos Previos

1. **Backend Laravel debe estar corriendo**
   - Debe estar en `http://localhost:8000`
   - Si no está corriendo, ejecuta:
   ```bash
   cd C:\Users\User\Desktop\teleserp
   php artisan serve
   ```

2. **App Next.js debe estar corriendo**
   - Se ejecuta en `http://localhost:3000`
   - Ya está iniciado en segundo plano

## 🚀 Acceder a la App

1. Abre tu navegador y ve a: **http://localhost:3000**

2. Serás redirigido automáticamente a la página de login

## 🔐 Credenciales de Prueba

Usa las credenciales del chofer creado:

- **Email:** `carlos@teleserp.com`
- **Password:** `12345678`

## ✅ Flujo de Prueba

### 1. Login
- Ingresa las credenciales
- Click en "Iniciar Sesión"
- Deberías ser redirigido a la lista de pedidos

### 2. Ver Pedidos
- Verás los pedidos asignados al chofer Carlos Mendoza
- Cada pedido muestra:
  - Número de pedido
  - Dirección de entrega
  - Estado (pendiente/en_ruta/entregado)
  - Cantidad de productos

### 3. Ver Detalle de Pedido
- Click en cualquier pedido
- Verás:
  - **Mapa de Google Maps** con la ubicación (si tienes API Key)
  - Botón "Iniciar Navegación" que abre Google Maps
  - Dirección completa
  - Lista de productos
  - Botón grande para iniciar/completar

### 4. Iniciar Ruta
- Si el pedido está en estado "pendiente"
- Click en "🚀 Iniciar Ruta"
- El estado cambiará a "en_ruta"
- El pedido se actualiza en el ERP

### 5. Completar Entrega
- Cuando el pedido está "en_ruta"
- Click en "✅ Completar Entrega"
- El estado cambiará a "entregado"
- El pago se marca como "completado" en el ERP
- Serás redirigido a la lista de pedidos

## 🗺️ Google Maps

**Importante:** Para ver el mapa integrado, necesitas configurar la API Key:

1. Edita el archivo `.env.local` en `courier-app/.env.local`
2. Agrega:
   ```
   NEXT_PUBLIC_GOOGLE_MAPS_API_KEY=tu_api_key_aqui
   ```
3. Reinicia el servidor Next.js

**Sin API Key:** La app funcionará igual, solo mostrará un botón para abrir Google Maps directamente en lugar del mapa integrado.

## 🐛 Solución de Problemas

### "Error al iniciar sesión"
- Verifica que el backend Laravel esté corriendo en `http://localhost:8000`
- Verifica que el chofer exista en la base de datos
- Revisa la consola del navegador (F12) para ver errores

### "No puedo ver pedidos"
- Verifica que haya pedidos asignados al chofer en el ERP
- El chofer debe tener `DeliveryAssignment` con estado "pendiente" o "en_ruta"

### "El mapa no carga"
- Verifica que tengas `NEXT_PUBLIC_GOOGLE_MAPS_API_KEY` configurada
- Verifica que la API Key tenga habilitado "Maps JavaScript API"
- Sin API Key, el botón "Iniciar Navegación" seguirá funcionando

### "No se conecta a la API"
- Verifica que `NEXT_PUBLIC_API_URL=http://localhost:8000/api` esté en `.env.local`
- Verifica CORS en Laravel
- Revisa la consola del navegador para errores de red

## 📱 Probar en Móvil

1. Encuentra tu IP local:
   ```bash
   ipconfig
   # Busca "IPv4 Address" (ej: 192.168.1.100)
   ```

2. En Next.js, edita `next.config.ts` y agrega:
   ```typescript
   const nextConfig = {
     // ...
     async rewrites() {
       return [];
     },
   };
   ```

3. Inicia Next.js con:
   ```bash
   npm run dev -- -H 0.0.0.0
   ```

4. En tu móvil, abre: `http://TU_IP:3000` (misma red WiFi)

## ✅ Checklist de Prueba

- [ ] Login funciona
- [ ] Se cargan los pedidos
- [ ] Se puede ver el detalle de un pedido
- [ ] El mapa se muestra (o botón de navegación)
- [ ] Se puede iniciar una ruta
- [ ] Se puede completar una entrega
- [ ] Los estados se actualizan en el ERP
- [ ] Los pagos se actualizan a "completado"

## 🎉 ¡Listo!

Si todo funciona, tu app está lista para deployment en Vercel.

