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
        </tr>

        <tr>
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
            QUANTITY
          </th>
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
        </tr>

        <tr>
          <th scope="row">ITEM</th>
          <td>UNIT</td>
          <td>UNIT COST</td>
          <td>CSR QUANTITY</td>
          <td>WARDS QUANTITY</td>
          <td>10000</td>
          <td>10000</td>
        </tr>
        <tr>
          <th scope="row">ITEM</th>
          <td>UNIT</td>
          <td>UNIT COST</td>
          <td>CSR QUANTITY</td>
          <td>WARDS QUANTITY</td>
          <td>10000</td>
          <td>10000</td>
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
    // users: Object,
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
    this.storeReportsInContainer();
  },
  methods: {
    // use storeUserInContainer() function so that every time you make
    // server request such as POST, the data in the table
    // is updated
    storeReportsInContainer() {
      //   this.usersList.push({
      //     id: e.id,
      //     image: e.image,
      //     employeeid: e.employeeid,
      //     designation: e.designation,
      //     role: e.roles[0].name,
      //     created_at: e.created_at,
      //   });
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
  font-weight: 900;
  background-color: rgb(0, 156, 120);
}
.header {
  font-size: 150%;
  font-weight: 900;
}
</style>
