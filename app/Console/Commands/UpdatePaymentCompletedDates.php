<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class UpdatePaymentCompletedDates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:update-completed-dates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualiza los pagos con estado completado que no tienen entregado_caja_at';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Buscando pagos con estado "completado" sin entregado_caja_at...');

        $payments = Payment::where('estado', 'completado')
            ->whereNull('entregado_caja_at')
            ->get();

        if ($payments->isEmpty()) {
            $this->info('✅ No hay pagos que actualizar.');
            return 0;
        }

        $this->info("Encontrados {$payments->count()} pagos para actualizar.");

        $updated = 0;
        $skipped = 0;

        foreach ($payments as $payment) {
            $dateToUse = null;

            // Si tiene un pedido relacionado y está finalizado, usar la fecha de actualización del pedido
            if ($payment->order_id && $payment->order) {
                $order = $payment->order;
                if ($order->estado === 'finalizado' && $order->updated_at) {
                    $dateToUse = $order->updated_at;
                }
            }

            // Si no se encontró fecha del pedido, usar la fecha de actualización del pago
            // o la fecha de creación si no fue actualizado
            if (!$dateToUse) {
                $dateToUse = $payment->updated_at ?? $payment->created_at;
            }

            if ($dateToUse) {
                $payment->update([
                    'entregado_caja_at' => $dateToUse
                ]);
                $updated++;
                $this->line("  ✓ Pago #{$payment->id}: {$dateToUse->format('Y-m-d H:i:s')}");
            } else {
                $skipped++;
                $this->warn("  ⚠ Pago #{$payment->id}: No se pudo determinar fecha");
            }
        }

        $this->info("\n✅ Proceso completado:");
        $this->info("   - Actualizados: {$updated}");
        $this->info("   - Omitidos: {$skipped}");

        return 0;
    }
}
