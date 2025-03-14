<?php


namespace App\Modules\Barber\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BarberResource extends JsonResource
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
            'title' => $this->title,
            'bio' => $this->bio,
            'years_experience' => $this->years_experience,
            'instagram_handle' => $this->instagram_handle,
            'is_active' => $this->is_active,
            'shop_id' => $this->shop_id,
            'shop' => $this->whenLoaded('shop', function () {
                return [
                    'id' => $this->shop->id,
                    'name' => $this->shop->name,
                    'address' => $this->shop->address,
                    'city' => $this->shop->city,
                    'state' => $this->shop->state,
                    'postal_code' => $this->shop->postal_code,
                ];
            }),
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                    'profile_image' => $this->user->profile_image,
                ];
            }),
            'services' => ServiceResource::collection($this->whenLoaded('services')),
            'working_hours' => WorkingHourResource::collection($this->whenLoaded('workingHours')),
            'time_off' => TimeOffResource::collection($this->whenLoaded('timeOff')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
