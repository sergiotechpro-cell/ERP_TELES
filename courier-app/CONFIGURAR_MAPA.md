# 🗺️ Configurar Google Maps en la App

## ✅ Estado Actual

La app **funciona perfectamente** sin API Key. Solo muestra un botón para abrir Google Maps directamente, que es más que suficiente para los choferes.

## 📍 Opción 1: Usar sin API Key (Recomendado para empezar)

**Ya está funcionando así ahora.** Los choferes pueden:
- Ver la ubicación en el botón "Ver en Google Maps"
- Usar el botón "Iniciar Navegación" que abre Google Maps directamente

**No necesitas hacer nada.** ✅

## 🗺️ Opción 2: Ver el mapa integrado (Opcional)

Si quieres ver el mapa directamente en la app, configura la API Key:

### 1. Obtener API Key de Google Maps

1. Ve a [Google Cloud Console](https://console.cloud.google.com/)
2. Crea un proyecto o usa uno existente
3. Habilita **Maps JavaScript API**
4. Ve a "Credenciales" → "Crear credenciales" → "API Key"
5. Copia tu API Key

### 2. Configurar en la app

1. Abre el archivo `courier-app/.env.local` (o créalo si no existe)
2. Agrega:
   ```env
   NEXT_PUBLIC_GOOGLE_MAPS_API_KEY=tu_api_key_aqui
   ```
3. Reinicia el servidor Next.js:
   ```bash
   # Detén el servidor (Ctrl+C) y vuelve a ejecutar:
   npm run dev
   ```

### 3. ¡Listo!

Ahora verás el mapa integrado directamente en la app. 🎉

## 💡 Recomendación

Para producción, **es mejor usar el botón de navegación** (sin API Key) porque:
- ✅ No tienes costo adicional
- ✅ Abre directamente la app de Google Maps del móvil
- ✅ Mejor experiencia para el chofer
- ✅ No necesitas configurar nada

La API Key solo es útil si quieres ver el mapa dentro de la app (visualización).

## 🔍 Verificar que funciona

1. Sin API Key: Verás un botón azul "Ver en Google Maps" ✅ (actual)
2. Con API Key: Verás el mapa interactivo + el botón de navegación 🗺️

**Ambas opciones funcionan perfectamente para los choferes!**

