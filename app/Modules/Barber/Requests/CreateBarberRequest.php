<?php


namespace App\Modules\Barber\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateBarberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Only allow shop owners or admin to create barbers
        return auth()->user()->role === 'shop_owner' || auth()->user()->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user_id' => 'required|exists:users,id',
            'shop_id' => 'required|exists:shops,id',
            'title' => 'nullable|string|max:100',
            'bio' => 'nullable|string|max:1000',
            'years_experience' => 'nullable|integer|min:0|max:100',
            'instagram_handle' => 'nullable|string|max:100',
            'is_active' => 'nullable|boolean',
            'services' => 'nullable|array',
            'services.*.id' => 'required|exists:services,id',
            'services.*.price_override' => 'nullable|numeric|min:0',
            'working_hours' => 'nullable|array',
            'working_hours.*.day_of_week' => 'required|integer|min:0|max:6',
            'working_hours.*.start_time' => 'required_if:working_hours.*.is_day_off,false|nullable|date_format:H:i',
            'working_hours.*.end_time' => 'required_if:working_hours.*.is_day_off,false|nullable|date_format:H:i|after:working_hours.*.start_time',
            'working_hours.*.is_day_off' => 'required|boolean',
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
            'user_id.required' => 'A user is required for the barber profile.',
            'user_id.exists' => 'The selected user does not exist.',
            'shop_id.required' => 'A shop is required for the barber profile.',
            'shop_id.exists' => 'The selected shop does not exist.',
            'title.max' => 'The title cannot exceed 100 characters.',
            'bio.max' => 'The bio cannot exceed 1000 characters.',
            'years_experience.min' => 'Experience years must be at least 0.',
            'years_experience.max' => 'Experience years cannot exceed 100.',
            'instagram_handle.max' => 'Instagram handle cannot exceed 100 characters.',
            'services.*.id.exists' => 'One or more selected services do not exist.',
            'services.*.price_override.min' => 'Price override cannot be negative.',
            'working_hours.*.day_of_week.min' => 'Day of week must be between 0 (Sunday) and 6 (Saturday).',
            'working_hours.*.day_of_week.max' => 'Day of week must be between 0 (Sunday) and 6 (Saturday).',
            'working_hours.*.start_time.required_if' => 'Start time is required when the day is not marked as day off.',
            'working_hours.*.end_time.required_if' => 'End time is required when the day is not marked as day off.',
            'working_hours.*.end_time.after' => 'End time must be after start time.',
        ];
    }
}
