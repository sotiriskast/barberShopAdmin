<?php


namespace App\Modules\Barber\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TimeOffResource extends JsonResource
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
            'start_datetime' => $this->start_datetime->format('Y-m-d H:i:s'),
            'end_datetime' => $this->end_datetime->format('Y-m-d H:i:s'),
            'reason' => $this->reason,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
