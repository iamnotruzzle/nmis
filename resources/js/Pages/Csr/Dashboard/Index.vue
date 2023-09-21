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
    completed_request_this_month: Number,
    completed_request_week: Number,
    completed_request_today: Number,
  },
  data() {
    return {
      completed_request_month_container: null,
      completed_request_week_container: null,
      completed_request_today_container: null,
      completed_request_container: [],
      currentMonth: null,
    };
  },
  mounted() {
    console.log(this.completed_request_this_month);
    this.storeCompletedRequestsCount();
    this.storeValueInRequestContainer();
    this.getCurrentMonth();
  },
  methods: {
    storeCompletedRequestsCount() {
      this.completed_request_month_container = this.completed_request_this_month;
      this.completed_request_week_container = this.completed_request_week;
      this.completed_request_today_container = this.completed_request_today;
    },
    storeValueInRequestContainer() {
      this.completed_request_container.push({
        month: this.completed_request_this_month,
        week: this.completed_request_week,
        today: this.completed_request_today,
      });
    },
    getCurrentMonth() {
      this.currentMonth = moment().format('MMMM');
    },
  },
};
</script>
