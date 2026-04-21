<?php

namespace App\Notifications;

use App\Models\Tasks\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskAssigned extends Notification
{
    use Queueable;

    public function __construct(private Task $task) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $priority = $this->task->priority;
        $due      = $this->task->due_date?->format('M j') ?? 'no due date';

        return [
            'icon'     => 'task',
            'message'  => "You've been assigned to task \"{$this->task->title}\" ({$priority}, due {$due}).",
            'task_id'  => $this->task->id,
            'url'      => route('admin.tasks.show', $this->task),
        ];
    }
}
