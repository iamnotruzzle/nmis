<template>
  <app-layout>
    <Head title="InvenTrackr - Reports" />

    <div class="card">
      <div class="mb-5 flex justify-content-between">
        <span class="font-bold text-4xl">Reports</span>
        <a
          href="http://csrw.test/csrstocks/export/"
          target="_blank"
        >
          <i
            class="pi pi-file-excel"
            :style="{ color: 'green', 'font-size': '2rem' }"
          ></i>
        </a>
      </div>
      <table class="table">
        <!-- <col />
        <colgroup span="2"></colgroup>
        <colgroup span="2"></colgroup> -->
        <tr>
          <td
            rowspan="2"
            class="group-header"
          >
            ITEM DESCRIPTION
          </td>
          <td
            rowspan="2"
            class="group-header"
          >
            UNIT
          </td>
          <td
            rowspan="2"
            class="group-header"
          >
            UNIT COST
          </td>
          <th
            scope="colgroup"
            class="group-header"
          >
            CSR
          </th>
          <th
            scope="colgroup"
            class="group-header"
          >
            WARDS
          </th>
          <th
            colspan="2"
            scope="colgroup"
            class="group-header"
          >
            TOTAL BEGINNING BALANCE
          </th>
          <th
            colspan="2"
            scope="colgroup"
            class="group-header"
          >
            SUPPLIES ISSUED TO WARDS
          </th>
          <th
            colspan="2"
            scope="colgroup"
            class="group-header"
          >
            CONSUMPTION
          </th>
        </tr>

        <tr>
          <!-- csr -->
          <th
            scope="col"
            class="header"
          >
            QUANTITY
          </th>
          <!-- wards -->
          <th
            scope="col"
            class="header"
          >
            QUANTITY
          </th>
          <!-- total beginning balance -->
          <th
            scope="col"
            class="header"
          >
            TOTAL QUANTITY
          </th>
          <th
            scope="col"
            class="header"
          >
            TOTAL COST
          </th>
          <!-- supplies issued to wards -->
          <th
            scope="col"
            class="header"
          >
            TOTAL QUANTITY
          </th>
          <th
            scope="col"
            class="header"
          >
            TOTAL COST
          </th>
          <!-- CONSUMPTiON -->
          <th
            scope="col"
            class="header"
          >
            QUANTITY
          </th>
          <th
            scope="col"
            class="header"
          >
            TOTAL COST
          </th>
        </tr>

        <tr
          v-for="rc in reportsContainer"
          :key="rc.cl2comb"
        >
          <th scope="row">{{ rc.item_description }}</th>
          <td>{{ rc.unit }}</td>
          <td>{{ rc.unit_cost }}</td>
          <td>{{ rc.csr_quantity }}</td>
          <td>{{ rc.ward_quantity }}</td>
          <td>{{ rc.total_beg_total_quantity }}</td>
          <td>{{ rc.total_beg_total_cost }}</td>
          <!-- SUPPLIES ISSUED TO WARDS -->
          <td>{{ rc.supplies_issued_to_wards_quantity }}</td>
          <td>{{ rc.supplies_issued_to_wards_total_cost }}</td>
          <!-- CONSUMPTION -->
          <td>{{ rc.consumption_quantity }}</td>
          <td>{{ rc.consumption_total_cost }}</td>
        </tr>
      </table>
    </div>
  </app-layout>
</template>

<script>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import moment from 'moment';

export default {
  components: {
    AppLayout,
    Head,
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
          csr_quantity: e.csr_quantity,
          ward_quantity: e.ward_quantity,
          supplies_issued_to_wards_quantity: e.supplies_issued_to_wards_quantity,
          supplies_issued_to_wards_total_cost: e.supplies_issued_to_wards_total_cost,
          total_beg_total_quantity: e.total_beg_total_quantity,
          total_beg_total_cost: e.total_beg_total_cost,
          consumption_quantity: e.consumption_quantity,
          consumption_total_cost: e.consumption_total_cost,
        });
      });

      console.log('container', this.reportsContainer);
    },

    updateData() {
      this.usersList = [];
      this.loading = true;

      this.$inertia.get('csrreports', this.params, {
        preserveState: true,
        preserveScroll: true,
        onFinish: (visit) => {
          this.totalRecords = this.users.total;
          this.usersList = [];
          this.storeUserInContainer();
          this.loading = false;
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
.group-header {
  text-align: center;
  font-size: 190%;
  font-weight: 700;
  background-color: rgb(0, 156, 120);
}
.header {
  font-size: 150%;
  font-weight: 500;
}
</style>
