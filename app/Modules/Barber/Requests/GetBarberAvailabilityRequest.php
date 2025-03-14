<?php

namespace App\Modules\Barber\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetBarberAvailabilityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Anyone can check barber availability
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
            'date' => 'required|date|after_or_equal:today',
            'service_id' => 'nullable|exists:services,id',
            'barber_id' => 'required|exists:barbers,id',
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
            'date.required' => 'A date is required to check availability.',
            'date.date' => 'The date must be a valid date format.',
            'date.after_or_equal' => 'The date must be today or in the future.',
            'service_id.exists' => 'The selected service does not exist.',
            'barber_id.required' => 'A barber is required to check availability.',
            'barber_id.exists' => 'The selected barber does not exist.',
        ];
    }
}
