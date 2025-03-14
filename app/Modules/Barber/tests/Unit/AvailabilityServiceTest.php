<?php

namespace App\Modules\Barber\Tests\Unit;

use App\Modules\Barber\Models\Barber;
use App\Modules\Barber\Models\TimeOff;
use App\Modules\Barber\Models\WorkingHour;
use App\Modules\Barber\Repositories\Eloquent\BarberRepository;
use App\Modules\Barber\Services\AvailabilityService;
use App\Modules\Service\Models\Service;
use App\Modules\Service\Repositories\Eloquent\ServiceRepository;
use App\Modules\Shop\Models\Shop;
use App\Modules\User\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvailabilityServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var AvailabilityService
     */
    protected $availabilityService;

    /**
     * @var Barber
     */
    protected $barber;

    /**
     * @var Service
     */
    protected $service;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        $user = User::factory()->create(['role' => 'barber']);
        $shop = Shop::factory()->create();

        $this->barber = Barber::factory()->create([
            'user_id' => $user->id,
            'shop_id' => $shop->id,
        ]);

        $this->service = Service::factory()->create([
            'shop_id' => $shop->id,
            'duration' => 30, // 30-minute service
        ]);

        // Add working hours for the barber
        $this->setupWorkingHours();

        // Setup repositories
        $barberRepository = new BarberRepository(new Barber());
        $serviceRepository = new ServiceRepository(new Service());

        // Create service instance
        $this->availabilityService = new AvailabilityService(
            $barberRepository,
            $serviceRepository
        );
    }

    /**
     * Setup working hours for the test barber
     */
    protected function setupWorkingHours()
    {
        // Create working hours for each day of the week
        for ($day = 0; $day < 7; $day++) {
            WorkingHour::create([
                'barber_id' => $this->barber->id,
                'day_of_week' => $day,
                'start_time' => '09:00',
                'end_time' => '17:00',
                'is_day_off' => $day === 0, // Sunday is a day off
            ]);
        }
    }

    /**
     * Test that getAvailableTimeSlots returns empty array on day off
     *
     * @return void
     */
    public function testGetAvailableTimeSlotsOnDayOff()
    {
        // Get a Sunday date
        $sunday = Carbon::now()->next(Carbon::SUNDAY)->format('Y-m-d');

        $slots = $this->availabilityService->getAvailableTimeSlots(
            $this->barber->id,
            $sunday,
            $this->service->id
        );

        $this->assertEmpty($slots);
    }

    /**
     * Test that getAvailableTimeSlots returns slots on working day
     *
     * @return void
     */
    public function testGetAvailableTimeSlotsOnWorkingDay()
    {
        // Get a Monday date
        $monday = Carbon::now()->next(Carbon::MONDAY)->format('Y-m-d');

        $slots = $this->availabilityService->getAvailableTimeSlots(
            $this->barber->id,
            $monday,
            $this->service->id
        );

        $this->assertNotEmpty($slots);

        // Check that the first slot starts at 9:00
        $firstSlot = $slots[0];
        $this->assertEquals($monday . ' 09:00', $firstSlot['start']);

        // Check that the last slot ends by 17:00
        $lastSlot = end($slots);
        $this->assertLessThanOrEqual($monday . ' 17:00', $lastSlot['end']);
    }

    /**
     * Test that time off periods are respected
     *
     * @return void
     */
    public function testTimeOffPeriodsAreRespected()
    {
        // Get a Monday date
        $monday = Carbon::now()->next(Carbon::MONDAY)->format('Y-m-d');

        // Add time off from 12:00 to 13:00
        TimeOff::create([
            'barber_id' => $this->barber->id,
            'start_datetime' => $monday . ' 12:00:00',
            'end_datetime' => $monday . ' 13:00:00',
            'reason' => 'Lunch break',
        ]);

        $slots = $this->availabilityService->getAvailableTimeSlots(
            $this->barber->id,
            $monday,
            $this->service->id
        );

        // Check that no slot overlaps with the time off period
        foreach ($slots as $slot) {
            $slotStart = Carbon::parse($slot['start']);
            $slotEnd = Carbon::parse($slot['end']);
            $timeOffStart = Carbon::parse($monday . ' 12:00:00');
            $timeOffEnd = Carbon::parse($monday . ' 13:00:00');

            $this->assertFalse(
                $slotStart < $timeOffEnd && $slotEnd > $timeOffStart,
                'Slot should not overlap with time off period'
            );
        }
    }

    /**
     * Test that isTimeSlotAvailable returns correct result
     *
     * @return void
     */
    public function testIsTimeSlotAvailable()
    {
        // Get a Monday date
        $monday = Carbon::now()->next(Carbon::MONDAY)->format('Y-m-d');

        // Check that 10:00 is available
        $isAvailable = $this->availabilityService->isTimeSlotAvailable(
            $this->barber->id,
            $monday . ' 10:00:00',
            $this->service->duration
        );

        $this->assertTrue($isAvailable);

        // Add time off at 10:00
        TimeOff::create([
            'barber_id' => $this->barber->id,
            'start_datetime' => $monday . ' 10:00:00',
            'end_datetime' => $monday . ' 10:30:00',
            'reason' => 'Short break',
        ]);

        // Check that 10:00 is no longer available
        $isAvailable = $this->availabilityService->isTimeSlotAvailable(
            $this->barber->id,
            $monday . ' 10:00:00',
            $this->service->duration
        );

        $this->assertFalse($isAvailable);
    }
}
