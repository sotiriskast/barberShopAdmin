<?php

namespace App\Modules\Shop\Resources;

use App\Modules\Barber\Resources\BarberResource;
use App\Modules\Service\Resources\ServiceResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return array_merge(
            (new ShopResource($this))->toArray($request),
            [
                'owner' => [
                    'id' => $this->owner->id,
                    'name' => $this->owner->name,
                    'email' => $this->owner->email,
                ],
                'barbers' => BarberResource::collection($this->whenLoaded('barbers')),
                'services' => ServiceResource::collection($this->whenLoaded('services')),
                'review_summary' => [
                    'average_rating' => $this->average_rating,
                    'review_count' => $this->review_count,
                    'rating_distribution' => $this->whenLoaded('reviews', function () {
                        $distribution = [
                            1 => 0,
                            2 => 0,
                            3 => 0,
                            4 => 0,
                            5 => 0,
                        ];

                        foreach ($this->reviews as $review) {
                            $distribution[$review->rating]++;
                        }

                        return $distribution;
                    }),
                ],
            ]
        );
    }
}
