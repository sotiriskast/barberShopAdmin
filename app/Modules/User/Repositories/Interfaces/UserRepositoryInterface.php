<?php

namespace App\Modules\User\Repositories\Interfaces;

use App\Modules\User\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    /**
     * Get all users with pagination.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllPaginated(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get user by ID.
     *
     * @param int $id
     * @return User|null
     */
    public function findById(int $id): ?User;

    /**
     * Get user by email.
     *
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User;

    /**
     * Create a new user.
     *
     * @param array $data
     * @return User
     */
    public function create(array $data): User;

    /**
     * Update an existing user.
     *
     * @param User $user
     * @param array $data
     * @return User
     */
    public function update(User $user, array $data): User;

    /**
     * Delete a user.
     *
     * @param User $user
     * @return bool
     */
    public function delete(User $user): bool;

    /**
     * Get users by role.
     *
     * @param string $role
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getByRole(string $role, int $perPage = 15): LengthAwarePaginator;
}
