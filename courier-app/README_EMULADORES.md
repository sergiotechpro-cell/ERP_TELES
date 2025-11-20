# 📱 Guía para Ejecutar en Emuladores - Android e iOS

Esta guía te ayudará a ejecutar la app **Teleserp Chofer** en emuladores de Android e iOS para desarrollo y pruebas.

## ✅ Requisitos Previos

### Para Android
- ✅ **Android Studio** instalado ([Descargar](https://developer.android.com/studio))
- ✅ **Android SDK** (se instala con Android Studio)
- ✅ **Java JDK** (incluido con Android Studio)
- ✅ **AVD (Android Virtual Device)** configurado

### Para iOS (solo macOS)
- ✅ **macOS** (requerido para iOS)
- ✅ **Xcode** instalado desde App Store
- ✅ **Xcode Command Line Tools**: `xcode-select --install`
- ✅ **CocoaPods**: `sudo gem install cocoapods`

---

## 🤖 Android - Emulador

### 🚀 Comandos Rápidos (Directo en Emulador - Sin Android Studio)

```bash
# 1. Iniciar el emulador (en una terminal)
emulator -avd Pixel_9

# 2. Esperar a que el emulador inicie completamente (30-60 segundos)
# Verás la pantalla de inicio de Android cuando esté listo

# 3. Verificar que el emulador esté conectado
adb devices
# Deberías ver: emulator-5554   device

# 4. Ir al directorio del proyecto
cd courier-app

# 5. Build y sincronizar con Capacitor
npm run cap:build

# 6. Compilar e instalar directamente en el emulador
cd android
./gradlew installDebug

# 7. (Opcional) Iniciar la app automáticamente
adb shell am start -n com.teleserp.courier/.MainActivity
```

**Todo en un solo comando (después de iniciar el emulador):**
```bash
cd courier-app && npm run cap:build && cd android && ./gradlew installDebug && adb shell am start -n com.teleserp.courier/.MainActivity
```

### 📋 Pasos Detallados

### Paso 1: Verificar que Android Studio esté instalado

```bash
# Verificar instalación
where android-studio  # Windows
which android-studio  # macOS/Linux
```

### Paso 2: Listar AVDs Disponibles

```bash
# Listar todos los AVDs configurados
emulator -list-avds
```

**Salida esperada:**
```
Pixel_9
Pixel_5_API_33
Nexus_6_API_30
```

### Paso 3: Iniciar el Emulador

```bash
# Iniciar un AVD específico (reemplaza "Pixel_9" con el nombre de tu AVD)
emulator -avd Pixel_9

# O iniciar en background (Windows PowerShell)
Start-Process emulator -ArgumentList "-avd", "Pixel_9"

# O iniciar en background (macOS/Linux)
emulator -avd Pixel_9 &
```

**Nota**: El emulador puede tardar 30-60 segundos en iniciar completamente. Espera hasta ver la pantalla de inicio del Android.

### Paso 4: Verificar que el Emulador Esté Conectado

```bash
# Ver dispositivos conectados
adb devices

# Deberías ver algo como:
# List of devices attached
# emulator-5554   device
```

Si no aparece, espera unos segundos más y vuelve a ejecutar `adb devices`.

### Paso 5: Compilar e Instalar en el Emulador (Sin Android Studio)

```bash
# 1. Asegúrate de estar en el directorio del proyecto
cd courier-app

# 2. Build y sincronizar con Capacitor
npm run cap:build

# 3. Ir al directorio de Android
cd android

# 4. Compilar e instalar directamente en el emulador
./gradlew installDebug

# El APK se compilará e instalará automáticamente en el emulador
# Verás mensajes como: "BUILD SUCCESSFUL" cuando termine
```

### Paso 6: Iniciar la App (Opcional)

```bash
# Iniciar la app automáticamente después de instalar
adb shell am start -n com.teleserp.courier/.MainActivity

# O simplemente busca "Teleserp Chofer" en el emulador y ábrela manualmente
```

### Paso 7: Verificar que Funciona

- La app debería aparecer instalada en el emulador
- Puedes abrirla desde el menú de apps o con el comando anterior
- Deberías ver la pantalla de login
- Para recargar cambios: vuelve a ejecutar `npm run cap:build && cd android && ./gradlew installDebug`

### 🔄 Alternativa: Usar Android Studio (Opcional)

Si prefieres usar Android Studio:

```bash
# 1. Build y sincronizar
npm run cap:build

# 2. Abrir el proyecto en Android Studio
npm run cap:open:android

# 3. En Android Studio: Click en Run ▶️ (o Shift + F10)
```

### 🔄 Flujo Rápido para Desarrollo

```bash
# Terminal 1: Iniciar emulador (dejar corriendo)
emulator -avd Pixel_9

# Terminal 2: Desarrollo (después de que el emulador inicie)
cd courier-app
npm run cap:build
cd android
./gradlew installDebug
adb shell am start -n com.teleserp.courier/.MainActivity
```

**Script rápido (todo en uno):**
```bash
# Después de iniciar el emulador, ejecuta esto:
cd courier-app && npm run cap:build && cd android && ./gradlew installDebug && adb shell am start -n com.teleserp.courier/.MainActivity
```

### Comandos Útiles para Android

```bash
# Ver dispositivos/emuladores conectados
adb devices

# Reiniciar el servidor ADB (si hay problemas)
adb kill-server
adb start-server

# Instalar la app directamente (sin Android Studio) - MÉTODO RECOMENDADO
cd courier-app
npm run cap:build
cd android
./gradlew installDebug

# Iniciar la app automáticamente
adb shell am start -n com.teleserp.courier/.MainActivity

# O todo en un comando:
cd courier-app && npm run cap:build && cd android && ./gradlew installDebug && adb shell am start -n com.teleserp.courier/.MainActivity

# Ver logs en tiempo real
adb logcat | grep -i capacitor

# Detener el emulador
adb -s emulator-5554 emu kill

# Reiniciar el emulador
adb -s emulator-5554 reboot
```

### ⚡ Comando Todo-en-Uno (Script Rápido)

Puedes crear un script para automatizar todo el proceso:

**Windows (PowerShell) - `run-emulator.ps1`:**
```powershell
# Iniciar emulador
Start-Process emulator -ArgumentList "-avd", "Pixel_9"

# Esperar a que inicie
Start-Sleep -Seconds 45

# Verificar conexión
adb devices

# Build e instalar directamente
cd courier-app
npm run cap:build
cd android
./gradlew installDebug
adb shell am start -n com.teleserp.courier/.MainActivity
```

**macOS/Linux - `run-emulator.sh`:**
```bash
#!/bin/bash
# Iniciar emulador en background
emulator -avd Pixel_9 &

# Esperar a que inicie
sleep 45

# Verificar conexión
adb devices

# Build e instalar directamente
cd courier-app
npm run cap:build
cd android
./gradlew installDebug
adb shell am start -n com.teleserp.courier/.MainActivity
```

---

## 🍎 iOS - Simulador (solo macOS)

### Paso 1: Verificar que Xcode esté instalado

```bash
# Verificar instalación
xcode-select -p

# Si no está instalado, instalar desde App Store
# Luego instalar Command Line Tools:
xcode-select --install
```

### Paso 2: Instalar CocoaPods (si no está instalado)

```bash
# Instalar CocoaPods
sudo gem install cocoapods

# Verificar instalación
pod --version
```

### Paso 3: Instalar Dependencias de iOS

```bash
# 1. Ir al directorio de iOS
cd courier-app/ios/App

# 2. Instalar pods
pod install

# 3. Volver al directorio raíz
cd ../..
```

### Paso 4: Sincronizar y Abrir el Proyecto

```bash
# 1. Build y sincronizar con Capacitor
npm run cap:build

# 2. Abrir el proyecto en Xcode
npm run cap:open:ios
```

### Paso 5: Ejecutar en el Simulador desde Xcode

1. **Espera a que Xcode abra el proyecto**
2. **Selecciona un simulador** en la barra superior:
   - Haz clic en el dispositivo (ej: "iPhone 15 Pro")
   - Selecciona el simulador que quieres usar
3. **Haz clic en el botón Run ▶️** (o presiona `Cmd + R`)
4. **Espera a que compile y se instale** en el simulador

### Paso 6: Verificar que Funciona

- El simulador debería abrirse automáticamente
- La app debería instalarse y ejecutarse
- Deberías ver la pantalla de login
- Puedes hacer cambios y recargar con `Cmd + R`

### Comandos Útiles para iOS

```bash
# Listar simuladores disponibles
xcrun simctl list devices

# Iniciar un simulador específico
xcrun simctl boot "iPhone 15 Pro"

# Abrir el simulador (si no está abierto)
open -a Simulator

# Instalar la app directamente (sin Xcode)
npm run cap:build
cd ios/App
xcodebuild -workspace App.xcworkspace \
           -scheme App \
           -configuration Debug \
           -destination 'platform=iOS Simulator,name=iPhone 15 Pro' \
           build

# Ver logs del simulador
xcrun simctl spawn booted log stream --predicate 'processImagePath contains "App"'
```

---

## 🔄 Flujo de Desarrollo Completo

### Cada vez que hagas cambios en el código web:

```bash
# 1. Hacer cambios en app/, components/, lib/, etc.

# 2. Rebuild y sincronizar
npm run cap:build

# 3. Recargar en el emulador/simulador:
#    - Android: Ctrl + R (o Cmd + R)
#    - iOS: Cmd + R
```

### Si cambias configuración nativa (AndroidManifest.xml, Info.plist, etc.):

```bash
# 1. Hacer cambios en android/ o ios/

# 2. Rebuild completo
npm run cap:build

# 3. Reejecutar desde Android Studio / Xcode
```

---

## 🐛 Solución de Problemas

### Android

**Problema: "No devices found"**
```bash
# Verificar que el emulador esté corriendo
adb devices

# Si no aparece, reiniciar ADB
adb kill-server
adb start-server
```

**Problema: "Gradle sync failed"**
```bash
# Limpiar y reconstruir
cd android
./gradlew clean
cd ..
npm run cap:build
```

**Problema: "App no se instala"**
```bash
# Desinstalar versión anterior
adb uninstall com.teleserp.courier

# Reinstalar
cd android
./gradlew installDebug
```

### iOS

**Problema: "CocoaPods not found"**
```bash
# Instalar CocoaPods
sudo gem install cocoapods

# Actualizar repositorio
pod repo update
```

**Problema: "Build failed"**
```bash
# Limpiar build
cd ios/App
rm -rf build
pod deintegrate
pod install
cd ../..

# Rebuild
npm run cap:build
```

**Problema: "Simulator no inicia"**
```bash
# Reiniciar todos los simuladores
xcrun simctl shutdown all
xcrun simctl erase all

# Iniciar uno nuevo
xcrun simctl boot "iPhone 15 Pro"
open -a Simulator
```

---

## 📝 Configuración de Variables de Entorno

Para que la app se conecte a tu API, asegúrate de tener configurado `.env.local`:

```env
NEXT_PUBLIC_API_URL=https://erpteles-production.up.railway.app/api
NEXT_PUBLIC_GOOGLE_MAPS_API_KEY=tu_api_key_aqui
```

**Nota**: Después de cambiar variables de entorno, ejecuta `npm run cap:build` para sincronizar.

---

## 🚀 Próximos Pasos

1. ✅ **Probar la app en el emulador**
2. ✅ **Verificar que el login funcione**
3. ✅ **Probar las funcionalidades principales**
4. ✅ **Configurar notificaciones push** (opcional)
5. ✅ **Agregar más plugins de Capacitor** según necesidad

---

## 📚 Recursos Adicionales

- [Documentación de Capacitor](https://capacitorjs.com/docs)
- [Guía de Android Studio](https://developer.android.com/studio)
- [Guía de Xcode](https://developer.apple.com/xcode/)
- [Guía de Emuladores Android](https://developer.android.com/studio/run/emulator)
- [Guía de Simuladores iOS](https://developer.apple.com/documentation/xcode/running-your-app-in-the-simulator-or-on-a-device)

---

## 💡 Tips

- **Hot Reload**: Los cambios en código web se pueden recargar con `Ctrl/Cmd + R` sin recompilar
- **Debugging**: Usa Chrome DevTools para Android (`chrome://inspect`) y Safari Web Inspector para iOS
- **Performance**: Los emuladores pueden ser lentos, considera usar un dispositivo físico para pruebas finales
- **Red**: Configura la red del emulador para que pueda acceder a tu API (verifica firewall/proxy)

