<?php


namespace App\Modules\Barber\Events;

use App\Modules\Barber\Models\Barber;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BarberServiceUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The barber instance.
     *
     * @var Barber
     */
    public $barber;

    /**
     * The updated service IDs.
     *
     * @var array
     */
    public $serviceIds;

    /**
     * Create a new event instance.
     *
     * @param Barber $barber
     * @param array $serviceIds
     * @return void
     */
    public function __construct(Barber $barber, array $serviceIds)
    {
        $this->barber = $barber;
        $this->serviceIds = $serviceIds;
    }
}
