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
          <div
            v-else
            @click="exportToExcel()"
          >
            <i
              class="pi pi-file-excel"
              :style="{ color: 'green', 'font-size': '2rem' }"
            ></i>
          </div>
        </div>
      </div>

      <div
        id="print"
        style="overflow-x: auto"
      >
        <table style="width: 100%; border-collapse: collapse; text-align: center">
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
            <th
              rowspan="1"
              scope="colgroup"
              style="background-color: white; color: black; padding: 8px; border: 1px solid black"
            >
              ESTIMATED BUDGET
            </th>
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
              TOTAL BEGINNING BALANCE
            </td>
            <th
              colspan="6"
              scope="colgroup"
              style="background-color: white; color: black; text-align: center; padding: 8px; border: 1px solid black"
            >
              CONSUMPTION
            </th>
            <td
              rowspan="2"
              style="background-color: white; color: black; padding: 8px; border: 1px solid black"
            >
              TOTAL CONSUMPTION
            </td>
            <th
              scope="colgroup"
              style="background-color: white; color: black; text-align: center; padding: 8px; border: 1px solid black"
            >
              TOTAL CONSUMPTION
            </th>
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
            <th
              scope="col"
              style="background-color: white; color: black; padding: 8px; border: 1px solid black"
            >
              UNIT COST
            </th>
            <th
              scope="col"
              style="background-color: white; color: black; padding: 8px; border: 1px solid black"
            >
              SURGERY
            </th>
            <th
              scope="col"
              style="background-color: white; color: black; padding: 8px; border: 1px solid black"
            >
              OB-GYNE
            </th>
            <th
              scope="col"
              style="background-color: white; color: black; padding: 8px; border: 1px solid black"
            >
              ORTHO
            </th>
            <th
              scope="col"
              style="background-color: white; color: black; padding: 8px; border: 1px solid black"
            >
              PEDIA
            </th>
            <th
              scope="col"
              style="background-color: white; color: black; padding: 8px; border: 1px solid black"
            >
              OPTHA
            </th>
            <th
              scope="col"
              style="background-color: white; color: black; padding: 8px; border: 1px solid black"
            >
              ENT
            </th>
            <th
              scope="col"
              style="background-color: white; color: black; padding: 8px; border: 1px solid black"
            >
              (ESTIMATED COST)
            </th>
          </tr>

          <tr
            v-for="rc in reportsContainer"
            :key="rc.cl2comb"
          >
            <th
              scope="row"
              style="padding: 8px; border: 1px solid black; text-align: left !important"
            >
              {{ rc.item_description }}
            </th>
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
              <span>total transfers</span>
            </td>
            <td style="padding: 8px; border: 1px solid black">
              {{ rc.ending_balance }}
            </td>
            <td style="padding: 8px; border: 1px solid black">
              <span>manual</span>
            </td>
            <td style="padding: 8px; border: 1px solid black">
              <span>variance</span>
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
          beginning_balance: e.beginning_balance,
          from_csr: e.from_csr,
          from_ward: e.from_ward,
          total_beg_bal: e.total_beg_bal,
          surgery: e.surgery,
          obgyne: e.obgyne,
          //   urology: e.urology,
          ortho: e.ortho,
          pedia: e.pedia,
          //   med: e.med,
          optha: e.optha,
          ent: e.ent,
          //   neuro: e.neuro,
          total_consumption: e.total_consumption,
          total_cons_estimated_cost: e.total_cons_estimated_cost,
          ending_balance: e.ending_balance,
          actual_inventory: e.actual_inventory,
        });
      });

      console.log('container', this.reportsContainer);
    },

    // print using windows word
    print() {
      //   console.log(data);
      //   console.log('Opening print dialog...');
      setTimeout(() => {
        this.$nextTick(() => {
          const printWindow = window.open('', '_blank');
          if (printWindow) {
            printWindow.document.write(`
            <html>
              <head>
                <title>Print</title>
                <style>
                    /* Print-specific styles */
                    body, #print {
                        font-family: Calibri, sans-serif;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                    }
                    th, td {
                        padding: 5px;
                        border: 1px solid black;
                        text-align: left;
                    }
                </style>
              </head>
              <body>
                ${document.getElementById('print').innerHTML}
              </body>
            </html>
          `);
            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
            printWindow.close();
          } else {
            console.error('Failed to open print window.');
          }
        });
      }, 200); // Slightly longer delay to ensure rendering
    },
    // export to excel
    exportToExcel() {
      const tableHTML = document.getElementById('print').outerHTML;
      const dataType = 'application/vnd.ms-excel';
      const tableStyle = `
        <style>
        body, table {
            font-family: Calibri, sans-serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
        }
        th, td {
            padding: 5px;
            border: 1px solid black;
            text-align: center;
        }
        </style>
    `;

      const excelHTML = `
        <html xmlns:o="urn:schemas-microsoft-com:office:office"
            xmlns:x="urn:schemas-microsoft-com:office:excel"
            xmlns="http://www.w3.org/TR/REC-html40">
        <head>
            <meta charset="UTF-8">
            <title>Export HTML to Excel</title>
            ${tableStyle}
        </head>
        <body>
            ${tableHTML}
        </body>
        </html>
    `;

      const blob = new Blob([excelHTML], { type: dataType });
      const link = document.createElement('a');
      link.href = URL.createObjectURL(blob);
      link.download = 'exported_table.xls';

      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
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
