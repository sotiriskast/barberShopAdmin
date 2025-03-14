<?php

namespace App\Modules\Barber\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class BarberCollection extends ResourceCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = BarberResource::class;

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'data' => $this->collection,
            'pagination' => [
                'total' => $this->resource->total(),
                'count' => $this->resource->count(),
                'per_page' => $this->resource->perPage(),
                'current_page' => $this->resource->currentPage(),
                'total_pages' => $this->resource->lastPage(),
                'has_more_pages' => $this->resource->hasMorePages(),
            ],
            'meta' => [
                'filters' => $request->query(),
                'sort' => [
                    'by' => $request->query('sort_by', 'name'),
                    'direction' => $request->query('sort_direction', 'asc'),
                ],
            ],
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function with($request)
    {
        return [
            'success' => true,
            'message' => 'Barbers retrieved successfully',
        ];
    }
}
