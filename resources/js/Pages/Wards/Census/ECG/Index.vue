<template>
  <app-layout>
    <Head title="NMIS - ECG report" />

    <div class="card">
      <h2 class="text-center my-4">ECG Consumption Report</h2>
      <div class="flex flex-row justify-content-center">
        <div class="flex flex-column w-2">
          <label
            for="from"
            class="mb-2"
            >FROM</label
          >
          <Calendar
            id="from"
            v-model="from"
            dateFormat="mm-dd-yy"
            placeholder="mm-dd-yyyy"
            showIcon
            showButtonBar
            :manualInput="true"
            :hideOnDateTimeSelect="true"
          />
        </div>
        <div class="mx-2"></div>
        <div class="flex flex-column w-2">
          <label
            for="to"
            class="mb-2"
            >TO</label
          >
          <Calendar
            id="to"
            v-model="to"
            dateFormat="mm-dd-yy"
            placeholder="mm-dd-yyyy"
            showIcon
            showButtonBar
            :manualInput="true"
            :hideOnDateTimeSelect="true"
          />
        </div>
      </div>
      <DataTable
        class="p-datatable-sm"
        :value="censusList"
        paginator
        :rows="20"
        :rowsPerPageOptions="[20, 30, 40]"
        dataKey="tsdesc"
        sortField="tsdesc"
        :sortOrder="1"
        removableSort
        showGridlines
      >
        <template #header>
          <div class="flex flex-wrap align-items-center justify-content-end"></div>
        </template>
        <template #empty> No data found. </template>
        <template #loading> Loading data. Please wait. </template>
        <Column
          field="tscode"
          header="TSCODE"
          style="width: 5%"
          sortable
        >
          <template #body="{ data }">
            <div class="flex flex-row align-items-center">
              {{ data.tscode }}
            </div>
          </template>
        </Column>
        <Column
          field="tsdesc"
          header="Type of service"
          style="width: 5%"
        >
          <template #body="{ data }">
            <div class="flex flex-row align-items-center">
              <div class="">
                <span class="text-green-500">{{ data.tscode }}</span>
              </div>
            </div>
          </template>
        </Column>
        <Column
          field="total_quantity"
          header="Quantity"
          sortable
          style="width: 10%"
        >
          <template #body="{ data }">
            <span>{{ data.total_quantity }}</span>
          </template>
        </Column>
        <Column
          field="total_cost"
          header="Total cost"
          sortable
          style="width: 10%"
        >
          <template #body="{ data }">
            <span>{{ data.total_cost }}</span>
          </template>
        </Column>
      </DataTable>
    </div>
  </app-layout>
</template>

<script>
import { FilterMatchMode } from 'primevue/api';
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
import RadioButton from 'primevue/radiobutton';
import Tag from 'primevue/tag';
import moment from 'moment';

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
    RadioButton,
  },
  props: {
    census: Object,
  },
  data() {
    return {
      // paginator
      loading: false,
      totalRecords: null,
      rows: null,
      options: {},
      params: {},
      censusList: [],
      from: null,
      to: null,
    };
  },
  // created will be initialize before mounted
  created() {
    this.totalRecords = this.census.total;
    this.params.page = this.census.current_page;
    this.rows = this.census.per_page;
  },
  mounted() {
    console.log('mounted', this.census);
    this.storeCensusInController();

    this.loading = false;
  },
  methods: {
    tzone(date) {
      if (date == null) {
        return '';
      } else {
        return moment.tz(date, 'Asia/Manila').format('LLL');
      }
    },
    getLocalDateString(utcStr) {
      const date = new Date(utcStr);
      return (
        date.getFullYear() +
        '-' +
        String(date.getMonth() + 1).padStart(2, '0') +
        '-' +
        String(date.getDate()).padStart(2, '0') +
        ' ' +
        String(date.getHours()).padStart(2, '0') +
        ':' +
        String(date.getMinutes()).padStart(2, '0')
      );
    },
    storeCensusInController() {
      console.log(this.census);
      this.census.forEach((e) => {
        this.censusList.push({
          tscode: e.tscode,
          tsdesc: e.tsdesc,
          total_quantity: e.total_quantity,
          total_cost: e.total_cost,
        });
      });
    },
    updateData() {
      this.censusList = [];
      this.loading = true;

      this.$inertia.get('ecgreports', this.params, {
        preserveState: true,
        preserveScroll: true,
        onFinish: (visit) => {
          this.totalRecords = this.census.total;
          this.censusList = [];
          this.storeCensusInController();
          this.loading = false;
        },
      });
    },
  },
  watch: {
    from: function (val) {
      if (val != null) {
        let from = this.getLocalDateString(val);
        this.params.from = from;
      } else {
        this.params.from = null;
        this.from = null;
      }
      this.updateData();
    },
    to: function (val) {
      if (val != null) {
        let to = this.getLocalDateString(val);
        this.params.to = to;
      } else {
        this.params.to = null;
        this.to = null;
      }
      this.updateData();
    },
  },
};
</script>

<style scoped></style>
