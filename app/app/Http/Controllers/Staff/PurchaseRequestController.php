<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Repositories\CheckoutRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PurchaseRequestController extends Controller
{
    public function __construct(protected CheckoutRepository $checkoutRepo) {}
    /**
     * Step 1: Choose a Category.
     */
    public function selectCategory(Request $request)
    {
        $categories = Category::where('status', 'active')->orderBy('name')->get();
        return view('staff.purchase-requests.select-category', [
            'categories' => $categories,
        ]);
    }

    /**
     * Step 2: Create PR for selected Category (show products in that category).
     */
    public function createByCategory(Category $category)
    {
        $products = Product::where('category', $category->name)->orderBy('name')->get();
        return view('staff.purchase-requests.create', [
            'category' => $category,
            'products' => $products,
        ]);
    }

    /**
     * Store a newly created Purchase Request. For now, validate and acknowledge.
     * A full implementation would persist PR and items and mark status as pending.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'purpose' => ['required', 'string'],
            'items' => ['required'], // accept JSON string
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
            'attachment_path' => null,
        ];

        try {
            // Skip stock validation/deduction when saving PRs
            $order = $this->checkoutRepo->processCheckout($orderData, $lineItems, /* materialRequirements */ [], /* skipStock */ true);

            return redirect()->route('staff.purchase-requests.select-category')
                ->with('status', 'Purchase Request #' . $order->order_number . ' saved (pending).');
        } catch (\Throwable $e) {
            return back()->withErrors(['checkout' => 'Failed to save Purchase Request: ' . $e->getMessage()])->withInput();
        }
    }
}