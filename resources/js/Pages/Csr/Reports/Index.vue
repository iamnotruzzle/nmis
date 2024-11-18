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
            <Calendar
              v-model="from"
              dateFormat="mm-dd-yy"
              placeholder="FROM"
              showIcon
              showButtonBar
              :manualInput="false"
              :hideOnDateTimeSelect="true"
              class="mr-2"
            />
            <Calendar
              v-model="to"
              dateFormat="mm-dd-yy"
              placeholder="TO"
              showIcon
              showButtonBar
              :manualInput="false"
              :hideOnDateTimeSelect="true"
              class="mr-2"
            />
          </div>

          <i
            v-if="from == null || to == null"
            class="pi pi-file-excel"
            :style="{ color: 'gray', 'font-size': '2rem' }"
          ></i>
          <a
            v-else
            :href="`csrstocks/export?from=${params.from}&to=${params.to}`"
            target="_blank"
          >
            <i
              class="pi pi-file-excel"
              :style="{ color: 'green', 'font-size': '2rem' }"
            ></i>
          </a>
        </div>
      </div>

      <div style="overflow-x: auto">
        <table class="min-w-full border">
          <!-- Table Header -->
          <thead>
            <tr>
              <th class="bg-white border px-4 py-2 text-center">Item Description</th>
              <th class="bg-white border px-4 py-2 text-center">Unit</th>
              <th class="bg-white border px-4 py-2 text-center">Unit Cost</th>

              <!-- CSR Column -->
              <th
                colspan="1"
                class="bg-yellow-500 border px-4 py-2 text-center"
              >
                CSR
              </th>

              <!-- Wards Column -->
              <th
                colspan="1"
                class="bg-yellow-500 border px-4 py-2 text-center"
              >
                Wards
              </th>

              <!-- Total Beginning Balance Column -->
              <th
                colspan="2"
                class="bg-yellow-500 border px-4 py-2 text-center"
              >
                Total Beginning Balance
              </th>

              <!-- Received from MMS Column -->
              <th
                colspan="2"
                class="bg-purple-500 border px-4 py-2 text-center"
              >
                Received from MMS
              </th>

              <!-- Supplies Issued to Wards Column -->
              <th
                colspan="2"
                class="bg-green-500 border px-4 py-2 text-center"
              >
                Supplies Issued to Wards
              </th>

              <!-- Consumption Column -->
              <th
                colspan="2"
                class="bg-orange-500 border px-4 py-2 text-center"
              >
                Consumption
              </th>

              <!-- end bal csr and ward -->
              <th
                colspan="1"
                class="bg-blue-500 border px-4 py-2 text-center"
              >
                CSR
              </th>
              <th
                colspan="1"
                class="bg-blue-500 border px-4 py-2 text-center"
              >
                WARD
              </th>

              <!-- Total Ending Balance Column -->
              <th
                colspan="2"
                class="bg-blue-500 border px-4 py-2 text-center"
              >
                Total Ending Balance
              </th>
            </tr>
            <tr>
              <!-- Sub-headers -->
              <th
                colspan="3"
                class="bg-white"
              ></th>
              <!-- csr -->
              <th class="bg-yellow-500 border px-4 py-2 text-center">Quantity</th>
              <!-- <th class="bg-yellow-500 border  px-4 py-2 text-center">Cost</th> -->

              <!-- wards -->
              <th class="bg-yellow-500 border px-4 py-2 text-center">Quantity</th>
              <!-- <th class="bg-yellow-500 border  px-4 py-2 text-center">Cost</th> -->

              <!-- total beginning balance -->
              <th class="bg-yellow-500 border px-4 py-2 text-center">Total Quantity</th>
              <th class="bg-yellow-500 border px-4 py-2 text-center">Total Cost</th>

              <!-- received from MMS -->
              <th class="bg-purple-500 border px-4 py-2 text-center">Quantity</th>
              <th class="bg-purple-500 border px-4 py-2 text-center">Cost</th>

              <!-- Supplies issued to wards -->
              <th class="bg-green-500 border px-4 py-2 text-center">Quantity</th>
              <th class="bg-green-500 border px-4 py-2 text-center">Cost</th>

              <!-- Consumption -->
              <th class="bg-orange-500 border px-4 py-2 text-center">Quantity</th>
              <th class="bg-orange-500 border px-4 py-2 text-center">Cost</th>

              <!-- end bal csr & ward -->
              <th class="bg-blue-500 border px-4 py-2 text-center">QUANTITY</th>
              <th class="bg-blue-500 border px-4 py-2 text-center">QUANTITY</th>

              <!-- Total ending balance -->
              <th class="bg-blue-500 border px-4 py-2 text-center">Total Quantity</th>
              <th class="bg-blue-500 border px-4 py-2 text-center">Total Cost</th>
            </tr>
          </thead>

          <!-- Table Body -->
          <tbody>
            <!-- Sample Data Row -->
            <tr
              v-for="(report, index) in reportsContainer"
              :key="index"
              class="text-center"
            >
              <td class="border px-4 py-2">{{ report.item_description }}</td>
              <td class="border px-4 py-2">{{ report.unit }}</td>
              <td class="border px-4 py-2">{{ report.unit_cost }}</td>

              <td class="border px-4 py-2">{{ report.beg_bal_csr_quantity }}</td>
              <!-- <td class="border  px-4 py-2 bg-yellow-100">-</td> -->

              <td class="border px-4 py-2">{{ report.beg_bal_ward_quantity }}</td>
              <td class="border px-4 py-2">{{ report.beg_bal_total_quantity }}</td>

              <td class="border px-4 py-2">{{ report.beg_bal_total_cost }}</td>
              <!-- <td class="border  px-4 py-2 bg-yellow-500">-</td> -->

              <td class="border px-4 py-2">{{ report.received_mms_qty }}</td>
              <td class="border px-4 py-2">{{ report.received_mms_total_cost }}</td>

              <td class="border px-4 py-2">{{ report.issued_qty }}</td>
              <td class="border px-4 py-2">{{ report.issued_total_cost }}</td>

              <td class="border px-4 py-2">{{ report.consump_quantity }}</td>
              <td class="border px-4 py-2">{{ report.consump_total_cost }}</td>

              <td class="border px-4 py-2">{{ report.end_bal_csr_quantity }}</td>
              <td class="border px-4 py-2">{{ report.end_bal_ward_quantity }}</td>
              <td class="border px-4 py-2">{{ report.end_bal_total_quantity }}</td>
              <td class="border px-4 py-2">{{ report.end_bal_total_cost }}</td>
            </tr>

            <!-- Grand Total Row -->
            <!-- <tr class="bg-green-500 font-bold text-white text-center">
              <td
                colspan="6"
                class="border  px-4 py-2"
              >
                GRAND TOTAL
              </td>
              <td class="border  px-4 py-2">-</td>
              <td class="border  px-4 py-2">89,388.50</td>
              <td class="border  px-4 py-2">-</td>
              <td class="border  px-4 py-2">388.50</td>
              <td
                colspan="4"
                class="border  px-4 py-2"
              >
                -
              </td>
              <td class="border  px-4 py-2">89,000.00</td>
            </tr> -->
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
import moment from 'moment';
import { Link } from '@inertiajs/vue3';

export default {
  components: {
    AppLayout,
    Head,
    Calendar,
    Button,
    Link,
  },
  props: {
    reports: Array,
  },
  data() {
    return {
      options: {},
      params: {},
      from: null,
      to: null,
      reportsContainer: [],
    };
  },
  mounted() {
    console.log(this.reports);

    this.storeReportsInContainer();
  },
  methods: {
    storeReportsInContainer() {
      this.reports.forEach((e) => {
        console.log(e);
        this.reportsContainer.push({
          cl2comb: e.cl2comb, // *
          item_description: e.item_description, // *
          unit: e.unit, // *
          unit_cost: e.unit_cost, // *

          beg_bal_csr_quantity: e.beg_bal_csr_quantity, // *
          beg_bal_ward_quantity: e.beg_bal_ward_quantity, // *
          beg_bal_total_quantity: e.beg_bal_total_quantity, // *
          beg_bal_total_cost: 0,

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
  },
  watch: {
    from: function (val) {
      if (val != null) {
        let from = moment(val).format('YYYY-MM-DD 12:00:00');
        // console.log('from', from);
        this.params.from = from;
      } else {
        this.params.from = null;
        this.from = null;
      }
      this.updateData();
    },
    to: function (val) {
      if (val != null) {
        let to = moment(val).format('YYYY-MM-DD 11:59:59');
        // console.log('to', to);
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
<style scoped>
table {
  width: 100%;
  border-collapse: collapse;
}

th,
td {
  border: 1px solid black; /* Adjust color as needed */
  padding: 8px;
}

th {
  /* background-color: inherit; */
  color: black;
}

/* table {
  width: 100%;
  border-collapse: collapse;
  border-color: white;
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
} */
</style>
