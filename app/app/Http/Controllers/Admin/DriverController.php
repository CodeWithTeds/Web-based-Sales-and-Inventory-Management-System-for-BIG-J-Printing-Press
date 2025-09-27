<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Repositories\DriverRepositoryInterface;
use App\Services\DriverService;
use Illuminate\Http\Request;

class DriverController extends BaseController
{
    public function __construct(
        /* Type removed to avoid conflict with parent */ DriverRepositoryInterface $repository,
        protected DriverService $service
    ) {
        $this->repository = $repository;
        $this->viewPath = 'drivers';
        $this->routePrefix = 'admin.drivers';
        $this->resourceName = 'Driver';
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
                'data' => $item,
            ]);
        }

        return view($this->viewPath . '.show', [
            'item' => $item,
            'resourceName' => $this->resourceName,
        ]);
    }

    public function edit($id)
    {
        $item = $this->repository->find($id);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $item,
                'message' => 'Edit form data',
            ]);
        }

        return view($this->viewPath . '.edit', [
            'item' => $item,
            'resourceName' => $this->resourceName,
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $this->validateRequest($request, $id);

        // Do not update password if left blank
        if (!isset($validated['password']) || empty($validated['password'])) {
            unset($validated['password']);
        }

        $this->repository->update($validated, $id);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $this->resourceName . ' updated successfully',
            ]);
        }

        return redirect()->route($this->routePrefix . '.show', $id)
            ->with('success', $this->resourceName . ' updated successfully');
    }

    protected function validateRequest(Request $request, $id = null)
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email' . ($id ? ",$id" : ''),
            'username' => 'nullable|string|max:255|unique:users,username' . ($id ? ",$id" : ''),
            'password' => ($id ? 'nullable' : 'required') . '|string|min:8',
        ]);
    }
}