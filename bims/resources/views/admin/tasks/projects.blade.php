@extends('layouts.app')
@section('title', 'Projects')
@section('page-title', 'Projects')

@section('content')
<div class="sm:flex sm:items-center sm:justify-between mb-6">
    <h2 class="text-xl font-semibold text-gray-900">Projects</h2>
    <a href="{{ route('admin.tasks.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Tasks</a>
</div>

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <div class="lg:col-span-2 overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-gray-50">
                <tr>
                    <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Name</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Tasks</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 hidden md:table-cell">Description</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($projects as $p)
                <tr class="hover:bg-gray-50">
                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">{{ $p->name }}</td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $p->tasks_count }}</td>
                    <td class="px-3 py-4 text-sm text-gray-500 max-w-xs truncate hidden md:table-cell">{{ $p->description ?? '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="3" class="py-10 text-center text-sm text-gray-500">No projects yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4">{{ $projects->links() }}</div>
    </div>

    {{-- Create project --}}
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-900">New Project</h3>
        </div>
        <form method="POST" action="{{ route('admin.tasks.projects.store') }}" class="px-6 py-5 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-900">Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" required
                       class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-900">Description</label>
                <textarea name="description" rows="3"
                          class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm"></textarea>
            </div>
            <button type="submit"
                    class="w-full rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                Create Project
            </button>
        </form>
    </div>
</div>
@endsection
