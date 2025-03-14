<?php

namespace App\Modules\User\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\User\Requests\UserRequest;
use App\Modules\User\Resources\UserResource;
use App\Modules\User\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * The user service instance.
     *
     * @var UserService
     */
    protected $userService;

    /**
     * Create a new controller instance.
     *
     * @param UserService $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display a listing of the users.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $role = $request->input('role');

        if ($role) {
            $users = $this->userService->getUsersByRole($role, $perPage);
        } else {
            $users = $this->userService->getAllUsers($perPage);
        }

        return response()->json([
            'success' => true,
            'data' => UserResource::collection($users),
            'meta' => [
                'total' => $users->total(),
                'per_page' => $users->perPage(),
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
            ],
        ]);
    }

    /**
     * Store a newly created user.
     *
     * @param UserRequest $request
     * @return JsonResponse
     */
    public function store(UserRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Handle profile image upload if provided
        if ($request->hasFile('profile_image')) {
            $data['profile_image'] = $this->handleImageUpload($request);
        }

        $user = $this->userService->createUser($data);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => new UserResource($user),
        ], 201);
    }

    /**
     * Display the specified user.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $user = $this->userService->getUserById($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new UserResource($user),
        ]);
    }

    /**
     * Update the specified user.
     *
     * @param UserRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UserRequest $request, int $id): JsonResponse
    {
        try {
            $data = $request->validated();

            // Handle profile image upload if provided
            if ($request->hasFile('profile_image')) {
                $data['profile_image'] = $this->handleImageUpload($request);
            }

            $user = $this->userService->updateUser($id, $data);

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'data' => new UserResource($user),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Remove the specified user.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->userService->deleteUser($id);

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Handle profile image upload.
     *
     * @param Request $request
     * @return string
     */
    private function handleImageUpload(Request $request): string
    {
        if ($request->hasFile('profile_image')) {
            $file = $request->file('profile_image');
            $path = $file->store('profile-images', 'public');
            return $path;
        }

        return '';
    }
}
