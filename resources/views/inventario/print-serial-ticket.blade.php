<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket - Número de Serie</title>
    <style>
        @media print {
            @page {
                size: 80mm auto;
                margin: 0;
            }
            html, body {
                width: 80mm;
                margin: 0;
                padding: 0;
            }
            .ticket {
                page-break-after: always;
                break-after: page;
                break-inside: avoid;
                margin: 0;
                padding: 10px;
            }
            .no-print {
                display: none;
            }
        }
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            max-width: 80mm;
            margin: 0 auto;
            padding: 10px;
        }
        .ticket {
            margin-bottom: 20px;
            padding: 10px;
            border: 1px dashed #ccc;
            box-sizing: border-box;
        }
        .header {
            text-align: center;
            border-bottom: 2px dashed #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .content {
            margin: 10px 0;
        }
        .label {
            font-weight: bold;
        }
        .value {
            margin-bottom: 8px;
        }
        .barcode {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 2px;
            margin: 15px 0;
            padding: 10px;
            border: 1px solid #000;
        }
        .qr-code {
            text-align: center;
            margin: 15px 0;
            padding: 10px;
        }
        .footer {
            text-align: center;
            border-top: 2px dashed #000;
            padding-top: 10px;
            margin-top: 10px;
            font-size: 10px;
        }
        .no-print {
            text-align: center;
            margin: 20px 0;
        }
        button {
            padding: 10px 20px;
            font-size: 14px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="ticket">
        <div class="header">
            <h2 style="margin: 0; font-size: 16px;">NÚMERO DE SERIE</h2>
        </div>
        
        <div class="content">
            <div class="value">
                <span class="label">Producto:</span><br>
                {{ $serialNumber->warehouseProduct->product->descripcion ?? 'N/A' }}
            </div>
            
            <div class="value">
                <span class="label">Número de Serie:</span>
                {{ $serialNumber->numero_serie }}
            </div>

            <div class="qr-code">
                <img src="{{ $qrCode }}" alt="Código QR - {{ $serialNumber->numero_serie }}" style="width: 150px; height: 150px;">
            </div>
            
            <div class="value">
                <span class="label">Estado:</span> {{ ucfirst($serialNumber->estado) }}
            </div>
            
            <div class="value">
                <span class="label">Fecha:</span> {{ $serialNumber->created_at->format('d/m/Y H:i') }}
            </div>
            
            <div class="value">
                <span class="label">ID:</span> #{{ $serialNumber->id }}
            </div>
        </div>
        
        <div class="footer">
            <div>ERP Teleserp</div>
            <div>{{ now()->format('d/m/Y H:i:s') }}</div>
        </div>
    </div>
    
    <div class="no-print">
        <button onclick="window.print()">🖨️ Imprimir</button>
        <button onclick="window.close()">❌ Cerrar</button>
    </div>
    
    <script>
        // Auto-imprimir al cargar (opcional, descomentar si se desea)
        // window.onload = function() {
        //     window.print();
        // }
    </script>
</body>
</html>

