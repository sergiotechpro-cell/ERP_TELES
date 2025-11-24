# 🚀 Configuración del Sistema de Tracking en Tiempo Real

## Resumen
Se ha implementado un sistema de tracking GPS en tiempo real tipo Uber/Rappi para monitorear a los choferes durante las entregas.

## 📋 Componentes Implementados

### Backend (Laravel)
✅ Migración `driver_locations` - Tabla para almacenar ubicaciones
✅ Modelo `DriverLocation` - Con relaciones y scopes
✅ Evento `DriverLocationUpdated` - Para broadcasting en tiempo real
✅ Controlador API `TrackingController` - Endpoints para recibir y consultar ubicaciones
✅ Rutas API configuradas
✅ Vista `tracking/map.blade.php` - Mapa en tiempo real para administradores
✅ Pusher PHP SDK instalado

### Frontend (ERP)
✅ Vista de mapa con Google Maps
✅ Integración con Pusher para actualizaciones en tiempo real
✅ Panel lateral con lista de choferes activos
✅ Ventanas de información con detalles de cada chofer
✅ Enlace en el menú del ERP (solo para usuarios con permiso `ver-dashboard`)

### App Móvil (Next.js + Capacitor)
✅ Hook personalizado `useLocationTracking` - Maneja el GPS y envío de ubicaciones
✅ Integración en la página de pedidos
✅ Indicador visual de GPS activo
✅ Tracking automático cuando hay pedidos en ruta

## 🔧 Configuración Requerida

### 1. Configurar Pusher (IMPORTANTE)

#### Paso 1: Crear cuenta en Pusher
1. Ve a https://pusher.com/
2. Crea una cuenta gratuita (permite 200k mensajes/día)
3. Crea un nuevo "Channel" (app)
4. Selecciona tu región más cercana (ej: us-east-1)

#### Paso 2: Obtener credenciales
En el dashboard de Pusher, ve a "App Keys" y copia:
- `app_id`
- `key`
- `secret`
- `cluster` (ej: mt1, us2, eu, ap1)

#### Paso 3: Configurar .env
Agrega estas variables a tu archivo `.env`:

```env
BROADCAST_DRIVER=pusher

PUSHER_APP_ID=tu_app_id
PUSHER_APP_KEY=tu_key
PUSHER_APP_SECRET=tu_secret
PUSHER_APP_CLUSTER=tu_cluster

# Opcional: Si usas un host personalizado
# PUSHER_HOST=
# PUSHER_PORT=443
# PUSHER_SCHEME=https
```

#### Paso 4: Configurar .env en Railway
Si estás usando Railway, agrega las mismas variables en:
1. Ve a tu proyecto en Railway
2. Settings > Variables
3. Agrega cada variable de Pusher

### 2. Verificar Google Maps API Key

Asegúrate de que tu `GOOGLE_MAPS_API_KEY` en `.env` tenga habilitadas estas APIs:
- ✅ Maps JavaScript API
- ✅ Geolocation API
- ✅ Places API (si usas autocompletado)

### 3. Configurar permisos de usuario

El módulo de tracking requiere el permiso `ver-dashboard`. Los usuarios que necesiten ver el mapa deben tener este permiso asignado.

## 📱 Uso del Sistema

### Para Administradores (ERP)

1. **Acceder al mapa:**
   - En el menú lateral, clic en "Tracking GPS"
   - O navega a: `/tracking/mapa`

2. **Funcionalidades:**
   - Ver todos los choferes activos en tiempo real
   - Ver qué pedido está entregando cada chofer
   - Ver velocidad y última actualización
   - Centrar el mapa en todos los choferes
   - Activar/desactivar capa de tráfico
   - Clic en un chofer para ver más detalles

### Para Choferes (App Móvil)

1. **Tracking automático:**
   - El GPS se activa automáticamente cuando hay un pedido "en ruta"
   - Se envía la ubicación cada 15 segundos
   - Aparece un indicador "GPS Activo" en la parte superior

2. **Permisos requeridos:**
   - La app solicitará permisos de ubicación la primera vez
   - Debe permitir "Siempre" o "Mientras usa la app"

3. **Consumo de batería:**
   - El intervalo de 15 segundos balancea precisión y batería
   - Para reducir consumo, puedes aumentar el intervalo en `useLocationTracking.ts`

## 🔌 Endpoints API

### Para la App Móvil (Chofer)
```
POST /api/courier/tracking/update
Headers: Authorization: Bearer {token}
Body: {
  latitude: number,
  longitude: number,
  speed: number | null,
  heading: number | null,
  accuracy: number | null,
  order_id: number | null
}
```

```
POST /api/courier/tracking/stop
Headers: Authorization: Bearer {token}
```

### Para el ERP (Admin)
```
GET /api/tracking/drivers
Headers: Authorization: Bearer {token}
Response: Lista de todos los choferes activos
```

```
GET /api/tracking/drivers/{driverId}
Headers: Authorization: Bearer {token}
Response: Última ubicación de un chofer específico
```

## 🧪 Testing

### Test Local (sin Pusher)
1. La vista del mapa cargará ubicaciones iniciales vía API
2. Sin Pusher configurado, no habrá actualizaciones en tiempo real
3. Puedes refrescar la página para ver nuevas ubicaciones

### Test con Pusher
1. Configura Pusher como se indicó arriba
2. Abre el mapa en el ERP
3. Abre la app móvil y inicia una entrega
4. Deberías ver el marcador del chofer moverse en tiempo real

### Debug Pusher
Para verificar que Pusher está funcionando:
1. Ve al Dashboard de Pusher
2. Pestaña "Debug Console"
3. Deberías ver eventos `location.updated` cuando un chofer se mueve

## 🎨 Personalización

### Cambiar intervalo de actualización
En `courier-app/app/hooks/useLocationTracking.ts`:
```typescript
interval: 15000, // Cambiar a 30000 para 30 segundos, etc.
```

### Cambiar estilo del mapa
En `resources/views/tracking/map.blade.php`, busca `styles:` en la inicialización del mapa.

### Cambiar color de marcadores
En `updateDriverMarker()`, modifica el objeto `icon`:
```javascript
fillColor: '#0d6efd', // Cambiar color aquí
```

## 📊 Datos Almacenados

La tabla `driver_locations` almacena:
- `user_id` - ID del chofer
- `order_id` - ID del pedido activo (nullable)
- `latitude` / `longitude` - Coordenadas GPS
- `speed` - Velocidad en km/h
- `heading` - Dirección en grados (0-360)
- `accuracy` - Precisión en metros
- `is_active` - Si es la ubicación actual
- `created_at` / `updated_at` - Timestamps

**Nota:** Solo la última ubicación de cada chofer tiene `is_active = true`. Las anteriores se mantienen como historial.

## 🔒 Seguridad

- ✅ Todas las rutas API requieren autenticación (Sanctum)
- ✅ Solo usuarios con `ver-dashboard` pueden ver el mapa
- ✅ Los choferes solo pueden actualizar su propia ubicación
- ✅ Las ubicaciones se envían por HTTPS
- ✅ Pusher usa TLS por defecto

## 🐛 Troubleshooting

### "No hay choferes activos"
- Verifica que la app móvil esté enviando ubicaciones
- Revisa los logs de Laravel: `php artisan log:tail`
- Verifica que el token de autenticación sea válido

### "Error GPS" en la app
- Verifica permisos de ubicación en el dispositivo
- Asegúrate de que el GPS esté activado
- Revisa la consola del navegador (si es web)

### Mapa no actualiza en tiempo real
- Verifica configuración de Pusher en `.env`
- Revisa Debug Console en Pusher Dashboard
- Abre la consola del navegador y busca errores de WebSocket

### "Mixed content" error
- Asegúrate de que tu sitio use HTTPS
- Verifica que `PUSHER_SCHEME=https` en `.env`

## 📈 Próximas Mejoras (Opcional)

- [ ] Historial de rutas completadas
- [ ] ETA (tiempo estimado de llegada) calculado
- [ ] Notificaciones push al cliente cuando el chofer está cerca
- [ ] Geofencing (alertas cuando el chofer sale de una zona)
- [ ] Heatmap de zonas más visitadas
- [ ] Reporte de kilometraje por chofer

## 💡 Alternativas a Pusher

Si prefieres no usar Pusher (servicio de pago después del plan gratuito):

### Opción 1: Laravel WebSockets (Gratis)
- Instala: `composer require beyondcode/laravel-websockets`
- Configura tu propio servidor WebSocket
- Más control, pero requiere configuración adicional

### Opción 2: Firebase Realtime Database
- La app escribe directamente a Firebase
- El ERP lee de Firebase
- Plan gratuito generoso

### Opción 3: Polling (Sin WebSockets)
- El mapa consulta `/api/tracking/drivers` cada X segundos
- Más simple, pero menos eficiente
- No es "verdadero" tiempo real

---

¿Necesitas ayuda? Revisa los logs o contacta al equipo de desarrollo.

