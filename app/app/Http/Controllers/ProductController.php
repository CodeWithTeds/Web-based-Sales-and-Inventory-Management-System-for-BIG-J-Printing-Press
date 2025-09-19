<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Http\Requests\ProductRequest;
use App\Repositories\ProductRepositoryInterface;
use App\Traits\ResponseHelpers;
use Illuminate\Http\JsonResponse as HttpJsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends BaseController
{
    use ResponseHelpers;
    /**
     * ProductController constructor.
     *
     * @param ProductRepositoryInterface $repository
     */
    public function __construct(ProductRepositoryInterface $repository)
    {
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
        $items = $this->repository->paginate();
        $categories = $this->repository->getUniqueCategories();

        if (request()->wantsJson()) {
            return $this->successResponse([
                'items' => $items,
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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response|JsonResponse
     */
    public function create()
    {
        $materials = app(\App\Repositories\MaterialRepositoryInterface::class)->all();
        
        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Create form data',
                'resourceName' => $this->resourceName,
                'materials' => $materials
            ]);
        }

        return view($this->viewPath . '.create', [
            'resourceName' => $this->resourceName,
            'materials' => $materials
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
        // If using a FormRequest, validation is already handled
        $validated = $request instanceof ProductRequest ? $request->validated() : $this->validateRequest($request);
        
        // Handle image upload if present
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $path = $request->file('image')->store('products', 'public');
            $validated['image_path'] = $path;
        }
        
        $item = $this->repository->create($validated);

        // Handle materials if provided
        if ($request->has('materials') && is_array($request->materials)) {
            foreach ($request->materials as $material) {
                if (isset($material['id'], $material['quantity'])) {
                    $this->repository->addMaterial(
                        $item->id,
                        $material['id'],
                        $material['quantity']
                    );
                }
            }
        }

        return $this->respondWith(
            $item->load('materials'),
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
        $item = $this->repository->find($id);
        $item->load('materials');
        $materials = app(\App\Repositories\MaterialRepositoryInterface::class)->all();
        
        if (request()->wantsJson()) {
            return $this->successResponse([
                'item' => $item,
                'materials' => $materials
            ]);
        }
        
        return view($this->viewPath . '.edit', [
            'item' => $item,
            'materials' => $materials,
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
        // If using a FormRequest, validation is already handled
        $validated = $request instanceof ProductRequest ? $request->validated() : $this->validateRequest($request, $id);
        $item = $this->repository->find($id);
        
        // Handle image upload if present
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            // Delete old image if exists
            if ($item->image_path) {
                Storage::disk('public')->delete($item->image_path);
            }
            
            $path = $request->file('image')->store('products', 'public');
            $validated['image_path'] = $path;
        }
        
        if ($item instanceof \Illuminate\Database\Eloquent\Model) {
            $this->repository->update($item, $validated);
        } else {
            $this->repository->update($id, $validated);
        }
        $item = $this->repository->find($id);

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
        $item = $this->repository->find($id);
        
        // Delete image if exists
        if ($item && $item->image_path) {
            Storage::disk('public')->delete($item->image_path);
        }
        
        if ($item instanceof \Illuminate\Database\Eloquent\Model) {
            $this->repository->delete($item);
        } else {
            $this->repository->delete($id);
        }

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
    public function byCategory(string $category = null)
    {
        $categories = $this->repository->getUniqueCategories();
        
        if ($category) {
            $items = $this->repository->getByCategory($category);
        } else {
            $items = $this->repository->all();
        }
        
        if (request()->wantsJson()) {
            return $this->successResponse([
                'items' => $items,
                'category' => $category,
                'categories' => $categories
            ]);
        }
        
        return view($this->viewPath . '.by-category', [
            'items' => $items,
            'category' => $category,
            'categories' => $categories,
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
        $item = $this->repository->find($id);
        $item->load('materials');
        $allMaterials = app(\App\Repositories\MaterialRepositoryInterface::class)->all();
        
        if (request()->wantsJson()) {
            return $this->successResponse([
                'item' => $item,
                'allMaterials' => $allMaterials
            ]);
        }
        
        return view($this->viewPath . '.materials', [
            'item' => $item,
            'allMaterials' => $allMaterials,
            'resourceName' => $this->resourceName
        ]);
    }
    
    /**
     * Add material to a product.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\Response|HttpJsonResponse
     */
    public function addMaterial(Request $request, string $id)
    {
        $request->validate([
            'material_id' => 'required|exists:materials,id',
            'quantity' => 'required|numeric|min:0.01'
        ]);
        
        $product = $this->repository->addMaterial(
            $id,
            $request->material_id,
            $request->quantity
        );
        
        return $this->respondWith(
            $product,
            'Material added to product successfully',
            $this->routePrefix . '.materials',
            ['id' => $id]
        );
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
        $this->repository->removeMaterial($id, $materialId);
        
        return $this->respondWith(
            null,
            'Material removed from product successfully',
            $this->routePrefix . '.materials',
            ['id' => $id]
        );
    }
}
