<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'name' => [
                'description' => 'The full name of the user.',
                'example' => 'John Doe',
            ],
            'email' => [
                'description' => 'A unique email address used for logging in.',
                'example' => 'john@example.com',
            ],
            'password' => [
                'description' => 'A secure password (minimum 8 characters).',
                'example' => 'password',
            ],
            'password_confirmation' => [
                'description' => 'Must match the password field.',
                'example' => 'password',
            ],
        ];
    }
}
