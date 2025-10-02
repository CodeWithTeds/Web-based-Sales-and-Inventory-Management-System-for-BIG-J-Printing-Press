<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::query()->with(['user'])->latest();

        // Staff can view only their own logs by default
        $query->where('user_id', Auth::id());

        if ($search = trim((string) $request->query('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('route_name', 'like', "%{$search}%")
                  ->orWhere('url', 'like', "%{$search}%");
            });
        }

        $logs = $query->paginate(20)->withQueryString();
        $metrics = [
            'total' => $query->count(),
        ];

        return view('staff.activity-logs.index', compact('logs', 'metrics'));
    }
}