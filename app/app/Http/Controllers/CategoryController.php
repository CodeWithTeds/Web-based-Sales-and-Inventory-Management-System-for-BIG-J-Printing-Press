<?php

namespace App\Http\Controllers;

use App\Repositories\CategoryRepositoryInterface;
use App\Services\CategoryService;
use Illuminate\Http\Request;

class CategoryController extends BaseController
{
    public function __construct(
        /* Type removed to avoid conflict with parent */ CategoryRepositoryInterface $repository,
        protected CategoryService $service
    ) {
        $this->repository = $repository;
        $this->viewPath = 'categories';
        $this->routePrefix = 'admin.categories';
        $this->resourceName = 'Category';
    }

    public function index()
    {
        $data = $this->service->getIndexData();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'items' => $data['items'],
                'metrics' => $data['metrics'] ?? null,
            ]);
        }

        return view($this->viewPath . '.index', [
            'items' => $data['items'],
            'metrics' => $data['metrics'] ?? null,
            'resourceName' => $this->resourceName,
        ]);
    }

    public function create()
    {
        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Create form data',
                'resourceName' => $this->resourceName,
            ]);
        }

        return view($this->viewPath . '.create', [
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
        $item = $this->repository->find($id);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'item' => $item,
            ]);
        }

        return view($this->viewPath . '.edit', [
            'item' => $item,
            'resourceName' => $this->resourceName
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $this->validateRequest($request, $id);
        $model = $this->repository->find($id);
        $this->repository->update($model, $validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $this->resourceName . ' updated successfully',
                'data' => $model
            ]);
        }

        return redirect()->route($this->routePrefix . '.index')
            ->with('success', $this->resourceName . ' updated successfully');
    }

    public function destroy($id)
    {
        $model = $this->repository->find($id);
        $this->repository->delete($model);

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
        $uniqueName = $id ? '|unique:categories,name,' . $id : '|unique:categories,name';

        return $request->validate([
            'name' => 'required|string|max:255' . $uniqueName,
            'status' => 'nullable|string|in:active,inactive',
            'notes' => 'nullable|string',
        ]);
    }
}