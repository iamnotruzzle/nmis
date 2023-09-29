import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import PrimeVue from 'primevue/config';
import ToastService from 'primevue/toastservice';
import moment from 'moment-timezone';

import Echo from 'laravel-echo';

window.Pusher = require('pusher-js');

window.Echo = new Echo({
  broadcaster: 'pusher',
  key: process.env.MIX_PUSHER_APP_KEY,
  cluster: process.env.MIX_PUSHER_APP_CLUSTER,
  forceTLS: true,
});

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
  RiFileExcel2Line,
  SiMicrosoftexcel,
  MdNewreleasesOutlined,
} from 'oh-vue-icons/icons';
addIcons(
  CoChartLine,
  CoBorderAll,
  LaCapsulesSolid,
  LaBoxesSolid,
  LaUsersSolid,
  MdHandshakeSharp,
  FaUserInjured,
  MdLogoutSharp,
  RiFileExcel2Line,
  SiMicrosoftexcel,
  MdNewreleasesOutlined
);

moment.tz.setDefault('Asia/Manila');

createInertiaApp({
  // progress bar
  progress: {
    // progress: false,
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
