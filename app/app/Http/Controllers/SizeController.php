<?php

namespace App\Http\Controllers;

use App\Repositories\SizeRepositoryInterface;
use App\Services\SizeService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SizeController extends BaseController
{
    public function __construct(
        /* Type removed to avoid conflict with parent */ SizeRepositoryInterface $repository,
        protected SizeService $service
    ) {
        $this->repository = $repository;
        $this->viewPath = 'sizes';
        $this->routePrefix = 'admin.sizes';
        $this->resourceName = 'Size';
    }

    public function index()
    {
        $data = $this->service->getIndexData();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'items' => $data['items'],
                'categories' => $data['categories'],
                'metrics' => $data['metrics'] ?? null,
            ]);
        }

        return view($this->viewPath . '.index', [
            'items' => $data['items'],
            'categories' => $data['categories'],
            'metrics' => $data['metrics'] ?? null,
            'resourceName' => $this->resourceName,
        ]);
    }

    public function create()
    {
        $data = $this->service->getCreateData();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'categories' => $data['categories'],
            ]);
        }

        return view($this->viewPath . '.create', [
            'categories' => $data['categories'],
            'resourceName' => $this->resourceName,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateRequest($request);
        $item = $this->repository->create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $this->resourceName . ' created successfully',
                'data' => $item
            ], 201);
        }

        return redirect()->route($this->routePrefix . '.index')
            ->with('success', $this->resourceName . ' created successfully');
    }

    public function show($id)
    {
        $item = $this->repository->find($id);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $item
            ]);
        }

        return view($this->viewPath . '.show', [
            'item' => $item,
            'resourceName' => $this->resourceName
        ]);
    }

    public function edit($id)
    {
        $data = $this->service->getEditData($id);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'item' => $data['item'],
                'categories' => $data['categories'],
            ]);
        }

        return view($this->viewPath . '.edit', [
            'item' => $data['item'],
            'categories' => $data['categories'],
            'resourceName' => $this->resourceName
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $this->validateRequest($request, $id);
        $this->repository->update($validated, $id);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $this->resourceName . ' updated successfully',
            ]);
        }

        return redirect()->route($this->routePrefix . '.index')
            ->with('success', $this->resourceName . ' updated successfully');
    }

    public function destroy($id)
    {
        $this->repository->delete($id);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $this->resourceName . ' deleted successfully'
            ]);
        }

        return redirect()->route($this->routePrefix . '.index')
            ->with('success', $this->resourceName . ' deleted successfully');
    }

    protected function validateRequest(Request $request, $id = null)
    {
        $categoryId = $request->input('category_id');
        $uniqueNameRule = Rule::unique('sizes', 'name');
        if ($categoryId) {
            $uniqueNameRule = $uniqueNameRule->where('category_id', $categoryId);
        }
        if ($id) {
            $uniqueNameRule = $uniqueNameRule->ignore($id);
        }

        return $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255', $uniqueNameRule],
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive'])],
            'notes' => ['nullable', 'string'],
        ]);
    }

    /**
     * Get sizes by category id.
     */
    public function byCategory(int $category)
    {
        $items = $this->repository->getByCategoryId($category);

        return response()->json([
            'success' => true,
            'items' => $items,
        ]);
    }
}