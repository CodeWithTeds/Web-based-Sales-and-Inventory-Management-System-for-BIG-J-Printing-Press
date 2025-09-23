<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Http\Requests\ProductAddMaterialRequest;
use App\Repositories\ProductRepositoryInterface;
use App\Services\ProductService;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Http\JsonResponse as HttpJsonResponse;
use App\Traits\ResponseHelpers;

class ProductController extends BaseController
{
    use ResponseHelpers;

    /**
     * ProductController constructor.
     */
    public function __construct(
        /* Type removed to avoid conflict with parent */ ProductRepositoryInterface $repository,
        protected ProductService $service
    ) {
        $this->repository = $repository;
        $this->viewPath = 'products';
        $this->routePrefix = 'products';
        $this->resourceName = 'Product';
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response|HttpJsonResponse
     */
    public function index()
    {
        $data = $this->service->getIndexData();

        if (request()->wantsJson()) {
            return $this->successResponse([
                'items' => $data['items'],
                'categories' => $data['categories'],
                'metrics' => $data['metrics'] ?? null,
            ]);
        }

        return view($this->viewPath . '.index', [
            'items' => $data['items'],
            'categories' => $data['categories'],
            'metrics' => $data['metrics'] ?? null,
            'resourceName' => $this->resourceName
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response|JsonResponse
     */
    public function create()
    {
        $data = $this->service->getCreateData();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Create form data',
                'resourceName' => $this->resourceName,
                'materials' => $data['materials']
            ]);
        }

        return view($this->viewPath . '.create', [
            'resourceName' => $this->resourceName,
            'materials' => $data['materials']
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response|JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request instanceof ProductRequest ? $request->validated() : $this->validateRequest($request);

        $item = $this->service->create(
            $validated,
            $request->hasFile('image') ? $request->file('image') : null,
            $request->has('material_ids') && is_array($request->material_ids) ? $request->material_ids : null,
            $request->has('quantities') && is_array($request->quantities) ? $request->quantities : null,
        );

        return $this->respondWith(
            $item,
            $this->resourceName . ' created successfully',
            $this->routePrefix . '.index'
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response|HttpJsonResponse
     */
    public function show($id)
    {
        $item = $this->repository->find($id);
        $item->load('materials');

        if (request()->wantsJson()) {
            return $this->successResponse($item);
        }

        return view($this->viewPath . '.show', [
            'item' => $item,
            'resourceName' => $this->resourceName
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response|HttpJsonResponse
     */
    public function edit($id)
    {
        $data = $this->service->getEditData($id);

        if (request()->wantsJson()) {
            return $this->successResponse([
                'item' => $data['item'],
                'materials' => $data['materials']
            ]);
        }

        return view($this->viewPath . '.edit', [
            'item' => $data['item'],
            'materials' => $data['materials'],
            'resourceName' => $this->resourceName
        ]);
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
        $validated = $request instanceof ProductRequest ? $request->validated() : $this->validateRequest($request, $id);

        $item = $this->service->update(
            $id,
            $validated,
            $request->hasFile('image') ? $request->file('image') : null,
        );

        return $this->respondWith(
            $item,
            $this->resourceName . ' updated successfully',
            $this->routePrefix . '.index'
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response|HttpJsonResponse
     */
    public function destroy($id)
    {
        $this->service->destroy($id);

        return $this->respondWith(
            null,
            $this->resourceName . ' deleted successfully',
            $this->routePrefix . '.index'
        );
    }

    /**
     * Display products by category.
     *
     * @param  string  $category
     * @return \Illuminate\Http\Response|HttpJsonResponse
     */
    public function byCategory(?string $category = null)
    {
        // Accept category from path param or query string
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
     * Show the form for managing materials for a product.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response|HttpJsonResponse
     */
    public function showMaterialsForm(string $id)
    {
        $data = $this->service->materialsFormData($id);

        if (request()->wantsJson()) {
            return $this->successResponse([
                'item' => $data['item'],
                'availableMaterials' => $data['allMaterials']
            ]);
        }

        return view($this->viewPath . '.materials', [
            'product' => $data['item'],
            'availableMaterials' => $data['allMaterials'],
            'resourceName' => $this->resourceName
        ]);
    }

    /**
     * Add material to a product.
     *
     * @param  ProductAddMaterialRequest  $request
     * @param  string  $id
     * @return \Illuminate\Http\Response|HttpJsonResponse
     */
    public function addMaterial(ProductAddMaterialRequest $request, string $id)
    {
        try {
            $product = $this->service->addMaterials($id, $request->material_ids, $request->quantities);

            if (request()->wantsJson()) {
                return $this->successResponse([
                    'product' => $product
                ]);
            }

            return redirect()->back()->with('success', 'Materials updated successfully!');
        } catch (\RuntimeException $e) {
            if (strpos($e->getMessage(), 'INSUFFICIENT_STOCK') === 0) {
                $errorMessage = str_replace('INSUFFICIENT_STOCK: ', '', $e->getMessage());
                
                if (request()->wantsJson()) {
                    return $this->errorResponse($errorMessage, 422);
                }
                
                return redirect()->back()->with('error', $errorMessage);
            }
            
            throw $e;
        }
    }

    /**
     * Remove material from a product.
     *
     * @param  string  $id
     * @param  string  $materialId
     * @return \Illuminate\Http\Response|JsonResponse
     */
    public function removeMaterial(string $id, string $materialId)
    {
        $this->service->removeMaterial($id, $materialId);

        return $this->respondWith(
            null,
            'Material removed from product successfully',
            'products.materials.form',
            ['product' => $id]
        );
    }
}