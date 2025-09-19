<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse as HttpJsonResponse;
use Illuminate\Http\RedirectResponse;

trait ResponseHelpers
{
    use ApiResponse;
    
    /**
     * Handle response based on request type (JSON or redirect).
     *
     * @param  mixed  $data
     * @param  string  $message
     * @param  string  $redirectRoute
     * @param  array  $routeParams
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    protected function respondWith($data = null, string $message = 'Operation successful', string $redirectRoute = null, array $routeParams = [])
    {
        if (request()->wantsJson()) {
            return $this->successResponse($data, $message);
        }
        
        $redirectRoute = $redirectRoute ?? $this->getDefaultRedirectRoute();
        
        return redirect()->route($redirectRoute, $routeParams)
            ->with('success', $message);
    }
    
    /**
     * Handle error response based on request type (JSON or redirect).
     *
     * @param  string  $message
     * @param  string  $redirectRoute
     * @param  array  $routeParams
     * @param  int  $code
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    protected function respondWithError(string $message = 'Operation failed', string $redirectRoute = null, array $routeParams = [], int $code = 400)
    {
        if (request()->wantsJson()) {
            return $this->errorResponse($message, $code);
        }
        
        $redirectRoute = $redirectRoute ?? $this->getDefaultRedirectRoute();
        
        return redirect()->route($redirectRoute, $routeParams)
            ->with('error', $message);
    }
    
    /**
     * Get the default redirect route.
     * Override this method in your controller to customize the default route.
     *
     * @return string
     */
    protected function getDefaultRedirectRoute(): string
    {
        return property_exists($this, 'routePrefix') 
            ? $this->routePrefix . '.index' 
            : 'dashboard';
    }
}