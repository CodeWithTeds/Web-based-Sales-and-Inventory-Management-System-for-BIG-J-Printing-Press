<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\ProductRepositoryInterface;

class TestController extends Controller
{
    protected $productRepository;

    /**
     * TestController constructor.
     *
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'ProductRepositoryInterface successfully resolved'
        ]);
    }
}
