# 🎮 Ejecutar la App en el Emulador

## ✅ La app ya está lista para ejecutarse

---

## 🚀 Opción 1: Desde Android Studio (Recomendado)

### Paso 1: Abrir Android Studio
Ya se abrió automáticamente, pero si se cerró:
```bash
npx cap open android
```

### Paso 2: Esperar la sincronización
- Espera a que Gradle termine de sincronizar (barra de progreso en la parte inferior)
- Verás "Gradle sync finished" cuando esté listo

### Paso 3: Seleccionar un emulador
- En la parte superior, busca el selector de dispositivos
- Haz clic en el menú desplegable
- Selecciona un emulador existente
- **Si no tienes emuladores**: 
  - Tools → Device Manager → Create Device
  - Selecciona un dispositivo (ej: Pixel 5)
  - Descarga una imagen del sistema (ej: API 34)
  - Finish

### Paso 4: Ejecutar
- Haz clic en el botón **▶️ Run** (verde)
- O presiona `Shift + F10`
- El emulador se iniciará automáticamente
- La app se instalará y abrirá

---

## 🖥️ Opción 2: Desde la línea de comandos

### Listar emuladores disponibles:
```bash
# Desde el directorio courier-app
cd android
..\..\..\AppData\Local\Android\Sdk\emulator\emulator -list-avds
```

### Iniciar un emulador:
```bash
# Reemplaza "nombre_emulador" con uno de la lista
..\..\..\AppData\Local\Android\Sdk\emulator\emulator -avd nombre_emulador
```

### Instalar la app en el emulador:
```bash
# En otra terminal, desde courier-app/android
adb install app\build\outputs\apk\debug\app-debug.apk
```

---

## 🔥 Opción 3: Live Reload (Para desarrollo)

Ejecuta la app con recarga automática:

```bash
# Desde el directorio courier-app
npx cap run android --livereload --external
```

Esto:
- ✅ Inicia el emulador
- ✅ Instala la app
- ✅ Conecta con tu servidor local
- ✅ Recarga automáticamente al hacer cambios

**Nota**: Asegúrate de tener el servidor Laravel corriendo en el puerto 8000

---

## 🧪 Probar la Optimización de Rutas

Una vez que la app esté abierta en el emulador:

### 1. Iniciar sesión
- Email: (correo de un chofer en tu base de datos)
- Contraseña: (contraseña del chofer)

### 2. Ver pedidos
- Ve a "Mis Pedidos"
- Deberías ver tus pedidos asignados

### 3. Optimizar rutas
- Si tienes 2+ pedidos con coordenadas, verás el botón "Optimizar Rutas"
- Haz clic en el botón
- Confirma el orden de entregas
- Se abrirá Google Maps en el navegador del emulador

---

## 🛠️ Solución de Problemas

### El emulador no arranca
```bash
# Verifica que el emulador esté configurado correctamente
cd %ANDROID_HOME%\emulator
emulator -list-avds
```

### La app no se conecta a la API
1. Verifica que `.env.local` tenga la URL correcta:
   ```env
   NEXT_PUBLIC_API_URL=http://10.0.2.2:8000/api
   ```
   (10.0.2.2 es la IP del host desde el emulador Android)

2. Rebuil y sincroniza:
   ```bash
   npm run cap:build
   ```

### El emulador está lento
- Asegúrate de tener habilitada la aceleración de hardware (HAXM en Intel, WHPX en AMD)
- Usa un emulador con API 28-34 para mejor rendimiento
- Cierra otras aplicaciones pesadas

---

## 📱 Crear un Emulador (si no tienes)

### Desde Android Studio:
1. **Tools → Device Manager**
2. **Create Device**
3. Selecciona un dispositivo (recomendado: **Pixel 5** o **Pixel 6**)
4. Selecciona una imagen del sistema:
   - **API 34** (Android 14) - Recomendado
   - **API 33** (Android 13) - También funciona bien
5. Dale un nombre y haz clic en **Finish**
6. El emulador se creará y estará listo para usar

### Desde la línea de comandos:
```bash
# Listar imágenes disponibles
sdkmanager --list | findstr "system-images"

# Instalar una imagen (ejemplo)
sdkmanager "system-images;android-34;google_apis;x86_64"

# Crear el emulador
avdmanager create avd -n Pixel_5_API_34 -k "system-images;android-34;google_apis;x86_64" -d "pixel_5"
```

---

## ✅ Checklist

- [ ] Android Studio abierto
- [ ] Gradle sincronizado correctamente
- [ ] Emulador creado o seleccionado
- [ ] App ejecutándose en el emulador
- [ ] Login exitoso como chofer
- [ ] Pedidos visibles
- [ ] Botón de optimización de rutas visible (si hay 2+ pedidos)
- [ ] Optimización de rutas probada

---

## 🎯 Comandos Rápidos

```bash
# Abrir Android Studio
npx cap open android

# Build y sincronizar
npm run cap:build

# Ejecutar con live reload
npx cap run android --livereload

# Instalar APK en emulador
adb install -r android\app\build\outputs\apk\debug\app-debug.apk

# Ver logs en tiempo real
adb logcat *:E
```

---

## 🎉 ¡Listo!

Tu app con optimización de rutas está lista para probar en el emulador.

**Disfruta probando la nueva funcionalidad de optimización de rutas! 🚚🗺️**

