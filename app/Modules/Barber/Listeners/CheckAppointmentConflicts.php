<?php


namespace App\Modules\Barber\Listeners;

use App\Modules\Barber\Events\BarberTimeOffCreated;
use App\Modules\Notification\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;

class CheckAppointmentConflicts implements ShouldQueue
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
     * @param BarberTimeOffCreated $event
     * @return void
     */
    public function handle(BarberTimeOffCreated $event)
    {
        // Get affected appointments that conflict with the new time off
        $affectedAppointments = DB::table('appointments')
            ->where('barber_id', $event->timeOff->barber_id)
            ->where('status', 'confirmed')
            ->where(function ($query) use ($event) {
                $query->where(function ($q) use ($event) {
                    $q->where('appointment_datetime', '<', $event->timeOff->end_datetime)
                        ->where('end_datetime', '>', $event->timeOff->start_datetime);
                });
            })
            ->get();

        foreach ($affectedAppointments as $appointment) {
            // Send notification to both barber and customer about the conflict
            $this->notificationService->sendTimeOffConflictNotification($appointment, $event->timeOff);
        }
    }
}
