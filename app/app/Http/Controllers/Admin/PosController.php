<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\PosRepository;
use App\Services\CartService;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Services\PosCheckoutService;
use App\Http\Requests\PosCheckoutRequest;
use App\Services\PosService;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Traits\HandlesPosErrors;
use Illuminate\Support\Facades\Route as RouteFacade;

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
}