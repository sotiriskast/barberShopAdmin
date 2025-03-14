<?php


namespace App\Modules\Barber\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServicesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Only allow barbers to update their own services
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
            'service_ids' => 'required|array',
            'service_ids.*' => 'required|integer|exists:services,id',
            'price_overrides' => 'nullable|array',
            'price_overrides.*' => 'nullable|numeric|min:0',
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
            'service_ids.required' => 'At least one service must be selected.',
            'service_ids.*.exists' => 'One or more selected services do not exist.',
            'price_overrides.*.numeric' => 'Price override must be a valid number.',
            'price_overrides.*.min' => 'Price override cannot be negative.',
        ];
    }
}
