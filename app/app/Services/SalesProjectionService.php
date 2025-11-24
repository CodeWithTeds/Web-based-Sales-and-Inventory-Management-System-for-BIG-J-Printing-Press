<?php

namespace App\Services;

use App\Models\OrderItem;
use Illuminate\Support\Carbon;

class SalesProjectionService
{
    /**
     * Compute weekly sales aggregates and a simple projection per product.
     *
     * - Aggregates last N weeks of `OrderItem` by product and ISO week
     * - Uses a moving average (last 4 weeks) for next-week projection
     *
     * @param int $weeks Number of weeks of history to include (default 8)
     * @param int|null $userId If provided, restrict to orders of the given user
     * @return array { weeks: string[], products: array[] }
     */
    public function getWeeklyProjections(int $weeks = 8, ?int $userId = null): array
    {
        $weeks = max(1, min($weeks, 52));

        // Build ISO week keys for the time range (string format: YYYY-WW)
        $start = Carbon::now()->startOfWeek()->subWeeks($weeks - 1);
        $weekKeys = [];
        $cursor = $start->copy();
        for ($i = 0; $i < $weeks; $i++) {
            $weekKeys[] = $cursor->format('o-W'); // ISO week-year
            $cursor->addWeek();
        }

        // Aggregate order items per ISO week and product
        $query = OrderItem::query()
            ->selectRaw("product_id, name, DATE_FORMAT(created_at, '%x-%v') as week_key, SUM(qty) as qty_sum, SUM(line_total) as amount_sum")
            ->where('created_at', '>=', $start)
            ->groupBy('product_id', 'name', 'week_key')
            ->orderBy('week_key');

        if ($userId !== null) {
            $query->whereHas('order', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            });
        }

        $rows = $query->get();

        // Group rows by product (use name fallback if product_id is null)
        $byProduct = [];
        foreach ($rows as $r) {
            $key = $r->product_id ?? ('NAME:' . ($r->name ?? 'Unknown'));
            if (!isset($byProduct[$key])) {
                $byProduct[$key] = [
                    'product_id' => $r->product_id,
                    'product_name' => $r->name ?? 'Unknown',
                    'week_qty' => array_fill_keys($weekKeys, 0),
                    'week_amount' => array_fill_keys($weekKeys, 0.0),
                ];
            }
            $wk = (string) $r->week_key;
            if (isset($byProduct[$key]['week_qty'][$wk])) {
                $byProduct[$key]['week_qty'][$wk] += (int) ($r->qty_sum ?? 0);
                $byProduct[$key]['week_amount'][$wk] += (float) ($r->amount_sum ?? 0);
            }
        }

        // Compute averages and next-week projections (simple moving average of last 4 weeks)
        $products = [];
        $last4Keys = array_slice($weekKeys, max(count($weekKeys) - 4, 0));
        foreach ($byProduct as $p) {
            $qtySeries = array_values($p['week_qty']);
            $amtSeries = array_values($p['week_amount']);

            $avgQty = $this->average($qtySeries);
            $avgAmt = $this->average($amtSeries);

            $last4Qty = $this->sumKeys($p['week_qty'], $last4Keys) / max(count($last4Keys), 1);
            $last4Amt = $this->sumKeys($p['week_amount'], $last4Keys) / max(count($last4Keys), 1);

            // Blend last-4 avg and overall avg (60/40)
            $projectedQty = round(($last4Qty * 0.6) + ($avgQty * 0.4));
            $projectedAmt = ($last4Amt * 0.6) + ($avgAmt * 0.4);

            $products[] = [
                'product_id' => $p['product_id'],
                'product_name' => $p['product_name'],
                'weekly_qty' => $p['week_qty'],
                'weekly_amount' => $p['week_amount'],
                'avg_qty' => round($avgQty, 2),
                'avg_amount' => round($avgAmt, 2),
                'projected_qty' => max(0, (int) $projectedQty),
                'projected_amount' => max(0.0, round($projectedAmt, 2)),
            ];
        }

        // Sort by projected_amount descending
        usort($products, fn($a, $b) => ($b['projected_amount'] <=> $a['projected_amount']));

        return [
            'weeks' => $weekKeys,
            'products' => $products,
        ];
    }

    private function average(array $series): float
    {
        $n = count($series);
        if ($n === 0) return 0.0;
        return array_sum($series) / $n;
    }

    private function sumKeys(array $map, array $keys): float
    {
        $sum = 0.0;
        foreach ($keys as $k) {
            $sum += (float) ($map[$k] ?? 0);
        }
        return $sum;
    }
}