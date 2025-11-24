<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Order;
use App\Models\DriverLocation;
use Carbon\Carbon;

class TrackingTestSeeder extends Seeder
{
    /**
     * Simular una ruta de entrega con ubicaciones GPS
     */
    public function run(): void
    {
        // Buscar un chofer (usuario con rol chofer, vendedor, o cualquier usuario)
        $chofer = User::whereHas('roles', function($q) {
            $q->whereIn('name', ['chofer', 'vendedor']);
        })->first();

        // Si no hay chofer, usar el primer usuario disponible
        if (!$chofer) {
            $chofer = User::first();
            if (!$chofer) {
                $this->command->error('No hay usuarios en la base de datos');
                return;
            }
            $this->command->warn('No se encontró un chofer, usando usuario: ' . $chofer->name);
        }

        // Buscar un pedido activo o crear uno de prueba
        $pedido = Order::where('estado', 'pendiente')->first();
        
        if (!$pedido) {
            $this->command->warn('No hay pedidos pendientes, creando uno de prueba...');
            // Aquí podrías crear un pedido de prueba si lo necesitas
        }

        $this->command->info("Generando ruta de prueba para: {$chofer->name}");
        if ($pedido) {
            $this->command->info("Pedido asignado: #{$pedido->id}");
        }

        // Ruta simulada: Ciudad de México (puedes cambiar estas coordenadas)
        // Simula un recorrido de ~5km desde el centro hacia el norte
        $ruta = [
            // Punto de inicio (Centro Histórico)
            ['lat' => 19.4326, 'lng' => -99.1332, 'speed' => 0],
            
            // Avanzando por Reforma
            ['lat' => 19.4340, 'lng' => -99.1350, 'speed' => 15],
            ['lat' => 19.4355, 'lng' => -99.1368, 'speed' => 25],
            ['lat' => 19.4370, 'lng' => -99.1386, 'speed' => 30],
            
            // Semáforo (detenido)
            ['lat' => 19.4385, 'lng' => -99.1404, 'speed' => 0],
            
            // Continuando
            ['lat' => 19.4400, 'lng' => -99.1422, 'speed' => 20],
            ['lat' => 19.4415, 'lng' => -99.1440, 'speed' => 35],
            ['lat' => 19.4430, 'lng' => -99.1458, 'speed' => 40],
            
            // Girando
            ['lat' => 19.4445, 'lng' => -99.1476, 'speed' => 25],
            ['lat' => 19.4460, 'lng' => -99.1494, 'speed' => 30],
            
            // Llegando al destino
            ['lat' => 19.4475, 'lng' => -99.1512, 'speed' => 15],
            ['lat' => 19.4490, 'lng' => -99.1530, 'speed' => 5],
            
            // Destino final (detenido)
            ['lat' => 19.4500, 'lng' => -99.1540, 'speed' => 0],
        ];

        $this->command->info("Generando " . count($ruta) . " puntos GPS...");

        // Desactivar ubicaciones anteriores del chofer
        DriverLocation::where('user_id', $chofer->id)
            ->update(['is_active' => false]);

        // Generar ubicaciones con timestamps progresivos
        $timestamp = Carbon::now()->subMinutes(count($ruta) * 2); // Empezar hace X minutos

        foreach ($ruta as $index => $punto) {
            // Calcular heading (dirección) hacia el siguiente punto
            $heading = null;
            if (isset($ruta[$index + 1])) {
                $heading = $this->calculateBearing(
                    $punto['lat'], $punto['lng'],
                    $ruta[$index + 1]['lat'], $ruta[$index + 1]['lng']
                );
            }

            // Crear ubicación
            $location = DriverLocation::create([
                'user_id' => $chofer->id,
                'order_id' => $pedido?->id,
                'latitude' => $punto['lat'],
                'longitude' => $punto['lng'],
                'speed' => $punto['speed'],
                'heading' => $heading,
                'accuracy' => rand(5, 20), // Precisión entre 5-20 metros
                'is_active' => false, // Todas inactivas excepto la última
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);

            $num = $index + 1;
            $this->command->info("  [{$num}] Lat: {$punto['lat']}, Lng: {$punto['lng']}, Speed: {$punto['speed']} km/h");

            // Avanzar el tiempo (2 minutos entre cada punto)
            $timestamp->addMinutes(2);
        }

        // Marcar la última ubicación como activa
        $ultimaUbicacion = DriverLocation::where('user_id', $chofer->id)
            ->latest('created_at')
            ->first();
        
        if ($ultimaUbicacion) {
            $ultimaUbicacion->update(['is_active' => true]);
            $this->command->info("\n✅ Última ubicación marcada como activa");
        }

        $this->command->info("\n🎉 Ruta de prueba generada exitosamente!");
        $this->command->info("📍 Puntos GPS: " . count($ruta));
        $this->command->info("👤 Chofer: {$chofer->name}");
        $this->command->info("🚗 Estado: " . ($ultimaUbicacion->speed > 5 ? 'En movimiento' : 'Detenido'));
        $this->command->info("\n🗺️  Abre el mapa en: /tracking/mapa");
    }

    /**
     * Calcular el bearing (dirección) entre dos puntos GPS
     */
    private function calculateBearing($lat1, $lon1, $lat2, $lon2): float
    {
        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        $dLon = $lon2 - $lon1;

        $y = sin($dLon) * cos($lat2);
        $x = cos($lat1) * sin($lat2) - sin($lat1) * cos($lat2) * cos($dLon);

        $bearing = atan2($y, $x);
        $bearing = rad2deg($bearing);
        $bearing = fmod(($bearing + 360), 360);

        return round($bearing, 2);
    }
}
