<template>
  <app-layout>
    <Head title="NMIS - Wards Inventory" />

    <Toast />

    <div style="width: 100%">
      <div
        class="card"
        style="width: 100%"
      >
        <!-- <DataTable
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
          <Column
            field="item_desc"
            header="ITEM"
            sortable
          >
          </Column>
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
        </DataTable> -->
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
import IconField from 'primevue/iconField';
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
    IconField,
  },
  props: {
    csrInventory: Object,
  },
  data() {
    return {
      csrInventoryList: [],
    };
  },
  mounted() {
    this.storeCsrInventoryInContainer();
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
    updateData() {
      this.$inertia.get('package', this.params, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: (visit) => {
          this.csrInventoryList = [];
          //   this.expandedRow = [];
          this.storeCsrInventoryInContainer();
        },
      });
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
