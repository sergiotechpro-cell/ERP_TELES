<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\Payment;

class OrderObserver
{
    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        // Si el estado cambió a "finalizado", reflejar el dinero en finanzas
        if ($order->wasChanged('estado') && $order->estado === 'finalizado') {
            // Actualizar todos los pagos del pedido a "completado" y "depositado"
            // Esto refleja el dinero en el sistema de finanzas
            $order->payments()->update([
                'estado' => 'completado',
                'entregado_caja_at' => now(),
            ]);
        }
    }
}

