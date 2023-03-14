import { createApp, h } from 'vue';
// import { createInertiaApp } from '@inertiajs/inertia-vue3';
import { createInertiaApp } from '@inertiajs/vue3';
import PrimeVue from 'primevue/config';
import ToastService from 'primevue/toastservice';

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Laravel';

createInertiaApp({
  progress: {
    color: '#29d',
  },
  title: (title) => `${title} - ${appName}`,
  resolve: (name) => require(`./Pages/${name}.vue`),
  setup({ el, App, props, plugin }) {
    return createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(PrimeVue, { ripple: true })
      .use(ToastService)
      .mixin({ methods: { route } })
      .mount(el);
  },
});
