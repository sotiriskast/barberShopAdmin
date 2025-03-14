<?php

namespace App\Modules\Barber\Repositories\Eloquent;

use App\Modules\Barber\Models\Barber;
use App\Modules\Barber\Repositories\Interfaces\BarberRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class BarberRepository implements BarberRepositoryInterface
{
    /**
     * @var Barber
     */
    protected $model;

    /**
     * BarberRepository constructor.
     *
     * @param Barber $model
     */
    public function __construct(Barber $model)
    {
        $this->model = $model;
    }

    /**
     * Get all barbers
     *
     * @param array $filters
     * @return Collection
     */
    public function getAll(array $filters = []): Collection
    {
        $query = $this->model->newQuery();

        // Apply filters
        if (!empty($filters['shop_id'])) {
            $query->where('shop_id', $filters['shop_id']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        // Include relationships if requested
        if (!empty($filters['with'])) {
            $query->with($filters['with']);
        }

        return $query->get();
    }

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
    ): \Illuminate\Pagination\LengthAwarePaginator {
        $query = $this->model->newQuery();

        // Apply filtering
        if (!empty($filters['shop_id'])) {
            $query->where('shop_id', $filters['shop_id']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (!empty($filters['min_experience'])) {
            $query->where('years_experience', '>=', $filters['min_experience']);
        }

        if (!empty($filters['max_experience'])) {
            $query->where('years_experience', '<=', $filters['max_experience']);
        }

        // For searching by name or title, we need to join with the users table
        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';

            $query->where(function($q) use ($searchTerm) {
                $q->whereHas('user', function($userQuery) use ($searchTerm) {
                    $userQuery->where('name', 'like', $searchTerm);
                })
                    ->orWhere('title', 'like', $searchTerm)
                    ->orWhere('bio', 'like', $searchTerm);
            });
        }

        // Filter by service
        if (!empty($filters['service_id'])) {
            $query->whereHas('services', function($q) use ($filters) {
                $q->where('services.id', $filters['service_id']);
            });
        }

        // Filter by availability on a specific date
        if (!empty($filters['available_on_date'])) {
            $dayOfWeek = date('w', strtotime($filters['available_on_date']));

            $query->whereHas('workingHours', function($q) use ($dayOfWeek) {
                $q->where('day_of_week', $dayOfWeek)
                    ->where('is_day_off', false);
            })
                ->whereDoesntHave('timeOff', function($q) use ($filters) {
                    $date = $filters['available_on_date'];
                    $q->where('start_datetime', '<=', "$date 23:59:59")
                        ->where('end_datetime', '>=', "$date 00:00:00");
                });
        }

        // Include relationships
        $with = ['user', 'shop'];

        if (!empty($filters['include_services'])) {
            $with[] = 'services';
        }

        if (!empty($filters['include_working_hours'])) {
            $with[] = 'workingHours';
        }

        $query->with($with);

        // Apply sorting
        switch ($sortBy) {
            case 'name':
                $query->join('users', 'barbers.user_id', '=', 'users.id')
                    ->orderBy('users.name', $sortDirection)
                    ->select('barbers.*'); // To avoid field conflicts
                break;

            case 'experience':
                $query->orderBy('years_experience', $sortDirection);
                break;

            case 'reviews':
                // This requires a join with the reviews table and a count or avg aggregate
                $query->leftJoin('reviews', 'barbers.id', '=', 'reviews.barber_id')
                    ->selectRaw('barbers.*, AVG(reviews.rating) as avg_rating')
                    ->groupBy('barbers.id')
                    ->orderBy('avg_rating', $sortDirection);
                break;

            case 'popularity':
                // This requires a join with the appointments table and a count aggregate
                $query->leftJoin('appointments', 'barbers.id', '=', 'appointments.barber_id')
                    ->selectRaw('barbers.*, COUNT(appointments.id) as appointment_count')
                    ->groupBy('barbers.id')
                    ->orderBy('appointment_count', $sortDirection);
                break;

            default:
                $query->orderBy($sortBy, $sortDirection);
        }

        return $query->paginate($perPage);
    }

    /**
     * Get barber by ID
     *
     * @param int $id
     * @return Barber|null
     */
    public function findById(int $id): ?Barber
    {
        return $this->model->find($id);
    }

    /**
     * Get barbers by shop ID
     *
     * @param int $shopId
     * @return Collection
     */
    public function getByShopId(int $shopId): Collection
    {
        return $this->model->where('shop_id', $shopId)
            ->with('user')
            ->get();
    }

    /**
     * Create a new barber
     *
     * @param array $data
     * @return Barber
     */
    public function create(array $data): Barber
    {
        return $this->model->create($data);
    }

    /**
     * Update a barber
     *
     * @param int $id
     * @param array $data
     * @return Barber
     */
    public function update(int $id, array $data): Barber
    {
        $barber = $this->findById($id);
        $barber->update($data);
        return $barber;
    }

    /**
     * Delete a barber
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $barber = $this->findById($id);
        return $barber->delete();
    }

    /**
     * Get barber with services
     *
     * @param int $id
     * @return Barber|null
     */
    public function findWithServices(int $id): ?Barber
    {
        return $this->model->with(['services', 'user'])
            ->find($id);
    }

    /**
     * Get barber with working hours
     *
     * @param int $id
     * @return Barber|null
     */
    public function findWithWorkingHours(int $id): ?Barber
    {
        return $this->model->with(['workingHours', 'user'])
            ->find($id);
    }

    /**
     * Get barber with full availability data (working hours + time off)
     *
     * @param int $id
     * @return Barber|null
     */
    public function findWithAvailabilityData(int $id): ?Barber
    {
        return $this->model->with(['workingHours', 'timeOff', 'user', 'shop'])
            ->find($id);
    }

    /**
     * Get barber by user ID
     *
     * @param int $userId
     * @return Barber|null
     */
    public function findByUserId(int $userId): ?Barber
    {
        return $this->model->where('user_id', $userId)->first();
    }
}
