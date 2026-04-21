<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HR\Employee;
use App\Models\Tasks\Project;
use App\Models\Tasks\Task;
use App\Models\Tasks\TaskComment;
use App\Models\User;
use App\Notifications\TaskAssigned;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(): View
    {
        $tasks = Task::with(['project', 'assignees'])
            ->whereNull('parent_task_id')
            ->orderByDesc('created_at')
            ->paginate(25);

        $projects = Project::orderBy('name')->get();

        return view('admin.tasks.index', compact('tasks', 'projects'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'project_id'    => ['nullable', 'exists:projects,id'],
            'parent_task_id'=> ['nullable', 'exists:tasks,id'],
            'title'         => ['required', 'string', 'max:255'],
            'description'   => ['nullable', 'string'],
            'priority'      => ['required', 'in:Low,Medium,High,Urgent'],
            'due_date'      => ['nullable', 'date'],
        ]);

        Task::create([
            ...$validated,
            'status'     => 'todo',
            'created_by' => auth()->id(),
        ]);

        return back()->with('success', 'Task created.');
    }

    public function show(Task $task): View
    {
        $task->load(['project', 'assignees', 'subtasks.assignees', 'comments.user', 'creator']);
        $employees = Employee::active()->orderBy('lastname')->get();

        return view('admin.tasks.show', compact('task', 'employees'));
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status'      => ['required', 'in:todo,in_progress,review,done,cancelled'],
            'priority'    => ['required', 'in:Low,Medium,High,Urgent'],
            'due_date'    => ['nullable', 'date'],
        ]);

        $task->update($validated);

        return back()->with('success', 'Task updated.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $task->delete();
        return redirect()->route('admin.tasks.index')->with('success', 'Task deleted.');
    }

    public function assign(Request $request, Task $task): RedirectResponse
    {
        $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
        ]);

        $user = User::where('employee_id', $request->employee_id)->first();
        if (! $user) {
            return back()->with('error', 'Employee has no user account.');
        }

        $task->assignees()->syncWithoutDetaching([$user->id]);

        if ($user->id !== auth()->id()) {
            $user->notify(new TaskAssigned($task));
        }

        return back()->with('success', 'Assignee added.');
    }

    public function comment(Request $request, Task $task): RedirectResponse
    {
        $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        TaskComment::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'body'    => $request->body,
        ]);

        return back()->with('success', 'Comment added.');
    }

    public function projects(): View
    {
        $projects = Project::withCount('tasks')->orderBy('name')->paginate(20);
        return view('admin.tasks.projects', compact('projects'));
    }

    public function storeProject(Request $request): RedirectResponse
    {
        $request->validate([
            'name'        => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        Project::create([
            'name'        => $request->name,
            'description' => $request->description,
            'created_by'  => auth()->id(),
        ]);

        return back()->with('success', 'Project created.');
    }
}
