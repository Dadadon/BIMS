import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import focus from '@alpinejs/focus';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// ── Alpine.js ────────────────────────────────────────────────────────────────
Alpine.plugin(collapse);
Alpine.plugin(focus);
window.Alpine = Alpine;
Alpine.start();

// ── Laravel Echo + Reverb ────────────────────────────────────────────────────
// Reverb uses the Pusher protocol, so we configure pusher-js with the
// Reverb host/port values injected via meta tags (set in layouts/app.blade.php).
const reverbAppKey  = document.head.querySelector('meta[name="reverb-app-key"]')?.content;
const reverbHost    = document.head.querySelector('meta[name="reverb-host"]')?.content;
const reverbPort    = parseInt(document.head.querySelector('meta[name="reverb-port"]')?.content ?? '8080');
const reverbScheme  = document.head.querySelector('meta[name="reverb-scheme"]')?.content ?? 'http';

if (reverbAppKey) {
    window.Pusher = Pusher;

    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: reverbAppKey,
        wsHost: reverbHost,
        wsPort: reverbScheme === 'https' ? 443 : reverbPort,
        wssPort: reverbScheme === 'https' ? 443 : reverbPort,
        forceTLS: reverbScheme === 'https',
        enabledTransports: ['ws', 'wss'],
        authEndpoint: '/broadcasting/auth',
    });
}
