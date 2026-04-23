@module('phone')
@php $spEmployee = auth()->user()->employee ?? null; @endphp
@if($spEmployee && $spEmployee->sip_extension)

<audio id="softphone-remote-audio" autoplay></audio>

<div id="softphone-widget" x-data="softphoneWidget()" x-init="boot()" class="fixed bottom-6 right-6 z-50">

    {{-- Minimized toggle button --}}
    <button x-show="!open" @click="open = true" title="Softphone"
            class="relative flex h-14 w-14 items-center justify-center rounded-full shadow-lg transition-colors focus:outline-none"
            :class="{
                'bg-green-600 hover:bg-green-500': status === 'connected',
                'bg-yellow-500 hover:bg-yellow-400 animate-pulse': status === 'ringing' || status === 'calling',
                'bg-indigo-600 hover:bg-indigo-500': !['connected','ringing','calling'].includes(status)
            }">
        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/>
        </svg>
        <span x-show="status === 'connected'" class="absolute -top-1 -right-1 flex h-4 w-4">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-4 w-4 bg-green-500"></span>
        </span>
    </button>

    {{-- Expanded panel --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95 translate-y-2"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-2"
         class="w-72 rounded-xl bg-white shadow-2xl ring-1 ring-gray-900/10 overflow-hidden">

        {{-- Header --}}
        <div class="flex items-center justify-between px-4 py-3 transition-colors"
             :class="{
                 'bg-green-600': status === 'connected',
                 'bg-yellow-500': status === 'ringing' || status === 'calling',
                 'bg-indigo-600': !['connected','ringing','calling'].includes(status)
             }">
            <div class="flex items-center gap-2">
                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/>
                </svg>
                <span class="text-sm font-semibold text-white">Softphone</span>
                <span class="text-xs text-white/70 font-mono">Ext. {{ $spEmployee->sip_extension }}</span>
            </div>
            <button @click="open = false" class="text-white/70 hover:text-white transition-colors">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                </svg>
            </button>
        </div>

        {{-- Status bar --}}
        <div class="flex items-center gap-2 px-4 py-2 bg-gray-50 border-b border-gray-100">
            <span class="h-2 w-2 rounded-full flex-shrink-0"
                  :class="{
                      'bg-green-500': ['registered','connected'].includes(status),
                      'bg-yellow-500 animate-pulse': ['registering','calling','ringing'].includes(status),
                      'bg-red-500': ['failed','unregistered'].includes(status),
                      'bg-gray-400': status === 'idle'
                  }"></span>
            <span class="text-xs text-gray-600" x-text="statusLabel"></span>
            <span x-show="status === 'connected'" class="ml-auto text-xs font-mono text-green-600 tabular-nums" x-text="formatDuration(duration)"></span>
        </div>

        <div class="px-4 py-4 space-y-3">

            {{-- CallHippo notice --}}
            <div x-show="provider === 'callhippo'" class="rounded-md bg-blue-50 p-3 text-xs text-blue-700 leading-relaxed">
                CallHippo is your active phone provider. Use the CallHippo web portal or browser extension to place and receive calls. Call history syncs automatically here.
            </div>

            {{-- SIP Dialer --}}
            <div x-show="provider === 'sip'" class="space-y-3">

                {{-- Idle/ready: show number input + keypad --}}
                <template x-if="!['calling','ringing','connected'].includes(status)">
                    <div class="space-y-2">
                        <input type="tel" x-model="number"
                               placeholder="Number or extension…"
                               class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 text-sm shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 font-mono"
                               @keydown.enter="makeCall()">
                        <div class="grid grid-cols-3 gap-1">
                            <template x-for="key in ['1','2','3','4','5','6','7','8','9','*','0','#']" :key="key">
                                <button @click="pressKey(key)"
                                        class="h-9 rounded-md bg-gray-100 text-sm font-semibold text-gray-800 hover:bg-gray-200 active:scale-95 transition-all"
                                        x-text="key"></button>
                            </template>
                        </div>
                    </div>
                </template>

                {{-- In-call display --}}
                <template x-if="['calling','ringing','connected'].includes(status)">
                    <div class="text-center py-2">
                        <p class="text-base font-mono font-semibold text-gray-800" x-text="number || 'Unknown'"></p>
                        <p class="text-xs text-gray-500 mt-1" x-text="statusLabel"></p>
                    </div>
                </template>

                {{-- Controls --}}
                <div class="flex items-center justify-center gap-4 pt-1">
                    {{-- Mute (in call) --}}
                    <button x-show="status === 'connected'"
                            @click="toggleMute()"
                            class="h-10 w-10 rounded-full flex items-center justify-center transition-colors"
                            :class="muted ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path x-show="!muted" stroke-linecap="round" stroke-linejoin="round" d="M12 18.75a6 6 0 006-6v-1.5m-6 7.5a6 6 0 01-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 01-3-3V4.5a3 3 0 116 0v8.25a3 3 0 01-3 3z"/>
                            <path x-show="muted" stroke-linecap="round" stroke-linejoin="round" d="M17.25 9.75L19.5 12m0 0l2.25 2.25M19.5 12l2.25-2.25M19.5 12l-2.25 2.25m-10.5-6l4.72-4.72a.75.75 0 011.28.531V19.94a.75.75 0 01-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.506-1.938-1.354A9.01 9.01 0 012.25 12c0-.83.112-1.633.322-2.395C2.806 8.757 3.63 8.25 4.51 8.25H6.75z"/>
                        </svg>
                    </button>

                    {{-- Call button --}}
                    <button x-show="['registered','idle'].includes(status)"
                            @click="makeCall()"
                            :disabled="!number"
                            class="h-13 w-13 h-12 w-12 rounded-full bg-green-600 flex items-center justify-center text-white hover:bg-green-500 disabled:opacity-40 disabled:cursor-not-allowed transition-colors shadow-md">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/>
                        </svg>
                    </button>

                    {{-- Hang-up --}}
                    <button x-show="['connected','calling','ringing'].includes(status)"
                            @click="hangup()"
                            class="h-12 w-12 rounded-full bg-red-600 flex items-center justify-center text-white hover:bg-red-500 transition-colors shadow-md">
                        <svg class="h-6 w-6 rotate-[135deg]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Error message --}}
            <div x-show="errorMsg" x-transition class="rounded-md bg-red-50 px-3 py-2 text-xs text-red-700" x-text="errorMsg"></div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function softphoneWidget() {
    return {
        open: false,
        status: 'idle',
        provider: null,
        number: '',
        muted: false,
        duration: 0,
        _durationTimer: null,
        _callStart: null,
        simpleUser: null,
        config: null,
        errorMsg: '',

        get statusLabel() {
            return {
                idle:         'Not connected',
                registering:  'Connecting…',
                registered:   'Ready',
                calling:      'Calling…',
                ringing:      'Ringing…',
                connected:    'In call',
                failed:       'Connection failed',
                unregistered: 'Disconnected',
            }[this.status] || this.status;
        },

        async boot() {
            try {
                const r = await fetch('{{ route('my.phone.config') }}', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const cfg = await r.json();
                if (!cfg.enabled) return;
                this.config = cfg;
                this.provider = cfg.provider;
                if (cfg.provider !== 'sip') return;
                await this._loadSipJs();
                await this._registerSip();
            } catch (e) {
                this.errorMsg = 'Phone init error: ' + e.message;
                setTimeout(() => this.errorMsg = '', 5000);
            }
        },

        _loadSipJs() {
            return new Promise((resolve, reject) => {
                if (window.SIP) return resolve();
                const s = document.createElement('script');
                s.src = 'https://cdn.jsdelivr.net/npm/sip.js@0.21.2/lib/platform/web/simple-user.min.js';
                s.onload = resolve;
                s.onerror = () => reject(new Error('Could not load SIP.js'));
                document.head.appendChild(s);
            });
        },

        async _registerSip() {
            const cfg = this.config;
            const iceServers = [];
            if (cfg.stun_server) iceServers.push({ urls: cfg.stun_server });
            if (cfg.turn_server) {
                const t = { urls: cfg.turn_server };
                if (cfg.turn_username) t.username = cfg.turn_username;
                if (cfg.turn_password) t.credential = cfg.turn_password;
                iceServers.push(t);
            }

            const ext = cfg.sip_uri?.split(':')[1]?.split('@')[0] || '';
            const self = this;

            const delegate = {
                onCallCreated:    () => { self.status = 'calling'; },
                onCallAnswered:   () => { self.status = 'connected'; self._startTimer(); },
                onCallHangup:     () => {
                    const disp = self.duration > 3 ? 'answered' : 'no_answer';
                    self._stopTimer();
                    self._logCall('completed', disp);
                    self.status = 'registered';
                    self.number = '';
                    self.muted  = false;
                },
                onRegistered:        () => { self.status = 'registered'; },
                onServerConnect:     () => {},
                onServerDisconnect:  () => { self.status = 'unregistered'; },
            };

            this.status = 'registering';
            this.simpleUser = new SIP.Web.SimpleUser(cfg.websocket_url, {
                aor: cfg.sip_uri,
                delegate,
                media: {
                    constraints: { audio: true, video: false },
                    remote: { audio: document.getElementById('softphone-remote-audio') },
                },
                userAgentOptions: {
                    authorizationPassword: cfg.password,
                    authorizationUsername: ext,
                    iceServers: iceServers.length ? iceServers : undefined,
                },
            });
            await this.simpleUser.connect();
            await this.simpleUser.register();
        },

        async makeCall() {
            if (!this.number || !this.simpleUser) return;
            const domain = this.config.sip_uri?.split('@')[1] || '';
            const target = this.number.includes('@')
                ? 'sip:' + this.number
                : 'sip:' + this.number + '@' + domain;
            try {
                this.status = 'calling';
                this._callStart = Date.now();
                await this.simpleUser.call(target, {
                    inviteWithoutSdp: false,
                    sessionDescriptionHandlerOptions: { constraints: { audio: true, video: false } },
                });
            } catch (e) {
                this.status = 'registered';
                this.errorMsg = 'Call failed: ' + e.message;
                setTimeout(() => this.errorMsg = '', 4000);
            }
        },

        async hangup() {
            try { if (this.simpleUser) await this.simpleUser.hangup(); } catch (_) {}
            const disp = this.duration > 3 ? 'answered' : 'no_answer';
            this._stopTimer();
            await this._logCall('completed', disp);
            this.status = 'registered';
            this.number = '';
            this.muted  = false;
        },

        toggleMute() {
            if (!this.simpleUser) return;
            this.muted = !this.muted;
            this.muted ? this.simpleUser.mute() : this.simpleUser.unmute();
        },

        pressKey(key) { this.number += key; },

        _startTimer() {
            this.duration = 0;
            this._callStart = Date.now();
            this._durationTimer = setInterval(() => {
                this.duration = Math.floor((Date.now() - this._callStart) / 1000);
            }, 1000);
        },

        _stopTimer() {
            if (this._durationTimer) { clearInterval(this._durationTimer); this._durationTimer = null; }
        },

        formatDuration(s) {
            const m = Math.floor(s / 60), sec = s % 60;
            return String(m).padStart(2, '0') + ':' + String(sec).padStart(2, '0');
        },

        async _logCall(status, disposition) {
            const ext = this.config?.sip_uri?.split(':')[1]?.split('@')[0] || '';
            try {
                await fetch('{{ route('my.phone.log') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        direction:        'outbound',
                        caller_number:    ext,
                        callee_number:    this.number,
                        status,
                        disposition,
                        duration_seconds: this.duration,
                    }),
                });
            } catch (_) {}
        },
    };
}
</script>
@endpush

@endif
@endmodule
