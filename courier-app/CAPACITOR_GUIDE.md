# 📱 Guía de Capacitor - Generación de APK e IPA

## ✅ Estado Actual

- ✅ Capacitor instalado y configurado
- ✅ Next.js configurado para export estático
- ✅ Proyectos Android e iOS generados
- ✅ Plugins básicos instalados (@capacitor/app, @capacitor/haptics, @capacitor/keyboard, @capacitor/status-bar)

## 📂 Estructura de Proyectos

```
courier-app/
├── android/          # Proyecto Android (Gradle)
├── ios/              # Proyecto iOS (Xcode)
├── out/              # Build estático de Next.js (se copia a las apps nativas)
└── capacitor.config.ts
```

## 🔄 Flujo de Trabajo

### 1. Hacer cambios en el código web (Next.js)

```bash
# Editar archivos en app/, components/, lib/, etc.
```

### 2. Rebuild y sincronizar

```bash
# Opción 1: Comando combinado (recomendado)
npm run cap:build

# Opción 2: Pasos manuales
npm run build          # Construye Next.js
npm run cap:sync       # Sincroniza con Android/iOS
```

## 🤖 Generar APK (Android)

### Requisitos Previos

1. **Android Studio** instalado
2. **Java JDK** (incluido con Android Studio)
3. **Android SDK** (se instala con Android Studio)

### Pasos para Generar APK

#### Opción A: Desde Android Studio (Recomendado para desarrollo)

```bash
# 1. Sincronizar cambios
npm run cap:build

# 2. Abrir Android Studio
npm run cap:open:android

# 3. En Android Studio:
#    - Build > Build Bundle(s) / APK(s) > Build APK(s)
#    - O Build > Generate Signed Bundle / APK
```

#### Opción B: Desde línea de comandos (Para CI/CD)

```bash
# 1. Sincronizar cambios
npm run cap:build

# 2. Generar APK de debug
cd android
./gradlew assembleDebug
# El APK estará en: android/app/build/outputs/apk/debug/app-debug.apk

# 3. Generar APK de release (requiere firma)
./gradlew assembleRelease
# El APK estará en: android/app/build/outputs/apk/release/app-release-unsigned.apk
```

### Firma del APK para Producción

Para publicar en Google Play Store, necesitas:

1. **Generar un keystore**:
```bash
keytool -genkey -v -keystore teleserp-release-key.jks -keyalg RSA -keysize 2048 -validity 10000 -alias teleserp
```

2. **Configurar la firma en `android/app/build.gradle`**:
```gradle
android {
    ...
    signingConfigs {
        release {
            storeFile file('path/to/teleserp-release-key.jks')
            storePassword 'tu-password'
            keyAlias 'teleserp'
            keyPassword 'tu-password'
        }
    }
    buildTypes {
        release {
            signingConfig signingConfigs.release
            ...
        }
    }
}
```

3. **Generar APK firmado**:
```bash
cd android
./gradlew assembleRelease
```

### Generar AAB (Android App Bundle) para Google Play

```bash
cd android
./gradlew bundleRelease
# El AAB estará en: android/app/build/outputs/bundle/release/app-release.aab
```

## 🍎 Generar IPA (iOS)

### Requisitos Previos

⚠️ **IMPORTANTE**: iOS requiere **macOS** con:
1. **Xcode** instalado (desde App Store)
2. **CocoaPods** instalado: `sudo gem install cocoapods`
3. **Cuenta de desarrollador de Apple** (para firmar)

### Pasos para Generar IPA

#### Opción A: Desde Xcode (Recomendado)

```bash
# 1. Sincronizar cambios
npm run cap:build

# 2. Instalar dependencias de CocoaPods
cd ios/App
pod install
cd ../..

# 3. Abrir Xcode
npm run cap:open:ios

# 4. En Xcode:
#    - Seleccionar dispositivo o simulador
#    - Product > Archive (para distribución)
#    - O Product > Run (para probar)
```

#### Opción B: Desde línea de comandos (CI/CD en macOS)

```bash
# 1. Sincronizar cambios
npm run cap:build

# 2. Instalar pods
cd ios/App
pod install
cd ../..

# 3. Build para dispositivo
xcodebuild -workspace ios/App/App.xcworkspace \
           -scheme App \
           -configuration Release \
           -archivePath build/App.xcarchive \
           archive

# 4. Exportar IPA
xcodebuild -exportArchive \
           -archivePath build/App.xcarchive \
           -exportPath build \
           -exportOptionsPlist ExportOptions.plist
```

## 📝 Comandos Útiles

```bash
# Build y sincronizar
npm run cap:build

# Solo sincronizar (sin rebuild)
npm run cap:sync

# Abrir proyectos nativos
npm run cap:open:android
npm run cap:open:ios

# Verificar configuración
npx cap doctor
```

## 🔧 Configuración de Capacitor

El archivo `capacitor.config.ts` contiene:

```typescript
{
  appId: 'com.teleserp.courier',
  appName: 'Teleserp Chofer',
  webDir: 'out',
  server: {
    androidScheme: 'https',
    iosScheme: 'https',
  }
}
```

### Personalizar la App

- **appId**: Cambiar en `capacitor.config.ts` y luego ejecutar `npm run cap:sync`
- **appName**: Cambiar en `capacitor.config.ts`
- **Iconos**: Reemplazar en `android/app/src/main/res/` y `ios/App/App/Assets.xcassets/AppIcon.appiconset/`
- **Splash Screen**: Configurar en `capacitor.config.ts` o reemplazar imágenes en las carpetas `res/`

## 🚀 Próximos Pasos

1. **Personalizar iconos y splash screen**
2. **Configurar variables de entorno** para producción
3. **Agregar más plugins de Capacitor** según necesidad:
   - `@capacitor/camera` - Para tomar fotos
   - `@capacitor/geolocation` - Para ubicación GPS
   - `@capacitor/push-notifications` - Para notificaciones push
   - `@capacitor/filesystem` - Para acceso a archivos

4. **Configurar CI/CD** para builds automáticos:
   - GitHub Actions
   - Bitrise
   - Codemagic
   - App Center

## 📚 Recursos

- [Documentación de Capacitor](https://capacitorjs.com/docs)
- [Guía de Android](https://capacitorjs.com/docs/android)
- [Guía de iOS](https://capacitorjs.com/docs/ios)
- [Workflow de desarrollo](https://capacitorjs.com/docs/basics/workflow)

