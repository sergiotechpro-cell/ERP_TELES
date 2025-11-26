# Script de compilación para App Chofer - Windows
# Autor: Sistema de Compilación Teleserp
# Descripción: Compila la app del chofer con optimización de rutas

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  📱 COMPILACIÓN APP CHOFER - TELESERP  " -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Función para mostrar mensajes de progreso
function Write-Step {
    param([string]$Message)
    Write-Host "🔹 $Message" -ForegroundColor Green
}

function Write-Success {
    param([string]$Message)
    Write-Host "✅ $Message" -ForegroundColor Green
}

function Write-Error-Custom {
    param([string]$Message)
    Write-Host "❌ $Message" -ForegroundColor Red
}

# Verificar que estamos en el directorio correcto
if (!(Test-Path "package.json")) {
    Write-Error-Custom "Error: No se encontró package.json. Asegúrate de ejecutar este script desde el directorio courier-app"
    exit 1
}

# Preguntar qué tipo de compilación
Write-Host "Selecciona el tipo de compilación:" -ForegroundColor Yellow
Write-Host "1. APK de Debug (para pruebas)" -ForegroundColor White
Write-Host "2. APK de Release (para producción - requiere keystore)" -ForegroundColor White
Write-Host "3. Solo actualizar y sincronizar (sin compilar APK)" -ForegroundColor White
Write-Host ""
$opcion = Read-Host "Ingresa el número de opción (1-3)"

# Paso 1: Instalar/Actualizar dependencias
Write-Step "Instalando dependencias de Node.js..."
npm install
if ($LASTEXITCODE -ne 0) {
    Write-Error-Custom "Error al instalar dependencias"
    exit 1
}
Write-Success "Dependencias instaladas correctamente"
Write-Host ""

# Paso 2: Build de Next.js
Write-Step "Compilando aplicación Next.js..."
npm run build
if ($LASTEXITCODE -ne 0) {
    Write-Error-Custom "Error al compilar Next.js"
    exit 1
}
Write-Success "Build de Next.js completado"
Write-Host ""

# Paso 3: Sincronizar con Capacitor
Write-Step "Sincronizando con Capacitor..."
npx cap sync
if ($LASTEXITCODE -ne 0) {
    Write-Error-Custom "Error al sincronizar con Capacitor"
    exit 1
}
Write-Success "Sincronización con Capacitor completada"
Write-Host ""

# Paso 4: Compilar según la opción seleccionada
switch ($opcion) {
    "1" {
        Write-Step "Compilando APK de Debug..."
        Set-Location android
        .\gradlew.bat assembleDebug
        if ($LASTEXITCODE -ne 0) {
            Write-Error-Custom "Error al compilar APK de Debug"
            Set-Location ..
            exit 1
        }
        Set-Location ..
        
        Write-Success "APK de Debug compilado exitosamente!"
        Write-Host ""
        Write-Host "📁 Ubicación del APK:" -ForegroundColor Yellow
        Write-Host "   android\app\build\outputs\apk\debug\app-debug.apk" -ForegroundColor White
        Write-Host ""
        
        # Preguntar si quiere instalar en dispositivo
        $instalar = Read-Host "¿Deseas instalar el APK en un dispositivo conectado? (S/N)"
        if ($instalar -eq "S" -or $instalar -eq "s") {
            Write-Step "Instalando APK en dispositivo..."
            adb install -r android\app\build\outputs\apk\debug\app-debug.apk
            if ($LASTEXITCODE -eq 0) {
                Write-Success "APK instalado en el dispositivo!"
            } else {
                Write-Error-Custom "Error al instalar APK. Verifica que el dispositivo esté conectado con depuración USB activada."
            }
        }
    }
    "2" {
        # Verificar que exista el keystore
        if (!(Test-Path "android\key.properties")) {
            Write-Host ""
            Write-Error-Custom "No se encontró el archivo de configuración key.properties"
            Write-Host ""
            Write-Host "Para compilar un APK de Release, primero debes:" -ForegroundColor Yellow
            Write-Host "1. Generar un keystore con el comando:" -ForegroundColor White
            Write-Host "   keytool -genkey -v -keystore android\teleserp-chofer-release.jks -keyalg RSA -keysize 2048 -validity 10000 -alias teleserp-chofer" -ForegroundColor Cyan
            Write-Host ""
            Write-Host "2. Crear el archivo android\key.properties con:" -ForegroundColor White
            Write-Host "   storePassword=tu_contraseña" -ForegroundColor Cyan
            Write-Host "   keyPassword=tu_contraseña" -ForegroundColor Cyan
            Write-Host "   keyAlias=teleserp-chofer" -ForegroundColor Cyan
            Write-Host "   storeFile=teleserp-chofer-release.jks" -ForegroundColor Cyan
            Write-Host ""
            exit 1
        }
        
        Write-Step "Compilando APK de Release (firmado)..."
        Set-Location android
        .\gradlew.bat assembleRelease
        if ($LASTEXITCODE -ne 0) {
            Write-Error-Custom "Error al compilar APK de Release"
            Set-Location ..
            exit 1
        }
        Set-Location ..
        
        Write-Success "APK de Release compilado y firmado exitosamente!"
        Write-Host ""
        Write-Host "📁 Ubicación del APK:" -ForegroundColor Yellow
        Write-Host "   android\app\build\outputs\apk\release\app-release.apk" -ForegroundColor White
        Write-Host ""
        Write-Host "🚀 Este APK está listo para subir a Google Play Store" -ForegroundColor Green
    }
    "3" {
        Write-Success "Sincronización completada. No se compiló APK."
        Write-Host ""
        Write-Host "Para abrir Android Studio, ejecuta:" -ForegroundColor Yellow
        Write-Host "   npm run cap:open:android" -ForegroundColor Cyan
    }
    default {
        Write-Error-Custom "Opción inválida. Ejecuta el script nuevamente."
        exit 1
    }
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "         ✅ PROCESO COMPLETADO          " -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "📚 Para más información, consulta COMPILAR_APP.md" -ForegroundColor Yellow
Write-Host ""

# Pausar al final para que el usuario pueda leer los mensajes
Read-Host "Presiona Enter para salir..."

