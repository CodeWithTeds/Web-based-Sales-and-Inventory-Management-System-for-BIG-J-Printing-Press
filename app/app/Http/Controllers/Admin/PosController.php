<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Repositories\PosRepository;
use App\Services\CartService;
use App\Services\PosCheckoutService;
use App\Services\PosService;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route as RouteFacade;
use App\Http\Requests\PosCheckoutRequest;
use App\Traits\HandlesPosErrors;

class PosController extends Controller
{
    use HandlesPosErrors;
    public function __construct(
        protected PosRepository $repo,
        protected CartService $cart,
        protected PosCheckoutService $checkoutService,
        protected PosService $posService,
    ) {}

    protected function getContext(): array
    {
        $current = RouteFacade::currentRouteName();
        $isClient = str_starts_with($current, 'client.ordering');
        return [
            'routePrefix' => $isClient ? 'client.ordering' : 'admin.pos',
            'title' => $isClient ? 'Online Ordering' : 'Admin POS',
        ];
    }

    public function index(Request $request)
    {
        $category = $request->query('category');
        $search = $request->query('search', '');
        $ctx = $this->getContext();

        if ($request->header('HX-Request')) {
            return response()->view('admin.partials.pos-cart', array_merge(
                $this->posService->getIndexData($category, $search),
                ['routePrefix' => $ctx['routePrefix']]
            ));
        }

        return view('admin.pos-blade', array_merge(
            $this->posService->getIndexData($category, $search),
            $ctx
        ));
    }

    public function add(Request $request, int $product)
    {
        $this->cart->add($product);

        if ($request->header('HX-Request')) {
            return response()->view('admin.partials.pos-cart', array_merge(
                $this->posService->getCartPartialData(),
                ['routePrefix' => $this->getContext()['routePrefix']]
            ));
        }

        return back();
    }

    public function increment(Request $request, int $product)
    {
        $this->cart->increment($product);

        if ($request->header('HX-Request')) {
            return response()->view('admin.partials.pos-cart', array_merge(
                $this->posService->getCartPartialData(),
                ['routePrefix' => $this->getContext()['routePrefix']]
            ));
        }

        return back();
    }

    public function decrement(Request $request, int $product)
    {
        $this->cart->decrement($product);

        if ($request->header('HX-Request')) {
            return response()->view('admin.partials.pos-cart', array_merge(
                $this->posService->getCartPartialData(),
                ['routePrefix' => $this->getContext()['routePrefix']]
            ));
        }

        return back();
    }

    public function remove(Request $request, int $product)
    {
        $this->cart->remove($product);

        if ($request->header('HX-Request')) {
            return response()->view('admin.partials.pos-cart', array_merge(
                $this->posService->getCartPartialData(),
                ['routePrefix' => $this->getContext()['routePrefix']]
            ));
        }

        return back();
    }

    public function clear(Request $request)
    {
        $this->cart->clear();

        if ($request->header('HX-Request')) {
            return response()->view('admin.partials.pos-cart', array_merge(
                $this->posService->getCartPartialData(),
                ['routePrefix' => $this->getContext()['routePrefix']]
            ));
        }

        return back();
    }

    public function checkout(PosCheckoutRequest $request)
    {
        $validated = $request->validated();

        $ctx = $this->getContext();
        if ($ctx['routePrefix'] === 'client.ordering') {
            // Online ordering: Start PayMongo Checkout (GCash only)
            $cart = $this->cart->all();
            if (empty($cart)) {
                return $this->respondCheckoutFailure(['error' => 'Cart is empty.'], $request);
            }

            $lineItems = [];
            foreach ($cart as $item) {
                if (!isset($item['name'], $item['qty'], $item['price'])) {
                    continue;
                }
                $lineItems[] = [
                    'name' => (string) $item['name'],
                    'quantity' => (int) $item['qty'],
                    'amount' => (int) round(((float) $item['price']) * 100), // amount in centavos
                    'currency' => 'PHP',
                ];
            }

            if (empty($lineItems)) {
                return $this->respondCheckoutFailure(['error' => 'Cart has no valid items.'], $request);
            }

            $successUrl = route('client.ordering.paymongo.success');
            $cancelUrl = route('client.ordering.paymongo.cancel');
            $description = 'Online Order via PayMongo';
            $reference = 'ORD-' . now()->format('YmdHis') . '-' . random_int(1000, 9999);

            $payload = [
                'data' => [
                    'attributes' => [
                        'line_items' => $lineItems,
                        'payment_method_types' => ['gcash'],
                        'success_url' => $successUrl,
                        'cancel_url' => $cancelUrl,
                        'description' => $description,
                        'send_email_receipt' => false,
                        'show_line_items' => true,
                        'show_description' => true,
                        'reference_number' => $reference,
                    ],
                ],
            ];

            try {
                $response = Http::withBasicAuth(config('services.paymongo.secret'), '')
                    ->acceptJson()
                    ->asJson()
                    ->post(rtrim(config('services.paymongo.base_url'), '/') . '/v1/checkout_sessions', $payload);

                if (!$response->successful()) {
                    $err = $response->json('errors.0.detail') ?? $response->body();
                    return $this->respondCheckoutFailure(['error' => 'PayMongo error: ' . $err], $request);
                }

                $data = $response->json('data');
                $checkoutUrl = $data['attributes']['checkout_url'] ?? null;
                $csId = $data['id'] ?? null;
                if (!$checkoutUrl || !$csId) {
                    return $this->respondCheckoutFailure(['error' => 'Invalid PayMongo response.'], $request);
                }

                // Persist session details to complete order after successful payment
                session()->put('paymongo.checkout_session_id', $csId);
                session()->put('paymongo.customer_name', $validated['customer_name'] ?? null);

                if ($request->header('HX-Request')) {
                    return response('', 204)->header('HX-Redirect', $checkoutUrl);
                }

                return redirect()->away($checkoutUrl);
            } catch (\Throwable $e) {
                return $this->respondCheckoutFailure(['error' => $e->getMessage()], $request);
            }
        }

        // Admin POS checkout: process immediately
        $result = $this->checkoutService->checkout($validated);

        if (!$result['success']) {
            return $this->respondCheckoutFailure($result, $request);
        }

        $ctx = $this->getContext();
        // Auto-view: redirect to the receipt page on success
        $receiptUrl = route($ctx['routePrefix'] . '.receipt', $result['order']);
        if ($request->header('HX-Request')) {
            // HTMX client-side redirect
            return response('', 204)->header('HX-Redirect', $receiptUrl);
        }

        return redirect()->to($receiptUrl);
    }

    public function receipt(Order $order)
    {
        $order->load('items');
        $ctx = $this->getContext();
        return view('admin.pos-receipt', [
            'order' => $order,
            'routePrefix' => $ctx['routePrefix']
        ]);
    }

    public function receiptDownload(Order $order)
    {
        $order->load('items');
        // Render the Blade view to HTML
        $html = view('admin.pos-receipt', ['order' => $order, 'download' => true])->render();

        // Configure Dompdf
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'receipt-' . $order->order_number . '.pdf';

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    // PayMongo success callback (no webhooks): verify payment then create order
    public function paymongoSuccess(Request $request)
    {
        $csId = session()->get('paymongo.checkout_session_id');
        if (!$csId) {
            return redirect()->route('client.ordering')->with('error', 'Missing checkout session.');
        }

        try {
            $resp = Http::withBasicAuth(config('services.paymongo.secret'), '')
                ->acceptJson()
                ->get(rtrim(config('services.paymongo.base_url'), '/') . '/v1/checkout_sessions/' . $csId);

            if (!$resp->successful()) {
                $err = $resp->json('errors.0.detail') ?? $resp->body();
                return redirect()->route('client.ordering')->with('error', 'Payment verification error: ' . $err);
            }

            $payments = $resp->json('data.attributes.payments') ?? [];
            $paid = false;
            foreach ($payments as $p) {
                if (($p['attributes']['status'] ?? null) === 'paid') {
                    $paid = true;
                    break;
                }
            }

            if (!$paid) {
                return redirect()->route('client.ordering')->with('error', 'Payment not completed.');
            }
        } catch (\Throwable $e) {
            return redirect()->route('client.ordering')->with('error', 'Payment verification failed: ' . $e->getMessage());
        }

        $customerName = session()->pull('paymongo.customer_name');
        session()->forget('paymongo.checkout_session_id');

        $result = $this->checkoutService->checkout(['customer_name' => $customerName]);
        if (!$result['success']) {
            return redirect()->route('client.ordering')->with('error', $result['error'] ?? 'Checkout failed.');
        }

        $order = $result['order'];
        Payment::create([
            'order_id' => $order->id,
            'provider' => 'paymongo',
            'method' => 'gcash',
            'amount' => $order->total,
            'currency' => 'PHP',
            'reference' => $csId,
            'paid_at' => now(),
        ]);

        $receiptUrl = route('client.ordering.receipt', $order);
        return redirect()->to($receiptUrl);
    }

    public function paymongoCancel()
    {
        session()->forget('paymongo.checkout_session_id');
        return redirect()->route('client.ordering')->with('error', 'Payment canceled.');
    }
}