# 🚀 Guía para Subir APK e IPA a las Tiendas - Producción

Esta guía te ayudará a publicar la app **Teleserp Chofer** en **Google Play Store** (Android) y **Apple App Store** (iOS).

---

## 📋 Tabla de Contenidos

1. [Requisitos Previos](#requisitos-previos)
2. [Preparación General](#preparación-general)
3. [Google Play Store (Android)](#google-play-store-android)
4. [Apple App Store (iOS)](#apple-app-store-ios)
5. [Checklist Final](#checklist-final)
6. [Solución de Problemas](#solución-de-problemas)

---

## ✅ Requisitos Previos

### Para Google Play Store
- ✅ **Cuenta de Desarrollador de Google Play** ($25 USD, pago único)
- ✅ **Android Studio** instalado
- ✅ **Java JDK** instalado
- ✅ **Keystore** para firmar la app (se genera una vez)

### Para Apple App Store
- ✅ **Cuenta de Desarrollador de Apple** ($99 USD/año)
- ✅ **macOS** (requerido para compilar iOS)
- ✅ **Xcode** instalado
- ✅ **Certificados de distribución** (se generan en Apple Developer)

---

## 🔧 Preparación General

### 1. Actualizar Información de la App

Edita `capacitor.config.ts`:

```typescript
const config: CapacitorConfig = {
  appId: 'com.teleserp.courier',        // ⚠️ IMPORTANTE: No cambiar después de publicar
  appName: 'Teleserp Chofer',          // Nombre visible en las tiendas
  webDir: 'out',
  // ... resto de configuración
};
```

### 2. Configurar Variables de Entorno de Producción

Asegúrate de que `.env.local` tenga las URLs de producción:

```env
NEXT_PUBLIC_API_URL=https://erpteles-production.up.railway.app/api
NEXT_PUBLIC_GOOGLE_MAPS_API_KEY=tu_api_key_produccion
```

### 3. Build Final

```bash
# Build y sincronizar
npm run cap:build

# Verificar que el build sea exitoso
npm run build
```

### 4. Personalizar Iconos y Splash Screen

- **Android**: `android/app/src/main/res/`
- **iOS**: `ios/App/App/Assets.xcassets/AppIcon.appiconset/`

---

## 🤖 Google Play Store (Android)

### Paso 1: Crear Cuenta de Desarrollador

1. Ve a [Google Play Console](https://play.google.com/console)
2. Crea una cuenta o inicia sesión
3. Paga la tarifa única de $25 USD
4. Completa el perfil de desarrollador

### Paso 2: Generar Keystore para Firma

**⚠️ IMPORTANTE**: Guarda este keystore de forma segura. Si lo pierdes, no podrás actualizar tu app.

```bash
# Generar keystore (solo la primera vez)
keytool -genkey -v -keystore teleserp-release-key.jks \
        -keyalg RSA -keysize 2048 -validity 10000 \
        -alias teleserp

# Te pedirá:
# - Contraseña del keystore (GUÁRDALA BIEN)
# - Información personal (nombre, organización, etc.)
# - Contraseña de la clave (puede ser la misma)
```

**Ubicación recomendada**: Crea una carpeta `keystore/` en la raíz del proyecto y guárdalo ahí.

### Paso 3: Configurar Firma en Android

Edita `android/app/build.gradle`:

```gradle
android {
    ...
    
    signingConfigs {
        release {
            storeFile file('../keystore/teleserp-release-key.jks')
            storePassword System.getenv("KEYSTORE_PASSWORD") ?: 'tu-password-aqui'
            keyAlias 'teleserp'
            keyPassword System.getenv("KEY_PASSWORD") ?: 'tu-password-aqui'
        }
    }
    
    buildTypes {
        release {
            signingConfig signingConfigs.release
            minifyEnabled true
            shrinkResources true
            proguardFiles getDefaultProguardFile('proguard-android-optimize.txt'), 'proguard-rules.pro'
        }
    }
}
```

**⚠️ SEGURIDAD**: Para producción, usa variables de entorno en lugar de hardcodear las contraseñas:

```bash
# En Windows (PowerShell)
$env:KEYSTORE_PASSWORD="tu-password"
$env:KEY_PASSWORD="tu-password"

# En macOS/Linux
export KEYSTORE_PASSWORD="tu-password"
export KEY_PASSWORD="tu-password"
```

### Paso 4: Generar AAB (Android App Bundle)

**Recomendado**: Google Play prefiere AAB sobre APK.

```bash
# 1. Ir al directorio de Android
cd android

# 2. Generar AAB firmado
./gradlew bundleRelease

# El AAB estará en:
# android/app/build/outputs/bundle/release/app-release.aab
```

**Alternativa - Generar APK** (si prefieres APK):

```bash
cd android
./gradlew assembleRelease

# El APK estará en:
# android/app/build/outputs/apk/release/app-release.apk
```

### Paso 5: Subir a Google Play Console

1. **Inicia sesión** en [Google Play Console](https://play.google.com/console)

2. **Crear nueva app**:
   - Click en **"Crear app"**
   - Completa:
     - Nombre de la app: `Teleserp Chofer`
     - Idioma predeterminado: `Español`
     - Tipo de app: `App`
     - Gratis o de pago: `Gratis`
     - Declaración de privacidad: (necesitarás una URL)

3. **Completar información de la tienda**:
   - **Gráficos de la app**:
     - Icono (512x512 px)
     - Capturas de pantalla (mínimo 2, máximo 8)
     - Imagen destacada (1024x500 px) - opcional
   - **Categoría**: Selecciona la apropiada (ej: "Productividad" o "Negocios")
   - **Clasificación de contenido**: Completa el cuestionario
   - **Precio y distribución**: Configura países y precio

4. **Subir el AAB/APK**:
   - Ve a **"Producción"** (o "Prueba interna" / "Prueba cerrada" para probar primero)
   - Click en **"Crear nueva versión"**
   - Sube el archivo `.aab` o `.apk`
   - Completa las **Notas de la versión**
   - Click en **"Revisar versión"**

5. **Revisar y publicar**:
   - Revisa toda la información
   - Click en **"Iniciar rollout en Producción"**

### Paso 6: Proceso de Revisión

- **Tiempo típico**: 1-3 días
- Google revisará:
  - Políticas de contenido
  - Funcionalidad básica
  - Permisos solicitados
  - Privacidad

---

## 🍎 Apple App Store (iOS)

### Paso 1: Crear Cuenta de Desarrollador

1. Ve a [Apple Developer](https://developer.apple.com)
2. Inicia sesión con tu Apple ID
3. Únete al programa de desarrolladores ($99 USD/año)
4. Completa el proceso de inscripción

### Paso 2: Configurar Certificados y Perfiles

1. **Abrir Xcode**:
   ```bash
   npm run cap:open:ios
   ```

2. **Configurar Signing & Capabilities**:
   - Selecciona el proyecto **App** en el navegador
   - Ve a la pestaña **"Signing & Capabilities"**
   - Marca **"Automatically manage signing"**
   - Selecciona tu **Team** (tu cuenta de desarrollador)
   - Xcode generará automáticamente los certificados

3. **Verificar Bundle Identifier**:
   - Debe ser: `com.teleserp.courier`
   - Debe coincidir con `capacitor.config.ts`

### Paso 3: Configurar App en App Store Connect

1. **Inicia sesión** en [App Store Connect](https://appstoreconnect.apple.com)

2. **Crear nueva app**:
   - Click en **"Mis apps"** > **"+"** > **"Nueva app"**
   - Completa:
     - Plataforma: `iOS`
     - Nombre: `Teleserp Chofer`
     - Idioma principal: `Español`
     - Bundle ID: `com.teleserp.courier` (debe estar registrado)
     - SKU: `teleserp-courier-001` (identificador único)

3. **Completar información de la app**:
   - **Información de la app**:
     - Categoría principal
     - Categoría secundaria (opcional)
     - Información de privacidad
   - **Precio y disponibilidad**: Configura países y precio
   - **Versión de la app**: Información de la primera versión

### Paso 4: Generar IPA para Distribución

1. **Abrir Xcode**:
   ```bash
   npm run cap:open:ios
   ```

2. **Seleccionar dispositivo**:
   - En la barra superior, selecciona **"Any iOS Device"** (no simulador)

3. **Crear Archive**:
   - Menú: **Product** > **Archive**
   - Espera a que compile y cree el archive

4. **Distribuir App**:
   - Se abrirá el **Organizer** automáticamente
   - Selecciona el archive más reciente
   - Click en **"Distribute App"**
   - Selecciona **"App Store Connect"**
   - Click en **"Next"**
   - Selecciona **"Upload"** (no "Export")
   - Selecciona opciones de distribución
   - Click en **"Upload"**

5. **Esperar procesamiento**:
   - Apple procesará el IPA (puede tardar 10-30 minutos)
   - Recibirás un email cuando esté listo

### Paso 5: Configurar Versión en App Store Connect

1. **Volver a App Store Connect**

2. **Seleccionar tu app** > **"Versión iOS"**

3. **Completar información**:
   - **Capturas de pantalla** (requeridas):
     - iPhone 6.7" (1290 x 2796 px) - mínimo 1
     - iPhone 6.5" (1242 x 2688 px) - opcional
     - iPad Pro 12.9" (2048 x 2732 px) - si soporta iPad
   - **Descripción**: Escribe una descripción atractiva
   - **Palabras clave**: Separa con comas
   - **URL de soporte**: URL de tu sitio web
   - **URL de marketing**: (opcional)
   - **Información de privacidad**: URL de tu política de privacidad

4. **Seleccionar build**:
   - En **"Build"**, selecciona el build que subiste
   - Si no aparece, espera unos minutos y refresca

5. **Información de clasificación**:
   - Completa el cuestionario de clasificación de contenido

6. **Información de revisión**:
   - **Información de contacto**: Tu email y teléfono
   - **Notas para el revisor**: Instrucciones si es necesario
   - **Demo account**: Si la app requiere login, proporciona credenciales de prueba

### Paso 6: Enviar para Revisión

1. **Revisar todo**:
   - Verifica que toda la información esté completa
   - Revisa capturas de pantalla
   - Verifica que el build esté seleccionado

2. **Enviar para revisión**:
   - Click en **"Enviar para revisión"** (arriba a la derecha)
   - Confirma

### Paso 7: Proceso de Revisión

- **Tiempo típico**: 1-7 días (puede variar)
- Apple revisará:
  - Funcionalidad
  - Políticas de la App Store
  - Contenido
  - Privacidad
  - Guías de interfaz humana

---

## ✅ Checklist Final

### Antes de Subir

#### Información General
- [ ] App ID configurado correctamente
- [ ] Nombre de la app verificado
- [ ] Versión de la app actualizada
- [ ] Variables de entorno de producción configuradas
- [ ] Build exitoso sin errores

#### Android (Google Play)
- [ ] Keystore generado y guardado de forma segura
- [ ] Contraseñas del keystore guardadas
- [ ] AAB/APK generado y firmado
- [ ] Icono de la app (512x512 px)
- [ ] Capturas de pantalla (mínimo 2)
- [ ] Descripción de la app escrita
- [ ] Política de privacidad (URL)
- [ ] Clasificación de contenido completada

#### iOS (App Store)
- [ ] Certificados de distribución configurados
- [ ] Bundle ID verificado
- [ ] IPA generado y subido
- [ ] Capturas de pantalla para todos los tamaños requeridos
- [ ] Descripción de la app escrita
- [ ] Palabras clave configuradas
- [ ] URL de soporte configurada
- [ ] Política de privacidad (URL)
- [ ] Información de revisión completada
- [ ] Cuenta de demo (si aplica)

#### Contenido
- [ ] Iconos personalizados
- [ ] Splash screen configurado
- [ ] Textos en español (o idioma correspondiente)
- [ ] Sin enlaces rotos
- [ ] Funcionalidad probada en dispositivos reales

---

## 🐛 Solución de Problemas

### Android

**Error: "Keystore not found"**
```bash
# Verificar ruta del keystore
# Asegúrate de que la ruta en build.gradle sea correcta
# Ruta relativa desde android/app/build.gradle
```

**Error: "Signing config not found"**
```bash
# Verificar que signingConfigs esté dentro de android {}
# Verificar que buildTypes.release tenga signingConfig
```

**Error: "AAB upload failed"**
- Verifica que el AAB esté firmado correctamente
- Verifica que la versión sea mayor que la anterior
- Verifica que el appId coincida

### iOS

**Error: "No signing certificate found"**
- Ve a Xcode > Preferences > Accounts
- Agrega tu cuenta de Apple Developer
- Selecciona tu Team en Signing & Capabilities

**Error: "Bundle ID already exists"**
- El Bundle ID debe ser único
- Si ya existe, cambia el appId en `capacitor.config.ts` y regenera

**Error: "Archive not available"**
- Asegúrate de seleccionar "Any iOS Device" antes de archivar
- No uses el simulador para crear archives

**Build no aparece en App Store Connect**
- Espera 10-30 minutos después de subir
- Verifica que el procesamiento haya terminado
- Revisa el email de Apple

---

## 📝 Notas Importantes

### Versionado

- **Android**: Incrementa `versionCode` en `android/app/build.gradle`
- **iOS**: Incrementa `version` y `build` en Xcode o `Info.plist`
- **Capacitor**: No requiere cambios, usa la versión de las plataformas nativas

### Actualizaciones Futuras

Para actualizar la app después de la primera publicación:

1. **Haz cambios en el código**
2. **Incrementa la versión**
3. **Build y sincroniza**: `npm run cap:build`
4. **Genera nuevo AAB/IPA**
5. **Sube a la tienda** (mismo proceso, pero selecciona "Nueva versión")

### Privacidad

Ambas tiendas requieren:
- **Política de privacidad**: URL pública accesible
- **Permisos justificados**: Explica por qué necesitas cada permiso
- **Datos recopilados**: Declara qué datos recopilas

### Testing

Antes de publicar en producción:
- Usa **"Prueba interna"** en Google Play
- Usa **"TestFlight"** en App Store
- Prueba en dispositivos reales
- Verifica todas las funcionalidades

---

## 🔗 Enlaces Útiles

### Google Play
- [Google Play Console](https://play.google.com/console)
- [Guía de publicación](https://support.google.com/googleplay/android-developer)
- [Políticas de contenido](https://play.google.com/about/developer-content-policy/)

### Apple App Store
- [App Store Connect](https://appstoreconnect.apple.com)
- [Guía de publicación](https://developer.apple.com/app-store/review/)
- [Guías de la App Store](https://developer.apple.com/app-store/review/guidelines/)

### Capacitor
- [Documentación de Capacitor](https://capacitorjs.com/docs)
- [Guía de publicación](https://capacitorjs.com/docs/guides/publishing-your-app)

---

## 💡 Tips

1. **Primera publicación**: Usa "Prueba interna" o "TestFlight" primero
2. **Versionado**: Usa versionado semántico (1.0.0, 1.0.1, 1.1.0, etc.)
3. **Backup**: Guarda el keystore de Android en múltiples lugares seguros
4. **Documentación**: Mantén documentación de cada versión publicada
5. **Monitoreo**: Revisa comentarios y calificaciones regularmente
6. **Actualizaciones**: Publica actualizaciones regularmente para mantener la app activa

---

## 🎉 ¡Listo!

Una vez que completes estos pasos, tu app estará en proceso de revisión. Después de la aprobación, estará disponible en las tiendas para que los usuarios la descarguen.

**¡Buena suerte con tu publicación!** 🚀

