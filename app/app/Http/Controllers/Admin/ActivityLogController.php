<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::query()->with(['user'])->latest();

        if ($search = trim((string) $request->query('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('route_name', 'like', "%{$search}%")
                  ->orWhere('url', 'like', "%{$search}%");
            });
        }

        if ($role = $request->query('role')) {
            $query->where('role', $role);
        }

        if ($userId = $request->query('user_id')) {
            $query->where('user_id', $userId);
        }

        $logs = $query->paginate(20)->withQueryString();
        $metrics = [
            'total' => ActivityLog::count(),
        ];

        return view('admin.activity-logs.index', compact('logs', 'metrics'));
    }
}