<template>
  <app-layout>
    <Head title="NMIS - Dashboard" />

    <div class="surface-ground">
      <Toast />

      <div class="grid">
        <div class="col-12 md:col-4 lg:col-4">
          <div class="surface-card shadow-2 p-3 border-round">
            <div class="mb-3">
              <div class="flex justify-content-between">
                <div>
                  <span class="block text-xl text-900 text-blue-500 font-bold">INCOMING REQUEST</span>
                </div>

                <!-- a -->

                <Link href="issueitems?page=1&status=PENDING">
                  <div
                    class="flex align-items-center justify-content-center bg-blue-100 border-round"
                    style="width: 3rem; height: 3rem"
                  >
                    <v-icon
                      name="bi-cart"
                      class="pi pi-send text-blue-500 text-xl"
                    ></v-icon>
                  </div>
                </Link>
              </div>
            </div>
            <div class="flex justify-content-center">
              <span class="text-5xl font-bold text-blue-500">{{ pending_requests_container }}</span>
            </div>
          </div>
        </div>
        <div class="col-12 md:col-4 lg:col-4">
          <div class="surface-card shadow-2 p-3 border-round">
            <div class="mb-3">
              <div class="flex justify-content-between">
                <div>
                  <span class="block text-xl text-900 text-orange-500 font-bold">CANCELLED</span>
                </div>

                <Link href="issueitems?page=1&status=CANCELLED">
                  <div
                    class="flex align-items-center justify-content-center bg-orange-100 border-round"
                    style="width: 3rem; height: 3rem"
                  >
                    <v-icon
                      name="fc-cancel"
                      class="pi pi-send text-orange-500 text-xl"
                    ></v-icon>
                  </div>
                </Link>
              </div>
            </div>
            <div class="flex justify-content-center">
              <span class="text-5xl font-bold text-orange-500">{{ cancelled_requests_container }}</span>
            </div>
          </div>
        </div>
        <div class="col-12 md:col-4 lg:col-4">
          <div class="surface-card shadow-2 p-3 border-round">
            <div class="mb-3">
              <div class="flex justify-content-between">
                <div>
                  <span class="block text-xl text-900 text-green-500 font-bold">COMPLETED</span>
                </div>

                <Link href="issueitems?page=1&status=RECEIVED">
                  <div
                    class="flex align-items-center justify-content-center bg-green-100 border-round"
                    style="width: 3rem; height: 3rem"
                  >
                    <v-icon
                      name="bi-check-lg"
                      class="pi pi-send text-green-500 text-xl"
                    ></v-icon>
                  </div>
                </Link>
              </div>
            </div>
            <div class="flex justify-content-center">
              <span class="text-5xl font-bold text-green-500">{{ completed_requests_container }}</span>
            </div>
          </div>
        </div>
        <!--  -->
      </div>
      <div class="my-2"></div>
      <div class="grid">
        <div class="col-12 md:col-12 lg:col-8">
          <div class="surface-card shadow-2 p-3 border-round"></div>
        </div>
        <div class="col-12 md:col-12 lg:col-4">
          <div class="surface-card shadow-2 p-3 border-round">
            <div class="mb-3">
              <div class="flex justify-content-between">
                <span class="block text-xl text-900 text-purple-500 font-bold">About to expire</span>
                <Link href="csrstocks">
                  <div
                    class="flex align-items-center justify-content-center bg-purple-100 border-round"
                    style="width: 2.5rem; height: 2.5rem"
                  >
                    <v-icon
                      name="md-newreleases-outlined"
                      class="text-purple-500 text-xl"
                    ></v-icon>
                  </div>
                </Link>
              </div>
            </div>
            <DataTable
              :value="about_to_expire_container"
              showGridlines
              class="p-datatable-sm"
              scrollable
              removableSort
              scrollHeight="500px"
              tableStyle="min-height: h-full;"
            >
              <Column
                field="item"
                header="ITEM"
                style="width: 80%"
              ></Column>
              <Column
                field="expiration_date"
                header="EXP. DATE"
                style="width: 20%"
              >
                <template #body="{ data }">
                  {{ tzone(data.expiration_date) }}
                </template>
              </Column>
            </DataTable>
          </div>
        </div>
      </div>
    </div>
  </app-layout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import { FilterMatchMode } from 'primevue/api';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dropdown from 'primevue/dropdown';
import Tag from 'primevue/tag';
import Textarea from 'primevue/textarea';
import Toast from 'primevue/toast';
import Avatar from 'primevue/avatar';
import Calendar from 'primevue/calendar';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import moment, { now } from 'moment';
import { use } from 'echarts/core';
import { CanvasRenderer } from 'echarts/renderers';
import { PieChart, BarChart } from 'echarts/charts';
import { TitleComponent, TooltipComponent, LegendComponent, GridComponent } from 'echarts/components';
import VChart, { THEME_KEY } from 'vue-echarts';
import Echo from 'laravel-echo';

use([CanvasRenderer, PieChart, BarChart, TitleComponent, TooltipComponent, LegendComponent, GridComponent]);

export default {
  components: {
    AppLayout,
    Head,
    Link,
    DataTable,
    Column,
    VChart,
    Dropdown,
    Tag,
    Textarea,
    Toast,
    Avatar,
    Calendar,
    Button,
    Dialog,
    InputText,
    InputNumber,
  },
  props: {
    pending_requests: Number,
    cancelled_requests: Number,
    completed_requests: Number,
    about_to_expire: Object,
  },
  data() {
    return {
      pending_requests_container: null,
      cancelled_requests_container: null,
      completed_requests_container: null,
      about_to_expire_container: [],
    };
  },
  mounted() {
    // console.log(this.about_to_expire);
    this.storePendingRequests();
    this.storeCancelledRequests();
    this.storeCompletedRequests();
    this.storeAboutToExpiredInContainer();
  },
  methods: {
    tzone(date) {
      if (date == null || date == '') {
        return null;
      } else {
        return moment.tz(date, 'Asia/Manila').format('L');
      }
    },
    checkIfAboutToExpire(date) {
      let current_date = moment.tz(moment(), 'Asia/Manila');
      let exp_date = moment.tz(date, 'Asia/Manila');

      // adding +1 to include the starting date
      let date_diff = exp_date.diff(current_date, 'days') + 1;

      //   console.log(current_date.format('MM-DD-YY') == exp_date.format('MM-DD-YY'));

      //    exp_date.format('MM-DD-YY') < current_date.format('MM-DD-YY')
      if (
        current_date.format('MM-DD-YY') == exp_date.format('MM-DD-YY') ||
        Date.parse(exp_date) < Date.parse(current_date)
      ) {
        return 'Item has expired.';
      } else if (date_diff == 1) {
        return date_diff + ' day remaining.';
      } else {
        return date_diff + ' days remaining.';
      }
    },
    storePendingRequests() {
      this.pending_requests_container = this.pending_requests;
    },
    storeCancelledRequests() {
      this.cancelled_requests_container = this.cancelled_requests;
    },
    storeCompletedRequests() {
      this.completed_requests_container = this.completed_requests;
    },
    storeAboutToExpiredInContainer() {
      this.about_to_expire_container = [];

      this.about_to_expire.forEach((e) => {
        this.about_to_expire_container.push({
          item: e.cl2desc,
          expiration_date: e.expiration_date,
        });
      });
    },
  },
};
</script>
<style scoped>
/* Remove arrow for input type number */
/* Chrome, Safari, Edge, Opera */
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

/* Firefox */
input[type='number'] {
  -moz-appearance: textfield;
}
/* END Remove arrow for input type number */
</style>
