<?php

namespace App\Modules\Shop\Repositories\Eloquent;

use App\Modules\Shop\Models\Shop;
use App\Modules\Shop\Repositories\Interfaces\ShopRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ShopRepository implements ShopRepositoryInterface
{
    /**
     * @var Shop
     */
    protected $model;

    /**
     * ShopRepository constructor.
     *
     * @param Shop $model
     */
    public function __construct(Shop $model)
    {
        $this->model = $model;
    }

    /**
     * Get all shops.
     */
    public function all(): Collection
    {
        return $this->model->all();
    }

    /**
     * Get paginated shops.
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->paginate($perPage);
    }

    /**
     * Find a shop by ID.
     */
    public function find(int $id): ?Shop
    {
        return $this->model->find($id);
    }

    /**
     * Create a new shop.
     */
    public function create(array $data): Shop
    {
        return $this->model->create($data);
    }

    /**
     * Update a shop.
     */
    public function update(Shop $shop, array $data): bool
    {
        return $shop->update($data);
    }

    /**
     * Delete a shop.
     */
    public function delete(Shop $shop): bool
    {
        return $shop->delete();
    }

    /**
     * Get shops owned by a specific user.
     */
    public function getByOwnerId(int $ownerId): Collection
    {
        return $this->model->where('owner_id', $ownerId)->get();
    }

    /**
     * Search shops by term.
     */
    public function search(string $term, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->search($term)->paginate($perPage);
    }

    /**
     * Filter shops by criteria.
     */
    public function filter(array $criteria, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if (isset($criteria['city'])) {
            $query->where('city', $criteria['city']);
        }

        if (isset($criteria['state'])) {
            $query->where('state', $criteria['state']);
        }

        if (isset($criteria['is_active']) && $criteria['is_active'] !== null) {
            $query->where('is_active', $criteria['is_active']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Get active shops.
     */
    public function getActive(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->active()->paginate($perPage);
    }

    /**
     * Get nearby shops based on latitude and longitude.
     */
    public function getNearby(float $latitude, float $longitude, int $radius = 10, int $perPage = 15): LengthAwarePaginator
    {
        // Calculate distance using the Haversine formula
        $haversine = "(
            6371 * acos(
                cos(radians({$latitude}))
                * cos(radians(latitude))
                * cos(radians(longitude) - radians({$longitude}))
                + sin(radians({$latitude}))
                * sin(radians(latitude))
            )
        )";

        return $this->model
            ->select('*')
            ->selectRaw("{$haversine} as distance")
            ->whereRaw("{$haversine} < ?", [$radius])
            ->orderBy('distance')
            ->paginate($perPage);
    }
}
