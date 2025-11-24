<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SalesProjectionService;
use Illuminate\Http\Request;

class SalesProjectionController extends Controller
{
    public function __construct(private SalesProjectionService $service) {}

    public function index(Request $request)
    {
        $weeks = (int) $request->input('weeks', 8);
        $weeks = max(1, min($weeks, 52));

        $data = $this->service->getWeeklyProjections($weeks);

        return view('admin.sales-projection.index', [
            'weeks' => $data['weeks'],
            'products' => $data['products'],
            'selectedWeeks' => $weeks,
            'title' => __('Sales Projection'),
        ]);
    }
}