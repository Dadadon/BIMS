<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sign In') — BIMS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full">
<div class="flex min-h-full flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="flex justify-center">
            <span class="text-4xl font-bold tracking-tight text-gray-900">
                <span class="text-indigo-600">B</span>IMS
            </span>
        </div>
        <p class="mt-1 text-center text-sm text-gray-500">Beroni Innovations Management System</p>
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
