import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import 'leaflet/dist/leaflet.css';

// Import PrimeVue et Pinia
import PrimeVue from 'primevue/config';
import Aura from '@primevue/themes/aura';
import ConfirmationService from 'primevue/confirmationservice';
import ToastService from 'primevue/toastservice';
import 'primeicons/primeicons.css';
import { createPinia } from 'pinia';

// Créer l'instance Pinia
const pinia = createPinia();

const appName = import.meta.env.VITE_APP_NAME || 'city-play';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(pinia)
            .use(PrimeVue, {
                theme: {
                    preset: Aura
                }
            })
            .use(ConfirmationService)
            .use(ToastService)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
