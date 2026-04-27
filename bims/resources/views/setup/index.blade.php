<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Initial Setup</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full">

<div class="min-h-full flex flex-col justify-center py-12 px-4 sm:px-6 lg:px-8">

    {{-- Header --}}
    <div class="sm:mx-auto sm:w-full sm:max-w-xl mb-8 text-center">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-indigo-600 mb-4">
            <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-900">Welcome — let's get you set up</h1>
        <p class="mt-1 text-sm text-gray-500">This wizard runs once. Fill in the details below to configure your workspace.</p>
    </div>

    {{-- Card --}}
    <div class="sm:mx-auto sm:w-full sm:max-w-xl">
        <div class="bg-white shadow-sm rounded-2xl ring-1 ring-gray-200 divide-y divide-gray-100">

            <form method="POST" action="{{ route('setup.store') }}" enctype="multipart/form-data" x-data="setupWizard()">
                @csrf

                {{-- Setup step error --}}
                @if(session('error'))
                <div class="px-8 pt-6">
                    <div class="rounded-lg bg-red-50 border border-red-200 p-4">
                        <p class="text-sm font-medium text-red-800">Setup failed</p>
                        <p class="text-sm text-red-700 mt-1">{{ session('error') }}</p>
                    </div>
                </div>
                @endif

                {{-- Validation errors --}}
                @if($errors->any())
                <div class="px-8 pt-6">
                    <div class="rounded-lg bg-red-50 border border-red-200 p-4">
                        <p class="text-sm font-medium text-red-800 mb-1">Please fix the following:</p>
                        <ul class="text-sm text-red-700 list-disc list-inside space-y-0.5">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif

                {{-- ── Section 1: Company ── --}}
                <div class="px-8 py-6">
                    <h2 class="text-sm font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <span class="flex items-center justify-center w-5 h-5 rounded-full bg-indigo-600 text-white text-xs font-bold">1</span>
                        Company Information
                    </h2>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Company Name <span class="text-red-500">*</span></label>
                            <input type="text" name="company_name" required
                                   value="{{ old('company_name') }}"
                                   placeholder="e.g. Acme Call Centre Ltd."
                                   class="mt-1.5 block w-full rounded-lg border-0 py-2 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Currency <span class="text-red-500">*</span></label>
                                <input type="text" name="currency" required maxlength="10"
                                       value="{{ old('currency') }}"
                                       placeholder="e.g. JMD, USD, GBP"
                                       class="mt-1.5 block w-full rounded-lg border-0 py-2 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Timezone <span class="text-red-500">*</span></label>
                                <select name="timezone" required
                                        class="mt-1.5 block w-full rounded-lg border-0 py-2 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                                    @foreach(\DateTimeZone::listIdentifiers() as $tz)
                                    <option value="{{ $tz }}" {{ old('timezone', 'America/Jamaica') === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Company Logo <span class="text-gray-400 font-normal">(optional)</span></label>
                            <p class="text-xs text-gray-500 mt-0.5">PNG, JPG, SVG or WebP · max 1 MB</p>
                            <input type="file" name="logo" accept="image/*"
                                   class="mt-1.5 block w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        </div>
                    </div>
                </div>

                {{-- ── Section 2: Region ── --}}
                <div class="px-8 py-6">
                    <h2 class="text-sm font-semibold text-gray-900 mb-1 flex items-center gap-2">
                        <span class="flex items-center justify-center w-5 h-5 rounded-full bg-indigo-600 text-white text-xs font-bold">2</span>
                        Region &amp; Tax Configuration
                    </h2>
                    <p class="text-xs text-gray-500 mb-4 ml-7">Pre-loads statutory tax deductions for your country. You can add more later in Settings.</p>

                    <div class="space-y-2">
                        @foreach($packs as $key => $pack)
                        <label class="flex items-start gap-3 rounded-lg border p-3 cursor-pointer transition-colors
                                      {{ old('country_pack', 'none') === $key ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-indigo-300 hover:bg-gray-50' }}"
                               x-bind:class="region === '{{ $key }}' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-indigo-300 hover:bg-gray-50'">
                            <input type="radio" name="country_pack" value="{{ $key }}"
                                   x-model="region"
                                   {{ old('country_pack', 'none') === $key ? 'checked' : '' }}
                                   class="mt-0.5 h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-600">
                            <span class="text-sm text-gray-800">{{ $pack['label'] }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- ── Section 3: Admin Account ── --}}
                <div class="px-8 py-6">
                    <h2 class="text-sm font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <span class="flex items-center justify-center w-5 h-5 rounded-full bg-indigo-600 text-white text-xs font-bold">3</span>
                        Administrator Account
                    </h2>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Full Name <span class="text-red-500">*</span></label>
                            <input type="text" name="admin_name" required
                                   value="{{ old('admin_name') }}"
                                   placeholder="e.g. Jane Smith"
                                   class="mt-1.5 block w-full rounded-lg border-0 py-2 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email Address <span class="text-red-500">*</span></label>
                            <input type="email" name="admin_email" required
                                   value="{{ old('admin_email') }}"
                                   placeholder="admin@yourcompany.com"
                                   class="mt-1.5 block w-full rounded-lg border-0 py-2 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Password <span class="text-red-500">*</span></label>
                                <input type="password" name="admin_password" required minlength="8"
                                       placeholder="Min. 8 characters"
                                       class="mt-1.5 block w-full rounded-lg border-0 py-2 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Confirm Password <span class="text-red-500">*</span></label>
                                <input type="password" name="admin_password_confirmation" required
                                       placeholder="Repeat password"
                                       class="mt-1.5 block w-full rounded-lg border-0 py-2 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="px-8 py-5 bg-gray-50 rounded-b-2xl flex items-center justify-between">
                    <p class="text-xs text-gray-400">This page will not be accessible once setup is complete.</p>
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                        </svg>
                        Complete Setup
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
function setupWizard() {
    return {
        region: '{{ old('country_pack', 'none') }}',
    };
}
</script>

</body>
</html>
