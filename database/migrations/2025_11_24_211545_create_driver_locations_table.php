<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('driver_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // El chofer
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null'); // Pedido activo
            $table->decimal('latitude', 10, 8); // Latitud con precisión
            $table->decimal('longitude', 11, 8); // Longitud con precisión
            $table->decimal('speed', 8, 2)->nullable(); // Velocidad en km/h
            $table->decimal('heading', 5, 2)->nullable(); // Dirección (0-360 grados)
            $table->decimal('accuracy', 8, 2)->nullable(); // Precisión del GPS en metros
            $table->boolean('is_active')->default(true); // Si está en servicio
            $table->timestamp('location_timestamp'); // Momento exacto de la ubicación
            $table->timestamps();
            
            // Índices para consultas rápidas
            $table->index(['user_id', 'is_active']);
            $table->index(['order_id', 'is_active']);
            $table->index('location_timestamp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_locations');
    }
};
