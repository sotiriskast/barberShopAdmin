<?php

namespace App\Modules\Shop\Services;

use App\Modules\Shop\Models\Shop;
use App\Modules\Shop\Repositories\Interfaces\ShopRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class ShopService
{
    /**
     * @var ShopRepositoryInterface
     */
    protected $shopRepository;

    /**
     * ShopService constructor.
     *
     * @param ShopRepositoryInterface $shopRepository
     */
    public function __construct(ShopRepositoryInterface $shopRepository)
    {
        $this->shopRepository = $shopRepository;
    }

    /**
     * Get all shops.
     */
    public function getAllShops(): Collection
    {
        return $this->shopRepository->all();
    }

    /**
     * Get paginated shops.
     */
    public function getPaginatedShops(int $perPage = 15): LengthAwarePaginator
    {
        return $this->shopRepository->paginate($perPage);
    }

    /**
     * Get a shop by ID.
     */
    public function getShopById(int $id): ?Shop
    {
        return $this->shopRepository->find($id);
    }

    /**
     * Create a new shop.
     */
    public function createShop(array $data): Shop
    {
        // Handle file uploads if present
        if (isset($data['logo_image']) && $data['logo_image']) {
            $data['logo_image'] = $this->uploadImage($data['logo_image'], 'shops/logos');
        }

        if (isset($data['cover_image']) && $data['cover_image']) {
            $data['cover_image'] = $this->uploadImage($data['cover_image'], 'shops/covers');
        }

        return $this->shopRepository->create($data);
    }

    /**
     * Update a shop.
     */
    public function updateShop(int $id, array $data): bool
    {
        $shop = $this->shopRepository->find($id);

        if (!$shop) {
            return false;
        }

        // Handle file uploads if present
        if (isset($data['logo_image']) && $data['logo_image']) {
            // Delete old image if exists
            if ($shop->logo_image) {
                Storage::delete($shop->logo_image);
            }

            $data['logo_image'] = $this->uploadImage($data['logo_image'], 'shops/logos');
        }

        if (isset($data['cover_image']) && $data['cover_image']) {
            // Delete old image if exists
            if ($shop->cover_image) {
                Storage::delete($shop->cover_image);
            }

            $data['cover_image'] = $this->uploadImage($data['cover_image'], 'shops/covers');
        }

        return $this->shopRepository->update($shop, $data);
    }

    /**
     * Delete a shop.
     */
    public function deleteShop(int $id): bool
    {
        $shop = $this->shopRepository->find($id);

        if (!$shop) {
            return false;
        }

        // Delete associated images
        if ($shop->logo_image) {
            Storage::delete($shop->logo_image);
        }

        if ($shop->cover_image) {
            Storage::delete($shop->cover_image);
        }

        return $this->shopRepository->delete($shop);
    }

    /**
     * Get shops by owner ID.
     */
    public function getShopsByOwner(int $ownerId): Collection
    {
        return $this->shopRepository->getByOwnerId($ownerId);
    }

    /**
     * Search shops by term.
     */
    public function searchShops(string $term, int $perPage = 15): LengthAwarePaginator
    {
        return $this->shopRepository->search($term, $perPage);
    }

    /**
     * Filter shops by criteria.
     */
    public function filterShops(array $criteria, int $perPage = 15): LengthAwarePaginator
    {
        return $this->shopRepository->filter($criteria, $perPage);
    }

    /**
     * Get active shops.
     */
    public function getActiveShops(int $perPage = 15): LengthAwarePaginator
    {
        return $this->shopRepository->getActive($perPage);
    }

    /**
     * Get nearby shops based on latitude and longitude.
     */
    public function getNearbyShops(float $latitude, float $longitude, int $radius = 10, int $perPage = 15): LengthAwarePaginator
    {
        return $this->shopRepository->getNearby($latitude, $longitude, $radius, $perPage);
    }

    /**
     * Handle image upload and return the path.
     */
    protected function uploadImage($image, string $path): string
    {
        return $image->store($path, 'public');
    }
}
