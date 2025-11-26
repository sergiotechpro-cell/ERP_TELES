# 📱 Guía de Compilación - App Chofer con Optimización de Rutas

## 🎯 Nuevas Funcionalidades

### ✨ Optimización de Rutas Inteligente

- **Optimización automática**: Genera una sola ruta para múltiples pedidos
- **Prioridad por orden**: Ordena los pedidos por fecha de asignación (más antiguos primero)
- **Vista previa**: Muestra la lista de pedidos antes de abrir Google Maps
- **Integración con Google Maps**: Abre directamente la ruta optimizada para navegación
- **UI mejorada**: Diseño moderno con gradientes y feedback visual

### 📋 Cómo Funciona

1. El chofer ve todos sus pedidos pendientes
2. Si tiene 2 o más pedidos con coordenadas válidas, aparece el botón de "Optimizar Rutas"
3. Al hacer clic, se muestra una confirmación con el orden de entregas por prioridad
4. La ruta se abre en Google Maps con optimización automática de waypoints
5. Google Maps calculará la ruta más eficiente respetando el orden de prioridad

---

## 🔧 Requisitos Previos

### Para Android
- ✅ **Node.js** v18 o superior
- ✅ **Android Studio** (incluye Android SDK y Java JDK)
- ✅ **Capacitor CLI** (se instalará automáticamente)

### Para iOS (macOS requerido)
- ✅ **Node.js** v18 o superior
- ✅ **Xcode** 14 o superior
- ✅ **CocoaPods** (`sudo gem install cocoapods`)
- ✅ **Capacitor CLI** (se instalará automáticamente)

---

## 🚀 Paso 1: Preparar el Proyecto

### 1.1 Navegar al directorio de la app

```bash
cd courier-app
```

### 1.2 Instalar dependencias

```bash
npm install
```

### 1.3 Actualizar Capacitor (opcional pero recomendado)

```bash
npm run cap:update
```

### 1.4 Configurar variables de entorno

Crea o edita el archivo `.env.local`:

```env
# URL de producción
NEXT_PUBLIC_API_URL=https://erpteles-production.up.railway.app/api

# API Key de Google Maps (para el mapa en la app)
NEXT_PUBLIC_GOOGLE_MAPS_API_KEY=tu_google_maps_api_key
```

**Para desarrollo local:**

```env
# URL local del servidor Laravel
NEXT_PUBLIC_API_URL=http://localhost:8000/api

# API Key de Google Maps
NEXT_PUBLIC_GOOGLE_MAPS_API_KEY=tu_google_maps_api_key
```

---

## 📦 Paso 2: Compilar la App Web

### 2.1 Build de Next.js

```bash
npm run build
```

Este comando genera el build estático en la carpeta `out/`.

---

## 🤖 Paso 3: Compilar para Android

### 3.1 Sincronizar con Capacitor

```bash
npm run cap:sync
```

O usar el comando combinado:

```bash
npm run cap:build
```

### 3.2 Opción A: Compilar desde línea de comandos (Recomendado)

#### APK de Debug (para pruebas)

```bash
npm run cap:build:android
```

El APK estará en: `android/app/build/outputs/apk/debug/app-debug.apk`

#### APK de Release (para producción)

```bash
npm run cap:build:android:release
```

**Nota**: Para release, necesitas configurar el keystore de firma (ver sección de firma abajo).

### 3.3 Opción B: Compilar desde Android Studio

```bash
# Abrir Android Studio
npm run cap:open:android
```

En Android Studio:
1. Espera a que Gradle termine de sincronizar
2. **Build → Build Bundle(s) / APK(s) → Build APK(s)**
3. Espera a que termine la compilación
4. Haz clic en "locate" para ver el APK generado

### 3.4 Instalar APK en dispositivo

#### Vía USB (ADB)

```bash
# Conecta tu dispositivo Android con depuración USB activada
adb install android/app/build/outputs/apk/debug/app-debug.apk
```

#### Vía archivo

1. Copia el APK a tu dispositivo
2. Abre el APK desde el explorador de archivos
3. Permite "Instalar apps de fuentes desconocidas"
4. Instala la app

---

## 🍎 Paso 4: Compilar para iOS (macOS requerido)

### 4.1 Sincronizar con Capacitor

```bash
npm run cap:sync
```

### 4.2 Instalar CocoaPods (si no lo has hecho)

```bash
sudo gem install cocoapods
```

### 4.3 Instalar dependencias de iOS

```bash
cd ios/App
pod install
cd ../..
```

### 4.4 Abrir Xcode

```bash
npm run cap:open:ios
```

### 4.5 Compilar en Xcode

1. Selecciona tu equipo de desarrollo en **Signing & Capabilities**
2. Selecciona un dispositivo o simulador
3. **Product → Build** (⌘B)
4. **Product → Run** (⌘R) para instalar en el dispositivo/simulador

---

## 🔐 Firma del APK para Producción (Android)

### Paso 1: Generar Keystore (solo una vez)

```bash
cd android
keytool -genkey -v -keystore teleserp-chofer-release.jks -keyalg RSA -keysize 2048 -validity 10000 -alias teleserp-chofer
```

**Guarda la información que ingreses** (contraseña, alias, etc.)

### Paso 2: Configurar Gradle

Crea el archivo `android/key.properties`:

```properties
storePassword=tu_contraseña_keystore
keyPassword=tu_contraseña_key
keyAlias=teleserp-chofer
storeFile=teleserp-chofer-release.jks
```

**⚠️ IMPORTANTE**: Añade `key.properties` al `.gitignore`

### Paso 3: Editar `android/app/build.gradle`

Después de `android {` añade:

```gradle
def keystorePropertiesFile = rootProject.file("key.properties")
def keystoreProperties = new Properties()
if (keystorePropertiesFile.exists()) {
    keystoreProperties.load(new FileInputStream(keystorePropertiesFile))
}
```

Dentro de `android {` añade:

```gradle
signingConfigs {
    release {
        if (keystorePropertiesFile.exists()) {
            keyAlias keystoreProperties['keyAlias']
            keyPassword keystoreProperties['keyPassword']
            storeFile file(keystoreProperties['storeFile'])
            storePassword keystoreProperties['storePassword']
        }
    }
}
buildTypes {
    release {
        signingConfig signingConfigs.release
        minifyEnabled false
        proguardFiles getDefaultProguardFile('proguard-android-optimize.txt'), 'proguard-rules.pro'
    }
}
```

### Paso 4: Generar APK Firmado

```bash
npm run cap:build:android:release
```

El APK firmado estará en: `android/app/build/outputs/apk/release/app-release.apk`

---

## 🧪 Probar la App

### En Emulador Android

```bash
npm run cap:run:android
```

### En Dispositivo Físico

1. Activa "Depuración USB" en tu dispositivo
2. Conecta vía USB
3. Ejecuta: `npm run cap:run:android`

### En Navegador (desarrollo web)

```bash
npm run dev
```

Abre `http://localhost:3000`

---

## 📝 Comandos Útiles

### Actualizar el Código Web en la App

```bash
# Rebuild y sincronizar
npm run cap:build
```

### Ver Logs de la App

```bash
# Android
npx cap run android --livereload --external

# iOS
npx cap run ios --livereload --external
```

### Limpiar Build

```bash
# Android
cd android
./gradlew clean
cd ..

# Next.js
rm -rf .next out
npm run build
```

---

## 🔧 Solución de Problemas

### Error: "ANDROID_HOME not set"

```bash
# Windows (PowerShell)
$env:ANDROID_HOME = "C:\Users\TU_USUARIO\AppData\Local\Android\Sdk"

# macOS/Linux
export ANDROID_HOME=$HOME/Library/Android/sdk
export PATH=$PATH:$ANDROID_HOME/emulator
export PATH=$PATH:$ANDROID_HOME/tools
export PATH=$PATH:$ANDROID_HOME/tools/bin
export PATH=$PATH:$ANDROID_HOME/platform-tools
```

### Error: "Gradle sync failed"

1. Abre Android Studio
2. **File → Invalidate Caches / Restart**
3. Espera a que re-sincronice

### La app no se conecta a la API

1. Verifica que `.env.local` tenga la URL correcta
2. Rebuil: `npm run cap:build`
3. Revisa los logs con Chrome DevTools: `chrome://inspect`

---

## 📚 Recursos Adicionales

- [Documentación de Capacitor](https://capacitorjs.com/docs)
- [Guía de Android Studio](https://developer.android.com/studio)
- [Guía de Xcode](https://developer.apple.com/xcode/)
- [Google Play Console](https://play.google.com/console)
- [Apple Developer](https://developer.apple.com/)

---

## ✅ Checklist de Compilación

### Antes de Compilar
- [ ] Actualizar `.env.local` con URLs de producción
- [ ] Verificar que todas las dependencias estén instaladas
- [ ] Probar la app en navegador (`npm run dev`)

### Para APK de Debug
- [ ] `npm run build`
- [ ] `npm run cap:sync`
- [ ] `npm run cap:build:android`
- [ ] Probar el APK en dispositivo

### Para APK de Release
- [ ] Generar keystore (solo la primera vez)
- [ ] Configurar `key.properties`
- [ ] `npm run cap:build:android:release`
- [ ] Probar el APK firmado
- [ ] Subir a Google Play Console

---

## 🎉 ¡Listo!

Tu app del chofer ahora incluye:
- ✅ Optimización inteligente de rutas
- ✅ Ordenamiento por prioridad
- ✅ Integración con Google Maps
- ✅ UI moderna y responsive
- ✅ Compatible con Android e iOS

**¡Felices entregas! 🚚📦**

