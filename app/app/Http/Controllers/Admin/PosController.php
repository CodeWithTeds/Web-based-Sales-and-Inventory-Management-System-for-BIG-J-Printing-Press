<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\PosRepository;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Material;

class PosController extends Controller
{
    public function __construct(
        protected PosRepository $repo,
        protected CartService $cart
    ) {}

    public function index(Request $request)
    {
        $category = $request->query('category');
        $search = $request->query('search', '');

        if ($request->header('HX-Request')) {
            // Partial response for htmx
            return response()->view('admin.partials.pos-cart', [
                'products' => $this->repo->getProducts($category, $search),
                'categories' => $this->repo->getCategories(),
                'cart' => $this->cart->all(),
                'total' => $this->cart->total(),
                'itemCount' => $this->cart->itemCount(),
                'category' => $category,
                'search' => $search,
            ]);
        }

        return view('admin.pos-blade', [
            'products' => $this->repo->getProducts($category, $search),
            'categories' => $this->repo->getCategories(),
            'cart' => $this->cart->all(),
            'total' => $this->cart->total(),
            'itemCount' => $this->cart->itemCount(),
            'category' => $category,
            'search' => $search,
        ]);
    }

    public function add(Request $request, int $product)
    {
        $this->cart->add($product);

        if ($request->header('HX-Request')) {
            return response()->view('admin.partials.pos-cart', [
                'cart' => $this->cart->all(),
                'total' => $this->cart->total(),
                'itemCount' => $this->cart->itemCount(),
                'success' => null,
            ]);
        }

        return back();
    }

    public function increment(Request $request, int $product)
    {
        $this->cart->increment($product);

        if ($request->header('HX-Request')) {
            return response()->view('admin.partials.pos-cart', [
                'cart' => $this->cart->all(),
                'total' => $this->cart->total(),
                'itemCount' => $this->cart->itemCount(),
                'success' => null,
            ]);
        }

        return back();
    }

    public function decrement(Request $request, int $product)
    {
        $this->cart->decrement($product);

        if ($request->header('HX-Request')) {
            return response()->view('admin.partials.pos-cart', [
                'cart' => $this->cart->all(),
                'total' => $this->cart->total(),
                'itemCount' => $this->cart->itemCount(),
                'success' => null,
            ]);
        }

        return back();
    }

    public function remove(Request $request, int $product)
    {
        $this->cart->remove($product);

        if ($request->header('HX-Request')) {
            return response()->view('admin.partials.pos-cart', [
                'cart' => $this->cart->all(),
                'total' => $this->cart->total(),
                'itemCount' => $this->cart->itemCount(),
                'success' => null,
            ]);
        }

        return back();
    }

    public function clear(Request $request)
    {
        $this->cart->clear();

        if ($request->header('HX-Request')) {
            return response()->view('admin.partials.pos-cart', [
                'cart' => $this->cart->all(),
                'total' => $this->cart->total(),
                'itemCount' => $this->cart->itemCount(),
                'success' => null,
            ]);
        }

        return back();
    }

    public function checkout(Request $request)
    {
        $cart = $this->cart->all();
        if (empty($cart)) {
            if ($request->header('HX-Request')) {
                return response()->view('admin.partials.pos-cart', [
                    'cart' => $cart,
                    'total' => 0,
                    'itemCount' => 0,
                    'success' => null,
                    'error' => 'Cart is empty.',
                ], 422);
            }
            return back()->withErrors(['cart' => 'Cart is empty.']);
        }

        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
        ]);

        try {
            $order = DB::transaction(function () use ($cart, $data) {
                // Build material requirements map: material_id => total_required_qty
                $requirements = [];
                foreach ($cart as $item) {
                    if (!isset($item['id'], $item['qty'])) {
                        // Skip malformed cart item
                        continue;
                    }
                    $product = Product::with('materials')->findOrFail((int) $item['id']);
                    foreach ($product->materials as $material) {
                        $required = (float) $material->pivot->quantity * (int) $item['qty'];
                        $requirements[$material->id] = ($requirements[$material->id] ?? 0) + $required;
                    }
                }

                // Lock materials for update to prevent race conditions
                $materials = empty($requirements)
                    ? collect([])
                    : Material::whereIn('id', array_keys($requirements))->lockForUpdate()->get();

                // Validate stock availability
                $insufficient = [];
                foreach ($materials as $mat) {
                    $required = (float) ($requirements[$mat->id] ?? 0);
                    if ($required > 0 && (float) $mat->quantity < $required) {
                        $insufficient[] = [
                            'name' => $mat->name,
                            'required' => $required,
                            'available' => (float) $mat->quantity,
                            'unit' => $mat->unit ?? '',
                        ];
                    }
                }

                if (!empty($insufficient)) {
                    throw new \RuntimeException('INSUFFICIENT_STOCK:' . json_encode($insufficient));
                }

                // Calculate totals
                $total = 0;
                foreach ($cart as $item) {
                    $total += $item['price'] * $item['qty'];
                }

                // Create order
                $order = Order::create([
                    'order_number' => 'POS-' . now()->format('YmdHis') . '-' . random_int(100, 999),
                    'customer_name' => $data['customer_name'],
                    'total' => $total,
                    'status' => 'completed',
                    'user_id' => Auth::id(),
                ]);

                // Create order items
                foreach ($cart as $item) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item['id'] ?? null,
                        'name' => $item['name'],
                        'qty' => $item['qty'],
                        'price' => $item['price'],
                        'line_total' => $item['price'] * $item['qty'],
                    ]);
                }

                // Deduct materials stock
                foreach ($materials as $mat) {
                    $required = (float) ($requirements[$mat->id] ?? 0);
                    if ($required > 0) {
                        $mat->quantity = (float) $mat->quantity - $required;
                        $mat->save();
                    }
                }

                return $order;
            });
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            if (strpos($msg, 'INSUFFICIENT_STOCK:') === 0) {
                $detailsJson = substr($msg, strlen('INSUFFICIENT_STOCK:'));
                $details = json_decode($detailsJson, true) ?: [];
                $errorLines = array_map(function ($d) {
                    $unit = isset($d['unit']) && $d['unit'] !== '' ? ' ' . $d['unit'] : '';
                    return sprintf(
                        '%s needs %.2f%s but only %.2f%s available',
                        $d['name'],
                        (float) $d['required'],
                        $unit,
                        (float) $d['available'],
                        $unit
                    );
                }, $details);
                $errorMessage = 'Insufficient material stock: ' . implode('; ', $errorLines) . '.';

                if ($request->header('HX-Request')) {
                    return response()->view('admin.partials.pos-cart', [
                        'cart' => $this->cart->all(),
                        'total' => $this->cart->total(),
                        'itemCount' => $this->cart->itemCount(),
                        'success' => null,
                        'error' => $errorMessage,
                    ], 422);
                }
                return back()->withErrors(['stock' => $errorMessage]);
            }

            // Unexpected fail
            if ($request->header('HX-Request')) {
                return response()->view('admin.partials.pos-cart', [
                    'cart' => $this->cart->all(),
                    'total' => $this->cart->total(),
                    'itemCount' => $this->cart->itemCount(),
                    'success' => null,
                    'error' => 'Checkout failed unexpectedly. Please try again.',
                ], 500);
            }
            return back()->withErrors(['checkout' => 'Checkout failed unexpectedly. Please try again.']);
        }

        // Clear cart only after successful transaction
        $this->cart->clear();

        if ($request->header('HX-Request')) {
            return response()->view('admin.partials.pos-cart', [
                'cart' => [],
                'total' => 0,
                'itemCount' => 0,
                'success' => 'Order placed successfully!',
                'orderId' => $order->id,
            ]);
        }

        return redirect()->route('admin.pos.receipt', $order);
    }

    public function receipt(Order $order)
    {
        $order->load('items');
        return view('admin.pos-receipt', [
            'order' => $order,
        ]);
    }

    public function receiptDownload(Order $order)
    {
        $order->load('items');
        $html = view('admin.pos-receipt', ['order' => $order, 'download' => true])->render();
        $filename = 'receipt-' . $order->order_number . '.html';
        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}