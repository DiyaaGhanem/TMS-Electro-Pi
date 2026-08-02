<?php

namespace App\Jobs;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendOverdueNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public array $taskIds) {}

    public function handle(): void
    {
        $tasks = Task::whereIn('id', $this->taskIds)
            ->with('project.user')
            ->get();

        foreach ($tasks as $task) {
            $user = $task->project->user;

            // Log instead of sending mails 
            Log::info("Overdue notification sent", [
                'user'     => $user->email,
                'name'     => $user->name,
                'task'     => $task->title,
                'due_date' => $task->due_date->format('Y-m-d'),
            ]);

            $task->update(['overdue_notified' => true]);
        }
    }
}
