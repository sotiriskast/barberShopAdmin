<?php


namespace App\Modules\Barber\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkingHoursRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Only allow barbers to update their own working hours
        // Note: Additional authorization logic is typically handled in the controller
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
            'working_hours' => 'required|array|size:7',
            'working_hours.*.day_of_week' => 'required|integer|min:0|max:6',
            'working_hours.*.start_time' => 'required_if:working_hours.*.is_day_off,false|date_format:H:i',
            'working_hours.*.end_time' => 'required_if:working_hours.*.is_day_off,false|date_format:H:i|after:working_hours.*.start_time',
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
            'working_hours.size' => 'You must provide working hours for all 7 days of the week.',
            'working_hours.*.day_of_week.min' => 'Day of week must be between 0 (Sunday) and 6 (Saturday).',
            'working_hours.*.day_of_week.max' => 'Day of week must be between 0 (Sunday) and 6 (Saturday).',
            'working_hours.*.start_time.required_if' => 'Start time is required when the day is not marked as day off.',
            'working_hours.*.end_time.required_if' => 'End time is required when the day is not marked as day off.',
            'working_hours.*.end_time.after' => 'End time must be after start time.',
        ];
    }
}
