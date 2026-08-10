import '../css/app.css';
import './bootstrap';

import { config, createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

config.set('visitOptions', (href, options) => ({
    ...options,
    viewTransition: true,
}));

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);

        const loader = document.getElementById('app-loader');

        if (loader) {
            requestAnimationFrame(() => {
                loader.classList.add('app-loader-leave');
                loader.addEventListener('transitionend', () => loader.remove(), { once: true });
            });
        }

        return app;
    },
    progress: {
        color: '#4F46E5',
    },
});
