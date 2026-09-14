import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

export function createConversationEcho(): Echo<'reverb'> | undefined {
    if (!import.meta.env.VITE_REVERB_APP_KEY) {
        return;
    }

    return new Echo({
        broadcaster: 'reverb',
        Pusher,
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: import.meta.env.VITE_REVERB_HOST ?? window.location.hostname,
        wsPort: Number(import.meta.env.VITE_REVERB_PORT ?? 8080),
        wssPort: Number(import.meta.env.VITE_REVERB_PORT ?? 443),
        forceTLS: import.meta.env.VITE_REVERB_SCHEME === 'https',
        enabledTransports: ['ws', 'wss'],
    });
}
