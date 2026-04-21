<?php

namespace App\Http\Controllers\Personal;

use App\Http\Controllers\Controller;
use App\Models\Tasks\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PersonalTaskController extends Controller
{
    public function index(): View
    {
        $user  = auth()->user();
        $tasks = Task::forUser($user->id)
            ->with(['project', 'assignees'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('personal.tasks', compact('tasks'));
    }

    public function updateStatus(Request $request, Task $task): RedirectResponse
    {
        $userId = auth()->id();
        if (! $task->assignees()->where('users.id', $userId)->exists()) {
            abort(403);
        }

        $request->validate([
            'status' => ['required', 'in:todo,in_progress,review,done'],
        ]);

        $task->update(['status' => $request->status]);

        return back()->with('success', 'Task status updated.');
    }
}
