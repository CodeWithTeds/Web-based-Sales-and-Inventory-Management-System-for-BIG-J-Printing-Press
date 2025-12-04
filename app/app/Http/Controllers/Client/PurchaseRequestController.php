<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Category;
use App\Models\Product;
use App\Models\Payment;
use App\Models\Material;
use App\Models\InventoryTransaction;
use App\Repositories\CheckoutRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route as RouteFacade;

class PurchaseRequestController extends Controller
{
    public function __construct(protected CheckoutRepository $checkoutRepo) {}

    /**
     * Step 1: Choose a Category (client-facing).
     */
    public function selectCategory(Request $request)
    {
        // Use product-derived categories to ensure filtering matches available products
        $productCategories = Product::query()
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');
        $pending = $this->getPendingPRForUser(Auth::id());
        $approved = $this->getApprovedPRForUser(Auth::id());
        $quoted = Order::where('user_id', Auth::id())
            ->where('order_number', 'like', 'PR-%')
            ->where('status', 'quoted')
            ->latest()
            ->first();

        return view('client.purchase-requests.select-category', [
            // Keep old 'categories' for compatibility, but prefer productCategories in view
            'categories' => [],
            'productCategories' => $productCategories,
            'hasPending' => (bool) $pending,
            'pendingOrder' => $pending,
            'approvedOrder' => $approved,
            'quotedOrder' => $quoted,
        ]);
    }

    public function accept(Order $order)
    {
        if ($order->user_id !== Auth::id() || $order->status !== 'quoted') {
            abort(403);
        }

        $materialAllocations = $order->material_allocations;
        $allocations = $materialAllocations['allocations'] ?? [];
        $manualMaterials = $materialAllocations['manual_materials'] ?? [];
        $requirements = $materialAllocations['requirements'] ?? [];

        try {
            DB::transaction(function () use ($order, $allocations, $manualMaterials, $requirements) {
                // Deduct materials; auto-clamp to available and track shortfalls
                foreach ($allocations as $materialId => $qty) {
                    /** @var Material $material */
                    $material = Material::findOrFail($materialId);
                    $current = (float) $material->quantity;
                    $requested = (float) $qty;
                    $allocate = min($requested, $current);
                    $material->quantity = max($current - $allocate, 0);
                    $material->save();

                    // Log inventory transaction for Materials Out / Used
                    if ($allocate > 0) {
                        InventoryTransaction::create([
                            'subject_type' => 'material',
                            'subject_id'   => (int) $material->id,
                            'type'         => 'out',
                            'quantity'     => (float) $allocate,
                            'unit'         => $material->unit ?? null,
                            'name'         => $material->name ?? null,
                            'notes'        => 'Used in order ' . ($order->order_number ?? ''),
                            'created_by'   => Auth::id(),
                        ]);
                    }
                }

                // Persist new material-to-product mappings for manually added materials
                $orderedQtyByProduct = [];
                foreach ($order->items as $it) {
                    if ($it->product_id) {
                        $orderedQtyByProduct[$it->product_id] = ($orderedQtyByProduct[$it->product_id] ?? 0) + (int) $it->qty;
                    }
                }
                if (!empty($manualMaterials)) {
                    foreach ($manualMaterials as $mRow) {
                        $mid = (int) ($mRow['id'] ?? 0);
                        $pid = isset($mRow['product_id']) ? (int) $mRow['product_id'] : 0;
                        $postedQty = (float) ($mRow['qty'] ?? 0);
                        $postedReq = isset($mRow['required']) ? (float) $mRow['required'] : null;
                        $basis = ($postedReq !== null && $postedReq > 0) ? $postedReq : $postedQty;
                        if ($mid > 0 && $pid > 0 && $basis > 0 && isset($orderedQtyByProduct[$pid]) && $orderedQtyByProduct[$pid] > 0) {
                            $perUnit = round($basis / (float) $orderedQtyByProduct[$pid], 6);
                            $product = Product::find($pid);
                            if ($product) {
                                $exists = $product->materials()->where('materials.id', $mid)->exists();
                                if (!$exists) {
                                    $product->materials()->attach($mid, ['quantity' => $perUnit]);
                                }
                            }
                        }
                    }
                }

                // Update Status
                $order->update([
                    'status' => 'approved',
                    'delivery_status' => 'preparing',
                ]);
            });
        } catch (\Throwable $e) {
            return back()->withErrors(['accept' => 'Failed to accept quotation: ' . $e->getMessage()]);
        }

        return redirect()->route('client.purchase-requests.payment')
            ->with('status', 'Quotation accepted. You can now proceed to payment.');
    }

    public function cancel(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id() || $order->status !== 'quoted') {
            abort(403);
        }

        $validated = $request->validate([
            'cancellation_reason' => ['required', 'string'],
        ]);

        $order->update([
            'status' => 'cancelled',
            'delivery_status' => 'cancelled',
            'cancellation_reason' => $validated['cancellation_reason'],
        ]);

        return redirect()->route('client.purchase-requests.select-category')
            ->with('status', 'Purchase Request cancelled.');
    }

    /**
     * Step 2: Create PR for selected Category (show products in that category).
     */
    public function createByCategory(Category $category)
    {
        // Enforce single active PR per client
        $pending = $this->getPendingPRForUser(Auth::id());
        if ($pending) {
            return redirect()->route('client.purchase-requests.select-category')
                ->with('status', 'You already have a pending Purchase Request #' . $pending->order_number . '.');
        }

        $products = Product::where('category', $category->name)->orderBy('name')->get();
        return view('client.purchase-requests.create', [
            'category' => $category,
            'products' => $products,
        ]);
    }

    /**
     * Store a newly created Purchase Request for client.
     * Validates items and persists a zero-priced, pending order via CheckoutRepository.
     */
    public function store(Request $request)
    {
        // Enforce single active PR per client
        $pending = $this->getPendingPRForUser(Auth::id());
        if ($pending) {
            return redirect()->route('client.purchase-requests.select-category')
                ->withErrors(['items' => 'You already have a pending Purchase Request #' . $pending->order_number . '.'])
                ->with('status', 'You already have a pending Purchase Request #' . $pending->order_number . '.');
        }

        $data = $request->validate([
            'purpose' => ['required', 'string'],
            'items' => ['required'], // accept JSON string
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        // Decode items payload if JSON
        $rawItems = $data['items'];
        if (is_string($rawItems)) {
            $decoded = json_decode($rawItems, true);
            $rawItems = is_array($decoded) ? $decoded : [];
        }
        if (!is_array($rawItems) || empty($rawItems)) {
            return back()->withErrors(['items' => 'Please select items and quantities.'])->withInput();
        }

        // Normalize and validate rows; only accept products for PR
        $lineItems = [];
        $total = 0.0; // For PRs, there is no price initially
        foreach ($rawItems as $row) {
            $type = (string) ($row['type'] ?? '');
            $productId = (int) ($row['id'] ?? 0);
            $qty = (int) ($row['qty'] ?? 0);
            if ($type !== 'products' || $productId <= 0 || $qty < 1) {
                return back()->withErrors(['items' => 'Invalid item selection or quantity.'])->withInput();
            }

            $product = Product::find($productId);
            if (!$product) {
                return back()->withErrors(['items' => 'Selected product not found.'])->withInput();
            }

            // For PRs, do NOT set price yet; admin will price later
            $price = 0.0;
            $lineTotal = 0.0;
            $lineItems[] = [
                'product_id' => $product->id,
                'name' => (string) $product->name,
                'qty' => $qty,
                'price' => $price,
                'line_total' => $lineTotal,
            ];
            // Keep total at 0 for PR initial state
        }

        // Build order data; mark as pending and skip stock handling
        $user = Auth::user();
        // Optional quotation attachment upload
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            try {
                $attachmentPath = $request->file('attachment')->store('order-attachments', 'public');
            } catch (\Throwable $e) {
                return back()->withErrors(['attachment' => 'Failed to upload attachment: ' . $e->getMessage()])->withInput();
            }
        }
        $orderData = [
            'order_number' => 'PR-' . now()->format('YmdHis') . '-' . random_int(100, 999),
            'customer_name' => $user?->name ? ('PR by ' . $user->name) : 'Purchase Request',
            'customer_email' => $user?->email ?? null,
            'total' => $total,
            'downpayment' => 0,
            'status' => 'pending',
            'delivery_status' => 'pending',
            'user_id' => Auth::id(),
            'user_address_id' => null,
            'attachment_path' => $attachmentPath,
            'purpose' => (string) ($data['purpose'] ?? ''),
        ];

        try {
            // Skip stock validation/deduction when saving PRs
            $order = $this->checkoutRepo->processCheckout($orderData, $lineItems, /* materialRequirements */ [], /* skipStock */ true);

            return redirect()->route('client.purchase-requests.select-category')
                ->with('status', 'Purchase Request #' . $order->order_number . ' saved (pending).');
        } catch (\Throwable $e) {
            return back()->withErrors(['checkout' => 'Failed to save Purchase Request: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Get the latest pending Purchase Request (Order) for user if any.
     */
    protected function getPendingPRForUser(?int $userId): ?Order
    {
        if (!$userId) { return null; }
        return Order::where('user_id', $userId)
            ->where('order_number', 'like', 'PR-%')
            ->where('status', 'pending')
            ->latest()
            ->first();
    }

    /**
     * Get the latest approved Purchase Request (Order) for user if any.
     */
    protected function getApprovedPRForUser(?int $userId): ?Order
    {
        if (!$userId) { return null; }
        return Order::where('user_id', $userId)
            ->where('order_number', 'like', 'PR-%')
            ->where('status', 'approved')
            ->latest()
            ->first();
    }

    /**
     * Start PayMongo checkout for 10% downpayment of the latest approved PR.
     */
    public function paymongoStartDownpayment(Request $request)
    {
        $order = $this->getApprovedPRForUser(Auth::id());
        if (!$order) {
            return redirect()->route('client.purchase-requests.select-category')
                ->with('error', 'No approved Purchase Request found.');
        }

        $total = (float) ($order->total ?? 0);
        if ($total <= 0) {
            return redirect()->route('client.purchase-requests.select-category')
                ->with('error', 'Approved order has no total set yet.');
        }

        $requiredDown = round($total * 0.10, 2);
        $alreadyDown = (float) ($order->downpayment ?? 0);
        if ($alreadyDown >= $requiredDown - 0.009) { // allow minor floating diff
            return redirect()->route('client.purchase-requests.select-category')
                ->with('status', 'Downpayment already recorded for order #' . $order->order_number . '.');
        }

        // Prepare PayMongo Checkout Session for GCash
        $successUrl = route('client.purchase-requests.paymongo.success');
        $cancelUrl = route('client.purchase-requests.paymongo.cancel');
        $description = '10% Downpayment for PR ' . $order->order_number;
        $reference = 'PRDP-' . now()->format('YmdHis') . '-' . random_int(1000, 9999);

        $amountCentavos = (int) round($requiredDown * 100);
        $payload = [
            'data' => [
                'attributes' => [
                    'line_items' => [[
                        'name' => $description,
                        'quantity' => 1,
                        'amount' => $amountCentavos,
                        'currency' => 'PHP',
                    ]],
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
                return redirect()->route('client.purchase-requests.select-category')->with('error', 'PayMongo error: ' . $err);
            }

            $data = $response->json('data');
            $checkoutUrl = $data['attributes']['checkout_url'] ?? null;
            $csId = $data['id'] ?? null;
            if (!$checkoutUrl || !$csId) {
                return redirect()->route('client.purchase-requests.select-category')->with('error', 'Invalid PayMongo response.');
            }

            // Persist session details to complete payment after successful checkout
            session()->put('paymongo.checkout_session_id', $csId);
            session()->put('paymongo.order_id', $order->id);
            session()->put('paymongo.downpayment_amount', $requiredDown);
            session()->put('paymongo.dp.reference_number', $reference);

            if ($request->header('HX-Request')) {
                return response('', 204)->header('HX-Redirect', $checkoutUrl);
            }
            return redirect()->away($checkoutUrl);
        } catch (\Throwable $e) {
            return redirect()->route('client.purchase-requests.select-category')->with('error', $e->getMessage());
        }
    }

    /**
     * PayMongo success handler: verify payment, record Payment, and update order downpayment.
     */
    public function paymongoDownpaymentSuccess()
    {
        $csId = session()->get('paymongo.checkout_session_id');
        $orderId = session()->get('paymongo.order_id');
        $amount = (float) session()->get('paymongo.downpayment_amount');
        if (!$csId || !$orderId) {
            return redirect()->route('client.purchase-requests.select-category')->with('error', 'Missing checkout session.');
        }

        try {
            $resp = Http::withBasicAuth(config('services.paymongo.secret'), '')
                ->acceptJson()
                ->get(rtrim(config('services.paymongo.base_url'), '/') . '/v1/checkout_sessions/' . $csId);

            if (!$resp->successful()) {
                $err = $resp->json('errors.0.detail') ?? $resp->body();
                return redirect()->route('client.purchase-requests.select-category')->with('error', 'Payment verification error: ' . $err);
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
                return redirect()->route('client.purchase-requests.select-category')->with('error', 'Payment not completed.');
            }
        } catch (\Throwable $e) {
            return redirect()->route('client.purchase-requests.select-category')->with('error', 'Payment verification failed: ' . $e->getMessage());
        }

        // Clear session values
        session()->forget('paymongo.checkout_session_id');
        session()->forget('paymongo.order_id');
        session()->forget('paymongo.downpayment_amount');
        session()->forget('paymongo.dp.reference_number');

        $order = Order::find($orderId);
        if (!$order) {
            return redirect()->route('client.purchase-requests.select-category')->with('error', 'Order not found.');
        }

        // Record payment and update downpayment field
        Payment::create([
            'order_id' => $order->id,
            'provider' => 'paymongo',
            'method' => 'gcash',
            'amount' => $amount,
            'currency' => 'PHP',
            // Use our DP reference prefix to allow exclusion from "paid sum"
            'reference' => session()->get('paymongo.dp.reference_number') ?? ('PRDP-' . now()->format('YmdHis')),
            'notes' => 'cs:' . $csId,
            'paid_at' => now(),
        ]);

        $order->downpayment = (float) ($order->downpayment ?? 0) + $amount;
        $order->save();

        return redirect()->route('client.orders.show', $order)->with('status', 'Downpayment paid successfully for PR ' . $order->order_number . '.');
    }

    public function paymongoDownpaymentCancel()
    {
        session()->forget('paymongo.checkout_session_id');
        session()->forget('paymongo.order_id');
        session()->forget('paymongo.downpayment_amount');
        return redirect()->route('client.purchase-requests.select-category')->with('error', 'Payment canceled.');
    }

    /**
     * Show PR Payment page with payment records and downpayment status.
     */
    public function payment(Request $request)
    {
        $pending = $this->getPendingPRForUser(Auth::id());
        $approved = $this->getApprovedPRForUser(Auth::id());

        $payments = collect();
        if ($approved) {
            $payments = Payment::where('order_id', $approved->id)->latest()->get();
        }

        return view('client.purchase-requests.payment', [
            'pendingOrder' => $pending,
            'approvedOrder' => $approved,
            'payments' => $payments,
        ]);
    }

    /**
     * Show PR History list for the authenticated client.
     */
    public function history(Request $request)
    {
        $orders = \App\Models\Order::where('user_id', Auth::id())
            ->where('order_number', 'like', 'PR-%')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('client.purchase-requests.history', [
            'orders' => $orders,
        ]);
    }
    /**
     * Start PayMongo checkout for remaining balance of the latest approved PR.
     */
    public function paymongoStartRemaining(Request $request)
    {
        // Allow paying remaining for a specific approved PR selected from history
        $orderId = $request->input('order_id');
        if ($orderId) {
            $order = Order::where('id', $orderId)
                ->where('user_id', Auth::id())
                ->where('order_number', 'like', 'PR-%')
                ->where('status', 'approved')
                ->first();
        } else {
            $order = $this->getApprovedPRForUser(Auth::id());
        }
        if (!$order) {
            return redirect()->route('client.purchase-requests.select-category')
                ->with('error', 'No approved Purchase Request found.');
        }

        $total = (float) ($order->total ?? 0);
        $paidTotal = (float) ($order->downpayment ?? 0);
        $remaining = round(max(0.0, $total - $paidTotal), 2);
        if ($remaining <= 0) {
            return redirect()->route('client.purchase-requests.payment')
                ->with('status', 'No remaining balance to pay for order #' . $order->order_number . '.');
        }

        $successUrl = route('client.purchase-requests.paymongo.remaining.success');
        $cancelUrl = route('client.purchase-requests.paymongo.remaining.cancel');
        $description = 'Remaining Balance for PR ' . $order->order_number;
        $reference = 'PRRB-' . now()->format('YmdHis') . '-' . random_int(1000, 9999);

        $amountCentavos = (int) round($remaining * 100);
        $payload = [
            'data' => [
                'attributes' => [
                    'line_items' => [[
                        'name' => $description,
                        'quantity' => 1,
                        'amount' => $amountCentavos,
                        'currency' => 'PHP',
                    ]],
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
                return redirect()->route('client.purchase-requests.payment')->with('error', 'PayMongo error: ' . $err);
            }

            $data = $response->json('data');
            $checkoutUrl = $data['attributes']['checkout_url'] ?? null;
            $csId = $data['id'] ?? null;
            if (!$checkoutUrl || !$csId) {
                return redirect()->route('client.purchase-requests.payment')->with('error', 'Invalid PayMongo response.');
            }

            // Persist session details to complete payment after successful checkout
            session()->put('paymongo.remaining.checkout_session_id', $csId);
            session()->put('paymongo.remaining.order_id', $order->id);
            session()->put('paymongo.remaining_amount', $remaining);
            session()->put('paymongo.remaining.reference_number', $reference);

            if ($request->header('HX-Request')) {
                return response('', 204)->header('HX-Redirect', $checkoutUrl);
            }
            return redirect()->away($checkoutUrl);
        } catch (\Throwable $e) {
            return redirect()->route('client.purchase-requests.payment')->with('error', $e->getMessage());
        }
    }

    /**
     * PayMongo success handler for remaining balance: verify payment and update order.
     */
    public function paymongoRemainingSuccess()
    {
        $csId = session()->get('paymongo.remaining.checkout_session_id');
        $orderId = session()->get('paymongo.remaining.order_id');
        $amount = (float) session()->get('paymongo.remaining_amount');
        if (!$csId || !$orderId) {
            return redirect()->route('client.purchase-requests.payment')->with('error', 'Missing checkout session.');
        }

        try {
            $resp = Http::withBasicAuth(config('services.paymongo.secret'), '')
                ->acceptJson()
                ->get(rtrim(config('services.paymongo.base_url'), '/') . '/v1/checkout_sessions/' . $csId);

            if (!$resp->successful()) {
                $err = $resp->json('errors.0.detail') ?? $resp->body();
                return redirect()->route('client.purchase-requests.payment')->with('error', 'Payment verification error: ' . $err);
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
                return redirect()->route('client.purchase-requests.payment')->with('error', 'Payment not completed.');
            }
        } catch (\Throwable $e) {
            return redirect()->route('client.purchase-requests.payment')->with('error', 'Payment verification failed: ' . $e->getMessage());
        }

        // Clear session values
        session()->forget('paymongo.remaining.checkout_session_id');
        session()->forget('paymongo.remaining.order_id');
        session()->forget('paymongo.remaining_amount');
        session()->forget('paymongo.remaining.reference_number');

        $order = Order::find($orderId);
        if (!$order) {
            return redirect()->route('client.purchase-requests.payment')->with('error', 'Order not found.');
        }

        // Record payment and update downpayment (acts as total paid tracker)
        Payment::create([
            'order_id' => $order->id,
            'provider' => 'paymongo',
            'method' => 'gcash',
            'amount' => $amount,
            'currency' => 'PHP',
            // Use our remaining balance reference prefix for consistency
            'reference' => session()->get('paymongo.remaining.reference_number') ?? ('PRRB-' . now()->format('YmdHis')),
            'notes' => 'cs:' . $csId,
            'paid_at' => now(),
        ]);

        $order->downpayment = (float) ($order->downpayment ?? 0) + $amount;
        $order->save();

        return redirect()->route('client.orders.show', $order)->with('status', 'Remaining balance paid successfully for PR ' . $order->order_number . '.');
    }

    public function paymongoRemainingCancel()
    {
        session()->forget('paymongo.remaining.checkout_session_id');
        session()->forget('paymongo.remaining.order_id');
        session()->forget('paymongo.remaining_amount');
        return redirect()->route('client.purchase-requests.payment')->with('error', 'Remaining payment canceled.');
    }

}
