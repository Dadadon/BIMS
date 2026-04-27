<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sign In') — {{ $settings?->company_name ?? 'Hub' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full">
<div class="flex min-h-full flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="flex justify-center">
            @if($settings?->logo_path)
                <img src="{{ asset('storage/' . $settings->logo_path) }}"
                     alt="{{ $settings->company_name }}"
                     class="max-h-16 w-auto object-contain">
            @else
                <span class="text-2xl font-bold tracking-tight text-gray-900">
                    {{ $settings?->company_name ?? 'My Company' }}
                </span>
            @endif
        </div>
        <h2 class="mt-6 text-center text-2xl font-bold leading-9 tracking-tight text-gray-900">@yield('heading')</h2>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white px-6 py-8 shadow sm:rounded-lg sm:px-12">
            @yield('content')
        </div>
    </div>
</div>
</body>
</html>
