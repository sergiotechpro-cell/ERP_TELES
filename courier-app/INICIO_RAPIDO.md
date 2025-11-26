# 🚀 Inicio Rápido - Compilar APK en 5 Minutos

## ⚡ Pasos Rápidos

### 1️⃣ Instalar Dependencias (solo la primera vez)

```bash
cd courier-app
npm install
```

### 2️⃣ Configurar Variables de Entorno

Crea `.env.local`:

```env
NEXT_PUBLIC_API_URL=https://erpteles-production.up.railway.app/api
NEXT_PUBLIC_GOOGLE_MAPS_API_KEY=tu_api_key
```

### 3️⃣ Compilar APK de Debug

**Opción A: Script Automático (Windows)**

```bash
.\compilar-apk.bat
```

Selecciona opción `1` para APK de debug.

**Opción B: Comandos Manuales**

```bash
npm run build
npx cap sync
cd android
.\gradlew.bat assembleDebug
cd ..
```

### 4️⃣ Instalar en Dispositivo

```bash
adb install -r android\app\build\outputs\apk\debug\app-debug.apk
```

---

## 📱 Ubicación del APK

El APK estará en:

```
android\app\build\outputs\apk\debug\app-debug.apk
```

---

## 🎯 Probar la Optimización de Rutas

1. Abre la app en tu dispositivo
2. Inicia sesión como chofer
3. Asegúrate de tener 2 o más pedidos asignados
4. Verás el botón "Optimizar Rutas" 
5. Haz clic y confirma
6. La ruta se abrirá en Google Maps

---

## ⚠️ Requisitos Previos

- ✅ **Node.js** v18+ instalado
- ✅ **Android Studio** instalado (incluye SDK y JDK)
- ✅ Dispositivo Android con **depuración USB activada**

---

## 🆘 Solución Rápida de Problemas

### Error: "gradlew not found"

```bash
# Instala gradlew si falta
cd android
npm run cap:sync
cd ..
```

### Error: "ANDROID_HOME not set"

```bash
# Windows (PowerShell)
$env:ANDROID_HOME = "C:\Users\TU_USUARIO\AppData\Local\Android\Sdk"
```

### Error de conexión a API

Verifica que `.env.local` tenga la URL correcta y rebuil:

```bash
npm run cap:build
```

---

## 📚 Más Información

- **Guía completa**: Ver `COMPILAR_APP.md`
- **Documentación Capacitor**: [capacitorjs.com](https://capacitorjs.com)
- **Guías de compilación**: Ver archivos `CAPACITOR_GUIDE.md` y `README_PRODUCCION_TIENDAS.md`

---

## ✅ Checklist Rápido

- [ ] Node.js instalado
- [ ] Android Studio instalado
- [ ] `npm install` ejecutado
- [ ] `.env.local` creado
- [ ] APK compilado exitosamente
- [ ] APK instalado en dispositivo
- [ ] App funcionando correctamente
- [ ] Optimización de rutas probada

---

¡Listo! Tu app del chofer con optimización de rutas está funcionando 🎉

