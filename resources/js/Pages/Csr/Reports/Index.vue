<template>
  <app-layout>
    <Head title="NMIS - Reports" />

    <div
      class="card"
      style="width: 100%"
    >
      <div class="mb-5 flex justify-content-between">
        <span class="font-bold text-4xl text-primary">Reports</span>
        <div class="flex flex-row align-items-center">
          <div class="flex flex-row">
            <!-- <Calendar
              v-model="from"
              dateFormat="mm-dd-yy"
              placeholder="FROM"
              showIcon
              showButtonBar
              :manualInput="false"
              :hideOnDateTimeSelect="true"
              class="mr-2"
            /> -->
            <div>
              <label class="font-bold text-lg mr-2">FROM:</label>
              <Dropdown
                v-model="from"
                :options="beg_bal_dates_list"
                optionLabel="name"
                optionValue="code"
                placeholder="Select beg. bal date"
                checkmark
                :highlightOnSelect="true"
                class="mr-2"
              />
            </div>
            <div>
              <label class="font-bold text-lg mr-2">TO:</label>
              <Dropdown
                v-model="to"
                :options="end_bal_dates_list"
                optionLabel="name"
                optionValue="code"
                placeholder="Select end bal date"
                checkmark
                :highlightOnSelect="true"
                class="mr-2"
              />
            </div>
          </div>

          <i
            v-if="from == null || to == null"
            class="pi pi-file-excel"
            :style="{ color: 'gray', 'font-size': '2rem' }"
          ></i>
          <i
            v-else
            class="pi pi-file-excel"
            :style="{ color: 'green', 'font-size': '2rem' }"
            @click="fnExcelReport"
          ></i>
        </div>
      </div>

      <div style="overflow-x: auto">
        <table
          id="theTable"
          style="width: 100%; border-collapse: collapse; border: 1px solid black"
        >
          <!-- Table Header -->
          <thead>
            <tr>
              <th style="background: white; border: 1px solid black; padding: 8px; text-align: center">
                Item Description
              </th>
              <th style="background: white; border: 1px solid black; padding: 8px; text-align: center">Unit</th>
              <th style="background: white; border: 1px solid black; padding: 8px; text-align: center">Unit Cost</th>

              <!-- CSR Column -->
              <th
                colspan="1"
                style="background: #facc15; border: 1px solid black; padding: 8px; text-align: center"
              >
                CSR
              </th>

              <!-- Wards Column -->
              <th
                colspan="1"
                style="background: #facc15; border: 1px solid black; padding: 8px; text-align: center"
              >
                Wards
              </th>

              <!-- Total Beginning Balance Column -->
              <th
                colspan="2"
                style="background: #facc15; border: 1px solid black; padding: 8px; text-align: center"
              >
                Total Beginning Balance
              </th>

              <!-- Received from MMS Column -->
              <th
                colspan="2"
                style="background: #a78bfa; border: 1px solid black; padding: 8px; text-align: center"
              >
                Received from MMS
              </th>

              <!-- Supplies Issued to Wards Column -->
              <th
                colspan="2"
                style="background: #22c55e; border: 1px solid black; padding: 8px; text-align: center"
              >
                Supplies Issued to Wards
              </th>

              <!-- Consumption Column -->
              <th
                colspan="2"
                style="background: #fb923c; border: 1px solid black; padding: 8px; text-align: center"
              >
                Consumption
              </th>

              <!-- End Bal CSR and Ward -->
              <th
                colspan="1"
                style="background: #3b82f6; border: 1px solid black; padding: 8px; text-align: center"
              >
                CSR
              </th>
              <th
                colspan="1"
                style="background: #3b82f6; border: 1px solid black; padding: 8px; text-align: center"
              >
                WARD
              </th>

              <!-- Total Ending Balance Column -->
              <th
                colspan="2"
                style="background: #3b82f6; border: 1px solid black; padding: 8px; text-align: center"
              >
                Total Ending Balance
              </th>
            </tr>
            <tr>
              <!-- Sub-headers -->
              <th
                colspan="3"
                style="background: white"
              ></th>
              <th style="background: #facc15; border: 1px solid black; padding: 8px; text-align: center">Quantity</th>
              <th style="background: #facc15; border: 1px solid black; padding: 8px; text-align: center">Quantity</th>
              <th style="background: #facc15; border: 1px solid black; padding: 8px; text-align: center">
                Total Quantity
              </th>
              <th style="background: #facc15; border: 1px solid black; padding: 8px; text-align: center">Total Cost</th>
              <th style="background: #a78bfa; border: 1px solid black; padding: 8px; text-align: center">Quantity</th>
              <th style="background: #a78bfa; border: 1px solid black; padding: 8px; text-align: center">Cost</th>
              <th style="background: #22c55e; border: 1px solid black; padding: 8px; text-align: center">Quantity</th>
              <th style="background: #22c55e; border: 1px solid black; padding: 8px; text-align: center">Cost</th>
              <th style="background: #fb923c; border: 1px solid black; padding: 8px; text-align: center">Quantity</th>
              <th style="background: #fb923c; border: 1px solid black; padding: 8px; text-align: center">Cost</th>
              <th style="background: #3b82f6; border: 1px solid black; padding: 8px; text-align: center">QUANTITY</th>
              <th style="background: #3b82f6; border: 1px solid black; padding: 8px; text-align: center">QUANTITY</th>
              <th style="background: #3b82f6; border: 1px solid black; padding: 8px; text-align: center">
                Total Quantity
              </th>
              <th style="background: #3b82f6; border: 1px solid black; padding: 8px; text-align: center">Total Cost</th>
            </tr>
          </thead>

          <!-- Table Body -->
          <tbody>
            <tr
              v-for="(report, index) in reportsContainer"
              :key="index"
              style="text-align: center"
            >
              <td style="border: 1px solid black; padding: 8px">{{ report.item_description }}</td>
              <td style="border: 1px solid black; padding: 8px">{{ report.unit }}</td>
              <td style="border: 1px solid black; padding: 8px">{{ report.unit_cost }}</td>
              <td style="border: 1px solid black; padding: 8px">{{ report.beg_bal_csr_quantity }}</td>
              <td style="border: 1px solid black; padding: 8px">{{ report.beg_bal_ward_quantity }}</td>
              <td style="border: 1px solid black; padding: 8px">{{ report.beg_bal_total_quantity }}</td>
              <td style="border: 1px solid black; padding: 8px">{{ report.beg_bal_total_cost }}</td>
              <td style="border: 1px solid black; padding: 8px">{{ report.received_mms_qty }}</td>
              <td style="border: 1px solid black; padding: 8px">{{ report.received_mms_total_cost }}</td>
              <td style="border: 1px solid black; padding: 8px">{{ report.issued_qty }}</td>
              <td style="border: 1px solid black; padding: 8px">{{ report.issued_total_cost }}</td>
              <td style="border: 1px solid black; padding: 8px">{{ report.consump_quantity }}</td>
              <td style="border: 1px solid black; padding: 8px">{{ report.consump_total_cost }}</td>
              <td style="border: 1px solid black; padding: 8px">{{ report.end_bal_csr_quantity }}</td>
              <td style="border: 1px solid black; padding: 8px">{{ report.end_bal_ward_quantity }}</td>
              <td style="border: 1px solid black; padding: 8px">{{ report.end_bal_total_quantity }}</td>
              <td style="border: 1px solid black; padding: 8px">{{ report.end_bal_total_cost }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </app-layout>
</template>

<script>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Calendar from 'primevue/calendar';
import Button from 'primevue/button';
import Dropdown from 'primevue/dropdown';
import moment from 'moment';
import { Link } from '@inertiajs/vue3';

export default {
  components: {
    AppLayout,
    Head,
    Calendar,
    Button,
    Dropdown,
    Link,
  },
  props: {
    reports: Array,
    beg_bal_dates: Array,
    end_bal_dates: Array,
  },
  data() {
    return {
      options: {},
      params: {},
      from: null,
      to: null,
      reportsContainer: [],
      beg_bal_dates_list: [],
      end_bal_dates_list: [],
    };
  },
  mounted() {
    // console.log(this.beg_bal_dates);

    this.storeReportsInContainer();
    this.storeDatesInContainer();
  },
  methods: {
    storeReportsInContainer() {
      this.reports.forEach((e) => {
        this.reportsContainer.push({
          cl2comb: e.cl2comb, // *
          item_description: e.item_description, // *
          unit: e.unit, // *
          unit_cost: e.unit_cost, // *

          beg_bal_csr_quantity: e.beg_bal_csr_quantity, // *
          beg_bal_ward_quantity: e.beg_bal_ward_quantity, // *
          beg_bal_total_quantity: e.beg_bal_total_quantity, // *
          beg_bal_total_cost: e.beg_bal_total_cost,

          received_mms_qty: e.received_mms_qty, // *
          received_mms_total_cost: e.received_mms_total_cost, // *

          issued_qty: e.issued_qty, // *
          issued_total_cost: e.issued_total_cost, // *

          consump_quantity: e.consump_quantity, // *
          consump_total_cost: e.consump_total_cost, // *

          end_bal_csr_quantity: e.end_bal_csr_quantity, // *
          end_bal_ward_quantity: e.end_bal_ward_quantity, // *

          end_bal_total_quantity: e.end_bal_total_quantity, // *
          end_bal_total_cost: e.end_bal_total_cost,
        });
      });

      //   console.log('container', this.reportsContainer);
    },
    storeDatesInContainer() {
      // Helper to remove duplicates manually
      const uniqueBegBalDates = this.beg_bal_dates.filter(
        (e, index, self) => index === self.findIndex((obj) => obj.date === e.date)
      );
      const uniqueEndBalDates = this.end_bal_dates.filter(
        (e, index, self) => index === self.findIndex((obj) => obj.date === e.date)
      );
      // Populate date list
      this.beg_bal_dates_list = uniqueBegBalDates.map((e) => ({
        name: e.date, // *
        code: e.date, // *
      }));
      this.end_bal_dates_list = uniqueEndBalDates.map((e) => ({
        name: e.date, // *
        code: e.date, // *
      }));
    },
    updateData() {
      this.reportsContainer = [];

      this.$inertia.get('csrreports', this.params, {
        preserveState: true,
        preserveScroll: true,
        onFinish: (visit) => {
          this.reportsContainer = [];
          this.storeReportsInContainer();
        },
      });
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
    fnExcelReport() {
      const table = document.getElementById('theTable');
      let tableHTML = table.outerHTML;
      const fileName = 'download.xls';

      const msie = window.navigator.userAgent.indexOf('MSIE ');

      // If Internet Explorer
      if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)) {
        const dummyFrame = document.getElementById('dummyFrame').contentWindow;
        dummyFrame.document.open('txt/html', 'replace');
        dummyFrame.document.write(tableHTML);
        dummyFrame.document.close();
        dummyFrame.focus();
        dummyFrame.document.execCommand('SaveAs', true, fileName);
      } else {
        // Other browsers
        const a = document.createElement('a');
        tableHTML = tableHTML.replace(/  /g, '').replace(/ /g, '%20'); // Replaces spaces
        a.href = 'data:application/vnd.ms-excel,' + tableHTML;
        a.setAttribute('download', fileName);
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
      }
    },
  },
  watch: {
    from: function (val) {
      this.params.from = val;
      this.updateData();
    },
    to: function (val) {
      this.params.to = val;
      this.updateData();
    },
  },
};
</script>
<style scoped>
table {
  width: 100%;
  border-collapse: collapse;
}

th,
td {
  border: 1px solid black;
  padding: 8px;
}

th {
  color: black;
}
</style>
