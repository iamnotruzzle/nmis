<template>
  <Head title="NMIS - Wards Inventory" />

  <Toast />

  <div
    class="bg-gray-500 flex align-items-center justify-content-between px-3"
    style="width: 100%; height: 100vh"
  >
    <!-- csr -->
    <div
      class="flex-1"
      style="height: 100%"
    >
      <DataTable
        class="p-datatable-sm h-full"
        v-model:filters="filters"
        :value="csrInventoryList"
        paginator
        :rows="30"
        :rowsPerPageOptions="[20, 30, 40]"
        dataKey="id"
        sortField="item"
        :sortOrder="1"
        removableSort
        :globalFilterFields="['item']"
        showGridlines
        scrollable
        scrollHeight="flex"
      >
        <template #header>
          <div class="flex flex-wrap align-items-center justify-content-between">
            <span class="text-xl font-bold text-primary">CSR INVENTORY</span>
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
                    size="large"
                  />
                </div>
              </div>
            </div>
          </div>
        </template>
        <template #empty> No data found. </template>
        <template #loading> Loading data. Please wait. </template>
        <Column
          field="id"
          header="ID"
          style="width: 20%"
        >
        </Column>
        <Column
          field="item"
          header="ITEM"
          sortable
        >
          <template #body="{ data }">
            <p class="py-1">
              {{ data.item }}
            </p>
          </template>
        </Column>
        <Column
          field="total_quantity"
          header="QUANTITY"
          sortable
          style="width: 5%; text-align: right"
          :pt="{ headerContent: 'justify-content-end' }"
        >
          <template #body="{ data }">
            <p class="text-right">
              {{ data.total_quantity }}
            </p>
          </template>
        </Column>
      </DataTable>
    </div>

    <div class="mx-5"></div>

    <!-- ward -->
    <div
      class="flex-1"
      style="height: 100%"
    >
      <DataTable
        class="p-datatable-sm h-full"
        v-model:filters="wardFilter"
        :value="wardsInventoryList"
        paginator
        :rows="30"
        :rowsPerPageOptions="[20, 30, 40]"
        dataKey="id"
        filterDisplay="row"
        sortField="item"
        :sortOrder="1"
        removableSort
        :globalFilterFields="['ward', 'item']"
        showGridlines
        scrollable
        scrollHeight="flex"
      >
        <template #header>
          <div class="flex flex-wrap align-items-center justify-content-between">
            <span class="text-xl text-900 font-bold text-primary">WARDS INVENTORY</span>
            <div class="flex">
              <div class="mr-2">
                <div class="p-inputgroup">
                  <span class="p-inputgroup-addon">
                    <i class="pi pi-search"></i>
                  </span>
                  <InputText
                    id="searchInput"
                    v-model="wardFilter['global'].value"
                    placeholder="Search"
                    size="large"
                  />
                </div>
              </div>
            </div>
          </div>
        </template>
        <template #empty> No data found. </template>
        <template #loading> Loading data. Please wait. </template>
        <Column
          field="ward"
          header="WARD"
          sortable
          :showFilterMenu="false"
          style="width: 20%"
        >
          <template #body="{ data }">
            {{ data.ward }}
          </template>

          <template #filter="{ filterModel, filterCallback }">
            <Dropdown
              v-model="filterModel.value"
              :options="locationFilter"
              @change="filterCallback()"
              size="large"
              filter
              optionLabel="name"
              optionValue="name"
              placeholder="NO FILTER"
            />
          </template>
        </Column>
        <Column
          field="item"
          header="ITEM"
          sortable
        >
          <template #body="{ data }">
            <p class="py-1">
              {{ data.item }}
            </p>
          </template>
        </Column>
        <!-- breakpoint -->
        <Column
          field="quantity"
          header="QTY"
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
  </div>
</template>

<script>
import { FilterMatchMode } from 'primevue/api';
import { router } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import InputText from 'primevue/inputtext';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Button from 'primevue/button';
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
import axios from 'axios';

export default {
  components: {
    Head,
    InputText,
    Column,
    DataTable,
    Button,
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
    csrInventory: Array,
    wardsInventory: Array,
    locations: Object,
  },
  data() {
    return {
      //   categoryFilter: [
      //     { name: 'Accountable forms', catID: 22 },
      //   ],
      csrInventoryList: [],
      wardsInventoryList: [],
      locationFilter: [],
      filters: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
        item: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
      wardFilter: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
        ward: { value: null, matchMode: FilterMatchMode.CONTAINS },
        item: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
    };
  },
  mounted() {
    this.fetchInventoryData(); // Initial load
    setInterval(this.fetchInventoryData, 40000); // Refresh every 10 seconds

    // console.log(this.currentStock);
    this.storeCsrInventoryInContainer();
    this.storeWardsInventoryInContainer();
    this.storeLocationsInContainer();
  },
  methods: {
    fetchInventoryData() {
      fetch('/api/inventory')
        .then((response) => response.json())
        .then((data) => {
          this.csrInventoryList = [];
          this.wardInventoryList = [];

          // Replace arrays instead of appending
          this.csrInventoryList = data.csrInventory.map((e) => ({
            id: e.ID,
            item: e.ITEM,
            total_quantity: e.TOTAL_QUANTITY,
          }));

          this.wardsInventoryList = data.wardsInventory.map((e) => ({
            ward: e.ward,
            item: e.item,
            quantity: e.quantity,
          }));

          console.log('Data updated.');
          //   console.log('Updated inventory:', this.csrInventoryList, this.wardsInventoryList);
        })
        .catch((error) => console.error('Error fetching inventory:', error));
    },
    storeCsrInventoryInContainer() {
      this.csrInventoryList = []; // reset

      this.csrInventory.forEach((e) => {
        this.csrInventoryList.push({
          id: e.ID,
          item: e.ITEM,
          total_quantity: e.TOTAL_QUANTITY,
        });
      });
    },
    storeWardsInventoryInContainer() {
      this.wardInventoryList = []; // reset

      this.wardsInventory.forEach((e) => {
        this.wardsInventoryList.push({
          ward: e.ward,
          item: e.item,
          quantity: e.quantity,
        });
      });

      //   console.log(this.wardsInventoryList);
    },
    storeLocationsInContainer() {
      this.locations.forEach((e) => {
        if (e.wardcode != 'CSR' && e.wardcode != 'ADMIN') {
          this.locationFilter.push({
            code: e.wardcode,
            name: e.wardname,
          });
        }
      });

      //   console.log(this.locationsList);
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
