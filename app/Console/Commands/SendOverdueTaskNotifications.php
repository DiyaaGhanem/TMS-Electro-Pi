<?php

namespace App\Console\Commands;

use App\Enums\TaskStatus;
use App\Jobs\SendOverdueNotificationJob;
use App\Models\Task;
use Illuminate\Console\Command;

class SendOverdueTaskNotifications extends Command
{
    protected $signature   = 'tasks:send-overdue-notifications';
    protected $description = 'Send notifications for overdue tasks';

    public function handle(): void
    {
        $this->info('Checking for overdue tasks...');

        $tasks = Task::where('overdue_notified', false)
            ->where('status', '!=', TaskStatus::Done->value)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now())
            ->get();

        if ($tasks->isEmpty()) {
            $this->info('No overdue tasks found.');
            return;
        }

        $this->info("Found {$tasks->count()} overdue tasks. Dispatching jobs...");

        $chunks          = $tasks->chunk(10);
        $totalDispatched = 0;

        foreach ($chunks as $chunk) {
            SendOverdueNotificationJob::dispatch($chunk->pluck('id')->toArray());
            $totalDispatched += $chunk->count();
        }

        $this->info("Done! Dispatched notifications for {$totalDispatched} overdue tasks.");
    }
}
