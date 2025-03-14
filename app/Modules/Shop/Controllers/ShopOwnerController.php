<?php

// Shop owner controller
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

class ShopOwnerController extends Controller
{
    /**
     * @var ShopService
     */
    protected $shopService;

    /**
     * ShopOwnerController constructor.
     *
     * @param ShopService $shopService
     */
    public function __construct(ShopService $shopService)
    {
        $this->shopService = $shopService;
    }

    /**
     * Display a listing of the shops owned by the authenticated user.
     */
    public function index(): JsonResponse
    {
        $ownerId = auth()->id();
        $shops = $this->shopService->getShopsByOwner($ownerId);

        return response()->json(ShopResource::collection($shops));
    }

    /**
     * Store a newly created shop.
     */
    public function store(CreateShopRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['owner_id'] = auth()->id();

        $shop = $this->shopService->createShop($data);

        return response()->json(new ShopResource($shop), 201);
    }

    /**
     * Display the specified shop.
     */
    public function show(int $id): JsonResponse
    {
        $shop = $this->shopService->getShopById($id);

        if (!$shop) {
            return response()->json([
                'message' => 'Shop not found'
            ], 404);
        }

        // Check if the authenticated user owns this shop
        if ($shop->owner_id !== auth()->id()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        // Load relationships for detailed view
        $shop->load(['barbers', 'services', 'reviews']);

        return response()->json(new ShopDetailResource($shop));
    }

    /**
     * Update the specified shop.
     */
    public function update(UpdateShopRequest $request, int $id): JsonResponse
    {
        $shop = $this->shopService->getShopById($id);

        if (!$shop) {
            return response()->json([
                'message' => 'Shop not found'
            ], 404);
        }

        // Check if the authenticated user owns this shop
        if ($shop->owner_id !== auth()->id()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $data = $request->validated();
        $success = $this->shopService->updateShop($id, $data);

        if ($success) {
            $shop = $this->shopService->getShopById($id);
            return response()->json(new ShopResource($shop));
        }

        return response()->json([
            'message' => 'Failed to update shop'
        ], 500);
    }

    /**
     * Remove the specified shop.
     */
    public function destroy(int $id): JsonResponse
    {
        $shop = $this->shopService->getShopById($id);

        if (!$shop) {
            return response()->json([
                'message' => 'Shop not found'
            ], 404);
        }

        // Check if the authenticated user owns this shop
        if ($shop->owner_id !== auth()->id()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $success = $this->shopService->deleteShop($id);

        if ($success) {
            return response()->json([
                'message' => 'Shop deleted successfully'
            ]);
        }

        return response()->json([
            'message' => 'Failed to delete shop'
        ], 500);
    }
}
