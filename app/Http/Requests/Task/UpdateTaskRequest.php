<?php

namespace App\Http\Requests\Task;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority'    => ['nullable', Rule::enum(TaskPriority::class)],
            'status'      => ['nullable', Rule::enum(TaskStatus::class)],
            'due_date'    => ['nullable', 'date'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'title' => [
                'description' => 'The task title.',
                'example'     => 'Updated task title',
            ],
            'description' => [
                'description' => 'The task description.',
                'example'     => 'Updated description.',
            ],
            'priority' => [
                'description' => 'The task priority (low, medium, high).',
                'example'     => 'medium',
            ],
            'status' => [
                'description' => 'The task status (todo, in_progress, done).',
                'example'     => 'in_progress',
            ],
            'due_date' => [
                'description' => 'The task due date (YYYY-MM-DD).',
                'example'     => '2026-08-15',
            ],
        ];
    }
}
