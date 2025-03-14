<?php


namespace App\Modules\Barber\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WorkingHourResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'barber_id' => $this->barber_id,
            'day_of_week' => $this->day_of_week,
            'day_name' => $this->day_name,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'is_day_off' => $this->is_day_off,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
