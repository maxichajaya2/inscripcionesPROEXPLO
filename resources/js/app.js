import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { createI18n } from 'vue-i18n';

//PrimeVue
import PrimeVue from 'primevue/config';
import { definePreset } from '@primevue/themes';
import Aura from '@primevue/themes/aura';
import 'primeicons/primeicons.css'
import ToastService from 'primevue/toastservice';
import ConfirmationService from 'primevue/confirmationservice';

// import as directive
import Tooltip from 'primevue/tooltip';
import BadgeDirective from 'primevue/badgedirective';

const appName = import.meta.env.VITE_APP_NAME || 'Inscripciones';

const MyPreset = definePreset(Aura, {
    semantic: {
        primary: {
            50: '{indigo.50}',
            100: '{indigo.100}',
            200: '{indigo.200}',
            300: '{indigo.300}',
            400: '{indigo.400}',
            500: '{indigo.500}',
            600: '{indigo.600}',
            700: '{indigo.700}',
            800: '{indigo.800}',
            900: '{indigo.900}',
            950: '{indigo.950}'
        }
    }
});


const i18n = createI18n({
    legacy: false, // Use Composition API style
    locale: 'es', // Default language
    messages: {
      en: {
        greeting: 'Hello',
      },
      es: {
        greeting: 'Hola',
      },
    },
  });

createInertiaApp({
    title: (title) => `${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            app.use(plugin)
            app.use(ZiggyVue)
            app.use(PrimeVue, {
                theme: {
                    preset: MyPreset
                }
            })
            app.use(ToastService)
            app.use(ConfirmationService)
            app.directive('tooltip', Tooltip)
            app.directive('badge', BadgeDirective);
            window.Swal = app.config.globalProperties.$swal
            app.mount(el);
    },
    progress: {
        color: '#4A8188',
    },
});
