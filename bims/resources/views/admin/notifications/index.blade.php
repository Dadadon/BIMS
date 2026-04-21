@extends('layouts.app')
@section('title', 'Notifications')
@section('page-title', 'Notifications')

@section('content')
<div class="max-w-2xl mx-auto">

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-semibold text-gray-900">All Notifications</h2>
        @if($notifications->total() > 0)
        <form method="POST" action="{{ route('my.notifications.markAllRead') }}">
            @csrf
            <button class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">Mark all as read</button>
        </form>
        @endif
    </div>

    @if($notifications->isEmpty())
    <div class="rounded-lg bg-white border border-gray-200 px-6 py-16 text-center">
        <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
        </svg>
        <p class="mt-3 text-sm text-gray-500">You're all caught up — no notifications yet.</p>
    </div>
    @else
    <div class="bg-white rounded-lg border border-gray-200 divide-y divide-gray-100 overflow-hidden">
        @foreach($notifications as $notification)
        @php $data = $notification->data; $isRead = ! is_null($notification->read_at); @endphp
        <div class="flex items-start gap-4 px-5 py-4 {{ $isRead ? '' : 'bg-indigo-50/40' }} hover:bg-gray-50 transition-colors">

            {{-- Icon --}}
            @php
                $icon  = $data['icon'] ?? 'bell';
                $color = match($icon) {
                    'leave'   => 'bg-blue-100 text-blue-600',
                    'payroll' => 'bg-green-100 text-green-600',
                    'task'    => 'bg-amber-100 text-amber-600',
                    'sale'    => 'bg-purple-100 text-purple-600',
                    default   => 'bg-gray-100 text-gray-500',
                };
            @endphp
            <div class="mt-0.5 flex-shrink-0 w-8 h-8 rounded-full {{ $color }} flex items-center justify-center">
                @if($icon === 'leave')
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5"/></svg>
                @elseif($icon === 'payroll')
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/></svg>
                @elseif($icon === 'task')
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                @elseif($icon === 'sale')
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941"/></svg>
                @else
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                @endif
            </div>

            {{-- Body --}}
            <div class="flex-1 min-w-0">
                <p class="text-sm text-gray-800">{{ $data['message'] ?? '' }}</p>
                <p class="mt-0.5 text-xs text-gray-400">{{ $notification->created_at->diffForHumans() }}</p>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 flex-shrink-0">
                @if(! $isRead)
                <form method="POST" action="{{ route('my.notifications.markRead', $notification->id) }}">
                    @csrf
                    <button class="text-xs text-indigo-600 hover:text-indigo-800 font-medium whitespace-nowrap">Mark read</button>
                </form>
                @endif
                <form method="POST" action="{{ route('my.notifications.destroy', $notification->id) }}">
                    @csrf @method('DELETE')
                    <button class="text-xs text-gray-400 hover:text-red-500">Remove</button>
                </form>
            </div>

            @if(! $isRead)
            <div class="mt-2 w-2 h-2 rounded-full bg-indigo-500 flex-shrink-0"></div>
            @endif
        </div>
        @endforeach
    </div>

    <div class="mt-4">
        {{ $notifications->links() }}
    </div>
    @endif

</div>
@endsection
