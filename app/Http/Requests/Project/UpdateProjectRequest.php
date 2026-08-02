<?php

namespace App\Http\Requests\Project;

use App\Enums\ProjectStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectRequest extends FormRequest
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
            'name'        => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status'      => ['nullable', Rule::enum(ProjectStatus::class)],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'name' => [
                'description' => 'The project name.',
                'example'     => 'Updated Project Name',
            ],
            'description' => [
                'description' => 'The project description.',
                'example'     => 'Updated description.',
            ],
            'status' => [
                'description' => 'The project status (active, completed, archived).',
                'example'     => 'completed',
            ],
        ];
    }
}
