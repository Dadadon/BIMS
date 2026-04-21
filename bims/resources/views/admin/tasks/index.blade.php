@extends('layouts.app')
@section('title', 'Tasks')
@section('page-title', 'Tasks')

@section('content')
<div class="sm:flex sm:items-center sm:justify-between mb-6">
    <h2 class="text-xl font-semibold text-gray-900">Tasks</h2>
    <div class="flex gap-3 mt-4 sm:mt-0">
        <a href="{{ route('admin.tasks.projects') }}"
           class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
            Projects
        </a>
        <button type="button" onclick="document.getElementById('new-task-modal').classList.remove('hidden')"
                class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
            + New Task
        </button>
    </div>
</div>

{{-- Kanban-style status columns --}}
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
    @foreach(['todo' => 'To Do', 'in_progress' => 'In Progress', 'review' => 'In Review', 'done' => 'Done'] as $statusKey => $statusGroup)
    @php
        $grouped = $tasks->getCollection()->filter(fn($t) => $t->status === $statusKey);
        $colors  = [
            'todo'        => 'bg-gray-100 text-gray-700',
            'in_progress' => 'bg-blue-100 text-blue-700',
            'review'      => 'bg-yellow-100 text-yellow-800',
            'done'        => 'bg-green-100 text-green-700',
        ];
    @endphp
    <div class="rounded-lg bg-white shadow">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
            <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium {{ $colors[$statusKey] }}">
                {{ $statusGroup }}
            </span>
            <span class="text-xs text-gray-500">{{ $grouped->count() }}</span>
        </div>
        <div class="p-3 space-y-2">
            @forelse($grouped as $task)
            <a href="{{ route('admin.tasks.show', $task) }}"
               class="block rounded-md border border-gray-200 bg-gray-50 p-3 hover:border-indigo-300 hover:bg-indigo-50 transition">
                <p class="text-sm font-medium text-gray-900 line-clamp-2">{{ $task->title }}</p>
                @if($task->project)
                <p class="text-xs text-gray-500 mt-1">{{ $task->project->name }}</p>
                @endif
                <div class="flex items-center justify-between mt-2">
                    @php
                        $pc = [
                            'Low'    => 'bg-gray-100 text-gray-600',
                            'Medium' => 'bg-blue-100 text-blue-700',
                            'High'   => 'bg-orange-100 text-orange-700',
                            'Urgent' => 'bg-red-100 text-red-700',
                        ][$task->priority] ?? 'bg-gray-100 text-gray-600';
                    @endphp
                    <span class="inline-flex rounded px-1.5 py-0.5 text-xs font-medium {{ $pc }}">{{ $task->priority }}</span>
                    @if($task->due_date)
                    <span class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($task->due_date)->format('M j') }}</span>
                    @endif
                </div>
            </a>
            @empty
            <p class="text-xs text-gray-400 text-center py-4">Empty</p>
            @endforelse
        </div>
    </div>
    @endforeach
</div>

{{-- New Task Modal --}}
<div id="new-task-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-lg shadow-xl max-w-lg w-full mx-4">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between">
            <h3 class="text-sm font-semibold text-gray-900">Create Task</h3>
            <button onclick="document.getElementById('new-task-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>
        <form method="POST" action="{{ route('admin.tasks.store') }}" class="px-6 py-5 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-900">Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" required
                       class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-900">Project</label>
                    <select name="project_id"
                            class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                        <option value="">— None —</option>
                        @foreach($projects as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-900">Priority</label>
                    <select name="priority"
                            class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                        @foreach(['Low','Medium','High','Urgent'] as $p)
                        <option value="{{ $p }}" {{ $p === 'Medium' ? 'selected' : '' }}>{{ $p }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-900">Due Date</label>
                <input type="date" name="due_date"
                       class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-900">Description</label>
                <textarea name="description" rows="3"
                          class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm"></textarea>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('new-task-modal').classList.add('hidden')"
                        class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Cancel</button>
                <button type="submit"
                        class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Create</button>
            </div>
        </form>
    </div>
</div>
@endsection
