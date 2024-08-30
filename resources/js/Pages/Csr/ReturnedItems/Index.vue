<template>
  <app-layout>
    <Head title="NMIS - Returned Items" />

    <div
      class="card"
      style="width: 100%"
    >
      <Toast />

      <DataTable
        class="p-datatable-sm"
        v-model:filters="filters"
        :value="returnedItemsList"
        paginator
        :rows="20"
        :rowsPerPageOptions="[20, 30, 40]"
        dataKey="id"
        filterDisplay="row"
        sortField="created_at"
        :sortOrder="1"
        removableSort
        :globalFilterFields="['ward', 'ris_no', 'item']"
        showGridlines
      >
        <template #header>
          <div class="flex flex-wrap align-items-center justify-content-between gap-2">
            <span class="text-xl text-900 font-bold text-primary">RETURNED ITEMS</span>
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
          header="FROM"
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
          field="ris_no"
          header="RIS NO."
        >
        </Column>
        <Column
          field="item"
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
          field="returned_by"
          header="RETURNED BY"
          sortable
          style="text-align: right"
          :pt="{ headerContent: 'justify-content-end' }"
        >
        </Column>
        <Column
          field="remarks"
          header="REMARKS"
          style="width: 10%; text-align: left"
          :pt="{ headerContent: 'justify-content-center' }"
        >
        </Column>
        <Column
          field="created_at"
          header="RETURN DATE"
          :showFilterMenu="false"
          sortable
          style="text-align: center"
          :pt="{ headerContent: 'justify-content-center' }"
        >
          <template #body="{ data }">
            {{ tzone(data.created_at) }}
          </template>
        </Column>
        <!-- <Column
          header="ACTION"
          style="width: 5%"
        >
          <template #body="slotProps">
            <div v-if="slotProps.data.charge_log_id != null">
              <Button
                icon="pi pi-pencil"
                class="mr-1"
                rounded
                text
                severity="warning"
                @click="editItem(slotProps.data)"
              />
            </div>
          </template>
        </Column> -->
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
    returnedItems: Object,
  },
  data() {
    return {
      //   categoryFilter: [
      //     { name: 'Accountable forms', catID: 22 },
      //   ],
      returnedItemsList: [],
      locationFilter: [],
      filters: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
        ward: { value: null, matchMode: FilterMatchMode.CONTAINS },
        ris_no: { value: null, matchMode: FilterMatchMode.CONTAINS },
        item: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
    };
  },
  mounted() {
    this.storeWardsInventoryInContainer();
    this.storeLocationsInContainer();
  },
  methods: {
    storeWardsInventoryInContainer() {
      this.returnedItemsList = []; // reset

      this.returnedItems.forEach((e) => {
        this.returnedItemsList.push({
          id: e.id,
          ris_no: e.ris_no,
          wardcode: e.wardcode,
          ward: e.ward,
          item: e.item,
          quantity: e.quantity,
          returned_by: e.returned_by,
          remarks: e.remarks,
          created_at: e.created_at,
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
