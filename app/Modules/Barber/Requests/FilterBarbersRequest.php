<?php

namespace App\Modules\Barber\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FilterBarbersRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Anyone can filter barbers
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'shop_id' => 'nullable|exists:shops,id',
            'service_id' => 'nullable|exists:services,id',
            'min_experience' => 'nullable|integer|min:0',
            'max_experience' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'available_on_date' => 'nullable|date',
            'search' => 'nullable|string|max:100',
            'sort_by' => 'nullable|in:name,experience,reviews,popularity',
            'sort_direction' => 'nullable|in:asc,desc',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'shop_id.exists' => 'The selected shop does not exist.',
            'service_id.exists' => 'The selected service does not exist.',
            'min_experience.min' => 'Minimum experience cannot be negative.',
            'max_experience.min' => 'Maximum experience cannot be negative.',
            'available_on_date.date' => 'The date must be a valid date format.',
            'search.max' => 'The search term cannot exceed 100 characters.',
            'sort_by.in' => 'Invalid sort option. Available options: name, experience, reviews, popularity.',
            'sort_direction.in' => 'Sort direction must be asc or desc.',
            'page.min' => 'Page must be at least 1.',
            'per_page.min' => 'Items per page must be at least 1.',
            'per_page.max' => 'Items per page cannot exceed 100.',
        ];
    }
}
