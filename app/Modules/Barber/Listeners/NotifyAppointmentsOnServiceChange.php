<?php

namespace App\Modules\Barber\Listeners;

use App\Modules\Barber\Events\BarberServiceUpdated;
use App\Modules\Notification\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;

class NotifyAppointmentsOnServiceChange implements ShouldQueue
{
    /**
     * @var NotificationService
     */
    protected $notificationService;

    /**
     * Create the event listener.
     *
     * @param NotificationService $notificationService
     * @return void
     */
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the event.
     *
     * @param BarberServiceUpdated $event
     * @return void
     */
    public function handle(BarberServiceUpdated $event)
    {
        // Get affected appointments that were scheduled for services that are no longer offered
        $affectedAppointments = DB::table('appointments')
            ->where('barber_id', $event->barber->id)
            ->where('status', 'confirmed')
            ->whereDate('appointment_datetime', '>', now())
            ->whereNotIn('service_id', $event->serviceIds)
            ->get();

        foreach ($affectedAppointments as $appointment) {
            // Send notification to customer about the service change
            $this->notificationService->sendServiceChangeNotification($appointment);
        }
    }
}
