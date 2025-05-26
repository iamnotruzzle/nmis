import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import PrimeVue from 'primevue/config';
import ToastService from 'primevue/toastservice';
import moment from 'moment-timezone';
import Tooltip from 'primevue/tooltip';
import NProgress from 'nprogress';
import 'nprogress/nprogress.css'; // Import default styles
import { router } from '@inertiajs/vue3';
import Echo from 'laravel-echo';
import * as echarts from 'echarts/core';
import { TreemapChart } from 'echarts/charts';
import { TitleComponent, TooltipComponent } from 'echarts/components';
import { CanvasRenderer } from 'echarts/renderers';
import VueECharts from 'vue-echarts';

echarts.use([TreemapChart, TitleComponent, TooltipComponent, CanvasRenderer]);

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
  BiHourglass,
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
  LaTruckMovingSolid,
  FcCancel,
  PrPencil,
  BiCart,
  BiCheckLg,
  SiConvertio,
  CoArrowThickRight,
  SiOxygen,
  BiJournalCheck,
  OiArrowSwitch,
  GiRun,
  RiUserSharedFill,
  BiArrowLeftRight,
  BiBoxes,
  LaReceiptSolid,
  GiReturnArrow,
  BiQuestionCircle,
  MdReceiptlongOutlined,
  OiPackage,
  BiCheck2,
  RiHandbagLine,
  BiBoundingBoxCircles,
  BiAt,
} from 'oh-vue-icons/icons';
addIcons(
  CoChartLine,
  BiHourglass,
  CoBorderAll,
  LaCapsulesSolid,
  LaBoxesSolid,
  LaUsersSolid,
  MdHandshakeSharp,
  FaUserInjured,
  MdLogoutSharp,
  RiFileExcel2Line,
  SiMicrosoftexcel,
  LaTruckMovingSolid,
  MdNewreleasesOutlined,
  FcCancel,
  PrPencil,
  BiCart,
  BiCheckLg,
  SiConvertio,
  CoArrowThickRight,
  SiOxygen,
  BiJournalCheck,
  OiArrowSwitch,
  GiRun,
  RiUserSharedFill,
  BiArrowLeftRight,
  BiBoxes,
  LaReceiptSolid,
  GiReturnArrow,
  BiQuestionCircle,
  MdReceiptlongOutlined,
  OiPackage,
  BiCheck2,
  RiHandbagLine,
  BiBoundingBoxCircles,
  BiAt
);

moment.tz.setDefault('Asia/Manila');

NProgress.configure({ showSpinner: false, speed: 400 });

createInertiaApp({
  progress: false,

  resolve: (name) => require(`./Pages/${name}.vue`),
  setup({ el, App, props, plugin }) {
    return createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(PrimeVue, { ripple: true })
      .use(ToastService)
      .directive('tooltip', Tooltip)
      .component('v-icon', OhVueIcon)
      .component('v-chart', VueECharts)
      .mixin({ methods: { route } })
      .mount(el);
  },
});

// // // Handle Inertia navigation events
// document.addEventListener('inertia:start', (event) => {
//   // Only start progress bar if it's a link click (not a form submission)
//   if (event.detail.visit.method === 'get') {
//     NProgress.start();
//   }
// });

// document.addEventListener('inertia:finish', (event) => {
//   if (!event.detail.visit.completed) return;
//   NProgress.done();
// });

// Global flag to prevent NProgress during Echo reloads
window.skipNProgress = false;

// Handle Inertia navigation events
document.addEventListener('inertia:start', (event) => {
  if (!window.skipNProgress && event.detail.visit.method === 'get') {
    NProgress.start();
  }
});

document.addEventListener('inertia:finish', (event) => {
  if (!event.detail.visit.completed) return;
  NProgress.done();
  window.skipNProgress = false; // Reset flag after navigation
});
