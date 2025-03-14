<?php

namespace App\Modules\Barber\Repositories\Interfaces;

use App\Modules\Barber\Models\Barber;
use Illuminate\Database\Eloquent\Collection;

interface BarberRepositoryInterface
{
    /**
     * Get all barbers
     *
     * @param array $filters
     * @return Collection
     */
    public function getAll(array $filters = []): Collection;

    /**
     * Get barber by ID
     *
     * @param int $id
     * @return Barber|null
     */
    public function findById(int $id): ?Barber;

    /**
     * Get barbers by shop ID
     *
     * @param int $shopId
     * @return Collection
     */
    public function getByShopId(int $shopId): Collection;

    /**
     * Create a new barber
     *
     * @param array $data
     * @return Barber
     */
    public function create(array $data): Barber;

    /**
     * Update a barber
     *
     * @param int $id
     * @param array $data
     * @return Barber
     */
    public function update(int $id, array $data): Barber;

    /**
     * Delete a barber
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Get barber with services
     *
     * @param int $id
     * @return Barber|null
     */
    public function findWithServices(int $id): ?Barber;

    /**
     * Get barber with working hours
     *
     * @param int $id
     * @return Barber|null
     */
    public function findWithWorkingHours(int $id): ?Barber;

    /**
     * Get barber with full availability data (working hours + time off)
     *
     * @param int $id
     * @return Barber|null
     */
    public function findWithAvailabilityData(int $id): ?Barber;

    /**
     * Get barber by user ID
     *
     * @param int $userId
     * @return Barber|null
     */
    public function findByUserId(int $userId): ?Barber;

    /**
     * Get filtered barbers with pagination
     *
     * @param array $filters
     * @param int $perPage
     * @param string $sortBy
     * @param string $sortDirection
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getFiltered(
        array $filters = [],
        int $perPage = 15,
        string $sortBy = 'created_at',
        string $sortDirection = 'desc'
    ): \Illuminate\Pagination\LengthAwarePaginator;
}
