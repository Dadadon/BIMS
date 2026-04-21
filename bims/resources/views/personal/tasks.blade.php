@extends('layouts.app')
@section('title', 'My Tasks')
@section('page-title', 'My Tasks')

@section('content')
<h2 class="text-xl font-semibold text-gray-900 mb-6">Assigned Tasks</h2>

<div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
    <table class="min-w-full divide-y divide-gray-300">
        <thead class="bg-gray-50">
            <tr>
                <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Task</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 hidden sm:table-cell">Project</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Priority</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Due</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                <th class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">Update</span></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
            @forelse($tasks as $task)
            <tr class="hover:bg-gray-50">
                <td class="py-4 pl-4 pr-3 text-sm sm:pl-6">
                    <p class="font-medium text-gray-900">{{ $task->title }}</p>
                    @if($task->description)
                    <p class="text-xs text-gray-500 truncate max-w-xs">{{ $task->description }}</p>
                    @endif
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 hidden sm:table-cell">{{ $task->project->name ?? '—' }}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm">
                    @php
                        $pc = ['Low'=>'text-gray-500','Medium'=>'text-blue-600','High'=>'text-orange-600','Urgent'=>'text-red-600'][$task->priority] ?? '';
                    @endphp
                    <span class="font-medium {{ $pc }}">{{ $task->priority }}</span>
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                    {{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('M j') : '—' }}
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-700">{{ $task->status }}</td>
                <td class="whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm sm:pr-6">
                    @if(! in_array($task->status, ['Done', 'Cancelled']))
                    <form method="POST" action="{{ route('my.tasks.status', $task) }}" class="inline-flex gap-1">
                        @csrf @method('PATCH')
                        <select name="status"
                                class="rounded border-gray-300 py-1 text-xs focus:ring-indigo-600">
                            @foreach(['todo' => 'To Do','in_progress' => 'In Progress','review' => 'In Review','done' => 'Done'] as $val => $label)
                            <option value="{{ $val }}" {{ $task->status === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="rounded bg-indigo-600 px-2 py-1 text-xs font-semibold text-white hover:bg-indigo-500">Update</button>
                    </form>
                    @else
                    <span class="text-gray-400 text-xs">{{ $task->status }}</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="py-10 text-center text-sm text-gray-500">No tasks assigned.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $tasks->links() }}</div>
@endsection
