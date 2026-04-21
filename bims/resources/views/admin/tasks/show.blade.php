@extends('layouts.app')
@section('title', $task->title)
@section('page-title', 'Task Detail')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <a href="{{ route('admin.tasks.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Tasks</a>
    <form method="POST" action="{{ route('admin.tasks.destroy', $task) }}"
          onsubmit="return confirm('Delete this task?')">
        @csrf @method('DELETE')
        <button type="submit" class="text-sm text-red-600 hover:text-red-900">Delete Task</button>
    </form>
</div>

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    {{-- Main detail --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Task info + edit --}}
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-base font-semibold text-gray-900">{{ $task->title }}</h2>
                @if($task->project)
                <p class="text-sm text-gray-500 mt-1">{{ $task->project->name }}</p>
                @endif
            </div>
            <form method="POST" action="{{ route('admin.tasks.update', $task) }}" class="px-6 py-5 space-y-4">
                @csrf @method('PUT')
                <div>
                    <label class="block text-sm font-medium text-gray-900">Title</label>
                    <input type="text" name="title" required value="{{ old('title', $task->title) }}"
                           class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-900">Description</label>
                    <textarea name="description" rows="4"
                              class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">{{ old('description', $task->description) }}</textarea>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-900">Status</label>
                        <select name="status"
                                class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                            @foreach(['todo' => 'To Do','in_progress' => 'In Progress','review' => 'In Review','done' => 'Done','cancelled' => 'Cancelled'] as $val => $label)
                            <option value="{{ $val }}" {{ old('status', $task->status) === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-900">Priority</label>
                        <select name="priority"
                                class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                            @foreach(['Low','Medium','High','Urgent'] as $p)
                            <option value="{{ $p }}" {{ old('priority', $task->priority) === $p ? 'selected' : '' }}>{{ $p }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-900">Due Date</label>
                        <input type="date" name="due_date"
                               value="{{ old('due_date', $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('Y-m-d') : '') }}"
                               class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit"
                            class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>

        {{-- Comments --}}
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900">Comments ({{ $task->comments->count() }})</h3>
            </div>
            <div class="px-6 py-4 space-y-4">
                @foreach($task->comments as $comment)
                <div class="flex gap-3">
                    <div class="h-7 w-7 shrink-0 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 text-xs font-semibold">
                        {{ substr($comment->user->name, 0, 1) }}
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-900">{{ $comment->user->name }}
                            <span class="font-normal text-gray-400">· {{ $comment->created_at->diffForHumans() }}</span>
                        </p>
                        <p class="text-sm text-gray-700 mt-0.5">{{ $comment->body }}</p>
                    </div>
                </div>
                @endforeach

                <form method="POST" action="{{ route('admin.tasks.comment', $task) }}" class="mt-4">
                    @csrf
                    <textarea name="body" rows="2" required placeholder="Add a comment…"
                              class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm"></textarea>
                    <div class="flex justify-end mt-2">
                        <button type="submit"
                                class="rounded-md bg-gray-900 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-gray-700">
                            Comment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-4">
        {{-- Assignees --}}
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900">Assignees</h3>
            </div>
            <div class="px-6 py-4">
                @if($task->assignees->isEmpty())
                <p class="text-sm text-gray-500">None assigned.</p>
                @else
                <div class="space-y-2">
                    @foreach($task->assignees as $assignee)
                    <p class="text-sm text-gray-900">{{ $assignee->name }}</p>
                    @endforeach
                </div>
                @endif

                <form method="POST" action="{{ route('admin.tasks.assign', $task) }}" class="mt-4">
                    @csrf
                    <select name="employee_id" required
                            class="block w-full rounded-md border-0 py-1.5 px-3 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 mb-2">
                        <option value="">— Add assignee —</option>
                        @foreach($employees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->display_name }}</option>
                        @endforeach
                    </select>
                    <button type="submit"
                            class="w-full rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                        Assign
                    </button>
                </form>
            </div>
        </div>

        {{-- Subtasks --}}
        @if($task->subtasks->isNotEmpty())
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900">Subtasks</h3>
            </div>
            <ul class="divide-y divide-gray-100">
                @foreach($task->subtasks as $sub)
                <li class="px-6 py-3">
                    <a href="{{ route('admin.tasks.show', $sub) }}" class="text-sm text-indigo-600 hover:text-indigo-900">{{ $sub->title }}</a>
                    <span class="text-xs text-gray-400 ml-2">{{ $sub->status }}</span>
                </li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>
</div>
@endsection
