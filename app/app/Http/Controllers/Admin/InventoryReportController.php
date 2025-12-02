<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryTransaction;
use App\Models\OrderItem;
use Illuminate\Http\Request;

class InventoryReportController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->query('from');
        $to = $request->query('to');

        // Product stock-in adjustments (only subject_type = 'product')
        $inQuery = InventoryTransaction::query()
            ->where('type', 'in')
            ->where('subject_type', 'product');

        // Material stock-in (only subject_type = 'material')
        $outQuery = InventoryTransaction::query()
            ->where('type', 'in')
            ->where('subject_type', 'material');

        // Material stock-out (only subject_type = 'material')
        $materialOutQuery = InventoryTransaction::query()
            ->where('type', 'out')
            ->where('subject_type', 'material');
        $productOutQuery = OrderItem::query();

        if ($from) {
            $inQuery->where('created_at', '>=', $from);
            $outQuery->where('created_at', '>=', $from);
            $materialOutQuery->where('created_at', '>=', $from);
            $productOutQuery->where('created_at', '>=', $from);
        }
        if ($to) {
            $inQuery->where('created_at', '<=', $to);
            $outQuery->where('created_at', '<=', $to);
            $materialOutQuery->where('created_at', '<=', $to);
            $productOutQuery->where('created_at', '<=', $to);
        }

        $stockIn = $inQuery->orderByDesc('created_at')->limit(100)->get();
        $stockOut = $outQuery->orderByDesc('created_at')->limit(100)->get();
        $materialsOut = $materialOutQuery->orderByDesc('created_at')->limit(100)->get();
        $productOut = $productOutQuery->with(['order', 'product'])->orderByDesc('created_at')->limit(100)->get();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'product_out' => $productOut,
                    'stock_in' => $stockIn,
                    'stock_out' => $stockOut,
                    'materials_out' => $materialsOut,
                ],
            ]);
        }

        return view('admin.reports.inventory', compact('productOut', 'stockIn', 'stockOut', 'materialsOut'));
    }
}