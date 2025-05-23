<template>
  <app-layout>
    <div class="surface-ground">
      <!-- top cards -->
      <div class="grid">
        <div class="col-12 md:col-6 lg:col-3">
          <div class="surface-card shadow-2 p-3 border-round h-full flex flex-column justify-between">
            <div class="flex justify-content-between mb-3">
              <div>
                <span class="block text-500 font-medium mb-3">Patient charges today</span>
                <div class="text-900 font-medium text-xl">
                  {{ patient_charges_total.toLocaleString('en-PH', { style: 'currency', currency: 'PHP' }) }}
                </div>
              </div>
              <div
                class="flex align-items-center justify-content-center bg-blue-100 border-round"
                style="width: 2.5rem; height: 2.5rem"
              >
                <i class="pi pi-credit-card text-blue-500 text-xl"></i>
              </div>
            </div>
            <span class="text-500">Total items charged to patients today</span>
          </div>
        </div>

        <div class="col-12 md:col-6 lg:col-3">
          <Link
            href="wardinv"
            class="no-underline text-inherit block h-full"
          >
            <div
              class="surface-card shadow-2 p-3 border-round h-full flex flex-column justify-between transition-all duration-200 hover:border-r-4 hover:border-green-500 cursor-pointer"
              style="border-right: 4px solid transparent"
            >
              <div class="flex justify-content-between mb-3">
                <div>
                  <span class="block text-500 font-medium mb-3">Low stock items</span>
                  <div class="text-900 font-medium text-xl">{{ low_stock_items }}</div>
                </div>
                <div
                  class="flex align-items-center justify-content-center bg-orange-100 border-round"
                  style="width: 2.5rem; height: 2.5rem"
                >
                  <i class="pi pi-exclamation-triangle text-orange-500 text-xl"></i>
                </div>
              </div>
              <span class="text-500">Need restocking (below reorder level)</span>
            </div>
          </Link>
        </div>

        <div class="col-12 md:col-6 lg:col-3">
          <Link
            href="requeststocks?page=1&status=FILLED"
            class="no-underline text-inherit block h-full"
          >
            <div
              class="surface-card shadow-2 p-3 border-round h-full flex flex-column justify-between transition-all duration-200 hover:border-r-4 hover:border-green-500 cursor-pointer"
              style="border-right: 4px solid transparent"
            >
              <div class="flex justify-content-between mb-3">
                <div>
                  <span class="block text-500 font-medium mb-3">Ready to Received</span>
                  <div class="text-900 font-medium text-xl">{{ ready_to_received }}</div>
                </div>
                <div
                  class="flex align-items-center justify-content-center bg-green-100 border-round"
                  style="width: 2.5rem; height: 2.5rem"
                >
                  <i class="pi pi-inbox text-green-500 text-xl"></i>
                </div>
              </div>
              <span class="text-500">Marked as filled by Central Supply</span>
            </div>
          </Link>
        </div>

        <div class="col-12 md:col-6 lg:col-3">
          <Link
            href="wardinv"
            class="no-underline text-inherit block h-full"
          >
            <div
              class="surface-card shadow-2 p-3 border-round h-full flex flex-column justify-between transition-all duration-200 hover:border-r-4 hover:border-green-500 cursor-pointer"
              style="border-right: 4px solid transparent"
            >
              <div class="flex justify-content-between mb-3">
                <div>
                  <span class="block text-500 font-medium mb-3">Expiring Soon</span>
                  <div class="text-900 font-medium text-xl">{{ expiring_soon }}</div>
                </div>
                <div
                  class="flex align-items-center justify-content-center bg-purple-100 border-round"
                  style="width: 2.5rem; height: 2.5rem"
                >
                  <i class="pi pi-clock text-purple-500 text-xl"></i>
                </div>
              </div>
              <span class="text-500">Within the next 30 days</span>
            </div>
          </Link>
        </div>
      </div>
    </div>

    <!-- endorsement and daily charges -->
    <div class="grid">
      <!-- endorsements -->
      <div class="col-6">
        <Card class="w-full shadow-md">
          <template #title> 🆕 Latest Endorsement </template>

          <template #content>
            <div v-if="latest_endorsement.length">
              <div class="text-xl mb-4">
                <p><strong>From:</strong> {{ latest_endorsement[0].firstname }} {{ latest_endorsement[0].lastname }}</p>
                <p><strong>Date:</strong> {{ tzone(latest_endorsement[0].created_at) }}</p>
              </div>

              <DataTable
                :value="latest_endorsement"
                class="p-datatable-sm"
                paginator
                :rows="5"
                removableSort
              >
                <Column
                  field="description"
                  header="DESCRIPTION"
                >
                  <template #body="{ data }">
                    <p class="text-justify">{{ data.description }}</p>
                  </template>
                </Column>
                <Column
                  field="tag"
                  header="TAG"
                  sortable
                />
                <Column
                  field="status"
                  header="STATUS"
                  sortable
                >
                  <template #body="{ data }">
                    <Tag
                      :value="data.status"
                      :severity="statusSeverity(data.status)"
                    />
                  </template>
                </Column>
              </DataTable>
            </div>
            <div
              v-else
              class="text-lg text-gray-500 italic text-center py-8"
            >
              No endorsements found for this ward.
            </div>
          </template>
        </Card>
      </div>

      <!-- daily charges chart -->
      <div class="col-6">
        <Card class="w-full">
          <template #content>
            <h3 class="text-xl font-bold mb-2">Daily Charges (₱)</h3>
            <Chart
              type="line"
              :data="chartData"
              :options="chartOptions"
            />
          </template>
        </Card>
      </div>
    </div>
  </app-layout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import Card from 'primevue/card';
import Tag from 'primevue/tag';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Chart from 'primevue/chart';
import moment from 'moment';

export default {
  components: {
    AppLayout,
    Link,
    Card,
    Tag,
    DataTable,
    Column,
    Chart,
  },
  props: {
    patient_charges_total: Number,
    low_stock_items: Number,
    ready_to_received: Number,
    expiring_soon: Number,
    latest_endorsement: Object,
    chargeChartData: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      chartData: this.chargeChartData,
      chartOptions: {
        devicePixelRatio: 4,
        responsive: true,
        plugins: {
          legend: {
            position: 'top',
          },
          title: {
            display: false,
            text: 'Daily Charges',
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            title: {
              display: true,
              text: '₱',
            },
          },
          x: {
            title: {
              display: true,
              text: 'Date',
            },
          },
        },
      },
    };
  },
  mounted() {},
  methods: {
    tzone(date) {
      if (date == null || date == '') {
        return null;
      } else {
        return moment.tz(date, 'Asia/Manila').format('L');
      }
    },
    statusSeverity(status) {
      //   console.log(status);
      switch (status) {
        case 'CANCELLED':
          return 'danger';

        case 'PENDING':
          return 'secondary';

        case 'ONGOING':
          return 'warning';

        case 'COMPLETED':
          return 'success';
      }
    },
  },
};
</script>
