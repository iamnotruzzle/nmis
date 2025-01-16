<template>
  <app-layout>
    <Head title="NMIS - Wards Inventory" />

    <Toast />

    <div
      class="flex flex-row justify-content-around"
      style="width: 100%"
    >
      <div
        class="card"
        style="width: 100%"
      >
        <DataTable
          class="p-datatable-sm w-full"
          v-model:filters="filters"
          :value="csrInventoryList"
          paginator
          :rows="20"
          :rowsPerPageOptions="[20, 30, 40]"
          dataKey="id"
          sortField="item_desc"
          :sortOrder="1"
          removableSort
          :globalFilterFields="['item_desc']"
          showGridlines
        >
          <template #header>
            <div class="flex flex-wrap align-items-center justify-content-between gap-2">
              <span class="text-xl text-900 font-bold text-primary">CSR INVENTORY</span>
              <div class="flex">
                <div class="mr-2">
                  <div class="p-inputgroup">
                    <span class="p-inputgroup-addon">
                      <i class="pi pi-search"></i>
                    </span>
                    <InputText
                      id="searchInput"
                      v-model="filters['global'].value"
                      placeholder="Search"
                    />
                  </div>
                </div>
              </div>
            </div>
          </template>
          <template #empty> No data found. </template>
          <template #loading> Loading data. Please wait. </template>
          <!-- <Column
          field="cl1comb"
          header="ID"
          style="width: 5%"
        >
        </Column> -->
          <Column
            field="item_desc"
            header="ITEM"
            sortable
          >
          </Column>
          <!-- breakpoint -->
          <Column
            field="quantity"
            header="QUANTITY"
            sortable
            style="width: 5%; text-align: right"
            :pt="{ headerContent: 'justify-content-end' }"
          >
            <template #body="{ data }">
              <p class="text-right">
                {{ data.quantity }}
              </p>
            </template>
          </Column>
        </DataTable>
      </div>

      <div class="mx-2"></div>

      <div
        class="card"
        style="width: 100%"
      >
        <DataTable
          class="p-datatable-sm w-full"
          v-model:filters="filtersCurrentStock"
          :value="currentStockList"
          paginator
          :rows="20"
          :rowsPerPageOptions="[20, 30, 40]"
          dataKey="id"
          sortField="item_desc"
          :sortOrder="1"
          removableSort
          :globalFilterFields="['item_desc']"
          showGridlines
        >
          <template #header>
            <div class="flex flex-wrap align-items-center justify-content-between gap-2">
              <span class="text-xl text-900 font-bold text-green-500">CURRENT STOCK</span>
              <div class="flex">
                <div class="mr-2">
                  <div class="p-inputgroup">
                    <span class="p-inputgroup-addon">
                      <i class="pi pi-search"></i>
                    </span>
                    <InputText
                      id="searchInput"
                      v-model="filtersCurrentStock['global'].value"
                      placeholder="Search"
                    />
                  </div>
                </div>
              </div>
            </div>
          </template>
          <template #empty> No data found. </template>
          <template #loading> Loading data. Please wait. </template>
          <!-- <Column
          field="cl1comb"
          header="ID"
          style="width: 5%"
        >
        </Column> -->
          <Column
            field="item_desc"
            header="ITEM"
            sortable
          >
          </Column>
          <!-- breakpoint -->
          <Column
            field="quantity"
            header="QUANTITY"
            sortable
            style="width: 5%; text-align: right"
            :pt="{ headerContent: 'justify-content-end' }"
          >
            <!-- <template #body="{ data }">
              <p class="text-right">
                {{ data.quantity }}
              </p>
            </template> -->
          </Column>
        </DataTable>
      </div>
    </div>
  </app-layout>
</template>

<script>
import { FilterMatchMode } from 'primevue/api';
import { router } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import FileUpload from 'primevue/fileupload';
import Toast from 'primevue/toast';
import Avatar from 'primevue/avatar';
import Calendar from 'primevue/calendar';
import Dropdown from 'primevue/dropdown';
import AutoComplete from 'primevue/autocomplete';
// import IconField from 'primevue/iconField';
import Tag from 'primevue/tag';
import moment from 'moment';
import { Link } from '@inertiajs/vue3';

export default {
  components: {
    AppLayout,
    Head,
    InputText,
    Column,
    Password,
    DataTable,
    Button,
    Dialog,
    FileUpload,
    Toast,
    Avatar,
    Calendar,
    Dropdown,
    AutoComplete,
    Tag,
    Link,
    // IconField,
  },
  props: {
    csrInventory: Object,
    currentStock: Object,
  },
  data() {
    return {
      //   categoryFilter: [
      //     { name: 'Accountable forms', catID: 22 },
      //   ],
      csrInventoryList: [],
      currentStockList: [],
      locationFilter: [],
      filters: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
        item_desc: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
      filtersCurrentStock: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
        item_desc: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
    };
  },
  mounted() {
    // console.log(this.currentStock);
    this.storeCsrInventoryInContainer();
    this.storeCurrentStockInContainer();
  },
  methods: {
    storeCsrInventoryInContainer() {
      this.csrInventoryList = []; // reset

      this.csrInventory.forEach((e) => {
        this.csrInventoryList.push({
          item_desc: e.item_desc,
          quantity: e.quantity,
        });
      });
    },
    storeCurrentStockInContainer() {
      this.currentStockList = []; // reset

      this.currentStock.forEach((e) => {
        this.currentStockList.push({
          item_desc: e.item_desc,
          quantity: e.quantity,
        });
      });

      //   console.log(this.currentStockList);
    },
    tzone(date) {
      return moment.tz(date, 'Asia/Manila').format('L');
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
