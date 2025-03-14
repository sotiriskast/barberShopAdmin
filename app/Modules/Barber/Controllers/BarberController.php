<?php

namespace App\Modules\Barber\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Barber\Requests\AddTimeOffRequest;
use App\Modules\Barber\Requests\CreateBarberRequest;
use App\Modules\Barber\Requests\FilterBarbersRequest;
use App\Modules\Barber\Requests\UpdateBarberRequest;
use App\Modules\Barber\Requests\UpdateServicesRequest;
use App\Modules\Barber\Requests\UpdateWorkingHoursRequest;
use App\Modules\Barber\Resources\AvailabilityResource;
use App\Modules\Barber\Resources\BarberCollection;
use App\Modules\Barber\Resources\BarberResource;
use App\Modules\Barber\Resources\ServiceResource;
use App\Modules\Barber\Resources\TimeOffResource;
use App\Modules\Barber\Resources\WorkingHourResource;
use App\Modules\Barber\Services\AvailabilityService;
use App\Modules\Barber\Services\BarberService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Auth;

class BarberController extends Controller
{
    /**
     * @var BarberService
     */
    protected $barberService;

    /**
     * @var AvailabilityService
     */
    protected $availabilityService;

    /**
     * BarberController constructor.
     *
     * @param BarberService $barberService
     * @param AvailabilityService $availabilityService
     */
    public function __construct(
        BarberService       $barberService,
        AvailabilityService $availabilityService
    )
    {
        $this->barberService = $barberService;
        $this->availabilityService = $availabilityService;
    }

    /**
     * Get filtered barbers
     *
     * @param FilterBarbersRequest $request
     * @return JsonResponse
     */
    public function getFilteredBarbers(FilterBarbersRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $perPage = $request->input('per_page', 15);
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');

        $barbers = $this->barberService->getFilteredBarbers(
            $filters,
            $perPage,
            $sortBy,
            $sortDirection
        );

        return response()->json(new BarberCollection($barbers));
    }

    /**
     * Create a new barber
     *
     * @param CreateBarberRequest $request
     * @return JsonResponse
     */
    public function createBarber(CreateBarberRequest $request): JsonResponse
    {
        try {
            $barber = $this->barberService->createBarber($request->validated());

            return response()->json([
                'message' => 'Barber created successfully',
                'data' => new BarberResource($barber)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create barber: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add time off period
     *
     * @param AddTimeOffRequest $request
     * @return JsonResponse
     */
    public function addTimeOff(AddTimeOffRequest $request): JsonResponse
    {
        $userId = Auth::id();
        $barber = $this->barberService->getBarberProfile($userId);

        if (!$barber) {
            return response()->json([
                'message' => 'Barber profile not found'
            ], 404);
        }

        try {
            $timeOff = $this->barberService->addTimeOff($barber->id, $request->validated());

            return response()->json([
                'message' => 'Time off added successfully',
                'data' => new TimeOffResource($timeOff)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to add time off: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get time off periods
     *
     * @param Request $request
     * @return JsonResponse|ResourceCollection
     */
    public function getTimeOff(Request $request)
    {
        $userId = Auth::id();
        $barber = $this->barberService->getBarberProfile($userId);

        if (!$barber) {
            return response()->json([
                'message' => 'Barber profile not found'
            ], 404);
        }

        $filters = [
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
        ];

        $timeOffPeriods = $this->barberService->getTimeOffPeriods($barber->id, $filters);

        return TimeOffResource::collection($timeOffPeriods);
    }

    /**
     * Delete time off period
     *
     * @param int $timeOffId
     * @return JsonResponse
     */
    public function deleteTimeOff(int $timeOffId): JsonResponse
    {
        $userId = Auth::id();
        $barber = $this->barberService->getBarberProfile($userId);

        if (!$barber) {
            return response()->json([
                'message' => 'Barber profile not found'
            ], 404);
        }

        $success = $this->barberService->removeTimeOff($barber->id, $timeOffId);

        if (!$success) {
            return response()->json([
                'message' => 'Time off period not found or could not be deleted'
            ], 404);
        }

        return response()->json([
            'message' => 'Time off period deleted successfully'
        ]);
    }

    /**
     * Get available time slots
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getAvailability(Request $request): JsonResponse
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'service_id' => 'nullable|exists:services,id',
        ]);

        $userId = Auth::id();
        $barber = $this->barberService->getBarberProfile($userId);

        if (!$barber) {
            return response()->json([
                'message' => 'Barber profile not found'
            ], 404);
        }

        $date = $request->input('date');
        $serviceId = $request->input('service_id');

        $availability = $this->availabilityService->getAvailableTimeSlots(
            $barber->id,
            $date,
            $serviceId
        );

        return response()->json(new AvailabilityResource([
            'date' => $date,
            'barber_id' => $barber->id,
            'service_id' => $serviceId,
            'time_slots' => $availability,
        ]));
    }

    /**
     * Check if a specific time slot is available
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function checkTimeSlot(Request $request): JsonResponse
    {
        $request->validate([
            'datetime' => 'required|date|after_or_equal:now',
            'service_id' => 'required|exists:services,id',
        ]);

        $userId = Auth::id();
        $barber = $this->barberService->getBarberProfile($userId);

        if (!$barber) {
            return response()->json([
                'message' => 'Barber profile not found'
            ], 404);
        }

        // Get service duration
        $service = \App\Modules\Service\Models\Service::find($request->input('service_id'));

        if (!$service) {
            return response()->json([
                'message' => 'Service not found'
            ], 404);
        }

        $isAvailable = $this->availabilityService->isTimeSlotAvailable(
            $barber->id,
            $request->input('datetime'),
            $service->duration
        );

        return response()->json([
            'is_available' => $isAvailable
        ]);
    }

    /**
     * Get barber profile
     *
     * @return JsonResponse
     */
    public function getProfile(): JsonResponse
    {
        $userId = Auth::id();
        $barber = $this->barberService->getBarberProfile($userId);

        if (!$barber) {
            return response()->json([
                'message' => 'Barber profile not found'
            ], 404);
        }

        return response()->json(new BarberResource($barber->load(['user', 'shop'])));
    }

    /**
     * Update barber profile
     *
     * @param UpdateBarberRequest $request
     * @return JsonResponse
     */
    public function updateProfile(UpdateBarberRequest $request): JsonResponse
    {
        $userId = Auth::id();
        $barber = $this->barberService->getBarberProfile($userId);

        if (!$barber) {
            return response()->json([
                'message' => 'Barber profile not found'
            ], 404);
        }

        $updatedBarber = $this->barberService->updateProfile($barber->id, $request->validated());

        return response()->json([
            'message' => 'Profile updated successfully',
            'data' => new BarberResource($updatedBarber->load(['user', 'shop']))
        ]);
    }

    /**
     * Get barber services
     *
     * @return JsonResponse|ResourceCollection
     */
    public function getServices()
    {
        $userId = Auth::id();
        $barber = $this->barberService->getBarberProfile($userId);

        if (!$barber) {
            return response()->json([
                'message' => 'Barber profile not found'
            ], 404);
        }

        $services = $this->barberService->getBarberServices($barber->id);

        return ServiceResource::collection($services);
    }

    /**
     * Update barber services
     *
     * @param UpdateServicesRequest $request
     * @return JsonResponse
     */
    public function updateServices(UpdateServicesRequest $request): JsonResponse
    {
        $userId = Auth::id();
        $barber = $this->barberService->getBarberProfile($userId);

        if (!$barber) {
            return response()->json([
                'message' => 'Barber profile not found'
            ], 404);
        }

        $serviceIds = $request->input('service_ids');
        $priceOverrides = $request->input('price_overrides', []);

        $success = $this->barberService->updateBarberServices($barber->id, $serviceIds, $priceOverrides);

        if (!$success) {
            return response()->json([
                'message' => 'Failed to update services'
            ], 500);
        }

        return response()->json([
            'message' => 'Services updated successfully'
        ]);
    }

    /**
     * Get barber working hours
     *
     * @return JsonResponse|ResourceCollection
     */
    public function getWorkingHours()
    {
        $userId = Auth::id();
        $barber = $this->barberService->getBarberProfile($userId);

        if (!$barber) {
            return response()->json([
                'message' => 'Barber profile not found'
            ], 404);
        }

        $workingHours = $this->barberService->getWorkingHours($barber->id);

        return WorkingHourResource::collection($workingHours);
    }

    /**
     * Update barber working hours
     *
     * @param UpdateWorkingHoursRequest $request
     * @return JsonResponse
     */
    public function updateWorkingHours(UpdateWorkingHoursRequest $request): JsonResponse
    {
        $userId = Auth::id();
        $barber = $this->barberService->getBarberProfile($userId);

        if (!$barber) {
            return response()->json([
                'message' => 'Barber profile not found'
            ], 404);
        }

        $workingHours = $request->input('working_hours');

        try {
            $success = $this->barberService->updateWorkingHours($barber->id, $workingHours);

            if (!$success) {
                return response()->json([
                    'message' => 'Failed to update working hours'
                ], 500);
            }

            return response()->json([
                'message' => 'Working hours updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update working hours: ' . $e->getMessage()
            ], 500);
        }
    }
}
