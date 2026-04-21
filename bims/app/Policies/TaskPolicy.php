<?php

namespace App\Policies;

use App\Models\Tasks\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('tasks', 'view');
    }

    public function view(User $user, Task $task): bool
    {
        if ($user->hasPermission('tasks', 'view')) return true;
        // Assignees can always view their tasks
        return $task->assignees()->where('users.id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('tasks', 'create');
    }

    public function update(User $user, Task $task): bool
    {
        if ($user->hasPermission('tasks', 'edit')) return true;
        // Assignees can update status on their own tasks
        return $task->assignees()->where('users.id', $user->id)->exists();
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->hasPermission('tasks', 'delete');
    }

    public function assign(User $user): bool
    {
        return $user->hasPermission('tasks', 'create');
    }
}
