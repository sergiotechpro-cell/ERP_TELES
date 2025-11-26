<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseProduct;
use App\Models\SerialNumber;

// Crear producto de prueba
$product = Product::create([
    'descripcion' => 'Producto de Prueba QR',
    'costo_unitario' => 100,
    'precio_venta' => 150,
    'price_tier' => 'menudeo'
]);

// Obtener primera bodega
$warehouse = Warehouse::first();
if ($warehouse) {
    // Crear warehouse_product
    $wp = WarehouseProduct::create([
        'warehouse_id' => $warehouse->id,
        'product_id' => $product->id,
        'stock' => 3
    ]);

    // Crear números de serie
    for ($i = 0; $i < 3; $i++) {
        SerialNumber::create([
            'warehouse_product_id' => $wp->id,
            'numero_serie' => 'TEST-' . strtoupper(uniqid()),
        ]);
    }

    echo "Producto y 3 números de serie creados exitosamente\n";
    echo "Primer número de serie: " . SerialNumber::first()->numero_serie . "\n";
} else {
    echo "No hay bodegas disponibles\n";
}
