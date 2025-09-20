<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

/**
 * Shared helpers for POS error handling.
 *
 * This trait expects the using class to define a `$posService` property
 * that provides:
 *  - getCartPartialData(): array
 *  - formatInsufficientStockError(array $details): string
 */
trait HandlesPosErrors
{
    /**
     * Standardize the checkout failure response for both HTMX and non-HTMX requests.
     *
     * @param array   $result  Result array containing success flag and error/message strings
     * @param Request $request Current HTTP request
     * @return Response|RedirectResponse
     */
    protected function respondCheckoutFailure(array $result, Request $request): Response|RedirectResponse
    {
        // Prefer a specific error message from the result
        $raw = $result['error'] ?? $result['message'] ?? 'Checkout failed.';

        // Map repository error for insufficient stock into a human friendly message
        if (is_string($raw) && str_starts_with($raw, 'INSUFFICIENT_STOCK:')) {
            $json = substr($raw, strlen('INSUFFICIENT_STOCK:'));
            $details = json_decode($json, true) ?: [];
            $msg = $this->posService->formatInsufficientStockError($details);
        } else {
            $msg = is_string($raw) ? $raw : 'Checkout failed.';
        }

        // HTMX requests render the cart partial with inline error
        if ($request->header('HX-Request')) {
            return response()->view('admin.partials.pos-cart', array_merge(
                $this->posService->getCartPartialData(),
                [ 'error' => $msg ]
            ));
        }

        // Non-HTMX requests: redirect back with errors
        return back()->withErrors(['checkout' => $msg]);
    }
}