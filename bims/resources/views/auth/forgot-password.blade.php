@extends('layouts.auth')
@section('title', 'Forgot Password')
@section('heading', 'Reset your password')

@section('content')
@if(session('status'))
<div class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-700">{{ session('status') }}</div>
@endif

<p class="mb-6 text-sm text-gray-600">Enter your email and we'll send a password reset link.</p>

<form class="space-y-6" method="POST" action="{{ route('password.email') }}">
    @csrf
    <div>
        <label for="email" class="block text-sm font-medium leading-6 text-gray-900">Email address</label>
        <div class="mt-2">
            <input id="email" name="email" type="email" autocomplete="email" required
                   value="{{ old('email') }}"
                   class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
        </div>
        @error('email')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <button type="submit"
                class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-indigo-500">
            Send reset link
        </button>
    </div>
    <p class="text-center text-sm text-gray-500">
        <a href="{{ route('login') }}" class="font-semibold text-indigo-600 hover:text-indigo-500">Back to sign in</a>
    </p>
</form>
@endsection
