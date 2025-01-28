<template>
  <app-layout>
    <Head title="NMIS - ECG report" />

    <div class="w-full flex align-items-center justify-content-center">
      <div class="card w-8 w-full">
        <h1 class="text-center my-4">ECG Consumption Report</h1>
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

        <div class="flex justify-content-end align-content-center">
          <!-- <v-icon
            name="la-receipt-solid"
            class="text-green-500 text-8xl"
            @click="print()"
          ></v-icon> -->

          <Button
            type="button"
            icon="pi pi-print"
            label="Print"
            @click="print()"
          />
        </div>

        <DataTable
          class="p-datatable-sm"
          :value="censusList"
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
          <Column header="TOTAL ECG DONE">
            <template #body="{ data }">
              <div>
                {{ data.total_ecg_done }}
              </div>
            </template>
          </Column>
          <Column header="INTERNAL MEDICINE">
            <template #body="{ data }">
              <div>
                {{ data.internal_medicine }}
              </div>
            </template>
          </Column>
          <Column header="SURGERY">
            <template #body="{ data }">
              <div>
                {{ data.surgery }}
              </div>
            </template>
          </Column>
          <Column header="GYNECOLOGY">
            <template #body="{ data }">
              <div>
                {{ data.gynecology }}
              </div>
            </template>
          </Column>
          <Column header="FAMILY MEDICINE">
            <template #body="{ data }">
              <div>
                {{ data.family_medicine }}
              </div>
            </template>
          </Column>
          <Column header="PEDIATRICS">
            <template #body="{ data }">
              <div>
                {{ data.pediatrics }}
              </div>
            </template>
          </Column>
          <Column header="TOTAL COST">
            <template #body="{ data }">
              <div class="text-right my-2">
                <span class="text-green-500"> ₱ {{ data.total_cost }}</span>
              </div>
            </template>
          </Column>
        </DataTable>
      </div>

      <div
        id="print"
        style="font-family: Arial, sans-serif; background-color: white; color: black; white; display: none;"
      >
        <div
          style="
            font-family: 'Times New Roman', Times, serif;
            text-align: center;
            display: flex;
            flex-direction: column;
          "
        >
          <span>Republic of the Philippines</span>
          <span>Department of Health</span>
          <span style="font-style: italic">Regional Office I</span>
          <span style="font-weight: bold">MARIANO MARCOS MEMORIAL HOSPITAL AND MEDICAL CENTER</span>
          <span>City of Batac, Ilocos Norte</span>
          <span>Trunk line 077-792-3144; Fax line 077-792-3133</span>
          <span>E-mail address: <span style="text-decoration: underline">mmmh.doh@gmail.com</span></span>
          <span style="font-weight: bold"> "PHIC Accredited Health Care Provider" </span>
          <span style="font-weight: bold">"ISO 9001:2015 Certified"</span>
        </div>

        <div
          style="
            font-family: 'Times New Roman', Times, serif;
            font-weight: bold;
            text-align: center;
            display: flex;
            flex-direction: column;
            margin: 15px 0px;
          "
        >
          <h2>ELECTROCARDIOGRAM REPORT</h2>
        </div>

        <table style="width: 100%; border-collapse: collapse; text-align: center; font-family: Arial">
          <thead style="text-transform: uppercase; font-size: 15px">
            <tr>
              <th style="border: 1px solid #ddd; padding: 6px; background-color: #f4f4f4">ECG Done</th>
              <th style="border: 1px solid #ddd; padding: 6px; background-color: #f4f4f4">Internal Medicine</th>
              <th style="border: 1px solid #ddd; padding: 6px; background-color: #f4f4f4">Surgery</th>
              <th style="border: 1px solid #ddd; padding: 6px; background-color: #f4f4f4">Gynecology</th>
              <th style="border: 1px solid #ddd; padding: 6px; background-color: #f4f4f4">Family Medicine</th>
              <th style="border: 1px solid #ddd; padding: 6px; background-color: #f4f4f4">Pediatrics</th>
              <th style="border: 1px solid #ddd; padding: 6px; background-color: #f4f4f4">Total Cost</th>
            </tr>
          </thead>
          <tbody>
            <tr style="background-color: #f9f9f9; white-space: nowrap">
              <td style="border: 1px solid #ddd; padding: 8px">{{ printForm.total_ecg_done }}</td>
              <td style="border: 1px solid #ddd; padding: 8px">{{ printForm.internal_medicine }}</td>
              <td style="border: 1px solid #ddd; padding: 8px">{{ printForm.surgery }}</td>
              <td style="border: 1px solid #ddd; padding: 8px">{{ printForm.gynecology }}</td>
              <td style="border: 1px solid #ddd; padding: 8px">{{ printForm.family_medicine }}</td>
              <td style="border: 1px solid #ddd; padding: 8px">{{ printForm.pediatrics }}</td>
              <td style="border: 1px solid #ddd; padding: 8px">₱ {{ printForm.total_cost }}</td>
            </tr>
          </tbody>
        </table>
      </div>
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
      printForm: this.$inertia.form({
        from: null,
        to: null,
        total_ecg_done: null,
        internal_medicine: null,
        surgery: null,
        gynecology: null,
        family_medicine: null,
        pediatrics: null,
        total_cost: null,
      }),
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
    print() {
      if (this.censusList) {
        // console.log(this.censusList[0]);
        // Set up the print form details
        this.printForm.from = this.from;
        this.printForm.to = this.to;
        this.printForm.total_ecg_done = this.censusList[0].total_ecg_done;
        this.printForm.internal_medicine = this.censusList[0].internal_medicine;
        this.printForm.surgery = this.censusList[0].surgery;
        this.printForm.gynecology = this.censusList[0].gynecology;
        this.printForm.family_medicine = this.censusList[0].family_medicine;
        this.printForm.pediatrics = this.censusList[0].pediatrics;
        this.printForm.total_cost = this.censusList[0].total_cost;

        this.$nextTick(() => {
          // Create a hidden iframe for printing
          const iframe = document.createElement('iframe');
          iframe.style.position = 'absolute';
          iframe.style.top = '-9999px';
          iframe.style.left = '-9999px';
          iframe.style.width = '0';
          iframe.style.height = '0';
          document.body.appendChild(iframe);

          // Write print content into the iframe
          const iframeDoc = iframe.contentWindow.document;
          iframeDoc.open();
          iframeDoc.write(`
                <html>
                <head>
                    <title>Print</title>
                    <style>
                    /* Add your print styles here */
                    </style>
                </head>
                <body>
                    ${document.getElementById('print').innerHTML}
                </body>
                </html>
            `);
          iframeDoc.close();

          // Trigger the print dialog
          iframe.contentWindow.focus();
          iframe.contentWindow.print();

          // Remove the iframe after a delay to ensure proper cleanup
          setTimeout(() => {
            document.body.removeChild(iframe);
          }, 100);
        });
      }
    },
    storeCensusInContainer() {
      // Initialize structure to hold pivoted data
      const result = {
        total_ecg_done: 0,
        internal_medicine: 0,
        surgery: 0,
        gynecology: 0,
        family_medicine: 0,
        pediatrics: 0,
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
