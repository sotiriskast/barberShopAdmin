<?php

namespace App\Modules\Barber\Services;

use App\Modules\Barber\Models\Barber;
use App\Modules\Barber\Models\WorkingHour;
use App\Modules\Barber\Models\TimeOff;
use App\Modules\Barber\Repositories\Interfaces\BarberRepositoryInterface;
use App\Modules\Service\Repositories\Interfaces\ServiceRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

class BarberService
{
    /**
     * @var BarberRepositoryInterface
     */
    protected $barberRepository;

    /**
     * @var ServiceRepositoryInterface
     */
    protected $serviceRepository;

    /**
     * BarberService constructor.
     *
     * @param BarberRepositoryInterface $barberRepository
     * @param ServiceRepositoryInterface $serviceRepository
     */
    public function __construct(
        BarberRepositoryInterface $barberRepository,
        ServiceRepositoryInterface $serviceRepository
    ) {
        $this->barberRepository = $barberRepository;
        $this->serviceRepository = $serviceRepository;
    }

    /**
     * Get barber profile
     *
     * @param int $userId
     * @return Barber|null
     */
    public function getBarberProfile(int $userId): ?Barber
    {
        return $this->barberRepository->findByUserId($userId);
    }

    /**
     * Update barber profile
     *
     * @param int $barberId
     * @param array $data
     * @return Barber
     */
    public function updateProfile(int $barberId, array $data): Barber
    {
        return $this->barberRepository->update($barberId, $data);
    }

    /**
     * Get barber services
     *
     * @param int $barberId
     * @return Collection
     */
    public function getBarberServices(int $barberId): Collection
    {
        $barber = $this->barberRepository->findWithServices($barberId);
        return $barber ? $barber->services : collect();
    }

    /**
     * Update barber services
     *
     * @param int $barberId
     * @param array $serviceIds
     * @param array $priceOverrides Optional price overrides for each service
     * @return bool
     */
    public function updateBarberServices(int $barberId, array $serviceIds, array $priceOverrides = []): bool
    {
        $barber = $this->barberRepository->findById($barberId);

        if (!$barber) {
            return false;
        }

        // Prepare the pivot data with price overrides
        $syncData = [];
        foreach ($serviceIds as $serviceId) {
            $syncData[$serviceId] = [
                'price_override' => $priceOverrides[$serviceId] ?? null
            ];
        }

        // Sync the services with the barber
        $barber->services()->sync($syncData);

        return true;
    }

    /**
     * Get barber working hours
     *
     * @param int $barberId
     * @return Collection
     */
    public function getWorkingHours(int $barberId): Collection
    {
        $barber = $this->barberRepository->findWithWorkingHours($barberId);
        return $barber ? $barber->workingHours : collect();
    }

    /**
     * Update barber working hours
     *
     * @param int $barberId
     * @param array $workingHours
     * @return bool
     */
    public function updateWorkingHours(int $barberId, array $workingHours): bool
    {
        $barber = $this->barberRepository->findById($barberId);

        if (!$barber) {
            return false;
        }

        try {
            DB::beginTransaction();

            // Delete existing working hours
            $barber->workingHours()->delete();

            // Create new working hours
            foreach ($workingHours as $workingHour) {
                $workingHour['barber_id'] = $barberId;
                WorkingHour::create($workingHour);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Add time off for barber
     *
     * @param int $barberId
     * @param array $timeOffData
     * @return TimeOff
     */
    public function addTimeOff(int $barberId, array $timeOffData): TimeOff
    {
        $timeOffData['barber_id'] = $barberId;
        return TimeOff::create($timeOffData);
    }

    /**
     * Get barber time off periods
     *
     * @param int $barberId
     * @param array $filters
     * @return Collection
     */
    public function getTimeOffPeriods(int $barberId, array $filters = []): Collection
    {
        $query = TimeOff::where('barber_id', $barberId);

        // Filter by date range if provided
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('start_datetime', '<=', $filters['end_date'] . ' 23:59:59')
                    ->where('end_datetime', '>=', $filters['start_date'] . ' 00:00:00');
            });
        }

        return $query->orderBy('start_datetime', 'asc')->get();
    }

    /**
     * Remove time off period
     *
     * @param int $barberId
     * @param int $timeOffId
     * @return bool
     */
    public function removeTimeOff(int $barberId, int $timeOffId): bool
    {
        $timeOff = TimeOff::where('id', $timeOffId)
            ->where('barber_id', $barberId)
            ->first();

        if (!$timeOff) {
            return false;
        }

        return $timeOff->delete();
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
    public function getFilteredBarbers(
        array $filters = [],
        int $perPage = 15,
        string $sortBy = 'created_at',
        string $sortDirection = 'desc'
    ): \Illuminate\Pagination\LengthAwarePaginator {
        return $this->barberRepository->getFiltered(
            $filters,
            $perPage,
            $sortBy,
            $sortDirection
        );
    }

    /**
     * Create a new barber
     *
     * @param array $data
     * @return Barber
     */
    public function createBarber(array $data): Barber
    {
        try {
            DB::beginTransaction();

            // Extract services and working hours from the data
            $services = $data['services'] ?? [];
            $workingHours = $data['working_hours'] ?? [];

            // Remove non-barber table fields
            unset($data['services'], $data['working_hours']);

            // Create the barber
            $barber = $this->barberRepository->create($data);

            // Add services if provided
            if (!empty($services)) {
                $serviceData = [];

                foreach ($services as $service) {
                    $serviceData[$service['id']] = [
                        'price_override' => $service['price_override'] ?? null
                    ];
                }

                $barber->services()->sync($serviceData);
            }

            // Add working hours if provided, or use defaults
            if (empty($workingHours)) {
                $workingHours = config('barber.default_working_hours', []);
            }

            foreach ($workingHours as $workingHour) {
                $workingHour['barber_id'] = $barber->id;
                WorkingHour::create($workingHour);
            }

            DB::commit();

            return $barber->fresh(['user', 'shop', 'services', 'workingHours']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
