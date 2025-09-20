<?php

namespace App\Http\Controllers;

use App\Http\Requests\MaterialRequest;
use App\Http\Requests\StockInRequest;
use App\Http\Requests\MaterialRequestFormRequest;
use App\Repositories\MaterialRepositoryInterface;
use App\Services\MaterialService;
use Illuminate\Http\Request;
use App\Models\Material;
use Illuminate\Http\JsonResponse as HttpJsonResponse;
use App\Traits\ResponseHelpers;

class MaterialController extends BaseController
{
    use ResponseHelpers;

    /**
     * MaterialController constructor.
     */
    public function __construct(
        /* Type removed to avoid conflict with parent */ MaterialRepositoryInterface $repository,
        protected MaterialService $service
    ) {
        $this->repository = $repository;
        $this->viewPath = 'materials';
        $this->routePrefix = 'materials';
        $this->resourceName = 'Material';
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
        return $request->all();
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

    /**
     * Display materials by category.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response|HttpJsonResponse
     */
    public function byCategory(Request $request)
    {
        $category = $request->input('category') ?? '';
        $data = $this->service->byCategoryData($category);
        
        if ($request->wantsJson()) {
            return $this->successResponse([
                'items' => $data['items'],
                'categories' => $data['categories'],
                'category' => $category
            ]);
        }
        
        return view($this->viewPath . '.by-category', [
            'items' => $data['items'],
            'categories' => $data['categories'],
            'category' => $category,
            'resourceName' => $this->resourceName
        ]);
    }

    /**
     * Show the form for requesting a material.
     *
     * @return \Illuminate\Http\Response|HttpJsonResponse
     */
    public function showRequestForm()
    {
        $data = $this->service->requestFormData();
        
        if (request()->wantsJson()) {
            return $this->successResponse($data['materials']);
        }
        
        return view($this->viewPath . '.request-form', [
            'materials' => $data['materials'],
            'resourceName' => $this->resourceName
        ]);
    }

    /**
     * Display low-stock materials report.
     *
     * @return \Illuminate\Http\Response|HttpJsonResponse
     */
    public function lowStock()
    {
        $data = $this->service->lowStockData();

        if (request()->wantsJson()) {
            return $this->successResponse([
                'items' => $data['items'],
            ]);
        }

        return view($this->viewPath . '.low-stock', [
            'items' => $data['items'],
            'resourceName' => $this->resourceName,
        ]);
    }

    /**
     * Process the material request.
     *
     * @param MaterialRequestFormRequest $request
     * @return \Illuminate\Http\Response|HttpJsonResponse
     */
    public function submitRequest(MaterialRequestFormRequest $request)
    {
        $validated = $request->validated();

        $message = $this->service->submitRequest($validated['material_id']);
        
        return $this->respondWith(null, $message, $this->routePrefix . '.index');
    }
}
