<?php


namespace App\Modules\Barber\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckTimeSlotRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Anyone can check time slot availability
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
            'barber_id' => 'required|exists:barbers,id',
            'datetime' => 'required|date|after_or_equal:now',
            'service_id' => 'required|exists:services,id',
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
            'barber_id.required' => 'A barber is required to check time slot availability.',
            'barber_id.exists' => 'The selected barber does not exist.',
            'datetime.required' => 'A date and time is required to check availability.',
            'datetime.date' => 'The datetime must be a valid date format.',
            'datetime.after_or_equal' => 'The datetime must be now or in the future.',
            'service_id.required' => 'A service is required to check time slot availability.',
            'service_id.exists' => 'The selected service does not exist.',
        ];
    }
}
