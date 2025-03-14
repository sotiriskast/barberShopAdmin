<?php


namespace App\Modules\Barber\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBarberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Only allow barbers to update their own profile
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
            'title' => 'nullable|string|max:100',
            'bio' => 'nullable|string|max:1000',
            'years_experience' => 'nullable|integer|min:0|max:100',
            'instagram_handle' => 'nullable|string|max:100',
            'is_active' => 'nullable|boolean',
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
            'title.max' => 'The title cannot exceed 100 characters.',
            'bio.max' => 'The bio cannot exceed 1000 characters.',
            'years_experience.min' => 'Experience years must be at least 0.',
            'years_experience.max' => 'Experience years cannot exceed 100.',
            'instagram_handle.max' => 'Instagram handle cannot exceed 100 characters.',
        ];
    }
}
