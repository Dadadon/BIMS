@extends('layouts.app')
@section('title', $integration->exists ? 'Edit Integration' : 'Add Integration')
@section('page-title', 'Phone Integration')

@section('content')
<div class="max-w-2xl">

<div class="flex items-center justify-between mb-6">
    <h2 class="text-xl font-semibold text-gray-900">{{ $integration->exists ? 'Edit Integration' : 'Add Integration' }}</h2>
    <a href="{{ route('admin.phone.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back</a>
</div>

<form method="POST"
      action="{{ $integration->exists ? route('admin.phone.update', $integration) : route('admin.phone.store') }}"
      class="space-y-6">
    @csrf
    @if($integration->exists) @method('PUT') @endif

    {{-- Basic --}}
    <div class="bg-white shadow rounded-lg divide-y divide-gray-100">
        <div class="px-6 py-4">
            <h3 class="text-sm font-semibold text-gray-900">Basic Info</h3>
        </div>
        <div class="px-6 py-5 grid grid-cols-1 gap-5 sm:grid-cols-2" x-data="{ type: '{{ old('type', $integration->type ?? '') }}' }">
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-900">Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" required value="{{ old('name', $integration->name) }}"
                       placeholder="e.g. MPV CallHippo, Office PBX"
                       class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-900">Provider Type <span class="text-red-500">*</span></label>
                <select name="type" x-model="type" required
                        class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                    <option value="">— Select —</option>
                    <option value="callhippo"  :selected="type==='callhippo'">CallHippo</option>
                    <option value="freepbx"    :selected="type==='freepbx'">FreePBX</option>
                    <option value="vicidial"   :selected="type==='vicidial'">VICIdial</option>
                    <option value="custom_sip" :selected="type==='custom_sip'">Custom SIP</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-900">Notes</label>
                <input type="text" name="notes" value="{{ old('notes', $integration->notes) }}"
                       class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
            </div>

            {{-- CallHippo fields --}}
            <div class="sm:col-span-2" x-show="type === 'callhippo'" x-cloak>
                <div class="rounded-md bg-blue-50 p-4 mb-4 space-y-1">
                    <p class="text-xs text-blue-800 font-semibold">Setup steps</p>
                    <ol class="text-xs text-blue-700 list-decimal list-inside space-y-0.5">
                        <li>In CallHippo → Integrations → REST API, generate your API token and paste it below.</li>
                        <li>Go to the Webhook section, click Connect, and set the URL to:<br>
                            <code class="bg-blue-100 px-1 py-0.5 rounded font-mono">{{ url('phone/webhook/') }}/<em>ID</em>?secret=<em>your-webhook-secret</em></code></li>
                        <li>Enable <strong>Calling Activity</strong> (required). Optionally enable <strong>Call Status Notification</strong> for real-time status.</li>
                        <li>Calls are matched to employees automatically using the agent's email address.</li>
                    </ol>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-900">API Key <span class="text-red-500">*</span></label>
                        <input type="text" name="api_key" value="{{ old('api_key', $integration->api_key) }}"
                               placeholder="Your CallHippo API token"
                               class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm font-mono">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-900">Webhook Secret {{ $integration->exists ? '(leave blank to keep)' : '' }}</label>
                        <input type="password" name="webhook_secret" autocomplete="new-password"
                               placeholder="Optional — appended as ?secret= in webhook URL"
                               class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                    </div>
                </div>
            </div>

            {{-- SIP fields (FreePBX / VICIdial / Custom) --}}
            <div class="sm:col-span-2" x-show="['freepbx','vicidial','custom_sip'].includes(type)" x-cloak>
                <div class="rounded-md bg-indigo-50 p-4 mb-4">
                    <p class="text-xs text-indigo-700 font-medium">The softphone connects directly to your PBX via WebSocket (WSS). Asterisk must have WebRTC enabled. Each employee needs a SIP extension configured on their profile.</p>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-900">SIP Domain <span class="text-red-500">*</span></label>
                        <input type="text" name="sip_domain" value="{{ old('sip_domain', $integration->sip_domain) }}"
                               placeholder="pbx.yourcompany.com"
                               class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-900">WebSocket URL <span class="text-red-500">*</span></label>
                        <input type="text" name="websocket_url" value="{{ old('websocket_url', $integration->websocket_url) }}"
                               placeholder="wss://pbx.yourcompany.com:8089/ws"
                               class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-900">SIP Port</label>
                        <input type="number" name="sip_port" value="{{ old('sip_port', $integration->sip_port ?? 5060) }}"
                               class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-900">SIP Transport</label>
                        <select name="sip_transport"
                                class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                            @foreach(['wss','ws','tcp','udp'] as $t)
                            <option value="{{ $t }}" {{ old('sip_transport', $integration->sip_transport ?? 'wss') === $t ? 'selected' : '' }}>{{ strtoupper($t) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-900">Webhook Secret {{ $integration->exists ? '(leave blank to keep)' : '' }}</label>
                        <input type="password" name="webhook_secret" autocomplete="new-password"
                               class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-900">STUN Server</label>
                        <input type="text" name="stun_server" value="{{ old('stun_server', $integration->stun_server ?? 'stun:stun.l.google.com:19302') }}"
                               class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-900">TURN Server <span class="text-gray-400 font-normal">(optional)</span></label>
                        <input type="text" name="turn_server" value="{{ old('turn_server', $integration->turn_server) }}"
                               placeholder="turn:turn.yourcompany.com:3478"
                               class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-900">TURN Username</label>
                        <input type="text" name="turn_username" value="{{ old('turn_username', $integration->turn_username) }}"
                               class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-900">TURN Password</label>
                        <input type="password" name="turn_password" autocomplete="new-password"
                               class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($errors->any())
    <div class="rounded-md bg-red-50 p-4">
        <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <div class="flex justify-end gap-3">
        <a href="{{ route('admin.phone.index') }}"
           class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
            Cancel
        </a>
        <button type="submit"
                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
            {{ $integration->exists ? 'Save Changes' : 'Add Integration' }}
        </button>
    </div>
</form>
</div>
@endsection
