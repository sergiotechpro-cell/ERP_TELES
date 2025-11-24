<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\DriverLocation;

echo "🚗 Simulando movimiento del chofer en tiempo real...\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Obtener el chofer
$chofer = User::first();
if (!$chofer) {
    echo "❌ No hay usuarios en la base de datos\n";
    exit(1);
}

echo "👤 Chofer: {$chofer->name}\n";
echo "🗺️  Abre el mapa: http://localhost:8000/tracking/mapa\n";
echo "⏱️  Actualizando cada 3 segundos...\n";
echo "⌨️  Presiona Ctrl+C para detener\n\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Ruta simulada: Recorrido por la Ciudad de México
$ruta = [
    // Centro Histórico - Inicio
    ['lat' => 19.4326, 'lng' => -99.1332, 'speed' => 0, 'desc' => 'Saliendo del almacén'],
    
    // Avanzando por Reforma
    ['lat' => 19.4335, 'lng' => -99.1345, 'speed' => 15, 'desc' => 'Av. Reforma - Velocidad baja'],
    ['lat' => 19.4345, 'lng' => -99.1358, 'speed' => 25, 'desc' => 'Acelerando'],
    ['lat' => 19.4355, 'lng' => -99.1371, 'speed' => 30, 'desc' => 'Velocidad crucero'],
    ['lat' => 19.4365, 'lng' => -99.1384, 'speed' => 35, 'desc' => 'Tráfico fluido'],
    
    // Semáforo
    ['lat' => 19.4375, 'lng' => -99.1397, 'speed' => 20, 'desc' => 'Reduciendo velocidad'],
    ['lat' => 19.4380, 'lng' => -99.1405, 'speed' => 0, 'desc' => '🚦 Detenido en semáforo'],
    
    // Continuando
    ['lat' => 19.4385, 'lng' => -99.1410, 'speed' => 10, 'desc' => 'Arrancando'],
    ['lat' => 19.4395, 'lng' => -99.1423, 'speed' => 25, 'desc' => 'Acelerando nuevamente'],
    ['lat' => 19.4405, 'lng' => -99.1436, 'speed' => 35, 'desc' => 'Velocidad normal'],
    ['lat' => 19.4415, 'lng' => -99.1449, 'speed' => 40, 'desc' => 'Vía rápida'],
    
    // Girando
    ['lat' => 19.4425, 'lng' => -99.1462, 'speed' => 30, 'desc' => 'Preparando giro'],
    ['lat' => 19.4435, 'lng' => -99.1475, 'speed' => 20, 'desc' => 'Girando a la derecha'],
    
    // Zona residencial
    ['lat' => 19.4445, 'lng' => -99.1485, 'speed' => 25, 'desc' => 'Zona residencial'],
    ['lat' => 19.4455, 'lng' => -99.1495, 'speed' => 30, 'desc' => 'Buscando dirección'],
    ['lat' => 19.4465, 'lng' => -99.1505, 'speed' => 20, 'desc' => 'Acercándose al destino'],
    
    // Llegando
    ['lat' => 19.4475, 'lng' => -99.1515, 'speed' => 15, 'desc' => 'Última cuadra'],
    ['lat' => 19.4485, 'lng' => -99.1525, 'speed' => 10, 'desc' => 'Buscando estacionamiento'],
    ['lat' => 19.4490, 'lng' => -99.1530, 'speed' => 5, 'desc' => 'Estacionando'],
    
    // Destino
    ['lat' => 19.4495, 'lng' => -99.1535, 'speed' => 0, 'desc' => '📍 Llegó al destino'],
];

$pedido = \App\Models\Order::first();

// Simular movimiento
foreach ($ruta as $index => $punto) {
    // Desactivar ubicación anterior
    DriverLocation::where('user_id', $chofer->id)->update(['is_active' => false]);
    
    // Calcular heading (dirección) hacia el siguiente punto
    $heading = null;
    if (isset($ruta[$index + 1])) {
        $lat1 = deg2rad($punto['lat']);
        $lon1 = deg2rad($punto['lng']);
        $lat2 = deg2rad($ruta[$index + 1]['lat']);
        $lon2 = deg2rad($ruta[$index + 1]['lng']);
        
        $dLon = $lon2 - $lon1;
        $y = sin($dLon) * cos($lat2);
        $x = cos($lat1) * sin($lat2) - sin($lat1) * cos($lat2) * cos($dLon);
        $heading = rad2deg(atan2($y, $x));
        $heading = fmod(($heading + 360), 360);
    }
    
    // Crear nueva ubicación
    $location = DriverLocation::create([
        'user_id' => $chofer->id,
        'order_id' => $pedido?->id,
        'latitude' => $punto['lat'],
        'longitude' => $punto['lng'],
        'speed' => $punto['speed'],
        'heading' => $heading,
        'accuracy' => rand(5, 15),
        'is_active' => true,
        'location_timestamp' => now(),
    ]);
    
    // Mostrar progreso
    $num = $index + 1;
    $total = count($ruta);
    $progressChars = (int)($num / $total * 30);
    $progress = str_repeat('█', $progressChars);
    $remaining = str_repeat('░', max(0, 30 - $progressChars));
    
    $statusIcon = $punto['speed'] > 5 ? '🚗' : '🛑';
    
    echo sprintf(
        "[%s/%s] %s %s\n",
        str_pad($num, 2, '0', STR_PAD_LEFT),
        $total,
        $statusIcon,
        $punto['desc']
    );
    
    echo "        📍 Lat: {$punto['lat']}, Lng: {$punto['lng']}\n";
    echo "        🏃 Velocidad: {$punto['speed']} km/h";
    
    if ($heading !== null) {
        $direccion = '';
        if ($heading >= 337.5 || $heading < 22.5) $direccion = 'Norte ⬆️';
        elseif ($heading >= 22.5 && $heading < 67.5) $direccion = 'Noreste ↗️';
        elseif ($heading >= 67.5 && $heading < 112.5) $direccion = 'Este ➡️';
        elseif ($heading >= 112.5 && $heading < 157.5) $direccion = 'Sureste ↘️';
        elseif ($heading >= 157.5 && $heading < 202.5) $direccion = 'Sur ⬇️';
        elseif ($heading >= 202.5 && $heading < 247.5) $direccion = 'Suroeste ↙️';
        elseif ($heading >= 247.5 && $heading < 292.5) $direccion = 'Oeste ⬅️';
        else $direccion = 'Noroeste ↖️';
        
        echo " | Dirección: {$direccion}";
    }
    
    echo "\n        [{$progress}{$remaining}] " . round(($num / $total) * 100) . "%\n\n";
    
    // Esperar 3 segundos antes del siguiente punto
    if ($index < count($ruta) - 1) {
        sleep(3);
    }
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "✅ Simulación completada!\n";
echo "📊 Puntos recorridos: " . count($ruta) . "\n";
echo "⏱️  Tiempo total: " . (count($ruta) * 3) . " segundos\n";
echo "🗺️  Refresca el mapa para ver la ruta completa\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

