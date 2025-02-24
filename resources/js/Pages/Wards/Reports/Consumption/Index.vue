<template>
  <app-layout>
    <Head title="NMIS - Reports" />

    <div
      class="card"
      style="width: 100%"
    >
      <div class="mb-5 flex justify-content-between">
        <span class="font-bold text-primary text-4xl">REPORTS</span>
        <div class="flex flex-row align-items-center">
          <div class="flex flex-row">
            <Dropdown
              v-model="selectedDate"
              :options="stockBalDatesList"
              optionLabel="name"
              optionValue="code"
              placeholder="Select a date"
              checkmark
              :highlightOnSelect="true"
              class="w-full mr-2"
            />
          </div>

          <Button
            v-if="selectedDate == null || selectedDate == ''"
            label="Download"
            icon="pi pi-download"
            disabled="true"
          />
          <Button
            v-else
            label="Download"
            icon="pi pi-download"
            @click="fnExcelReport"
          />
        </div>
      </div>

      <div
        id="print"
        style="overflow-x: auto"
      >
        <table
          id="theTable"
          style="width: 100%; border-collapse: collapse; text-align: center"
        >
          <tr>
            <td
              rowspan="2"
              style="background-color: white; color: black; padding: 8px; border: 1px solid black"
            >
              <div>
                <p style="margin: 0">ITEM DESCRIPTION</p>
              </div>
            </td>
            <td
              rowspan="2"
              style="background-color: white; color: black; padding: 8px; border: 1px solid black"
            >
              UNIT
            </td>
            <td
              rowspan="1"
              scope="colgroup"
              style="background-color: white; color: black; padding: 8px; border: 1px solid black"
            >
              ESTIMATED BUDGET
            </td>
            <td
              rowspan="2"
              style="background-color: white; color: black; padding: 8px; border: 1px solid black"
            >
              BEGINNING BALANCE
            </td>
            <td
              rowspan="2"
              style="background-color: white; color: black; padding: 8px; border: 1px solid black"
            >
              RECEIVED FROM CSR
            </td>
            <td
              rowspan="2"
              style="background-color: white; color: black; padding: 8px; border: 1px solid black"
            >
              RECEIVED FROM WARD
            </td>
            <td
              rowspan="2"
              style="background-color: white; color: black; padding: 8px; border: 1px solid black"
            >
              TOTAL STOCKS
            </td>
            <td
              colspan="6"
              scope="colgroup"
              style="background-color: white; color: black; text-align: center; padding: 8px; border: 1px solid black"
            >
              CONSUMPTION
            </td>
            <td
              rowspan="2"
              style="background-color: white; color: black; padding: 8px; border: 1px solid black"
            >
              TOTAL CONSUMPTION
            </td>
            <td
              scope="colgroup"
              style="background-color: white; color: black; text-align: center; padding: 8px; border: 1px solid black"
            >
              TOTAL CONSUMPTION
            </td>
            <td
              rowspan="2"
              style="background-color: white; color: black; padding: 8px; border: 1px solid black"
            >
              TOTAL TRANSFERS
            </td>
            <td
              rowspan="2"
              style="background-color: white; color: black; text-align: center; padding: 8px; border: 1px solid black"
            >
              ENDING BALANCE
            </td>
            <td
              rowspan="2"
              style="background-color: white; color: black; text-align: center; padding: 8px; border: 1px solid black"
            >
              PHYSICAL COUNT
            </td>
            <td
              rowspan="2"
              style="background-color: white; color: black; text-align: center; padding: 8px; border: 1px solid black"
            >
              VARIANCE
            </td>
          </tr>

          <tr>
            <td
              scope="col"
              style="background-color: white; color: black; padding: 8px; border: 1px solid black"
            >
              UNIT COST
            </td>
            <td
              scope="col"
              style="background-color: white; color: black; padding: 8px; border: 1px solid black"
            >
              SURGERY
            </td>
            <td
              scope="col"
              style="background-color: white; color: black; padding: 8px; border: 1px solid black"
            >
              OB-GYNE
            </td>
            <td
              scope="col"
              style="background-color: white; color: black; padding: 8px; border: 1px solid black"
            >
              ORTHO
            </td>
            <td
              scope="col"
              style="background-color: white; color: black; padding: 8px; border: 1px solid black"
            >
              PEDIA
            </td>
            <td
              scope="col"
              style="background-color: white; color: black; padding: 8px; border: 1px solid black"
            >
              OPTHA
            </td>
            <td
              scope="col"
              style="background-color: white; color: black; padding: 8px; border: 1px solid black"
            >
              ENT
            </td>
            <td
              scope="col"
              style="background-color: white; color: black; padding: 8px; border: 1px solid black"
            >
              (ESTIMATED COST)
            </td>
          </tr>

          <tr
            v-for="rc in reportsContainer"
            :key="rc.cl2comb"
          >
            <td
              scope="row"
              style="padding: 8px; border: 1px solid black; text-align: left !important"
            >
              {{ rc.item_description }}
            </td>
            <td style="padding: 8px; border: 1px solid black">
              {{ rc.unit }}
            </td>
            <td style="padding: 8px; border: 1px solid black">
              {{ rc.unit_cost }}
            </td>
            <td style="padding: 8px; border: 1px solid black">
              {{ rc.beginning_balance }}
            </td>
            <td style="padding: 8px; border: 1px solid black">
              {{ rc.from_csr }}
            </td>
            <td style="padding: 8px; border: 1px solid black">
              {{ rc.from_ward }}
            </td>
            <td style="padding: 8px; border: 1px solid black">
              {{ rc.total_beg_bal }}
            </td>
            <td style="padding: 8px; border: 1px solid black">
              {{ rc.surgery }}
            </td>
            <td style="padding: 8px; border: 1px solid black">
              {{ rc.obgyne }}
            </td>
            <td style="padding: 8px; border: 1px solid black">
              {{ rc.ortho }}
            </td>
            <td style="padding: 8px; border: 1px solid black">
              {{ rc.pedia }}
            </td>
            <td style="padding: 8px; border: 1px solid black">
              {{ rc.optha }}
            </td>
            <td style="padding: 8px; border: 1px solid black">
              {{ rc.ent }}
            </td>
            <td style="padding: 8px; border: 1px solid black">
              {{ rc.total_consumption }}
            </td>
            <td style="padding: 8px; border: 1px solid black">
              {{ rc.total_cons_estimated_cost }}
            </td>
            <td style="padding: 8px; border: 1px solid black">
              {{ rc.transferred_qty }}
            </td>
            <td style="padding: 8px; border: 1px solid black">
              {{ rc.ending_balance }}
            </td>
            <td style="padding: 8px; border: 1px solid black">
              <!-- <span>manual</span> -->
            </td>
            <td style="padding: 8px; border: 1px solid black">
              <!-- <span>variance</span> -->
            </td>
          </tr>
        </table>
      </div>
    </div>
  </app-layout>
</template>

<script>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Calendar from 'primevue/calendar';
import Dropdown from 'primevue/dropdown';
import Button from 'primevue/button';
import moment from 'moment';
import { Link } from '@inertiajs/vue3';

export default {
  components: {
    AppLayout,
    Head,
    Calendar,
    Button,
    Link,
    Dropdown,
  },
  props: {
    reports: Object,
    locationStockBalance: Object,
    stockBalDates: Array,
  },
  data() {
    return {
      options: {},
      params: {},
      selectedDate: '',
      stockBalDatesList: [],
      reportsContainer: [],
    };
  },
  mounted() {
    // console.log('reports', this.reports);

    this.storeReportsInContainer();
    this.storeStockBalDatesInContainer();
  },
  methods: {
    storeStockBalDatesInContainer() {
      //   this.stockBalDatesList = []; // Clear the list to avoid duplicates

      this.stockBalDates.forEach((e) => {
        this.stockBalDatesList.push({
          name: `[ ${e.beg_bal_date} ] - [ ${e.end_bal_date === null ? 'ONGOING' : e.end_bal_date} ]`,
          code: `[ ${e.beg_bal_date} ] - [ ${e.end_bal_date || 'ONGOING'} ]`, // Use 'ONGOING' for null end dates
        });
      });
    },
    storeReportsInContainer() {
      this.reports.forEach((e) => {
        this.reportsContainer.push({
          cl2comb: e.cl2comb,
          item_description: e.item_description,
          unit: e.unit,
          unit_cost: e.unit_cost == null ? 0 : e.unit_cost,
          beginning_balance: e.beginning_balance == null ? 0 : e.beginning_balance,
          from_csr: e.from_csr == null ? 0 : e.from_csr,
          from_ward: e.from_ward == null ? 0 : e.from_ward,
          total_beg_bal: e.total_beg_bal == null ? 0 : e.total_beg_bal,
          surgery: e.surgery == null ? 0 : e.surgery,
          obgyne: e.obgyne == null ? 0 : e.obgyne,
          //   urology: e.urology,
          ortho: e.ortho == null ? 0 : e.ortho,
          pedia: e.pedia == null ? 0 : e.pedia,
          //   med: e.med,
          optha: e.optha == null ? 0 : e.optha,
          ent: e.ent == null ? 0 : e.ent,
          //   neuro: e.neuro,
          total_consumption: e.total_consumption == null ? 0 : e.total_consumption,
          total_cons_estimated_cost:
            e.total_cons_estimated_cost == null ? 0 : Number(e.total_cons_estimated_cost).toFixed(2),
          transferred_qty: e.transferred_qty == null ? 0 : e.transferred_qty,
          ending_balance: e.ending_balance == null ? 0 : e.ending_balance,
          actual_inventory: e.actual_inventory == null ? 0 : e.actual_inventory,
        });
      });

      //   console.log('container', this.reportsContainer);
    },

    fnExcelReport() {
      const table = document.getElementById('theTable');
      let tableHTML = table.outerHTML;
      const fileName = `${this.selectedDate}.xls`;

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

    updateData() {
      this.reportsContainer = [];

      this.$inertia.get('wardreports', this.params, {
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
  },
  watch: {
    selectedDate: function (val, oldVal) {
      this.params.date = val;
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
table,
th,
td {
  border: 1px solid;
  padding: 10px;
}
.colored-header {
  color: black;
}
.group-header {
  text-align: center;
  font-size: 120%;
  font-weight: 700;
}
.header {
  font-size: 100%;
  font-weight: 600;
}

@media print {
  @page {
    margin: 0;
    /* font-size: 50px; */
    /* font-weight: bold; */
  }
  body {
    font-family: Calibri, sans-serif;
  }
  #print {
    font-family: Calibri, sans-serif;
  }
}
</style>
