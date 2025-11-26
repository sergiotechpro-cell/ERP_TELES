@echo off
chcp 65001 >nul
:: Script de compilación para App Chofer - Windows
:: Autor: Sistema de Compilación Teleserp

color 0B
echo ========================================
echo   📱 COMPILACIÓN APP CHOFER - TELESERP  
echo ========================================
echo.

:: Verificar que estamos en el directorio correcto
if not exist "package.json" (
    color 0C
    echo ❌ Error: No se encontró package.json
    echo    Ejecuta este script desde el directorio courier-app
    pause
    exit /b 1
)

:: Menú de opciones
echo Selecciona el tipo de compilación:
echo.
echo 1. APK de Debug (para pruebas)
echo 2. APK de Release (para producción - requiere keystore)
echo 3. Solo actualizar y sincronizar (sin compilar APK)
echo.
set /p opcion="Ingresa el número de opción (1-3): "

echo.
echo 🔹 Instalando dependencias de Node.js...
call npm install
if errorlevel 1 (
    color 0C
    echo ❌ Error al instalar dependencias
    pause
    exit /b 1
)
echo ✅ Dependencias instaladas correctamente
echo.

echo 🔹 Compilando aplicación Next.js...
call npm run build
if errorlevel 1 (
    color 0C
    echo ❌ Error al compilar Next.js
    pause
    exit /b 1
)
echo ✅ Build de Next.js completado
echo.

echo 🔹 Sincronizando con Capacitor...
call npx cap sync
if errorlevel 1 (
    color 0C
    echo ❌ Error al sincronizar con Capacitor
    pause
    exit /b 1
)
echo ✅ Sincronización con Capacitor completada
echo.

:: Compilar según la opción
if "%opcion%"=="1" goto debug
if "%opcion%"=="2" goto release
if "%opcion%"=="3" goto sync_only
goto invalid

:debug
echo 🔹 Compilando APK de Debug...
cd android
call gradlew.bat assembleDebug
if errorlevel 1 (
    color 0C
    echo ❌ Error al compilar APK de Debug
    cd ..
    pause
    exit /b 1
)
cd ..

echo.
echo ✅ APK de Debug compilado exitosamente!
echo.
echo 📁 Ubicación del APK:
echo    android\app\build\outputs\apk\debug\app-debug.apk
echo.

set /p instalar="¿Deseas instalar el APK en un dispositivo conectado? (S/N): "
if /i "%instalar%"=="S" (
    echo 🔹 Instalando APK en dispositivo...
    adb install -r android\app\build\outputs\apk\debug\app-debug.apk
    if errorlevel 1 (
        echo ⚠️ Error al instalar APK. Verifica que el dispositivo esté conectado.
    ) else (
        echo ✅ APK instalado en el dispositivo!
    )
)
goto end

:release
if not exist "android\key.properties" (
    color 0E
    echo.
    echo ❌ No se encontró el archivo de configuración key.properties
    echo.
    echo Para compilar un APK de Release, primero debes:
    echo.
    echo 1. Generar un keystore con el comando:
    echo    keytool -genkey -v -keystore android\teleserp-chofer-release.jks
    echo            -keyalg RSA -keysize 2048 -validity 10000 -alias teleserp-chofer
    echo.
    echo 2. Crear el archivo android\key.properties con:
    echo    storePassword=tu_contraseña
    echo    keyPassword=tu_contraseña
    echo    keyAlias=teleserp-chofer
    echo    storeFile=teleserp-chofer-release.jks
    echo.
    pause
    exit /b 1
)

echo 🔹 Compilando APK de Release (firmado)...
cd android
call gradlew.bat assembleRelease
if errorlevel 1 (
    color 0C
    echo ❌ Error al compilar APK de Release
    cd ..
    pause
    exit /b 1
)
cd ..

echo.
echo ✅ APK de Release compilado y firmado exitosamente!
echo.
echo 📁 Ubicación del APK:
echo    android\app\build\outputs\apk\release\app-release.apk
echo.
echo 🚀 Este APK está listo para subir a Google Play Store
goto end

:sync_only
echo ✅ Sincronización completada. No se compiló APK.
echo.
echo Para abrir Android Studio, ejecuta:
echo    npm run cap:open:android
goto end

:invalid
color 0C
echo ❌ Opción inválida. Ejecuta el script nuevamente.
pause
exit /b 1

:end
echo.
echo ========================================
echo          ✅ PROCESO COMPLETADO          
echo ========================================
echo.
echo 📚 Para más información, consulta COMPILAR_APP.md
echo.
pause

