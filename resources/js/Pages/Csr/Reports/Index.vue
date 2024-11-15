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
        <table class="min-w-full border border-black">
          <!-- Table Header -->
          <thead>
            <tr>
              <th class="bg-black border border-black px-4 py-2 text-center">Item Description</th>
              <th class="bg-black border border-black px-4 py-2 text-center">Unit</th>
              <th class="bg-black border border-black px-4 py-2 text-center">Unit Cost</th>

              <!-- CSR Column -->
              <th
                colspan="1"
                class="bg-yellow-500 border border-black px-4 py-2 text-center"
              >
                CSR
              </th>

              <!-- Wards Column -->
              <th
                colspan="1"
                class="bg-yellow-500 border border-black px-4 py-2 text-center"
              >
                Wards
              </th>

              <!-- Total Beginning Balance Column -->
              <th
                colspan="2"
                class="bg-yellow-500 border border-black px-4 py-2 text-center"
              >
                Total Beginning Balance
              </th>

              <!-- Received from MMS Column -->
              <th
                colspan="2"
                class="bg-purple-500 border border-black px-4 py-2 text-center"
              >
                Received from MMS
              </th>

              <!-- Supplies Issued to Wards Column -->
              <th
                colspan="2"
                class="bg-green-500 border border-black px-4 py-2 text-center"
              >
                Supplies Issued to Wards
              </th>

              <!-- Consumption Column -->
              <th
                colspan="2"
                class="bg-yellow-500 border border-black px-4 py-2 text-center"
              >
                Consumption
              </th>

              <!-- Total Ending Balance Column -->
              <th
                colspan="2"
                class="bg-blue-500 border border-black px-4 py-2 text-center"
              >
                Total Ending Balance
              </th>
            </tr>
            <tr>
              <!-- Sub-headers -->
              <th colspan="3"></th>
              <th class="bg-yellow-500 border border-black px-4 py-2 text-center">Quantity</th>
              <!-- <th class="bg-yellow-500 border border-black px-4 py-2 text-center">Cost</th> -->

              <th class="bg-yellow-500 border border-black px-4 py-2 text-center">Quantity</th>
              <!-- <th class="bg-yellow-500 border border-black px-4 py-2 text-center">Cost</th> -->

              <th class="bg-yellow-500 border border-black px-4 py-2 text-center">Total Quantity</th>
              <th class="bg-yellow-500 border border-black px-4 py-2 text-center">Total Cost</th>

              <th class="bg-purple-500 border border-black px-4 py-2 text-center">Quantity</th>
              <th class="bg-purple-500 border border-black px-4 py-2 text-center">Cost</th>

              <th class="bg-green-500 border border-black px-4 py-2 text-center">Quantity</th>
              <th class="bg-green-500 border border-black px-4 py-2 text-center">Cost</th>

              <th class="bg-yellow-500 border border-black px-4 py-2 text-center">Quantity</th>
              <th class="bg-yellow-500 border border-black px-4 py-2 text-center">Cost</th>

              <th class="bg-blue-500 border border-black px-4 py-2 text-center">Total Quantity</th>
              <th class="bg-blue-500 border border-black px-4 py-2 text-center">Total Cost</th>
            </tr>
          </thead>

          <!-- Table Body -->
          <tbody>
            <!-- Sample Data Row -->
            <tr class="text-center">
              <td class="border border-black px-4 py-2">Suture. Synthetic non-absorbable Monofilament</td>
              <td class="border border-black px-4 py-2">Piece</td>
              <td class="border border-black px-4 py-2">2,472.22</td>

              <td class="border border-black px-4 py-2 bg-yellow-500">beg csr quantity</td>
              <!-- <td class="border border-black px-4 py-2 bg-yellow-100">-</td> -->

              <td class="border border-black px-4 py-2 bg-yellow-500">beg ward_quantity</td>
              <td class="border border-black px-4 py-2 bg-yellow-500">beg Total quantity</td>

              <td class="border border-black px-4 py-2 bg-yellow-500">beg total cost</td>
              <!-- <td class="border border-black px-4 py-2 bg-yellow-500">-</td> -->

              <td class="border border-black px-4 py-2 bg-purple-500">mms quantity</td>
              <td class="border border-black px-4 py-2 bg-purple-500">mms cost</td>

              <td class="border border-black px-4 py-2 bg-green-500">issued quantity</td>
              <td class="border border-black px-4 py-2 bg-green-500">issued cost</td>

              <td class="border border-black px-4 py-2 bg-yellow-500">consump qty</td>
              <td class="border border-black px-4 py-2 bg-yellow-500">consump cost</td>

              <td class="border border-black px-4 py-2 bg-blue-500">end total qty</td>
              <td class="border border-black px-4 py-2 bg-blue-500">end total cost</td>
            </tr>

            <!-- Grand Total Row -->
            <!-- <tr class="bg-green-500 font-bold text-white text-center">
              <td
                colspan="6"
                class="border border-black px-4 py-2"
              >
                GRAND TOTAL
              </td>
              <td class="border border-black px-4 py-2">-</td>
              <td class="border border-black px-4 py-2">89,388.50</td>
              <td class="border border-black px-4 py-2">-</td>
              <td class="border border-black px-4 py-2">388.50</td>
              <td
                colspan="4"
                class="border border-black px-4 py-2"
              >
                -
              </td>
              <td class="border border-black px-4 py-2">89,000.00</td>
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
    reports: Object,
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
        this.reportsContainer.push({
          cl2comb: e.cl2comb,
          item_description: e.item_description,
          unit: e.unit,
          unit_cost: e.unit_cost,
          beg_bal_csr_quantity: e.beg_bal_csr_quantity, // csr beginning balance
          csr_total_cost: e.csr_total_cost,
          ward_beginning_balance: e.ward_beginning_balance, // ward beginning balance
          ward_total_cost: e.ward_total_cost,
          received_mms_qty: 0,
          received_mms_total_cost: 0,
          supplies_issued_to_wards_quantity: e.supplies_issued_to_wards_quantity,
          supplies_issued_to_wards_total_cost: e.supplies_issued_to_wards_total_cost,
          total_beg_total_quantity: e.total_beg_total_quantity,
          total_beg_total_cost: e.total_beg_total_cost,
          consumption_quantity: e.consumption_quantity,
          consumption_total_cost: e.consumption_total_cost,
          csr_quantity_ending_bal: e.csr_quantity_ending_bal,
          csr_total_cost_ending_bal: e.csr_total_cost_ending_bal,
          ward_quantity_ending_bal: e.ward_quantity_ending_bal,
          ward_total_cost_ending_bal: e.ward_total_cost_ending_bal,
          total_end_total_quantity: e.total_end_total_quantity,
          total_end_total_cost: e.total_end_total_cost,
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
  /* background-color: rgb(0, 156, 120); */
}
.header {
  font-size: 100%;
  font-weight: 600;
}
</style>
