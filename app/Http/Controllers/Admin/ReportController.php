<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function sales(Request $request)
    {
        $fromInput = $request->input('from');
        $toInput = $request->input('to');
        $productId = $request->input('product_id');
        $formaPago = $request->input('forma_pago');

        $from = $fromInput ? Carbon::parse($fromInput)->startOfDay() : now()->subDays(30)->startOfDay();
        $to = $toInput ? Carbon::parse($toInput)->endOfDay() : now()->endOfDay();

        $baseQuery = SaleItem::with([
                'sale.customer',
                'sale.user',
                'product.warehouses'
            ])
            ->whereHas('sale', fn($q) => $q->whereBetween('created_at', [$from, $to]));

        if ($productId) {
            $baseQuery->where('product_id', $productId);
        }

        if ($formaPago) {
            $baseQuery->whereHas('sale', fn($q) => $q->where('forma_pago', $formaPago));
        }

        $summary = (clone $baseQuery)
            ->selectRaw('COALESCE(SUM(cantidad), 0) as total_unidades')
            ->selectRaw('COALESCE(SUM(cantidad * precio_unitario), 0) as total_monto')
            ->selectRaw('COUNT(DISTINCT sale_id) as total_ventas')
            ->first();

        $saleItems = (clone $baseQuery)
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        $products = Product::orderBy('descripcion')->get(['id', 'descripcion']);

        return view('admin.reports.sales', [
            'saleItems' => $saleItems,
            'summary' => $summary,
            'filters' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
                'product_id' => $productId,
                'forma_pago' => $formaPago,
            ],
            'products' => $products,
        ]);
    }
}

