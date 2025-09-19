<?php

namespace App\Http\Controllers;

use App\Repositories\Repository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BaseController extends Controller
{
    /**
     * @var Repository
     */
    protected $repository;

    /**
     * @var string
     */
    protected $viewPath = '';

    /**
     * @var string
     */
    protected $routePrefix = '';

    /**
     * @var string
     */
    protected $resourceName = '';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response|JsonResponse
     */
    public function index()
    {
        $items = $this->repository->paginate();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $items
            ]);
        }

        return view($this->viewPath . '.index', [
            'items' => $items,
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
        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Create form data',
                'resourceName' => $this->resourceName
            ]);
        }

        return view($this->viewPath . '.create', [
            'resourceName' => $this->resourceName
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
        // Otherwise, use the validateRequest method
        $validated = method_exists($request, 'validated') ? $request->validated() : $this->validateRequest($request);
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

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response|JsonResponse
     */
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

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response|JsonResponse
     */
    public function edit($id)
    {
        $item = $this->repository->find($id);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $item
            ]);
        }

        return view($this->viewPath . '.edit', [
            'item' => $item,
            'resourceName' => $this->resourceName
        ]);
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
        // If using a FormRequest, validation is already handled
        // Otherwise, use the validateRequest method
        $validated = method_exists($request, 'validated') ? $request->validated() : $this->validateRequest($request, $id);
        $model = $this->repository->find($id);
        if ($model instanceof Model) {
            $updated = $this->repository->update($model, $validated);
        }

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

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response|JsonResponse
     */
    public function destroy($id)
    {
        $model = $this->repository->find($id);
        if ($model instanceof Model) {
            $this->repository->delete($model);
        }

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $this->resourceName . ' deleted successfully'
            ]);
        }

        return redirect()->route($this->routePrefix . '.index')
            ->with('success', $this->resourceName . ' deleted successfully');
    }

    /**
     * Validate the request
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int|null  $id
     * @return array
     */
    protected function validateRequest(Request $request, $id = null)
    {
        // This method should be overridden in child classes
        return $request->all();
    }
}
