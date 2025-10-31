# Instrucciones: Configuración de Bodegas y Dirección de Origen

## Por qué marca CDMX por defecto

El sistema marca desde **Ciudad de México (CDMX)** porque usa coordenadas por defecto cuando:
- No hay bodegas configuradas en la base de datos
- Las bodegas no tienen coordenadas (lat/lng) guardadas
- No hay variables de entorno configuradas

**Coordenadas por defecto:** `19.432608, -99.133209` (Centro de CDMX)

## Cómo configurar tu bodega principal

### Opción 1: Crear/Editar bodega con coordenadas (Recomendado)

1. Ve a **Inventario** → Click en **"Nueva bodega"**
2. Ingresa el nombre de tu bodega
3. En el campo **"Dirección"**:
   - Escribe la dirección completa
   - Selecciona una opción del autocompletado de Google
   - Las coordenadas se capturarán automáticamente
4. Guarda la bodega

**Importante:** El sistema usará automáticamente la **primera bodega con coordenadas** como punto de origen para calcular rutas.

### Opción 2: Configurar en variables de entorno

Si prefieres usar variables de entorno en lugar de bodegas, agrega en tu archivo `.env`:

```env
# Coordenadas de la bodega origen (opcional)
WAREHOUSE_ORIGIN_LAT=20.659698
WAREHOUSE_ORIGIN_LNG=-103.349609
WAREHOUSE_ORIGIN_ADDRESS=Tu dirección completa aquí
```

**Nota:** Las coordenadas de bodegas en la base de datos tienen **prioridad** sobre las variables de entorno.

## Prioridad de configuración

El sistema usa las coordenadas en este orden:

1. **Primera bodega con coordenadas** en la base de datos (si existe)
2. Variables de entorno `WAREHOUSE_ORIGIN_LAT` y `WAREHOUSE_ORIGIN_LNG`
3. Valores por defecto de CDMX (última opción)

## Verificar bodegas actuales

Puedes verificar qué bodegas tienes y si tienen coordenadas ejecutando:

```bash
php artisan tinker
```

Luego:
```php
App\Models\Warehouse::all(['id', 'nombre', 'direccion', 'lat', 'lng']);
```

## Actualizar bodega existente

Actualmente no hay una vista de edición de bodegas. Puedes:
1. Editar directamente en la base de datos, o
2. Crear una nueva bodega con las coordenadas correctas

¿Quieres que agregue una funcionalidad de edición de bodegas?

