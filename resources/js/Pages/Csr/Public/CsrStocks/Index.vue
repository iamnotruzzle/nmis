<template>
  <Head title="NMIS - Wards Inventory" />

  <Toast />

  <div
    class="bg-bluegray-500 flex align-items-center justify-content-center"
    style="width: 100%"
  >
    <div class="flex justify-content-center h-screen w-7">
      <DataTable
        class="p-datatable-sm w-9 h-full"
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
  </div>
</template>

<script>
import { FilterMatchMode } from 'primevue/api';
import { router } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
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
      filters: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
        item: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
    };
  },
  mounted() {
    // console.log(this.currentStock);
    this.storeCsrInventoryInContainer();
  },
  methods: {
    storeCsrInventoryInContainer() {
      this.csrInventoryList = []; // reset

      this.csrInventory.forEach((e) => {
        this.csrInventoryList.push({
          id: e.ID,
          item: e.ITEM,
          total_quantity: e.TOTAL_QUANTITY,
        });
      });

      console.log(this.csrInventory);
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
