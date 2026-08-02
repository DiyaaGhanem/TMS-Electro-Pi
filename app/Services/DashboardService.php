<?php

namespace App\Services;

use App\Enums\ProjectStatus;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;

class DashboardService
{
    public function getStats(): array
    {
        $userId = Auth::id();

        $projectIds = Project::where('user_id', $userId)->pluck('id');

        $totalProjects  = $projectIds->count();
        $activeProjects = Project::where('user_id', $userId)
            ->where('status', ProjectStatus::Active->value)
            ->count();

        $totalTasks = Task::whereIn('project_id', $projectIds)->count();

        $completedTasks = Task::whereIn('project_id', $projectIds)
            ->where('status', TaskStatus::Done->value)
            ->count();

        $pendingTasks = Task::whereIn('project_id', $projectIds)
            ->whereIn('status', [TaskStatus::Todo->value, TaskStatus::InProgress->value])
            ->count();

        $overdueTasks = Task::whereIn('project_id', $projectIds)
            ->where('status', '!=', TaskStatus::Done->value)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now())
            ->count();

        return [
            'total_projects'  => $totalProjects,
            'active_projects' => $activeProjects,
            'total_tasks'     => $totalTasks,
            'completed_tasks' => $completedTasks,
            'pending_tasks'   => $pendingTasks,
            'overdue_tasks'   => $overdueTasks,
        ];
    }
}
