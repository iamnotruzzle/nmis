<template>
  <app-layout>
    <div class="surface-ground">
      <div class="grid">
        <div class="col-12 md:col-6 lg:col-4">
          <div class="surface-card shadow-2 p-3 border-round">
            <div class="mb-3">
              <div class="flex justify-content-between">
                <div class="flex flex-column">
                  <span class="text-xl text-blue-500 font-semibold">{{ currentMonth }}</span>
                  <span class="block text-xl text-900 font-bold">Completed requests</span>
                </div>

                <Link href="issueitems">
                  <div
                    class="flex align-items-center justify-content-center bg-blue-100 border-round"
                    style="width: 3rem; height: 3rem"
                  >
                    <i class="pi pi-send text-blue-500 text-xl"></i>
                  </div>
                </Link>
              </div>
            </div>
            <div class="flex justify-content-center">
              <span class="text-5xl font-bold text-blue-500">{{ completed_requests_month_container }}</span>
            </div>
          </div>
        </div>
        <div class="col-12 md:col-6 lg:col-4">
          <div class="surface-card shadow-2 p-3 border-round">
            <div class="mb-3">
              <div class="flex justify-content-between">
                <div class="flex flex-column">
                  <span class="text-xl text-yellow-500 font-semibold">{{ currentMonth }}</span>
                  <span class="block text-xl text-900 font-bold">Pending requests</span>
                </div>

                <Link href="issueitems">
                  <div
                    class="flex align-items-center justify-content-center bg-yellow-100 border-round"
                    style="width: 3rem; height: 3rem"
                  >
                    <i class="pi pi-send text-yellow-500 text-xl"></i>
                  </div>
                </Link>
              </div>
            </div>
            <div class="flex justify-content-center">
              <span class="text-5xl font-bold text-yellow-500">{{ pending_requests_month_container }}</span>
            </div>
          </div>
        </div>
        <!-- <div class="col-12 md:col-6 lg:col-3">
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
        </div> -->
        <div class="col-12 md:col-6 lg:col-4">
          <div class="surface-card shadow-2 p-3 border-round">
            <div class="mb-3">
              <div class="flex justify-content-between">
                <div class="flex flex-column">
                  <span class="text-xl text-blue-500 font-semibold">{{ currentMonth }}</span>
                  <span class="block text-xl text-900 font-bold">Completed requests</span>
                </div>

                <Link href="issueitems">
                  <div
                    class="flex align-items-center justify-content-center bg-blue-100 border-round"
                    style="width: 3rem; height: 3rem"
                  >
                    <i class="pi pi-send text-blue-500 text-xl"></i>
                  </div>
                </Link>
              </div>
            </div>
            <div class="flex justify-content-center">
              <span class="text-5xl font-bold">10</span>
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
    completed_requests_month: Number,
    pending_requests_month: Number,
    most_requested_month: Object,
  },
  data() {
    return {
      completed_requests_month_container: null,
      pending_requests_month_container: null,
      most_requested_container: [],
      currentMonth: null,
    };
  },
  mounted() {
    console.log(this.completed_requests_month);
    this.storeCompletedRequests();
    this.storePendingRequests();
    // this.storeValueInMostRequestedContainer();
    this.getCurrentMonth();
  },
  methods: {
    storeCompletedRequests() {
      this.completed_requests_month_container = this.completed_requests_month;
    },
    storePendingRequests() {
      this.pending_requests_month_container = this.pending_requests_month;
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
