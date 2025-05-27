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
                  <span class="block text-500 font-medium mb-3">Ready to Receive</span>
                  <div class="text-900 font-medium text-xl">{{ ready_to_receive }}</div>
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
      <div class="col-12 md:col-6">
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
              No endorsements found for this location.
            </div>
          </template>
        </Card>
      </div>

      <!-- daily charges chart -->
      <div class="col-12 md:col-6">
        <Card class="w-full">
          <template #content>
            <h3 class="text-xl font-bold mb-1">💵 Daily Charges (₱)</h3>
            <Chart
              type="line"
              :data="dailyChargeChartData"
              :options="dailyChargeChartOptions"
            />
          </template>
        </Card>
      </div>
    </div>

    <!-- top diagnoses -->
    <div class="grid">
      <div class="col-12 md:col-6">
        <Card
          class="w-full shadow-md"
          style="max-width: 100%; margin: auto"
        >
          <template #title>
            <div class="flex justify-content-between">
              <div>
                <h3 class="text-xl font-bold mb-1">🏥 Top Diagnoses</h3>
                <span class="text-base font-normal mb-2 text-blue-500 font-italic">.</span>
              </div>
              <div class="flex justify-content-between align-items-center">
                <input
                  v-model="topDiagFrom"
                  type="date"
                  id="from"
                />
                <div class="mx-2">-</div>
                <input
                  v-model="topDiagTo"
                  type="date"
                  id="to"
                />
                <Button
                  icon="pi pi-check"
                  aria-label="Filter"
                  class="ml-2"
                  @click="fetchTopDiagnoses"
                />
              </div>
            </div>
          </template>
          <template #content>
            <!-- <div
              v-if="loadingTopDiagnosis"
              class="flex justify-content-center align-items-center"
              style="height: 400px; width: 100%"
            >
              <i class="pi pi-spin pi-spinner text-4xl text-blue-500"></i>
            </div>
            <div v-else>
              <Chart
                type="bar"
                :data="topDiagnosisChartData"
                :options="topDiagnosisChartOptions"
                style="height: 400px; width: 100%"
              />
            </div> -->
            <Chart
              type="bar"
              :data="topDiagnosisChartData"
              :options="topDiagnosisChartOptions"
              style="height: 400px; width: 100%"
            />
          </template>
        </Card>
      </div>

      <div class="col-12 md:col-6">
        <Card
          class="w-full shadow-md"
          style="max-width: 100%; margin: auto"
        >
          <template #title>
            <h3 class="text-xl font-bold mb-1">🏥 Top Diagnoses</h3>
            <span class="text-base font-normal mb-2 text-blue-500 font-italic">Updated weekly</span>
          </template>
          <template #content>
            <Chart
              type="bar"
              :data="topDiagnosisChartData"
              :options="topDiagnosisChartOptions"
              style="height: 400px; width: 100%"
            />
          </template>
        </Card>
      </div>
    </div>

    <!-- top items and  -->
    <div class="grid">
      <!-- top items -->
      <div class="col-12 md:col-6">
        <Card class="w-full shadow-md">
          <template #title>
            <h3 class="text-xl font-bold mb-1">📦 Top Items Charged</h3>
            <span class="text-base font-normal mb-2 text-orange-500 font-italic">Refresh every 5mins</span>
          </template>
          <template #content>
            <Chart
              type="bar"
              :data="topItemsChartData"
              :options="topItemsChartOptions"
            />
          </template>
        </Card>
      </div>

      <div class="col-12 md:col-6">
        <Card class="w-full shadow-md">
          <template #title>
            <h3 class="text-xl font-bold mb-1">📊 Monthly Charge Comparison</h3>
            <span class="text-base font-normal mb-2 text-orange-500 font-italic">.</span>
          </template>
          <template #content>
            <Chart
              type="bar"
              :data="monthlyChargeData"
              :options="monthlyChargeOptions"
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
import Button from 'primevue/button';
import Chart from 'primevue/chart';
import moment from 'moment';
import axios from 'axios';

export default {
  components: {
    AppLayout,
    Link,
    Card,
    Tag,
    DataTable,
    Column,
    Chart,
    Button,
  },
  props: {
    patient_charges_total: Number,
    low_stock_items: Number,
    ready_to_receive: Number,
    expiring_soon: Number,
    latest_endorsement: Object,
    topItems: Array,
    topItems_labels: Array,
    topItems_dataQty: Array,
    topItems_dataAmount: Array,
    chargeChartData: {
      type: Object,
      required: true,
    },
    lastMonthTotal: Number,
    currentMonthTotal: Number,
  },
  data() {
    return {
      dailyChargeChartData: this.chargeChartData,
      dailyChargeChartOptions: {
        devicePixelRatio: 4,
        responsive: true,
        // maintainAspectRatio: false,
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

      topItemsChartData: {
        labels: [], // from backend
        datasets: [
          {
            label: 'Total Quantity',
            backgroundColor: '#21ff76',
            data: [], // from backend
          },
          // Optional second dataset:
          // {
          //   label: 'Total Amount (₱)',
          //   backgroundColor: '#66BB6A',
          //   data: [], // total_amount
          // },
        ],
      },
      topItemsChartOptions: {
        responsive: true,
        plugins: {
          legend: { position: 'top' },
          title: {
            display: true,
            text: 'Top Charged Items - Last 7 Days',
          },
          tooltip: {
            callbacks: {
              title(tooltipItems) {
                // Show full label in tooltip
                return tooltipItems[0].labelFull || tooltipItems[0].label;
              },
            },
          },
        },
        scales: {
          x: {
            ticks: {
              callback: function (val, index) {
                const label = this.getLabelForValue(index);
                const maxLength = 15;
                return label.length > maxLength ? label.substring(0, maxLength) + '...' : label;
              },
            },
          },
        },
      },

      monthlyChargeData: {
        labels: ['Last Month', 'This Month'],
        datasets: [
          {
            label: 'Total Charges (₱)',
            backgroundColor: ['#f59e0b', '#3b82f6'],
            barPercentage: 0.5,
            barThickness: 80,
            maxBarThickness: 80,
            minBarLength: 2,
            data: [0, 0], // Placeholder
          },
        ],
      },
      monthlyChargeOptions: {
        responsive: true,
        plugins: {
          legend: {
            display: true,
            position: 'bottom',
          },
          tooltip: {
            callbacks: {
              label: function (context) {
                return `₱${context.raw.toLocaleString()}`;
              },
            },
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: function (value) {
                return '₱' + value.toLocaleString();
              },
            },
          },
        },
      },

      loadingTopDiagnosis: false,
      topDiagnosisChartData: {
        labels: [], // Filled in mounted
        datasets: [
          {
            label: 'Patients',
            backgroundColor: '#3398DB',
            data: [], // Filled in mounted
          },
        ],
      },
      topDiagnosisChartOptions: {
        indexAxis: 'y', // ✅ Horizontal bar chart
        responsive: true,
        plugins: {
          legend: { position: 'top' },
          title: {
            display: true,
            text: 'Top 10 Diagnoses - Current Month',
          },
          tooltip: {
            callbacks: {
              title(tooltipItems) {
                return tooltipItems[0].labelFull || tooltipItems[0].label;
              },
            },
          },
        },
        scales: {
          x: {
            beginAtZero: true,
            title: {
              display: true,
              text: 'Patients',
            },
          },
          y: {
            ticks: {
              callback: function (val, index) {
                const label = this.getLabelForValue(index);
                const maxLength = 16;
                return label.length > maxLength ? label.substring(0, maxLength) + '...' : label;
              },
            },
          },
        },
      },

      topDiagFrom: null,
      topDiagTo: null,
    };
  },
  mounted() {
    // top items
    this.topItemsChartData.labels = this.topItems_labels;
    this.topItemsChartData.datasets[0].data = this.topItems_dataQty;

    // monthly charge
    this.monthlyChargeData.datasets[0].data = [Number(this.lastMonthTotal), Number(this.currentMonthTotal)];

    // top diagnoses
    const today = new Date();
    // First day of current month
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    this.topDiagFrom = firstDay.toISOString().split('T')[0];
    // Today
    this.topDiagTo = today.toISOString().split('T')[0];
    this.fetchTopDiagnoses();
  },
  methods: {
    async fetchTopDiagnoses() {
      try {
        this.loadingTopDiagnosis = true;
        const response = await axios.get('warddashboard/top-diagnoses', {
          params: {
            from: this.topDiagFrom,
            to: this.topDiagTo,
          },
        });

        this.topDiagnosis = response.data;

        // update chart data
        this.topDiagnosisLabels = this.topDiagnosis.map((item) => item.final_diagnosis);
        this.topDiagnosisQty = this.topDiagnosis.map((item) => item.patient_count);

        this.topDiagnosisChartData.labels = this.topDiagnosisLabels;
        this.topDiagnosisChartData.datasets[0].data = this.topDiagnosisQty;
      } catch (error) {
        console.error('Failed to fetch top diagnosis data:', error);
      } finally {
        this.loadingTopDiagnosis = false;
      }
    },
    generateColors(num) {
      const colors = [];
      for (let i = 0; i < num; i++) {
        // Generate HSL colors evenly spaced in the hue range 0-360
        const hue = Math.round((360 / num) * i);
        colors.push(`hsl(${hue}, 70%, 60%)`);
      }
      return colors;
    },
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

<style scoped>
.chart {
  width: 100%;
}
</style>
