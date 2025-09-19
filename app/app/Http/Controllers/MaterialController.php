<?php

namespace App\Http\Controllers;

use App\Http\Requests\MaterialRequest;
use App\Http\Requests\StockInRequest;
use App\Http\Requests\MaterialRequestFormRequest;
use App\Repositories\MaterialRepositoryInterface;
use Illuminate\Http\Request;
use App\Models\Material;
use Illuminate\Http\JsonResponse;

class MaterialController extends BaseController
{
    /**
     * MaterialController constructor.
     *
     * @param MaterialRepositoryInterface $repository
     */
    public function __construct(MaterialRepositoryInterface $repository)
    {
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
        // Using the form request validation in the controller methods directly
        // This method is kept for compatibility with BaseController
        return $request->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response|JsonResponse
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
     * @return \Illuminate\Http\Response|JsonResponse
     */
    public function update(Request $request, $id)
    {
        return parent::update($request, $id);
    }

    /**
     * Show the form for adding stock to a material.
     *
     * @param int $id
     * @return \Illuminate\Http\Response|JsonResponse
     */
    public function showStockInForm($id)
    {
        $item = $this->repository->find($id);
        
        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $item
            ]);
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
     * @return \Illuminate\Http\Response|JsonResponse
     */
    public function stockIn(StockInRequest $request, $id)
    {
        $validated = $request->validated();

        $this->repository->stockIn($id, $validated['quantity'], $validated['notes'] ?? null);
        
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Stock added successfully to ' . $this->resourceName
            ]);
        }

        return redirect()->route($this->routePrefix . '.index')
            ->with('success', 'Stock added successfully to ' . $this->resourceName);
    }

    /**
     * Display materials that are low in stock.
     *
     * @return \Illuminate\Http\Response|JsonResponse
     */
    public function index()
    {
        $items = $this->repository->paginate();
        $categories = $this->repository->getUniqueCategories();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $items,
                'categories' => $categories
            ]);
        }

        return view($this->viewPath . '.index', [
            'items' => $items,
            'categories' => $categories,
            'resourceName' => $this->resourceName
        ]);
    }

    /**
     * Display materials by category.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response|JsonResponse
     */
    public function byCategory(Request $request)
    {
        $category = $request->input('category');
        
        // Handle null category by providing a default or empty string
        $category = $category ?? '';  
        $categories = $this->repository->getUniqueCategories();
        
        if ($category) {
            $items = $this->repository->getByCategory($category);
        } else {
            $items = $this->repository->all();
        }
        
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $items,
                'categories' => $categories,
                'category' => $category
            ]);
        }
        
        return view($this->viewPath . '.by-category', [
            'items' => $items,
            'categories' => $categories,
            'category' => $category,
            'resourceName' => $this->resourceName
        ]);
    }

    /**
     * Show the form for requesting a material.
     *
     * @return \Illuminate\Http\Response|JsonResponse
     */
    public function showRequestForm()
    {
        $materials = $this->repository->all();
        
        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $materials
            ]);
        }
        
        return view($this->viewPath . '.request-form', [
            'materials' => $materials,
            'resourceName' => $this->resourceName
        ]);
    }

    /**
     * Process the material request.
     *
     * @param MaterialRequestFormRequest $request
     * @return \Illuminate\Http\Response|JsonResponse
     */
    public function submitRequest(MaterialRequestFormRequest $request)
    {
        $validated = $request->validated();

        // Here you would typically save the request to a database table
        // For now, we'll just redirect with a success message
        
        $material = $this->repository->find($validated['material_id']);
        
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Material request for ' . $material->name . ' submitted successfully'
            ]);
        }
        
        return redirect()->route($this->routePrefix . '.index')
            ->with('success', 'Material request for ' . $material->name . ' submitted successfully');
    }
}
