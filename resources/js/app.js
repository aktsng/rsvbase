import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { route, ZiggyVue } from 'ziggy-js';
// window.Ziggy は blade の @routes によってグローバルに提供されます

// `script setup` 形式のVueコンポーネント内等で `route()` をグローバル利用可能にするため
window.route = route;

createInertiaApp({
    title: (title) => title ? `${title} - RsvBase` : 'RsvBase',
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
        return pages[`./Pages/${name}.vue`];
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#6366f1',
        showSpinner: true,
    },
});
