<?php

namespace App\Services;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;

class QRService
{
    /**
     * Genera un código QR con el número de serie
     *
     * @param string $serialNumber
     * @return string Base64 encoded PNG image
     */
    public function generateSerialQR(string $serialNumber): string
    {
        $builder = new Builder(
            writer: new PngWriter(),
            writerOptions: [],
            validateResult: false,
            data: $serialNumber,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Low,
            size: 150,
            margin: 10
        );

        $result = $builder->build();

        // Retornar como base64 para embeber en HTML
        return 'data:image/png;base64,' . base64_encode($result->getString());
    }
}
