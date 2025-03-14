<?php


namespace App\Modules\Barber\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddTimeOffRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Only allow barbers to add their own time off
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
            'start_datetime' => 'required|date|after_or_equal:today',
            'end_datetime' => 'required|date|after:start_datetime',
            'reason' => 'nullable|string|max:255',
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
            'start_datetime.required' => 'The start date and time is required.',
            'start_datetime.after_or_equal' => 'The start date and time must be today or in the future.',
            'end_datetime.required' => 'The end date and time is required.',
            'end_datetime.after' => 'The end date and time must be after the start date and time.',
            'reason.max' => 'The reason cannot exceed 255 characters.',
        ];
    }
}
