<?php

namespace App\Http\Requests\Task;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority'    => ['nullable', Rule::enum(TaskPriority::class)],
            'status'      => ['nullable', Rule::enum(TaskStatus::class)],
            'due_date'    => ['nullable', 'date', 'after_or_equal:today'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'title' => [
                'description' => 'The task title.',
                'example'     => 'Fix login bug',
            ],
            'description' => [
                'description' => 'The task description.',
                'example'     => 'The login button is not working on mobile.',
            ],
            'priority' => [
                'description' => 'The task priority (low, medium, high).',
                'example'     => 'high',
            ],
            'status' => [
                'description' => 'The task status (todo, in_progress, done).',
                'example'     => 'todo',
            ],
            'due_date' => [
                'description' => 'The task due date (YYYY-MM-DD).',
                'example'     => '2026-08-10',
            ],
        ];
    }
}
