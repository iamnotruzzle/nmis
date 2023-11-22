<template>
  <app-layout>
    <div class="surface-ground">
      <div class="grid">
        <div class="col-12 md:col-4 lg:col-4">
          <div class="surface-card shadow-2 p-3 border-round">
            <div class="mb-3">
              <div class="flex justify-content-between">
                <div>
                  <span class="block text-xl text-900 font-bold">PENDING</span>
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
                  <span class="block text-xl text-900 font-bold">CANCELLED</span>
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
              <span class="text-5xl font-bold text-orange-500">{{ pending_requests_container }}</span>
            </div>
          </div>
        </div>
        <div class="col-12 md:col-4 lg:col-4">
          <div class="surface-card shadow-2 p-3 border-round">
            <div class="mb-3">
              <div class="flex justify-content-between">
                <div>
                  <span class="block text-xl text-900 font-bold">COMPLETED</span>
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
          <div class="surface-card shadow-2 p-3 border-round">
            <div class="mb-3">
              <div class="flex justify-content-between">
                <div class="flex flex-column">
                  <span class="text-xl text-pink-500 font-semibold">{{ currentMonth }}</span>
                  <span class="block text-xl text-900 font-bold">Top 5 requested stocks</span>
                </div>

                <Link href="issueitems">
                  <div
                    class="flex align-items-center justify-content-center bg-pink-100 border-round"
                    style="width: 2.5rem; height: 2.5rem"
                  >
                    <i class="pi pi-heart text-pink-500 text-xl"></i>
                  </div>
                </Link>
              </div>
            </div>
            <v-chart
              class="h-20rem w-full ma-0 pa-0"
              :option="mostRequestedItemsOptions()"
              autoresize
            />
          </div>
        </div>
        <div class="col-12 md:col-12 lg:col-4">
          <div class="surface-card shadow-2 p-3 border-round">
            <div class="mb-3">
              <div class="flex justify-content-between">
                <span class="block text-xl text-900 font-bold">New stocks</span>
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
              :value="new_stocks_container"
              showGridlines
              class="p-datatable-sm"
              scrollable
              scrollHeight="230px"
              tableStyle="min-height: h-full;"
            >
              <template #header>
                <div class="flex justify-content-start">
                  <p class="text-xl text-purple-500 font-semibold">{{ currentMonth }}</p>
                </div>
              </template>
              <Column
                field="item"
                header="ITEM"
              ></Column>
              <Column
                field="expiration_date"
                header="EXP. DATE"
              >
                <template #body="{ data }">
                  {{ tzone(data.expiration_date) }}
                </template>
              </Column>
            </DataTable>
          </div>
        </div>
      </div>
      <div
        class="my-2"
        v-show="false"
      >
        <div class="grid">
          <div class="col-12 md:col-12 lg:col-12">
            <div class="surface-card shadow-2 p-3 border-round">
              <div class="mb-3">
                <div class="flex justify-content-between">
                  <div class="flex flex-column">
                    <span class="text-xl text-pink-500 font-semibold">{{ currentMonth }}</span>
                    <span class="block text-xl text-900 font-bold">Top 5 requested stocks</span>
                  </div>

                  <Link href="issueitems">
                    <div
                      class="flex align-items-center justify-content-center bg-pink-100 border-round"
                      style="width: 2.5rem; height: 2.5rem"
                    >
                      <i class="pi pi-heart text-pink-500 text-xl"></i>
                    </div>
                  </Link>
                </div>
              </div>
              <v-chart
                class="ma-0 pa-0"
                style="height: 410px; margin: 0; padding: 0"
                :option="ordersAndIssuedOptions()"
                autoresize
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  </app-layout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
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
    Link,
    DataTable,
    Column,
    VChart,
  },
  props: {
    pending_requests: Number,
    cancelled_requests: Number,
    completed_requests: Number,
    most_requested_month: Object,
    new_stocks: Object,
  },
  data() {
    return {
      pending_requests_container: null,
      cancelled_requests_container: null,
      completed_requests_container: null,
      most_requested_container: [],
      new_stocks_container: [],
      currentMonth: null,
    };
  },
  mounted() {
    window.Echo.channel('request').listen('RequestStock', (args) => {
      router.reload({
        onSuccess: (e) => {
          this.pending_requests_container = null;
          this.cancelled_requests_container = null;
          this.completed_requests_container = null;
          this.most_requested_container = [];
          this.new_stocks_container = [];

          this.storePendingRequests();
          this.storeCancelledRequests();
          this.storeCompletedRequests();
          this.storeValueInMostRequestedContainer();
          this.storeValueInNewStocksContainer();
          this.getCurrentMonth();
        },
      });
    });
    // console.log(this.pending_requests);
    this.storePendingRequests();
    this.storeCancelledRequests();
    this.storeCompletedRequests();
    this.storeValueInMostRequestedContainer();
    this.storeValueInNewStocksContainer();
    this.getCurrentMonth();
  },
  methods: {
    storePendingRequests() {
      this.pending_requests_container = this.pending_requests;

      //   console.log(this.pending_requests);
    },
    storeCancelledRequests() {
      this.cancelled_requests_container = this.cancelled_requests;
    },
    storeCompletedRequests() {
      this.completed_requests_container = this.completed_requests;
    },

    storeValueInMostRequestedContainer() {
      this.most_requested_month.forEach((e) => {
        this.most_requested_container.push({
          item: e.item,
          quantity: e.quantity,
        });
      });
    },
    storeValueInNewStocksContainer() {
      this.new_stocks.forEach((e) => {
        this.new_stocks_container.push({
          item: e.item,
          expiration_date: e.expiration_date,
        });
      });
    },
    mostRequestedItemsOptions() {
      let option = {
        title: {
          left: 'center',
          textStyle: {
            fontSize: 18,
            fontWeight: 'bold',
            color: '#FFFFFF',
          },
        },
        // legend: {
        //   orient: 'vertical',
        //   left: 'left',
        //   textStyle: {
        //     color: '#FFFFFF',
        //   },
        // },
        label: {
          color: '#fff',
        },
        tooltip: {
          trigger: 'item',
        },
        series: [
          {
            name: 'ITEM',
            type: 'pie',
            radius: '85%', // chart size
            data: [],
          },
        ],
      };

      this.most_requested_container.forEach((e) => {
        option.series[0].data.push({
          value: e.quantity,
          name: e.item,
        });
      });

      return option;
    },
    ordersAndIssuedOptions() {
      let option = {
        tooltip: {
          trigger: 'axis',
          //   axisPointer: {
          //     // Use axis to trigger tooltip
          //     type: 'shadow', // 'shadow' as default; can also be 'line' or 'shadow'
          //   },
        },
        legend: {
          textStyle: {
            color: 'white',
          },
        },
        // set height of the chart canvas
        grid: {
          show: true,
          bottom: 20,
          left: 30,
          right: 30,
        },
        xAxis: {
          data: ['MON', 'TUE', 'WED', 'THU', 'FRI'],
          splitLine: {
            show: true,
          },
        },
        yAxis: {},
        // color: ['#67B6FF', '#4F7AA5'], // bar colors
        textStyle: {
          fontSize: 12,
        },
        series: [
          {
            data: [10, 22, 28, 43, 49],
            name: 'ORDERS',
            type: 'bar',
            stack: 'x',
            label: {
              show: true, // show value inside of the bars
            },
            emphasis: {
              focus: 'series',
            },
          },
          {
            data: [5, 4, 3, 5, 10],
            name: 'ISSUED',
            type: 'bar',
            stack: 'x',
            label: {
              show: true, // show value inside of the bars
            },
            emphasis: {
              focus: 'series',
            },
          },
        ],
      };

      return option;
    },
    getCurrentMonth() {
      this.currentMonth = moment().format('MMMM');
    },
    tzone(date) {
      if (date == null || date == '') {
        return null;
      } else {
        return moment.tz(date, 'Asia/Manila').format('LL');
      }
    },
  },
};
</script>
