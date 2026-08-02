<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Pagination\LengthAwarePaginator;

class TaskService
{
    public function index(Project $project, array $filters): LengthAwarePaginator
    {
        $query = $project->tasks();

        // Filter by status
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by priority
        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        // Search by title
        if (!empty($filters['search'])) {
            $query->where('title', 'like', '%' . $filters['search'] . '%');
        }

        return $query->paginate(10);
    }

    public function store(Project $project, array $data): Task
    {
        return $project->tasks()->create($data);
    }

    public function update(Task $task, array $data): Task
    {
        $task->update($data);
        return $task;
    }

    public function destroy(Task $task): void
    {
        $task->delete();
    }
}
