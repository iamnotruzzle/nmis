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
          header="TOTAL ECG DONE"
          sortable
        >
          <template #body="{ data }">
            <div class="flex flex-row align-items-center">
              {{ data.total_ecg_done }}
            </div>
          </template>
        </Column>
        <Column
          header="Internal Medicine"
          sortable
        >
          <template #body="{ data }">
            <div class="flex flex-row align-items-center">
              {{ data.internal_medicine }}
            </div>
          </template>
        </Column>
        <Column
          header="Surgery"
          sortable
        >
          <template #body="{ data }">
            <div class="flex flex-row align-items-center">
              {{ data.surgery }}
            </div>
          </template>
        </Column>
        <Column
          header="Internal Medicine"
          sortable
        >
          <template #body="{ data }">
            <div class="flex flex-row align-items-center">
              {{ data.internal_medicine }}
            </div>
          </template>
        </Column>
        <Column
          header="Family medicine"
          sortable
        >
          <template #body="{ data }">
            <div class="flex flex-row align-items-center">
              {{ data.family_medicine }}
            </div>
          </template>
        </Column>
        <Column
          header="Pediatrics"
          sortable
        >
          <template #body="{ data }">
            <div class="flex flex-row align-items-center">
              {{ data.pediatrics }}
            </div>
          </template>
        </Column>
        <Column
          header="Gynecology"
          sortable
        >
          <template #body="{ data }">
            <div class="flex flex-row align-items-center">
              {{ data.gynecology }}
            </div>
          </template>
        </Column>
        <Column
          header="Total cost"
          sortable
        >
          <template #body="{ data }">
            <div class="flex flex-row align-items-center">
              {{ data.total_cost }}
            </div>
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
    // console.log('mounted', this.census);
    this.storeCensusInContainer();

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
    storeCensusInContainer() {
      //   console.log(this.census);

      // Initialize structure to hold pivoted data
      const result = {
        total_ecg_done: 0,
        internal_medicine: 0,
        surgery: 0,
        family_medicine: 0,
        pediatrics: 0,
        gynecology: 0,
        total_cost: 0,
      };

      // Helper function to convert text to snake_case
      const toSnakeCase = (str) => str.toLowerCase().replace(/\s+/g, '_'); // Lowercase and replace spaces with underscores

      // Loop through the census data and populate the result
      this.census.forEach((e) => {
        const key = toSnakeCase(e.tsdesc); // Convert tsdesc to snake_case
        result.total_ecg_done += parseInt(e.total_quantity, 10); // Add to total ECG done
        result[key] = parseInt(e.total_quantity, 10); // Assign quantity to specific department
        result.total_cost += parseFloat(e.total_cost); // Add to total cost
      });

      // Format total_cost to 2 decimal points
      result.total_cost = result.total_cost.toFixed(2);

      // Assign the final structured data
      //   console.log(result);
      this.censusList = [result]; // Store as an array with a single object
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
          this.storeCensusInContainer();
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
