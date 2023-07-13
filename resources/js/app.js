import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import PrimeVue from 'primevue/config';
import ToastService from 'primevue/toastservice';
import moment from 'moment-timezone';

import { OhVueIcon, addIcons } from 'oh-vue-icons';
import {
  CoChartLine,
  CoBorderAll,
  LaCapsulesSolid,
  LaBoxesSolid,
  LaUsersSolid,
  MdHandshakeSharp,
  FaUserInjured,
  MdLogoutSharp,
} from 'oh-vue-icons/icons';
addIcons(
  CoChartLine,
  CoBorderAll,
  LaCapsulesSolid,
  LaBoxesSolid,
  LaUsersSolid,
  MdHandshakeSharp,
  FaUserInjured,
  MdLogoutSharp
);

moment.tz.setDefault('Asia/Manila');
createInertiaApp({
  progress: {
    color: '#29d',
  },
  resolve: (name) => require(`./Pages/${name}.vue`),
  setup({ el, App, props, plugin }) {
    return createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(PrimeVue, { ripple: true })
      .use(ToastService)
      .component('v-icon', OhVueIcon)
      .mixin({ methods: { route } })
      .mount(el);
  },
});
