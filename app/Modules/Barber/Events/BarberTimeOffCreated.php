<?php


namespace App\Modules\Barber\Events;

use App\Modules\Barber\Models\TimeOff;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BarberTimeOffCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The time off instance.
     *
     * @var TimeOff
     */
    public $timeOff;

    /**
     * Create a new event instance.
     *
     * @param TimeOff $timeOff
     * @return void
     */
    public function __construct(TimeOff $timeOff)
    {
        $this->timeOff = $timeOff;
    }
}
