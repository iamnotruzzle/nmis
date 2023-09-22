<template>
  <app-layout>
    <div class="surface-ground">
      <div class="grid">
        <div class="col-12 md:col-6 lg:col-3">
          <div class="surface-card shadow-2 p-3 border-round">
            <div class="mb-3">
              <div class="flex justify-content-between">
                <span class="block text-xl text-900 font-bold">Completed request</span>
                <Link href="issueitems">
                  <div
                    class="flex align-items-center justify-content-center bg-blue-100 border-round"
                    style="width: 2.5rem; height: 2.5rem"
                  >
                    <i class="pi pi-send text-blue-500 text-xl"></i>
                  </div>
                </Link>
              </div>
            </div>
            <DataTable
              :value="completed_request_container"
              showGridlines
            >
              <template #header>
                <div class="flex justify-content-start">
                  <p class="text-xl text-blue-500 font-semibold">{{ currentMonth }}</p>
                </div>
              </template>
              <Column
                field="month"
                header="MONTHLY"
              ></Column>
              <Column
                field="week"
                header="WEEKLY"
              ></Column>
              <Column
                field="today"
                header="TODAY"
              ></Column>
            </DataTable>
          </div>
        </div>
        <div class="col-12 md:col-6 lg:col-3">
          <div class="surface-card shadow-2 p-3 border-round">
            <div class="mb-3">
              <div class="flex justify-content-between">
                <span class="block text-xl text-900 font-bold">Total cost of issued items</span>
                <Link href="issueitems">
                  <div
                    class="flex align-items-center justify-content-center bg-green-100 border-round"
                    style="width: 2.5rem; height: 2.5rem"
                  >
                    <i class="pi pi-money-bill text-green-500 text-xl"></i>
                  </div>
                </Link>
              </div>
            </div>
            <DataTable
              :value="total_cost_container"
              showGridlines
            >
              <template #header>
                <div class="flex justify-content-start">
                  <p class="text-xl text-green-500 font-semibold">{{ currentMonth }}</p>
                </div>
              </template>
              <Column header="MONTHLY">
                <template #body="{ data }"> ₱ {{ data.month }} </template>
              </Column>
              <Column header="WEEKLY">
                <template #body="{ data }"> ₱ {{ data.week }} </template>
              </Column>
              <Column header="TODAY">
                <template #body="{ data }"> ₱ {{ data.today }} </template></Column
              >
            </DataTable>
          </div>
        </div>
        <div class="col-12 md:col-6 lg:col-3">
          <div class="surface-card shadow-2 p-3 border-round">
            <div class="mb-3">
              <div class="flex justify-content-between">
                <span class="block text-xl text-900 font-bold">Top 5 requested items</span>
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
            <DataTable
              :value="most_requested_container"
              showGridlines
              class="p-datatable-sm"
            >
              <template #header>
                <div class="flex justify-content-start">
                  <p class="text-xl text-pink-500 font-semibold">{{ currentMonth }}</p>
                </div>
              </template>
              <Column
                field="item"
                header="ITEM"
              ></Column>
              <Column
                field="quantity"
                header="quantity"
              ></Column>
            </DataTable>
          </div>
        </div>
        <div class="col-12 md:col-6 lg:col-3">
          <div class="surface-card shadow-2 p-3 border-round">
            <div class="mb-3">
              <div class="flex justify-content-between">
                <span class="block text-xl text-900 font-bold">Completed request</span>
                <Link href="issueitems">
                  <div
                    class="flex align-items-center justify-content-center bg-blue-100 border-round"
                    style="width: 2.5rem; height: 2.5rem"
                  >
                    <i class="pi pi-send text-blue-500 text-xl"></i>
                  </div>
                </Link>
              </div>
            </div>
            <DataTable
              :value="completed_request_container"
              showGridlines
            >
              <template #header>
                <div class="flex justify-content-start">
                  <p class="text-xl text-blue-500 font-semibold">{{ currentMonth }}</p>
                </div>
              </template>
              <Column
                field="month"
                header="MONTHLY"
              ></Column>
              <Column
                field="week"
                header="WEEKLY"
              ></Column>
              <Column
                field="today"
                header="TODAY"
              ></Column>
            </DataTable>
          </div>
        </div>
      </div>
    </div>
  </app-layout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import moment from 'moment';

export default {
  components: {
    AppLayout,
    Link,
    DataTable,
    Column,
  },
  props: {
    completed_request_month: Number,
    completed_request_week: Number,
    completed_request_today: Number,
    total_cost_month: Object,
    total_cost_week: Object,
    total_cost_today: Object,
    most_requested_month: Object,
    most_requested_week: Object,
  },
  data() {
    return {
      completed_request_container: [],
      total_cost_container: [],
      most_requested_container: [],
      currentMonth: null,
    };
  },
  mounted() {
    console.log(this.most_requested_month);
    this.storeValueInRequestContainer();
    this.storeValueInTotalCostContainer();
    this.storeValueInMostRequestedContainer();
    this.getCurrentMonth();
  },
  methods: {
    storeValueInRequestContainer() {
      this.completed_request_container.push({
        month: this.completed_request_month,
        week: this.completed_request_week,
        today: this.completed_request_today,
      });
    },
    storeValueInTotalCostContainer() {
      // currency formatter
      const formatter = new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2,
      });

      let month = 0;
      let week = 0;
      let today = 0;

      this.total_cost_month.forEach((e) => {
        month += Number(e.total_cost);
      });
      this.total_cost_week.forEach((e) => {
        week += Number(e.total_cost);
      });
      this.total_cost_today.forEach((e) => {
        today += Number(e.total_cost);
      });

      this.total_cost_container.push({
        month: formatter.format(month).replace('$', '').trim(),
        week: formatter.format(week).replace('$', '').trim(),
        today: formatter.format(today).replace('$', '').trim(),
      });
    },
    storeValueInMostRequestedContainer() {
      this.most_requested_month.forEach((e) => {
        this.most_requested_container.push({
          item: e.item,
          quantity: e.quantity,
        });
      });
    },
    getCurrentMonth() {
      this.currentMonth = moment().format('MMMM');
    },
  },
};
</script>
