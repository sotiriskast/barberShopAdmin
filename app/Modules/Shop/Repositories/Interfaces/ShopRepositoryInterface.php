<?php

namespace App\Modules\Shop\Repositories\Interfaces;

use App\Modules\Shop\Models\Shop;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ShopRepositoryInterface
{
    /**
     * Get all shops.
     */
    public function all(): Collection;

    /**
     * Get paginated shops.
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * Find a shop by ID.
     */
    public function find(int $id): ?Shop;

    /**
     * Create a new shop.
     */
    public function create(array $data): Shop;

    /**
     * Update a shop.
     */
    public function update(Shop $shop, array $data): bool;

    /**
     * Delete a shop.
     */
    public function delete(Shop $shop): bool;

    /**
     * Get shops owned by a specific user.
     */
    public function getByOwnerId(int $ownerId): Collection;

    /**
     * Search shops by term.
     */
    public function search(string $term, int $perPage = 15): LengthAwarePaginator;

    /**
     * Filter shops by criteria.
     */
    public function filter(array $criteria, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get active shops.
     */
    public function getActive(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get nearby shops based on latitude and longitude.
     */
    public function getNearby(float $latitude, float $longitude, int $radius = 10, int $perPage = 15): LengthAwarePaginator;
}
