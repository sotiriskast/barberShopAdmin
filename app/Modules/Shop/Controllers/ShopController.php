<?php

namespace App\Modules\Shop\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Shop\Requests\CreateShopRequest;
use App\Modules\Shop\Requests\UpdateShopRequest;
use App\Modules\Shop\Resources\ShopCollection;
use App\Modules\Shop\Resources\ShopDetailResource;
use App\Modules\Shop\Resources\ShopResource;
use App\Modules\Shop\Services\ShopService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    /**
     * @var ShopService
     */
    protected $shopService;

    /**
     * ShopController constructor.
     *
     * @param ShopService $shopService
     */
    public function __construct(ShopService $shopService)
    {
        $this->shopService = $shopService;
    }

    /**
     * Display a listing of the shops.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);

        // Check if search term is provided
        if ($request->has('search') && $request->get('search')) {
            $shops = $this->shopService->searchShops($request->get('search'), $perPage);
            return response()->json(new ShopCollection($shops));
        }

        // Check if filter criteria are provided
        if ($request->has('filter')) {
            $shops = $this->shopService->filterShops($request->get('filter'), $perPage);
            return response()->json(new ShopCollection($shops));
        }

        // Check if nearby location is provided
        if ($request->has('latitude') && $request->has('longitude')) {
            $radius = $request->get('radius', 10);
            $shops = $this->shopService->getNearbyShops(
                $request->get('latitude'),
                $request->get('longitude'),
                $radius,
                $perPage
            );
            return response()->json(new ShopCollection($shops));
        }

        // Default: return active shops
        $shops = $this->shopService->getActiveShops($perPage);
        return response()->json(new ShopCollection($shops));
    }

    /**
     * Display a specific shop.
     */
    public function show(int $id): JsonResponse
    {
        $shop = $this->shopService->getShopById($id);

        if (!$shop) {
            return response()->json([
                'message' => 'Shop not found'
            ], 404);
        }

        // Load relationships for detailed view
        $shop->load(['barbers', 'services', 'reviews']);

        return response()->json(new ShopDetailResource($shop));
    }
}
