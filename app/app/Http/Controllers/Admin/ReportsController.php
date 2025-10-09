<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Payment;

class ReportsController extends Controller
{
    public function export(Request $request)
    {
        $from = Carbon::parse($request->input('from', now()->subDays(29)->toDateString()))->startOfDay();
        $to = Carbon::parse($request->input('to', now()->toDateString()))->endOfDay();

        if ($to->lt($from)) {
            [$from, $to] = [$to, $from];
        }

        // Initialize daily buckets
        $labels = [];
        $dailyTotals = [];
        $cursor = $from->copy();
        while ($cursor->lte($to)) {
            $key = $cursor->format('Y-m-d');
            $labels[] = $key;
            $dailyTotals[$key] = 0.0;
            $cursor->addDay();
        }

        // Collect payments in range, excluding POS downpayments
        $payments = Payment::where(function ($q) {
                $q->whereNull('reference')->orWhere('reference', 'not like', 'POSDP-%');
            })
            ->where(function ($q) use ($from, $to) {
                $q->whereBetween('created_at', [$from, $to])
                  ->orWhereBetween('paid_at', [$from, $to]);
            })
            ->get();

        foreach ($payments as $p) {
            $date = optional($p->paid_at ?? $p->created_at)->format('Y-m-d');
            if ($date && isset($dailyTotals[$date])) {
                $dailyTotals[$date] += (float) ($p->amount ?? 0);
            }
        }

        $filename = sprintf('sales-report-%s-to-%s.csv', $from->format('Ymd'), $to->format('Ymd'));

        // Aggregate product/material breakdown in the range
        $items = \App\Models\OrderItem::with(['product.materials'])
            ->whereBetween('created_at', [$from, $to])
            ->get();
        $agg = [];
        foreach ($items as $it) {
            $key = $it->product_id ?: ('NAME:' . ($it->name ?? 'Unknown'));
            if (!isset($agg[$key])) {
                $productName = $it->product->name ?? $it->name ?? 'Unknown';
                $materials = $it->product ? $it->product->materials->pluck('name')->implode(', ') : '—';
                $agg[$key] = [
                    'product_name' => $productName,
                    'materials' => $materials,
                    'total_qty' => 0,
                    'total_amount' => 0.0,
                ];
            }
            $agg[$key]['total_qty'] += (int) ($it->qty ?? 0);
            $agg[$key]['total_amount'] += (float) ($it->line_total ?? 0);
        }
        uasort($agg, fn($a, $b) => $b['total_amount'] <=> $a['total_amount']);
        $productRows = array_values($agg);

        return response()->streamDownload(function () use ($labels, $dailyTotals, $productRows) {
            $out = fopen('php://output', 'w');
            // Landscape CSV: one header row of dates, one data row of totals
            fputcsv($out, $labels);
            $totalsRow = [];
            foreach ($labels as $d) {
                $totalsRow[] = number_format($dailyTotals[$d] ?? 0, 2, '.', '');
            }
            fputcsv($out, $totalsRow);

            // Blank row separator
            fputcsv($out, []);

            // Product/material breakdown section
            fputcsv($out, ['Product', 'Materials', 'Qty', 'Total Sales']);
            foreach ($productRows as $row) {
                fputcsv($out, [
                    $row['product_name'],
                    $row['materials'],
                    (int) $row['total_qty'],
                    number_format($row['total_amount'] ?? 0, 2, '.', ''),
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }
}