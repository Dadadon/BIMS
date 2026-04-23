<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- Reverb / Echo config --}}
    <meta name="reverb-app-key" content="{{ config('broadcasting.connections.reverb.key') }}">
    <meta name="reverb-host"    content="{{ config('broadcasting.connections.reverb.options.host', '127.0.0.1') }}">
    <meta name="reverb-port"    content="{{ config('broadcasting.connections.reverb.options.port', 8080) }}">
    <meta name="reverb-scheme"  content="{{ config('broadcasting.connections.reverb.options.scheme', 'http') }}">
    <title>@yield('title', 'BIMS') — Beroni Innovations</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="h-full font-sans antialiased" x-data="{ sidebarOpen: false }">

{{-- Mobile sidebar overlay --}}
<div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-300"
     x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-linear duration-300"
     x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
     class="relative z-50 lg:hidden" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-900/80"></div>
    <div class="fixed inset-0 flex">
        <div class="relative mr-16 flex w-full max-w-xs flex-1" @click.outside="sidebarOpen = false">
            <div class="absolute left-full top-0 flex w-16 justify-center pt-5">
                <button type="button" class="-m-2.5 p-2.5" @click="sidebarOpen = false">
                    <span class="sr-only">Close sidebar</span>
                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-gray-900 px-6 pb-4 ring-1 ring-white/10">
                @include('layouts.partials.sidebar-content')
            </div>
        </div>
    </div>
</div>

{{-- Static sidebar for desktop --}}
<div class="hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-64 lg:flex-col">
    <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-gray-900 px-6 pb-4">
        @include('layouts.partials.sidebar-content')
    </div>
</div>

{{-- Main content --}}
<div class="lg:pl-64">
    {{-- Top navigation --}}
    <div class="sticky top-0 z-40 flex h-16 shrink-0 items-center gap-x-4 border-b border-gray-200 bg-white px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:px-8">
        <button type="button" class="-m-2.5 p-2.5 text-gray-700 lg:hidden" @click="sidebarOpen = true">
            <span class="sr-only">Open sidebar</span>
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
            </svg>
        </button>

        <div class="flex flex-1 gap-x-4 self-stretch lg:gap-x-6">
            <div class="flex flex-1 items-center">
                <h1 class="text-sm font-semibold text-gray-900">@yield('page-title')</h1>
            </div>
            <div class="flex items-center gap-x-4 lg:gap-x-6">

                {{-- Notifications bell --}}
                <div class="relative" x-data="{
                    open: false,
                    items: [],
                    unread: {{ auth()->user()->unreadNotifications()->count() }},
                    loading: false,
                    async toggle() {
                        this.open = !this.open;
                        if (this.open) await this.load();
                    },
                    async load() {
                        this.loading = true;
                        const r = await fetch('{{ route('my.notifications.recent') }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                        const d = await r.json();
                        this.items = d.items;
                        this.unread = d.unread_count;
                        this.loading = false;
                    },
                    async markAllRead() {
                        await fetch('{{ route('my.notifications.markAllRead') }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'X-Requested-With': 'XMLHttpRequest' } });
                        this.items.forEach(i => i.read = true);
                        this.unread = 0;
                    }
                }">
                    <button type="button" @click="toggle()" class="relative -m-2.5 p-2.5 text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Notifications</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                        </svg>
                        <span x-show="unread > 0" x-text="unread > 9 ? '9+' : unread"
                              class="absolute -top-0.5 -right-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white leading-none"></span>
                    </button>

                    <div x-show="open" x-transition @click.outside="open = false"
                         class="absolute right-0 z-20 mt-2.5 w-80 origin-top-right rounded-lg bg-white shadow-lg ring-1 ring-gray-900/5 divide-y divide-gray-100">

                        {{-- Header --}}
                        <div class="flex items-center justify-between px-4 py-3">
                            <span class="text-sm font-semibold text-gray-900">Notifications</span>
                            <div class="flex items-center gap-3">
                                <button @click="markAllRead()" x-show="unread > 0"
                                        class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Mark all read</button>
                                <a href="{{ route('my.notifications.index') }}" class="text-xs text-gray-500 hover:text-gray-700">View all</a>
                            </div>
                        </div>

                        {{-- Loading --}}
                        <div x-show="loading" class="px-4 py-6 text-center text-sm text-gray-400">Loading…</div>

                        {{-- Empty --}}
                        <div x-show="!loading && items.length === 0" class="px-4 py-6 text-center text-sm text-gray-400">No notifications</div>

                        {{-- Items --}}
                        <ul x-show="!loading && items.length > 0" class="divide-y divide-gray-50 max-h-80 overflow-y-auto">
                            <template x-for="item in items" :key="item.id">
                                <li class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 transition-colors"
                                    :class="{ 'bg-indigo-50/40': !item.read }">
                                    {{-- Icon --}}
                                    <div class="mt-0.5 flex-shrink-0 w-7 h-7 rounded-full flex items-center justify-center"
                                         :class="{
                                             'bg-blue-100 text-blue-600':   item.icon === 'leave',
                                             'bg-green-100 text-green-600': item.icon === 'payroll',
                                             'bg-amber-100 text-amber-600': item.icon === 'task',
                                             'bg-purple-100 text-purple-600': item.icon === 'sale',
                                             'bg-gray-100 text-gray-500':   !['leave','payroll','task','sale'].includes(item.icon),
                                         }">
                                        {{-- Leave --}}
                                        <svg x-show="item.icon === 'leave'" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5"/></svg>
                                        {{-- Payroll --}}
                                        <svg x-show="item.icon === 'payroll'" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/></svg>
                                        {{-- Task --}}
                                        <svg x-show="item.icon === 'task'" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        {{-- Sale --}}
                                        <svg x-show="item.icon === 'sale'" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941"/></svg>
                                        {{-- Fallback --}}
                                        <svg x-show="!['leave','payroll','task','sale'].includes(item.icon)" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                                    </div>

                                    {{-- Content --}}
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs text-gray-800 leading-snug" x-text="item.message"></p>
                                        <p class="mt-0.5 text-[11px] text-gray-400" x-text="item.time"></p>
                                    </div>

                                    {{-- Unread dot --}}
                                    <div x-show="!item.read" class="mt-1.5 w-2 h-2 rounded-full bg-indigo-500 flex-shrink-0"></div>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>

                @module('chat')
                <a href="{{ route('chat.index') }}" class="relative -m-2.5 p-2.5 text-gray-400 hover:text-gray-500">
                    <span class="sr-only">Chat</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"/>
                    </svg>
                </a>
                @endmodule

                {{-- User dropdown --}}
                <div class="relative" x-data="{ open: false }">
                    <button type="button" class="-m-1.5 flex items-center p-1.5" @click="open = !open">
                        <span class="sr-only">Open user menu</span>
                        <div class="h-8 w-8 rounded-full bg-indigo-600 flex items-center justify-center">
                            <span class="text-white text-sm font-medium">{{ substr(auth()->user()->name, 0, 1) }}</span>
                        </div>
                        <span class="hidden lg:flex lg:items-center">
                            <span class="ml-4 text-sm font-semibold leading-6 text-gray-900">{{ auth()->user()->name }}</span>
                            <svg class="ml-2 h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
                            </svg>
                        </span>
                    </button>
                    <div x-show="open" x-transition @click.outside="open = false"
                         class="absolute right-0 z-10 mt-2.5 w-48 origin-top-right rounded-md bg-white py-2 shadow-lg ring-1 ring-gray-900/5">
                        @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="block px-3 py-1 text-sm leading-6 text-gray-900 hover:bg-gray-50">Admin Panel</a>
                        @endif
                        <a href="{{ route('my.dashboard') }}" class="block px-3 py-1 text-sm leading-6 text-gray-900 hover:bg-gray-50">My Dashboard</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full text-left px-3 py-1 text-sm leading-6 text-gray-900 hover:bg-gray-50">Sign out</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Page content --}}
    <main class="py-8">
        <div class="px-4 sm:px-6 lg:px-8">

            {{-- Flash messages --}}
            @if(session('success'))
            <div class="mb-4 rounded-md bg-green-50 p-4" x-data="{ show: true }" x-show="show">
                <div class="flex">
                    <svg class="h-5 w-5 text-green-400 shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/>
                    </svg>
                    <p class="ml-3 text-sm font-medium text-green-800">{{ session('success') }}</p>
                    <button @click="show = false" class="ml-auto -mx-1.5 -my-1.5 rounded-md p-1.5 text-green-500 hover:bg-green-100">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"/></svg>
                    </button>
                </div>
            </div>
            @endif

            @if(session('error'))
            <div class="mb-4 rounded-md bg-red-50 p-4" x-data="{ show: true }" x-show="show">
                <div class="flex">
                    <svg class="h-5 w-5 text-red-400 shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd"/>
                    </svg>
                    <p class="ml-3 text-sm font-medium text-red-800">{{ session('error') }}</p>
                    <button @click="show = false" class="ml-auto -mx-1.5 -my-1.5 rounded-md p-1.5 text-red-500 hover:bg-red-100">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"/></svg>
                    </button>
                </div>
            </div>
            @endif

            @yield('content')
        </div>
    </main>
</div>

@include('layouts.partials.softphone-widget')
@stack('scripts')
</body>
</html>
