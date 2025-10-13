<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $ventasMensuales = Payment::selectRaw("DATE_TRUNC('month', created_at) as mes, SUM(monto) as total")
            ->groupBy('mes')->orderBy('mes')->get();

        $inventario = Product::count();
        $pedidos = Order::count();
        $ventasHoy = Payment::whereDate('created_at', now())->sum('monto');

        return view('dashboard', compact('ventasMensuales','inventario','pedidos','ventasHoy'));
    }
}
