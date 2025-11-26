<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\QRService;

$qrService = new QRService();
$qrCode = $qrService->generateSerialQR('TEST-123456');

echo "Código QR generado exitosamente!\n";
echo "Longitud del base64: " . strlen($qrCode) . " caracteres\n";
echo "Empieza con: " . substr($qrCode, 0, 50) . "...\n";

// Verificar que contiene data:image/png;base64,
if (strpos($qrCode, 'data:image/png;base64,') === 0) {
    echo "✓ Formato correcto (base64 data URL)\n";
} else {
    echo "✗ Formato incorrecto\n";
}

// Extraer y verificar que el base64 es válido
$base64Data = str_replace('data:image/png;base64,', '', $qrCode);
if (base64_decode($base64Data, true) !== false) {
    echo "✓ Base64 válido\n";
} else {
    echo "✗ Base64 inválido\n";
}
