<?php

namespace App\Http\Controllers;

use App\Http\Requests\MaterialRequest;
use App\Http\Requests\StockInRequest;
use App\Http\Requests\MaterialRequestFormRequest;
use App\Http\Requests\StockOutRequest;
use App\Repositories\MaterialRepositoryInterface;
use App\Services\MaterialService;
use Illuminate\Http\Request;
use App\Models\Material;
use Illuminate\Http\JsonResponse as HttpJsonResponse;
use App\Traits\ResponseHelpers;
use App\Repositories\SupplierRepositoryInterface;

class MaterialController extends BaseController
{
    use ResponseHelpers;

    /**
     * MaterialController constructor.
     */
    public function __construct(
        /* Type removed to avoid conflict with parent */ MaterialRepositoryInterface $repository,
        protected MaterialService $service,
        protected SupplierRepositoryInterface $suppliers,
    ) {
        $this->repository = $repository;
        $this->viewPath = 'materials';
        $this->routePrefix = 'materials';
        $this->resourceName = 'Material';
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response|HttpJsonResponse
     */
    public function create()
    {
        $suppliers = $this->suppliers->all();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Create form data',
                'resourceName' => $this->resourceName,
                'suppliers' => $suppliers,
            ]);
        }

        return view($this->viewPath . '.create', [
            'resourceName' => $this->resourceName,
            'suppliers' => $suppliers,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response|HttpJsonResponse
     */
    public function edit($id)
    {
        $item = $this->repository->find($id);
        $suppliers = $this->suppliers->all();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'item' => $item,
                    'suppliers' => $suppliers,
                    'resourceName' => $this->resourceName,
                ]
            ]);
        }

        return view($this->viewPath . '.edit', [
            'item' => $item,
            'suppliers' => $suppliers,
            'resourceName' => $this->resourceName,
        ]);
    }

    /**
     * Display materials by category.
     *
     * @param string|null $category
     * @return \Illuminate\Http\Response|HttpJsonResponse
     */
    public function byCategory(?string $category = null)
    {
        $category = $category ?? request('category');
        $data = $this->service->byCategoryData($category);
        $items = $data['items'];

        if (request()->wantsJson()) {
            return $this->successResponse([
                'items' => $items,
                'category' => $category,
                'categories' => $data['categories']
            ]);
        }

        return view($this->viewPath . '.by-category', [
            'items' => $items,
            'category' => $category,
            'categories' => $data['categories'],
            'resourceName' => $this->resourceName
        ]);
    }

    /**
     * Show low stock report.
     *
     * @return \Illuminate\Http\Response|HttpJsonResponse
     */
    public function lowStock()
    {
        $data = $this->service->lowStockData();
        $items = $data['items'];

        if (request()->wantsJson()) {
            return $this->successResponse(['items' => $items]);
        }

        return view($this->viewPath . '.low-stock', [
            'items' => $items,
            'resourceName' => $this->resourceName
        ]);
    }

    /**
     * Show request form for materials.
     *
     * @return \Illuminate\Http\Response|HttpJsonResponse
     */
    public function showRequestForm()
    {
        $data = $this->service->requestFormData();

        if (request()->wantsJson()) {
            return $this->successResponse($data);
        }

        return view($this->viewPath . '.request-form', [
            'materials' => $data['materials'],
            'resourceName' => $this->resourceName
        ]);
    }

    /**
     * Submit a material request.
     *
     * @param MaterialRequestFormRequest $request
     * @return \Illuminate\Http\Response|HttpJsonResponse
     */
    public function submitRequest(MaterialRequestFormRequest $request)
    {
        $message = $this->service->submitRequest((int) $request->input('material_id'));

        return $this->respondWith(null, $message, $this->routePrefix . '.index');
    }

    /**
     * Validate the request using MaterialRequest
     *
     * @param Request $request
     * @param int|null $id
     * @return array
     */
    protected function validateRequest(Request $request, $id = null)
    {
        // Validate using MaterialRequest rules when FormRequest binding is not used
        $rules = (new MaterialRequest())->rules();
        return $request->validate($rules);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response|HttpJsonResponse
     */
    public function store(Request $request)
    {
        return parent::store($request);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response|HttpJsonResponse
     */
    public function update(Request $request, $id)
    {
        return parent::update($request, $id);
    }

    /**
     * Show the form for adding stock to a material.
     *
     * @param int $id
     * @return \Illuminate\Http\Response|HttpJsonResponse
     */
    public function showStockInForm($id)
    {
        $item = $this->service->stockInFormData($id);
        
        if (request()->wantsJson()) {
            return $this->successResponse($item);
        }
        
        return view($this->viewPath . '.stock-in', [
            'item' => $item,
            'resourceName' => $this->resourceName
        ]);
    }

    /**
     * Process the stock-in request.
     *
     * @param StockInRequest $request
     * @param int $id
     * @return \Illuminate\Http\Response|HttpJsonResponse
     */
    public function stockIn(StockInRequest $request, $id)
    {
        $validated = $request->validated();

        $this->service->stockIn($id, $validated['quantity'], $validated['notes'] ?? null);
        
        $message = 'Stock added successfully to ' . $this->resourceName;
        return $this->respondWith(null, $message, $this->routePrefix . '.index');
    }

    /**
     * Show the form for stock out.
     *
     * @param int $id
     * @return \Illuminate\Http\Response|HttpJsonResponse
     */
    public function showStockOutForm($id)
    {
        $item = $this->service->stockOutFormData($id);

        if (request()->wantsJson()) {
            return $this->successResponse($item);
        }

        return view($this->viewPath . '.stock-out', [
            'item' => $item,
            'resourceName' => $this->resourceName
        ]);
    }

    /**
     * Process the stock-out request.
     *
     * @param StockOutRequest $request
     * @param int $id
     * @return \Illuminate\Http\Response|HttpJsonResponse
     */
    public function stockOut(StockOutRequest $request, $id)
    {
        $validated = $request->validated();

        try {
            $material = $this->service->stockOut($id, $validated['quantity']);

            $message = $material->quantity == 0
                ? 'Stock deducted successfully. Material is now out of stock.'
                : 'Stock deducted successfully from ' . $this->resourceName;

            return $this->respondWith(null, $message, $this->routePrefix . '.index');
        } catch (\RuntimeException $e) {
            // Map repository error for insufficient stock into a friendly message
            $raw = $e->getMessage();
            if (is_string($raw) && str_starts_with($raw, 'INSUFFICIENT_STOCK:')) {
                $json = substr($raw, strlen('INSUFFICIENT_STOCK:'));
                $details = json_decode($json, true) ?: [];
                $msg = 'Insufficient stock to deduct the requested quantity.';
                if (!empty($details) && is_array($details)) {
                    $first = $details[0] ?? null;
                    if (is_array($first)) {
                        $msg = sprintf(
                            'Insufficient stock: %s requires %.2f %s, only %.2f %s available.',
                            $first['name'] ?? 'Material',
                            (float) ($first['required'] ?? 0),
                            $first['unit'] ?? '',
                            (float) ($first['available'] ?? 0),
                            $first['unit'] ?? ''
                        );
                    }
                }
                return $this->respondWithError($msg, $this->routePrefix . '.stock-out.form', ['material' => $id]);
            }
            return $this->respondWithError('Stock deduction failed: ' . $e->getMessage(), $this->routePrefix . '.stock-out.form', ['material' => $id]);
        } catch (\Throwable $e) {
            return $this->respondWithError('Stock deduction failed. Please try again.', $this->routePrefix . '.stock-out.form', ['material' => $id]);
        }
    }

    /**
     * Display materials that are low in stock.
     *
     * @return \Illuminate\Http\Response|HttpJsonResponse
     */
    public function index()
    {
        $data = $this->service->indexData();
        $items = $data['items'];
        $categories = $data['categories'];
        $metrics = $data['metrics'] ?? null;

        if (request()->wantsJson()) {
            return $this->successResponse([
                'items' => $items,
                'categories' => $categories,
                'metrics' => $metrics,
            ]);
        }

        return view($this->viewPath . '.index', [
            'items' => $items,
            'categories' => $categories,
            'metrics' => $metrics,
            'resourceName' => $this->resourceName
        ]);
    }
}
