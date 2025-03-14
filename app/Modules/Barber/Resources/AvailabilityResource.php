<?php


namespace App\Modules\Barber\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AvailabilityResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'date' => $this['date'] ?? null,
            'barber_id' => $this['barber_id'] ?? null,
            'service_id' => $this['service_id'] ?? null,
            'time_slots' => $this['time_slots'] ?? [],
        ];
    }
}
