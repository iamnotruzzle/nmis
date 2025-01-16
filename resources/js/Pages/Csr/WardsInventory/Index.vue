<template>
  <app-layout>
    <Head title="NMIS - Wards Inventory" />

    <div
      class="card"
      style="width: 100%"
    >
      <Toast />

      <DataTable
        class="p-datatable-sm"
        v-model:filters="filters"
        :value="wardsInventoryList"
        paginator
        :rows="20"
        :rowsPerPageOptions="[20, 30, 40]"
        dataKey="id"
        filterDisplay="row"
        sortField="expiration_date"
        :sortOrder="1"
        removableSort
        :globalFilterFields="['ward', 'cl2desc']"
        showGridlines
      >
        <template #header>
          <div class="flex flex-wrap align-items-center justify-content-between gap-2">
            <span class="text-xl text-900 font-bold text-primary">WARDS INVENTORY</span>
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
          field="ward"
          header="WARD"
          sortable
          :showFilterMenu="false"
        >
          <template #body="{ data }">
            {{ data.ward }}
          </template>

          <template #filter="{ filterModel, filterCallback }">
            <Dropdown
              v-model="filterModel.value"
              :options="locationFilter"
              @change="filterCallback()"
              :virtualScrollerOptions="{ itemSize: 38 }"
              filter
              optionLabel="name"
              optionValue="name"
              placeholder="NO FILTER"
            />
          </template>
        </Column>
        <Column
          field="cl2desc"
          header="ITEM"
          sortable
        >
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
        <Column
          field="expiration_date"
          header="EXP. DATE"
          :showFilterMenu="false"
          sortable
          style="text-align: center"
          :pt="{ headerContent: 'justify-content-center' }"
        >
          <template #body="{ data }">
            {{ tzone(data.expiration_date) }}
          </template>
        </Column>
      </DataTable>
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
    wardsInventory: Object,
  },
  data() {
    return {
      //   categoryFilter: [
      //     { name: 'Accountable forms', catID: 22 },
      //   ],
      wardsInventoryList: [],
      locationFilter: [],
      filters: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
        ward: { value: null, matchMode: FilterMatchMode.CONTAINS },
        cl2desc: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
    };
  },
  mounted() {
    this.storeWardsInventoryInContainer();
    this.storeLocationsInContainer();
  },
  methods: {
    storeWardsInventoryInContainer() {
      this.wardsInventoryList = []; // reset

      this.wardsInventory.forEach((e) => {
        this.wardsInventoryList.push({
          id: e.id,
          ward: e.ward,
          cl2desc: e.cl2desc,
          quantity: e.quantity,
          expiration_date: e.expiration_date,
        });
      });
    },
    storeLocationsInContainer() {
      this.$page.props.locations.forEach((e) => {
        if (e.wardcode != 'CSR' && e.wardcode != 'ADMIN') {
          this.locationFilter.push({
            code: e.wardcode,
            name: e.wardname,
          });
        }
      });

      //   console.log(this.locationsList);
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
