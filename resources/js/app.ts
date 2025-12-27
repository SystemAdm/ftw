import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { i18nVue } from 'laravel-vue-i18n';
import { initializeTheme } from './composables/useAppearance';
import { configureEcho } from '@laravel/echo-vue';

const scheme = (import.meta.env.VITE_REVERB_SCHEME ?? (window.location.protocol === 'https:' ? 'https' : 'http')) as 'http' | 'https';
const isTLS = scheme === 'https';

configureEcho({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST ?? window.location.hostname,
    // When the page is served over HTTPS, browsers require WSS and typically default to port 443.
    // When served over HTTP, use WS and your local Reverb port (8080 by default via Herd).
    wsPort: Number(import.meta.env.VITE_REVERB_WS_PORT ?? import.meta.env.VITE_REVERB_PORT ?? 80),
    wssPort: Number(import.meta.env.VITE_REVERB_WSS_PORT ?? 443),
    forceTLS: isTLS,
    enabledTransports: isTLS ? ['wss'] : ['ws', 'wss'],
});

createInertiaApp({
    title: (title) => {
        const appName = JSON.parse(document.getElementById('app')?.dataset.page || '{}').props?.name || import.meta.env.VITE_APP_NAME || 'Laravel';
        return title ? `${title} - ${appName}` : appName;
    },
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>('./pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(i18nVue, {
                resolve: async (lang: string) => {
                    const langs = import.meta.glob('../../lang/**.json');
                    return await langs[`../../lang/${lang}.json`]!();
                },
                // default locale can be set here if needed, e.g., fallback: 'en'
            })
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();
