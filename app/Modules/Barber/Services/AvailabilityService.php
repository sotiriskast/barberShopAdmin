<?php

namespace App\Modules\Barber\Services;

use App\Modules\Barber\Models\Barber;
use App\Modules\Barber\Repositories\Interfaces\BarberRepositoryInterface;
use App\Modules\Service\Repositories\Interfaces\ServiceRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AvailabilityService
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
     * Default time slot interval in minutes
     *
     * @var int
     */
    protected $slotInterval = 15;

    /**
     * AvailabilityService constructor.
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
     * Get available time slots for a barber on a specific date
     *
     * @param int $barberId
     * @param string $date
     * @param int|null $serviceId
     * @return array
     */
    public function getAvailableTimeSlots(int $barberId, string $date, ?int $serviceId = null): array
    {
        // Get barber with availability data
        $barber = $this->barberRepository->findWithAvailabilityData($barberId);

        if (!$barber) {
            return [];
        }

        // Get service duration
        $serviceDuration = 30; // Default 30 minutes
        if ($serviceId) {
            $service = $this->serviceRepository->findById($serviceId);
            if ($service) {
                $serviceDuration = $service->duration;
            }
        }

        // Get working hours for the day
        $dayOfWeek = Carbon::parse($date)->dayOfWeek;
        $workingHours = $barber->workingHours()
            ->where('day_of_week', $dayOfWeek)
            ->first();

        if (!$workingHours || $workingHours->is_day_off) {
            return [];
        }

        // Get time off periods for this date
        $timeOffPeriods = $barber->timeOff()
            ->where('start_datetime', '<=', $date . ' 23:59:59')
            ->where('end_datetime', '>=', $date . ' 00:00:00')
            ->get();

        // Get existing appointments for this date
        $existingAppointments = $barber->appointments()
            ->whereDate('appointment_datetime', $date)
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->get();

        // Generate available time slots
        return $this->generateAvailableTimeSlots(
            $date,
            $workingHours->start_time,
            $workingHours->end_time,
            $serviceDuration,
            $existingAppointments,
            $timeOffPeriods
        );
    }

    /**
     * Generate available time slots
     *
     * @param string $date
     * @param string $startTime
     * @param string $endTime
     * @param int $serviceDuration
     * @param Collection $existingAppointments
     * @param Collection $timeOffPeriods
     * @return array
     */
    protected function generateAvailableTimeSlots(
        string $date,
        string $startTime,
        string $endTime,
        int $serviceDuration,
        Collection $existingAppointments,
        Collection $timeOffPeriods
    ): array {
        $startTime = Carbon::parse($date . ' ' . $startTime);
        $endTime = Carbon::parse($date . ' ' . $endTime);

        $currentTime = Carbon::now();
        if ($startTime->lt($currentTime)) {
            $startTime = $currentTime->copy()->addMinutes($this->slotInterval - ($currentTime->minute % $this->slotInterval));
        }

        $availableSlots = [];
        $currentSlot = $startTime->copy();

        while ($currentSlot->copy()->addMinutes($serviceDuration) <= $endTime) {
            $slotStart = $currentSlot->copy();
            $slotEnd = $slotStart->copy()->addMinutes($serviceDuration);

            // Check if slot conflicts with appointments
            $hasConflict = $existingAppointments->some(function ($appointment) use ($slotStart, $slotEnd) {
                $apptStart = Carbon::parse($appointment->appointment_datetime);
                $apptEnd = Carbon::parse($appointment->end_datetime);

                return $slotStart->lt($apptEnd) && $slotEnd->gt($apptStart);
            });

            // Check if slot conflicts with time off
            $hasTimeOffConflict = $timeOffPeriods->some(function ($timeOff) use ($slotStart, $slotEnd) {
                $timeOffStart = Carbon::parse($timeOff->start_datetime);
                $timeOffEnd = Carbon::parse($timeOff->end_datetime);

                return $slotStart->lt($timeOffEnd) && $slotEnd->gt($timeOffStart);
            });

            if (!$hasConflict && !$hasTimeOffConflict) {
                $availableSlots[] = [
                    'start' => $slotStart->format('Y-m-d H:i'),
                    'end' => $slotEnd->format('Y-m-d H:i'),
                ];
            }

            $currentSlot->addMinutes($this->slotInterval);
        }

        return $availableSlots;
    }

    /**
     * Check if a specific time slot is available for booking
     *
     * @param int $barberId
     * @param string $dateTime
     * @param int $serviceDuration
     * @return bool
     */
    public function isTimeSlotAvailable(int $barberId, string $dateTime, int $serviceDuration): bool
    {
        $barber = $this->barberRepository->findWithAvailabilityData($barberId);

        if (!$barber) {
            return false;
        }

        $startTime = Carbon::parse($dateTime);
        $endTime = $startTime->copy()->addMinutes($serviceDuration);
        $date = $startTime->format('Y-m-d');

        // Check if the time is within working hours
        $dayOfWeek = $startTime->dayOfWeek;
        $workingHours = $barber->workingHours()
            ->where('day_of_week', $dayOfWeek)
            ->first();

        if (!$workingHours || $workingHours->is_day_off) {
            return false;
        }

        $workStart = Carbon::parse($date . ' ' . $workingHours->start_time);
        $workEnd = Carbon::parse($date . ' ' . $workingHours->end_time);

        if ($startTime->lt($workStart) || $endTime->gt($workEnd)) {
            return false;
        }

        // Check for conflicts with existing appointments
        $existingAppointments = $barber->appointments()
            ->whereDate('appointment_datetime', $date)
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->get();

        foreach ($existingAppointments as $appointment) {
            $apptStart = Carbon::parse($appointment->appointment_datetime);
            $apptEnd = Carbon::parse($appointment->end_datetime);

            if ($startTime->lt($apptEnd) && $endTime->gt($apptStart)) {
                return false;
            }
        }

        // Check for conflicts with time off
        $timeOffPeriods = $barber->timeOff()
            ->where('start_datetime', '<=', $endTime->format('Y-m-d H:i:s'))
            ->where('end_datetime', '>=', $startTime->format('Y-m-d H:i:s'))
            ->exists();

        if ($timeOffPeriods) {
            return false;
        }

        return true;
    }
}
