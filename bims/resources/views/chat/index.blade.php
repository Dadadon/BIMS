@extends('layouts.app')
@section('title', 'Chat')
@section('page-title', 'Chat')

@section('content')
<div class="sm:flex sm:items-center sm:justify-between mb-6">
    <h2 class="text-xl font-semibold text-gray-900">Messages</h2>
    <button type="button" onclick="document.getElementById('new-chat-modal').classList.remove('hidden')"
            class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
        + New Conversation
    </button>
</div>

@if($conversations->isEmpty())
<div class="text-center py-16">
    <p class="text-gray-500">No conversations yet. Start one!</p>
</div>
@else
<div class="divide-y divide-gray-200 bg-white shadow rounded-lg overflow-hidden">
    @foreach($conversations as $conv)
    @php
        $other  = $conv->participants->firstWhere('id', '!=', auth()->id());
        $latest = $conv->messages->first();
    @endphp
    <a href="{{ route('chat.show', $conv) }}" class="flex items-center gap-4 px-6 py-4 hover:bg-gray-50">
        <div class="h-10 w-10 shrink-0 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-semibold">
            {{ $other ? substr($other->name, 0, 1) : 'G' }}
        </div>
        <div class="flex-1 min-w-0">
            <p class="font-medium text-gray-900">{{ $other?->name ?? $conv->subject ?? 'Group' }}</p>
            <p class="text-sm text-gray-500 truncate">{{ $latest?->body ?? 'No messages yet' }}</p>
        </div>
        @if($latest)
        <span class="text-xs text-gray-400 shrink-0">{{ $latest->created_at->diffForHumans() }}</span>
        @endif
    </a>
    @endforeach
</div>
@endif

{{-- New conversation modal --}}
<div id="new-chat-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between">
            <h3 class="text-sm font-semibold text-gray-900">New Conversation</h3>
            <button onclick="document.getElementById('new-chat-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>
        <form method="POST" action="{{ route('chat.start') }}" class="px-6 py-5 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-900">Recipient <span class="text-red-500">*</span></label>
                <select name="user_id" required
                        class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                    <option value="">— Select —</option>
                    @foreach(\App\Models\User::where('id', '!=', auth()->id())->where('status', true)->orderBy('name')->get() as $u)
                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('new-chat-modal').classList.add('hidden')"
                        class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Cancel</button>
                <button type="submit"
                        class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Start Chat</button>
            </div>
        </form>
    </div>
</div>
@endsection
