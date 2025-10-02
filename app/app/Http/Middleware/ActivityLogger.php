<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class ActivityLogger
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $user = Auth::user();

        // Log only for authenticated staff and client users based on role string
        if ($user && in_array($user->role, ['staff', 'client'], true)) {
            try {
                $payload = $this->sanitizePayload($request->all());

                ActivityLog::create([
                    'user_id' => $user->id,
                    'role' => $user->role,
                    'description' => $this->buildDescription($request),
                    'route_name' => Route::currentRouteName(),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'ip' => $request->ip(),
                    'user_agent' => substr((string) $request->userAgent(), 0, 255),
                    'status_code' => method_exists($response, 'getStatusCode') ? $response->getStatusCode() : null,
                    'payload' => $payload,
                ]);
            } catch (\Throwable $e) {
                // Never break the request due to logging failure
                // Optionally: \Log::warning('ActivityLogger error: '.$e->getMessage());
            }
        }

        return $response;
    }

    protected function sanitizePayload(array $payload): array
    {
        // Remove sensitive fields commonly used in forms
        $hiddenKeys = ['password', 'password_confirmation', 'current_password', 'token', 'remember'];
        foreach ($hiddenKeys as $key) {
            if (array_key_exists($key, $payload)) {
                $payload[$key] = '***';
            }
        }

        // Limit overall payload size
        return collect($payload)->map(function ($value) {
            if (is_string($value)) {
                return mb_strimwidth($value, 0, 500, '...');
            }
            return $value;
        })->all();
    }

    protected function buildDescription(Request $request): string
    {
        $routeName = Route::currentRouteName();
        $path = $request->path();
        $method = $request->method();
        $actionType = $request->isMethod('GET') ? 'view' : 'action';
        return trim(sprintf('Visited %s [%s] %s', $routeName ?: $path, $method, $actionType));
    }
}