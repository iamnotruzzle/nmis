<template>
  <app-layout>
    <Head title="InvenTrackr - Reports" />

    <div class="card">
      <!-- <div class="mb-5 flex justify-content-between">
        <span class="font-bold text-4xl">Reports</span>
        <div class="flex flex-row align-items-center">
          <div class="flex flex-row">
            <Calendar
              v-model="from"
              dateFormat="mm-dd-yy"
              placeholder="FROM"
              showIcon
              showButtonBar
              :hideOnDateTimeSelect="true"
              class="mr-2"
            />
            <Calendar
              v-model="to"
              dateFormat="mm-dd-yy"
              placeholder="TO"
              showIcon
              showButtonBar
              :hideOnDateTimeSelect="true"
              class="mr-2"
            />
          </div>
          <a
            :href="`csrstocks/export?from=${params.from}&to=${params.to}`"
            target="_blank"
          >
            <i
              class="pi pi-file-excel"
              :style="{ color: 'green', 'font-size': '2rem' }"
            ></i>
          </a>
        </div>
      </div> -->

      <table class="table">
        <tr>
          <td
            rowspan="2"
            class="group-header bg-white colored-header"
          >
            <div>
              <p>ITEM DESCRIPTION</p>
            </div>
          </td>
          <td
            rowspan="2"
            class="group-header bg-white colored-header"
          >
            UNIT
          </td>
          <th
            rowspan="1"
            scope="colgroup"
            class="group-header bg-white colored-header"
          >
            ESTIMATED BUDGET
          </th>
          <td
            rowspan="2"
            class="group-header bg-white colored-header"
          >
            BEGINNING BALANCE
          </td>
          <td
            rowspan="2"
            class="group-header bg-white colored-header"
          >
            RECEIVED FROM CSR
          </td>
          <td
            rowspan="2"
            class="group-header bg-white colored-header"
          >
            TOTAL STOCK
          </td>
        </tr>

        <tr>
          <!-- ESTIMATED BUDGET -->
          <th
            scope="col"
            class="header bg-white colored-header"
          >
            UNIT COST
          </th>
        </tr>

        <tr
          v-for="rc in reportsContainer"
          :key="rc.cl2comb"
        >
          <th scope="row">{{ rc.item_description }}</th>
          <td>{{ rc.unit }}</td>
          <td>{{ rc.unit_cost }}</td>
          <td>{{ rc.beginning_balance }}</td>
          <td>{{ rc.from_csr }}</td>
          <td>{{ rc.total_stock }}</td>
        </tr>
      </table>
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
    // console.log('reports', this.reports);

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
          beginning_balance: 0,
          from_csr: e.from_csr,
          total_stock: e.total_stock,
          consumption: e.consumption.filter((x) => x.cl2comb == e.cl2comb),
        });
      });

      console.log('container', this.reportsContainer);
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
    from: function (val) {
      if (val != null) {
        let from = moment(val).format('LL');
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
        let to = moment(val).add(1, 'd').format('LL');
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
