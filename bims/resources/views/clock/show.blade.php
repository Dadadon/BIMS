@extends('layouts.app')
@section('title', 'SmartClock')
@section('page-title', 'SmartClock')

@section('content')
@if(! $employee)
<div class="rounded-md bg-yellow-50 p-4">
    <p class="text-sm text-yellow-800">Your account is not linked to an employee record. Please contact your administrator.</p>
</div>
@else

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

    {{-- Clock In / Out card --}}
    <div class="lg:col-span-1 space-y-4">

        {{-- Current time --}}
        <div class="rounded-lg bg-gray-900 text-white p-6 text-center">
            <p class="text-sm text-gray-400">{{ now()->format('l, F j, Y') }}</p>
            <p class="mt-1 text-5xl font-bold tabular-nums" id="live-clock">{{ now()->format('g:i:s A') }}</p>
            <p class="mt-2 text-sm text-gray-400">{{ config('app.timezone') }}</p>
        </div>

        {{-- Status --}}
        @if($openLog)
        <div class="rounded-lg bg-green-50 border border-green-200 p-4">
            <div class="flex items-center gap-3">
                <span class="flex h-3 w-3 relative">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                </span>
                <div>
                    <p class="text-sm font-semibold text-green-800">Currently clocked in</p>
                    <p class="text-xs text-green-600">Since {{ $openLog->clock_in->format('g:i A') }} · {{ $openLog->reason }}</p>
                </div>
            </div>
        </div>
        @else
        <div class="rounded-lg bg-gray-50 border border-gray-200 p-4">
            <div class="flex items-center gap-3">
                <span class="flex h-3 w-3 rounded-full bg-gray-400"></span>
                <p class="text-sm text-gray-600">Not clocked in</p>
            </div>
        </div>
        @endif

        {{-- Clock In form --}}
        @if(! $openLog)
        <div class="rounded-lg bg-white shadow p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Clock In</h3>
            <form method="POST" action="{{ route('clock.in') }}">
                @csrf
                <div class="mb-3">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Reason</label>
                    <select name="reason"
                            class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 text-sm">
                        <option value="Shift">Shift</option>
                        <option value="Lunch">Lunch Return</option>
                        <option value="Break">Break Return</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Comment (optional)</label>
                    <input type="text" name="comment" placeholder="e.g. WFH today"
                           class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 text-sm">
                </div>
                <button type="submit"
                        class="w-full rounded-md bg-green-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-green-500">
                    Clock In
                </button>
            </form>
        </div>
        @else
        {{-- Clock Out form --}}
        <div class="rounded-lg bg-white shadow p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Clock Out</h3>
            <form method="POST" action="{{ route('clock.out') }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Reason</label>
                    <select name="reason"
                            class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 text-sm">
                        <option value="Shift">End of Shift</option>
                        <option value="Lunch">Lunch Break</option>
                        <option value="Break">Short Break</option>
                    </select>
                </div>
                <button type="submit"
                        class="w-full rounded-md bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-500">
                    Clock Out
                </button>
            </form>
        </div>
        @endif

        {{-- Log a Sale --}}
        @module('sales')
        <div class="rounded-lg bg-white shadow p-5" x-data="{ open: false }">
            <button @click="open = !open"
                    class="flex w-full items-center justify-between text-sm font-semibold text-gray-900">
                Log a Sale
                <svg class="h-5 w-5 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                </svg>
            </button>
            <div x-show="open" x-transition class="mt-4">
                <form method="POST" action="{{ route('clock.sale') }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Sale Type <span class="text-red-500">*</span></label>
                        <select name="sale_type_id" required
                                class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 text-sm">
                            <option value="">— Select —</option>
                            @foreach($saleTypes as $st)
                            <option value="{{ $st->id }}">{{ $st->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Customer Name <span class="text-red-500">*</span></label>
                        <input type="text" name="customer_name" required
                               class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Customer Phone</label>
                        <input type="text" name="customer_phone"
                               class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Sale Date <span class="text-red-500">*</span></label>
                        <input type="date" name="sale_date" required value="{{ now()->format('Y-m-d') }}"
                               class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Remarks</label>
                        <input type="text" name="remarks"
                               class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 text-sm">
                    </div>
                    <button type="submit"
                            class="w-full rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                        Submit Sale
                    </button>
                </form>
            </div>
        </div>
        @endmodule

        {{-- SmartClock softphone panel --}}
        @module('phone')
        @if($employee->sip_extension)
        <div class="rounded-lg bg-white shadow p-5" x-data="clockSoftphone()" x-init="boot()">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/>
                    </svg>
                    Softphone
                </h3>
                <div class="flex items-center gap-1.5">
                    <span class="h-2 w-2 rounded-full"
                          :class="{
                              'bg-green-500': ['registered','connected'].includes(status),
                              'bg-yellow-500 animate-pulse': ['registering','calling','ringing'].includes(status),
                              'bg-red-400': ['failed','unregistered'].includes(status),
                              'bg-gray-400': status === 'idle'
                          }"></span>
                    <span class="text-xs text-gray-500" x-text="statusLabel"></span>
                </div>
            </div>

            {{-- CallHippo --}}
            <div x-show="provider === 'callhippo'" class="text-xs text-blue-700 bg-blue-50 rounded-md p-2 leading-relaxed">
                CallHippo active — use the CallHippo portal to make calls.
            </div>

            {{-- SIP Dialer --}}
            <div x-show="provider === 'sip'" class="space-y-2">
                <template x-if="!['calling','ringing','connected'].includes(status)">
                    <div class="space-y-2">
                        <input type="tel" x-model="number" placeholder="Number or extension"
                               class="block w-full rounded-md border-0 py-1.5 px-3 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 font-mono"
                               @keydown.enter="makeCall()">
                        <div class="grid grid-cols-3 gap-1">
                            <template x-for="k in ['1','2','3','4','5','6','7','8','9','*','0','#']" :key="k">
                                <button @click="number += k"
                                        class="h-8 rounded-md bg-gray-100 text-sm font-semibold text-gray-800 hover:bg-gray-200 active:scale-95 transition-all"
                                        x-text="k"></button>
                            </template>
                        </div>
                        <button @click="makeCall()" :disabled="!number"
                                class="w-full rounded-md bg-green-600 py-2 text-sm font-semibold text-white hover:bg-green-500 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                            Call
                        </button>
                    </div>
                </template>

                <template x-if="['calling','ringing','connected'].includes(status)">
                    <div class="space-y-3 text-center">
                        <p class="font-mono text-gray-800 text-base font-semibold" x-text="number"></p>
                        <p class="text-xs text-gray-500" x-text="statusLabel"></p>
                        <p x-show="status === 'connected'" class="text-sm font-mono text-green-600 tabular-nums" x-text="formatDuration(duration)"></p>
                        <div class="flex justify-center gap-3">
                            <button x-show="status === 'connected'" @click="toggleMute()"
                                    class="h-9 w-9 rounded-full flex items-center justify-center"
                                    :class="muted ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-600'">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path x-show="!muted" stroke-linecap="round" stroke-linejoin="round" d="M12 18.75a6 6 0 006-6v-1.5m-6 7.5a6 6 0 01-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 01-3-3V4.5a3 3 0 116 0v8.25a3 3 0 01-3 3z"/>
                                    <path x-show="muted" stroke-linecap="round" stroke-linejoin="round" d="M17.25 9.75L19.5 12m0 0l2.25 2.25M19.5 12l2.25-2.25M19.5 12l-2.25 2.25m-10.5-6l4.72-4.72a.75.75 0 011.28.531V19.94a.75.75 0 01-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.506-1.938-1.354A9.01 9.01 0 012.25 12c0-.83.112-1.633.322-2.395C2.806 8.757 3.63 8.25 4.51 8.25H6.75z"/>
                                </svg>
                            </button>
                            <button @click="hangup()"
                                    class="h-9 w-9 rounded-full bg-red-600 flex items-center justify-center text-white hover:bg-red-500">
                                <svg class="h-4 w-4 rotate-[135deg]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </template>

                <div x-show="errorMsg" x-transition class="rounded-md bg-red-50 px-2 py-1.5 text-xs text-red-700" x-text="errorMsg"></div>
            </div>
        </div>

        @push('scripts')
        <script>
        function clockSoftphone() {
            return {
                status: 'idle', provider: null, number: '', muted: false,
                duration: 0, _timer: null, _callStart: null, simpleUser: null, config: null, errorMsg: '',
                get statusLabel() {
                    return { idle:'Not connected', registering:'Connecting…', registered:'Ready',
                             calling:'Calling…', ringing:'Ringing…', connected:'In call',
                             failed:'Failed', unregistered:'Disconnected' }[this.status] || this.status;
                },
                async boot() {
                    try {
                        const r = await fetch('{{ route('my.phone.config') }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                        const cfg = await r.json();
                        if (!cfg.enabled) return;
                        this.config = cfg; this.provider = cfg.provider;
                        if (cfg.provider !== 'sip') return;
                        await this._loadSipJs();
                        await this._registerSip();
                    } catch(e) { this.errorMsg = e.message; setTimeout(() => this.errorMsg = '', 5000); }
                },
                _loadSipJs() {
                    return new Promise((resolve, reject) => {
                        if (window.SIP) return resolve();
                        const s = document.createElement('script');
                        s.src = 'https://cdn.jsdelivr.net/npm/sip.js@0.21.2/lib/platform/web/simple-user.min.js';
                        s.onload = resolve; s.onerror = () => reject(new Error('SIP.js load failed'));
                        document.head.appendChild(s);
                    });
                },
                async _registerSip() {
                    const cfg = this.config, self = this;
                    const iceServers = [];
                    if (cfg.stun_server) iceServers.push({ urls: cfg.stun_server });
                    if (cfg.turn_server) {
                        const t = { urls: cfg.turn_server };
                        if (cfg.turn_username) t.username = cfg.turn_username;
                        if (cfg.turn_password) t.credential = cfg.turn_password;
                        iceServers.push(t);
                    }
                    const ext = cfg.sip_uri?.split(':')[1]?.split('@')[0] || '';
                    this.status = 'registering';
                    this.simpleUser = new SIP.Web.SimpleUser(cfg.websocket_url, {
                        aor: cfg.sip_uri,
                        delegate: {
                            onCallCreated:   () => { self.status = 'calling'; },
                            onCallAnswered:  () => { self.status = 'connected'; self._startTimer(); },
                            onCallHangup:    () => {
                                self._stopTimer(); self._logCall('completed', self.duration > 3 ? 'answered' : 'no_answer');
                                self.status = 'registered'; self.number = ''; self.muted = false;
                            },
                            onRegistered:       () => { self.status = 'registered'; },
                            onServerDisconnect: () => { self.status = 'unregistered'; },
                        },
                        media: { constraints: { audio: true, video: false }, remote: { audio: document.getElementById('softphone-remote-audio') } },
                        userAgentOptions: { authorizationPassword: cfg.password, authorizationUsername: ext, iceServers: iceServers.length ? iceServers : undefined },
                    });
                    await this.simpleUser.connect(); await this.simpleUser.register();
                },
                async makeCall() {
                    if (!this.number || !this.simpleUser) return;
                    const domain = this.config.sip_uri?.split('@')[1] || '';
                    const target = this.number.includes('@') ? 'sip:' + this.number : 'sip:' + this.number + '@' + domain;
                    try { this.status = 'calling'; await this.simpleUser.call(target, { sessionDescriptionHandlerOptions: { constraints: { audio: true, video: false } } });
                    } catch(e) { this.status = 'registered'; this.errorMsg = e.message; setTimeout(() => this.errorMsg = '', 4000); }
                },
                async hangup() {
                    try { if (this.simpleUser) await this.simpleUser.hangup(); } catch(_) {}
                    this._stopTimer(); await this._logCall('completed', this.duration > 3 ? 'answered' : 'no_answer');
                    this.status = 'registered'; this.number = ''; this.muted = false;
                },
                toggleMute() { if (!this.simpleUser) return; this.muted = !this.muted; this.muted ? this.simpleUser.mute() : this.simpleUser.unmute(); },
                _startTimer() { this.duration = 0; this._callStart = Date.now(); this._timer = setInterval(() => { this.duration = Math.floor((Date.now() - this._callStart) / 1000); }, 1000); },
                _stopTimer() { if (this._timer) { clearInterval(this._timer); this._timer = null; } },
                formatDuration(s) { const m = Math.floor(s/60), sec = s%60; return String(m).padStart(2,'0')+':'+String(sec).padStart(2,'0'); },
                async _logCall(status, disposition) {
                    const ext = this.config?.sip_uri?.split(':')[1]?.split('@')[0] || '';
                    try { await fetch('{{ route('my.phone.log') }}', { method:'POST', headers:{ 'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content,'X-Requested-With':'XMLHttpRequest' }, body: JSON.stringify({ direction:'outbound', caller_number:ext, callee_number:this.number, status, disposition, duration_seconds:this.duration }) }); } catch(_) {}
                },
            };
        }
        </script>
        @endpush
        @endif
        @endmodule

    </div>

    {{-- Today's log --}}
    <div class="lg:col-span-2">
        <div class="rounded-lg bg-white shadow">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900">Today's Log — {{ now()->format('F j, Y') }}</h3>
            </div>

            @if($todayLogs->isEmpty())
            <div class="px-6 py-12 text-center text-sm text-gray-500">No records for today yet.</div>
            @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reason</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Clock In</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Clock Out</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Duration</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @foreach($todayLogs as $log)
                        <tr>
                            <td class="whitespace-nowrap px-6 py-3 text-sm text-gray-900">{{ $log->reason }}</td>
                            <td class="whitespace-nowrap px-6 py-3 text-sm text-gray-700">{{ $log->clock_in->format('g:i A') }}</td>
                            <td class="whitespace-nowrap px-6 py-3 text-sm text-gray-700">
                                {{ $log->clock_out ? $log->clock_out->format('g:i A') : '—' }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-3 text-sm text-gray-700">
                                {{ $log->clock_out ? $log->duration : '—' }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-3 text-sm">
                                @php
                                    $statusLabel = $log->status_out ?? $log->status_in;
                                    $statusColor = match(true) {
                                        in_array($statusLabel, ['In Time', 'On Time']) => 'bg-green-50 text-green-700 ring-green-600/20',
                                        in_array($statusLabel, ['Late In'])             => 'bg-red-50 text-red-700 ring-red-600/20',
                                        in_array($statusLabel, ['Early Out'])           => 'bg-yellow-50 text-yellow-800 ring-yellow-600/20',
                                        default                                         => 'bg-gray-50 text-gray-600 ring-gray-500/10',
                                    };
                                @endphp
                                <span class="inline-flex rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $statusColor }}">
                                    {{ $statusLabel ?? 'Open' }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            {{-- Daily summary --}}
            @if($todayLogs->whereNotNull('clock_out')->isNotEmpty())
            @php
                $totalMinutes = $todayLogs->whereNotNull('clock_out')
                                          ->where('reason', 'Shift')
                                          ->sum('total_minutes');
                $hours = intdiv($totalMinutes, 60);
                $mins  = $totalMinutes % 60;
            @endphp
            <div class="px-6 py-4 bg-gray-50 rounded-b-lg flex justify-end">
                <p class="text-sm text-gray-600">
                    Total Shift Time: <span class="font-semibold text-gray-900">{{ $hours }}h {{ $mins }}m</span>
                </p>
            </div>
            @endif
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
    function updateClock() {
        const el = document.getElementById('live-clock');
        if (!el) return;
        const now = new Date();
        const h = now.getHours() % 12 || 12;
        const m = String(now.getMinutes()).padStart(2, '0');
        const s = String(now.getSeconds()).padStart(2, '0');
        const ampm = now.getHours() >= 12 ? 'PM' : 'AM';
        el.textContent = `${h}:${m}:${s} ${ampm}`;
    }
    updateClock();
    setInterval(updateClock, 1000);
</script>
@endpush
@endsection
