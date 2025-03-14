<?php


namespace App\Modules\Barber\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $price = $this->pivot && $this->pivot->price_override
            ? $this->pivot->price_override
            : $this->price;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'duration' => $this->duration,
            'price' => $price,
            'original_price' => $this->price,
            'price_override' => $this->when($this->pivot, function () {
                return $this->pivot->price_override;
            }),
            'image' => $this->image,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
