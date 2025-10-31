# Configuración de Google Maps API

## Variables de Entorno Requeridas

Para que el módulo de Rutas y Entregas funcione correctamente, necesitas configurar las siguientes variables en tu archivo `.env`:

```env
# API Key de Google Maps (para visualización en el navegador)
GOOGLE_MAPS_API_KEY=tu_api_key_aqui

# API Key de Google Routes (para cálculo de rutas, opcional - puede ser la misma)
GOOGLE_ROUTES_API_KEY=tu_api_key_aqui

# Coordenadas de la bodega/almacén origen (opcional, valores por defecto: CDMX)
WAREHOUSE_ORIGIN_LAT=19.432608
WAREHOUSE_ORIGIN_LNG=-99.133209
```

## Pasos para Obtener la API Key

1. Ve a [Google Cloud Console](https://console.cloud.google.com/)
2. Crea un nuevo proyecto o selecciona uno existente
3. Habilita las siguientes APIs:
   - **Maps JavaScript API** (para mostrar mapas en el navegador)
   - **Directions API** (para calcular rutas)
   - **Geocoding API** (opcional, para convertir direcciones en coordenadas)
   - **Routes API** (opcional, versión nueva)

4. Ve a "Credenciales" y crea una nueva API Key
5. Restringe la API Key (recomendado):
   - Por dominio HTTP (para producción)
   - Por IP (para desarrollo)
6. Copia la API Key y agrega ambas variables en tu `.env`

## Notas Importantes

- Puedes usar la misma API Key para ambas variables (`GOOGLE_MAPS_API_KEY` y `GOOGLE_ROUTES_API_KEY`)
- Si solo configuras `GOOGLE_MAPS_API_KEY`, se usará esa para ambos propósitos
- La geocodificación automática solo funciona si habilitas la Geocoding API
- Asegúrate de habilitar facturación en Google Cloud para usar las APIs

## Módulos del Sistema

### ✅ Módulos Funcionales

1. **Dashboard** - Estadísticas generales
2. **Inventario** - Gestión de productos y almacenes
   - Crear productos con múltiples almacenes
   - Agregar unidades a productos existentes
   - Control de números de serie
3. **Pedidos** - Gestión de pedidos (sin requerir clientes)
4. **Punto de Venta (POS)** - Ventas con efectivo y transferencia
5. **Rutas y Entregas** - Visualización de rutas con Google Maps
6. **Finanzas** - Cierres diarios y gestión de pagos
7. **Empleados** - Gestión de empleados
8. **Calendario** - Programación de entregas

### ❌ Módulos Eliminados

- **Clientes** (eliminado)
- **Traspasos** (eliminado)
- **Garantías** (eliminado)

