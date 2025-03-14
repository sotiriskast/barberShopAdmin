<?php

namespace App\Modules\User\Requests;

use App\Modules\User\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // In a real-world app, you would check permissions here
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($this->user),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', Rule::in(User::roles())],
            'profile_image' => ['nullable', 'image', 'max:2048'], // 2MB max
        ];

        // Add password rule for creating new users
        if ($this->isMethod('post')) {
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        } else if ($this->isMethod('put') || $this->isMethod('patch')) {
            // Make password optional for updates
            $rules['password'] = ['nullable', 'string', 'min:8', 'confirmed'];
        }

        return $rules;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'email.required' => 'The email field is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already in use.',
            'role.required' => 'The role field is required.',
            'role.in' => 'The selected role is invalid.',
            'password.required' => 'The password field is required.',
            'password.min' => 'The password must be at least 8 characters.',
            'password.confirmed' => 'The password confirmation does not match.',
            'profile_image.image' => 'The uploaded file must be an image.',
            'profile_image.max' => 'The image may not be greater than 2MB.',
        ];
    }
}
