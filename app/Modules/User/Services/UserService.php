<?php

namespace App\Modules\User\Services;

use App\Modules\User\Models\User;
use App\Modules\User\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class UserService
{
    /**
     * @var UserRepositoryInterface
     */
    protected $userRepository;

    /**
     * UserService constructor.
     *
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Get all users with pagination.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllUsers(int $perPage = 15): LengthAwarePaginator
    {
        return $this->userRepository->getAllPaginated($perPage);
    }

    /**
     * Get user by ID.
     *
     * @param int $id
     * @return User|null
     */
    public function getUserById(int $id): ?User
    {
        return $this->userRepository->findById($id);
    }

    /**
     * Create a new user.
     *
     * @param array $data
     * @return User
     */
    public function createUser(array $data): User
    {
        // Hash the password
        $data['password'] = Hash::make($data['password']);

        return $this->userRepository->create($data);
    }

    /**
     * Update an existing user.
     *
     * @param int $id
     * @param array $data
     * @return User
     */
    public function updateUser(int $id, array $data): User
    {
        $user = $this->getUserById($id);

        if (!$user) {
            throw new \Exception('User not found');
        }

        // Only hash the password if it's provided
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return $this->userRepository->update($user, $data);
    }

    /**
     * Delete a user.
     *
     * @param int $id
     * @return bool
     */
    public function deleteUser(int $id): bool
    {
        $user = $this->getUserById($id);

        if (!$user) {
            throw new \Exception('User not found');
        }

        return $this->userRepository->delete($user);
    }

    /**
     * Get users by role.
     *
     * @param string $role
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getUsersByRole(string $role, int $perPage = 15): LengthAwarePaginator
    {
        return $this->userRepository->getByRole($role, $perPage);
    }

    /**
     * Update user profile image.
     *
     * @param int $id
     * @param string $imagePath
     * @return User
     */
    public function updateProfileImage(int $id, string $imagePath): User
    {
        $user = $this->getUserById($id);

        if (!$user) {
            throw new \Exception('User not found');
        }

        return $this->userRepository->update($user, [
            'profile_image' => $imagePath
        ]);
    }
}
