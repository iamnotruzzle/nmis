<template>
  <app-layout>
    <Head title="NMIS - Reports" />

    <div
      class="card"
      style="width: 100%"
    >
      <Toast />

      <DataTable
        class="p-datatable-sm"
        v-model:filters="filters"
        :value="balanceContainer"
        paginator
        :rows="10"
        :rowsPerPageOptions="[10, 30, 50]"
        removableSort
        sortField="created_at"
        :sortOrder="1"
        showGridlines
        :loading="loading"
      >
        <template #header>
          <div class="flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="">
              <p class="text-xl text-900 font-bold text-primary">STOCK BALANCE</p>
              <Button
                severity="success"
                icon="pi pi-save"
                label="Beginning balance"
                class="mr-2"
                @click="generateBegBalance"
                :disabled="canBeginBalance !== true"
              />
              <Button
                severity="danger"
                icon="pi pi-save"
                label="Ending balance"
                @click="generateEndBalance"
                :disabled="canBeginBalance !== false"
              />
            </div>

            <div class="w-full md:w-auto mt-2 md:mt-0">
              <Dropdown
                v-model="selectedDate"
                :options="stockBalDatesList"
                optionLabel="name"
                optionValue="code"
                placeholder="Select a date"
                checkmark
                :highlightOnSelect="true"
                class="w-full"
              />
              <div class="my-2"></div>
              <div class="p-inputgroup">
                <span class="p-inputgroup-addon">
                  <i class="pi pi-search"></i>
                </span>
                <InputText
                  class="w-auto"
                  id="searchInput"
                  v-model="filters['global'].value"
                  placeholder="Search item"
                />
              </div>
            </div>
          </div>
        </template>
        <template #empty> No stock found. </template>
        <template #loading> Loading stock data. Please wait. </template>
        <Column
          field="cl2desc"
          header="ITEM"
          style="width: 30%"
        >
          <template #body="{ data }">
            <span style="text-wrap: nowrap"> {{ data.cl2desc }}</span>
          </template>
        </Column>
        <Column
          field="beginning_balance"
          header="BEGINNING BALANCE"
          style="text-align: center; width: 5%"
          :pt="{ headerContent: 'justify-content-end' }"
        >
          <template #body="{ data }">
            {{ data.beginning_balance }}
          </template>
        </Column>
        <Column
          field="ending_balance"
          header="ENDING BALANCE"
          style="text-align: center; width: 5%"
        >
          <template #body="{ data }">
            {{ data.ending_balance }}
          </template>
        </Column>
        <Column
          header="PPU"
          style="text-align: right"
          :pt="{ headerContent: 'justify-content-end' }"
        >
          <template #body="{ data }">
            <span class="text-blue-500 text-bold"> ₱ {{ data.price_per_unit }}</span>
          </template>
        </Column>
        <Column
          header="BEG. BAL DATE"
          style="text-align: right"
          :pt="{ headerContent: 'justify-content-end' }"
        >
          <template #body="{ data }">
            <span class="text-green-500 text-bold"> {{ tzone(data.beg_bal_created_at) }}</span>
          </template>
        </Column>
        <Column
          header="END. BAL DATE"
          style="text-align: right"
          :pt="{ headerContent: 'justify-content-end' }"
        >
          <template #body="{ data }">
            <span class="text-error text-bold"> {{ tzone(data.end_bal_created_at) }}</span>
          </template>
        </Column>
        <!-- <Column
          header="ACTION"
          style="text-align: center; width: 5%"
        >
          <template #body="slotProps">
            <div class="flex flex-row justify-content-center">
              <Button
                icon="pi pi-trash"
                rounded
                text
                severity="danger"
                @click="confirmDeleteItem(slotProps.data)"
              />
            </div>
          </template>
        </Column> -->
      </DataTable>

      <Dialog
        v-model:visible="deleteItemDialog"
        :style="{ width: '450px' }"
        header="Confirm"
        :modal="true"
        dismissableMask
      >
        <div class="flex align-items-center justify-content-center">
          <i
            class="pi pi-exclamation-triangle mr-3"
            style="font-size: 2rem"
          />
          <span v-if="form"
            >Are you sure you want to delete <b>{{ form.cl2desc }} </b> ?</span
          >
        </div>
        <template #footer>
          <Button
            label="No"
            icon="pi pi-times"
            class="p-button-text"
            @click="deleteItemDialog = false"
          />
          <Button
            label="Yes"
            icon="pi pi-check"
            severity="danger"
            text
            :disabled="form.processing"
            @click="deleteItem"
          />
        </template>
      </Dialog>
    </div>
  </app-layout>
</template>

<script>
import { FilterMatchMode } from 'primevue/api';
import { router } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import FileUpload from 'primevue/fileupload';
import InputNumber from 'primevue/inputnumber';
import Toast from 'primevue/toast';
import Avatar from 'primevue/avatar';
import Calendar from 'primevue/calendar';
import Dropdown from 'primevue/dropdown';
import AutoComplete from 'primevue/autocomplete';
import Tooltip from 'primevue/tooltip';
import Tag from 'primevue/tag';
import moment from 'moment';
import { Link } from '@inertiajs/vue3';

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
    Link,
    InputNumber,
    Tooltip,
  },
  directives: {
    tooltip: Tooltip,
  },
  props: {
    locationStockBalance: Object,
    hasBalance: Number,
    canBeginBalance: Boolean,
    stockBalDates: Array,
  },
  data() {
    return {
      // paginator
      loading: false,
      // end paginator
      itemId: null,
      isUpdate: false,
      cl2desc: '',
      deleteItemDialog: false,
      selectedDate: '',
      stockBalDatesList: [],
      search: '',
      options: {},
      params: {},
      balanceContainer: [],
      filters: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
      form: this.$inertia.form({
        beg_bal: null,
        end_bal: null,
        isAbleToGenerate: null,
        id: null,
        ris_no: null,
        cl2comb: null,
        ending_balance: null,
        beginning_balance: null,
        entry_by: null,
      }),
    };
  },
  mounted() {
    // console.log(moment().format('YYYY-MM-DD HH:mm:ss'));
    // console.log(this.stockBalDates);

    this.storeStockBalDatesInContainer();
    this.storeStockBalanceInContainer();

    this.loading = false;

    this.form.entry_by = this.$page.props.auth.user.userDetail.employeeid;
  },
  computed: {
    isBetween10thAndLastDay() {
      // Get the current date
      const currentDate = moment();

      // Get the 10th of the current month
      const day10th = moment().set('date', 10);

      // Get the last day of the current month
      const lastDayOfMonth = moment().endOf('month');

      // Return true if the current date is between the 10th and the last day of the month (inclusive)
      //   console.log(currentDate.isSameOrAfter(day10th, 'day') && currentDate.isSameOrBefore(lastDayOfMonth, 'day'));
      return currentDate.isSameOrAfter(day10th, 'day') && currentDate.isSameOrBefore(lastDayOfMonth, 'day');
    },
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
    storeStockBalanceInContainer() {
      this.locationStockBalance.forEach((e) => {
        this.balanceContainer.push({
          ris_no: e.ris_no,
          cl2comb: e.cl2comb,
          cl2desc: e.cl2desc,
          beginning_balance: e.beginning_balance,
          ending_balance: e.ending_balance,
          beg_bal_created_at: e.beg_bal_created_at,
          end_bal_created_at: e.end_bal_created_at,
          price_per_unit: e.price_per_unit,
        });
      });
      //   console.log('container', this.reportsContainer);
    },
    updateData() {
      this.balanceContainer = [];
      this.loading = true;

      this.$inertia.get('csrstockbal', this.params, {
        preserveState: true,
        preserveScroll: true,
        onFinish: (visit) => {
          //   this.totalRecords = this.users.total;
          this.balanceContainer = [];
          this.storeStockBalanceInContainer();
          this.loading = false;
        },
      });
    },
    tzone(date) {
      //   console.log('date', date);
      if (date == null || date == undefined) {
        return '';
      } else {
        return moment.tz(date, 'Asia/Manila').format('L');
      }
    },
    onPage(event) {
      this.params.page = event.page + 1;
      this.updateData();
    },
    generateBegBalance() {
      this.form.beg_bal = true;

      this.form.isAbleToGenerate = true;
      this.form.entry_by = this.$page.props.auth.user.userDetail.employeeid;

      if (this.form.processing && this.form.isAbleToGenerate != true) {
        return false;
      }

      this.form.post(route('csrstockbal.store'), {
        preserveScroll: true,
        onSuccess: () => {
          this.cancel();
          this.updateData();
          this.createdMsg();
        },
        onError: () => {
          //   console.log(this.$page.props.errors.error);
          this.errorMsg();
        },
      });
    },
    generateEndBalance() {
      this.form.end_bal = true;

      this.form.isAbleToGenerate = true;
      this.form.entry_by = this.$page.props.auth.user.userDetail.employeeid;

      if (this.form.processing && this.form.isAbleToGenerate != true) {
        return false;
      }

      this.form.post(route('csrstockbal.store'), {
        preserveScroll: true,
        onSuccess: () => {
          this.cancel();
          this.updateData();
          this.createdMsg();
        },
        onError: () => {
          //   console.log(this.$page.props.errors.error);
          this.errorMsg();
        },
      });
    },
    clickOutsideDialog() {
      this.$emit(
        'hide',
        (this.itemId = null),
        (this.isUpdate = false),
        (this.cl2desc = ''),
        this.form.clearErrors(),
        this.form.reset(),
        (this.form.entry_by = this.$page.props.auth.user.userDetail.employeeid)
      );
    },
    submit() {
      if (this.form.processing) {
        return false;
      }

      let id = this.form.id;
      if (this.isUpdate) {
        this.form.put(route('csrstockbal.update', id), {
          preserveScroll: true,
          onSuccess: () => {
            this.cancel();
            this.updateData();
            this.updatedMsg();
          },
        });
      } else {
        this.form.post(route('csrstockbal.store'), {
          preserveScroll: true,
          onSuccess: () => {
            this.cancel();
            this.updateData();
            this.createdMsg();
          },
        });
      }
    },
    whenDialogIsHidden() {
      this.$emit(
        'hide',
        (this.isUpdate = false),
        this.form.clearErrors(),
        this.form.reset(),
        (this.form.entry_by = this.$page.props.auth.user.userDetail.employeeid)
      );
    },
    confirmDeleteItem(item) {
      this.itemId = item.id;
      this.deleteItemDialog = true;
    },
    confirmDeleteItem(item) {
      this.form.id = item.id;
      this.form.cl2desc = item.cl2desc;
      this.deleteItemDialog = true;
    },
    deleteItem() {
      this.form.delete(route('csrstockbal.destroy', this.form.id), {
        preserveScroll: true,
        onSuccess: () => {
          this.balanceContainer = [];
          this.deleteItemDialog = false;
          this.form.clearErrors();
          this.form.reset();
          this.updateData();
          this.deletedMsg();
          this.form.entry_by = this.$page.props.auth.user.userDetail.employeeid;
        },
      });
    },
    cancel() {
      this.itemId = null;
      this.isUpdate = false;
      this.form.reset();
      this.form.clearErrors();
      this.form.entry_by = this.$page.props.auth.user.userDetail.employeeid;
    },
    createdMsg() {
      this.$toast.add({
        severity: 'success',
        summary: 'Success',
        detail: 'Starting and ending balance declared',
        life: 3000,
      });
    },
    errorMsg() {
      this.$toast.add({
        severity: 'error',
        summary: 'Error',
        detail: 'Stock balance for this month already declared.',
        life: 5000,
      });
    },
    updatedMsg() {
      this.$toast.add({
        severity: 'warn',
        summary: 'Success',
        detail: 'Starting and ending balance updated',
        life: 3000,
      });
    },
    deletedMsg() {
      this.$toast.add({
        severity: 'error',
        summary: 'Success',
        detail: 'Starting and ending balance deleted',
        life: 3000,
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
    search: function (val, oldVal) {
      this.params.search = val;
      this.updateData();
    },
    selectedDate: function (val, oldVal) {
      this.params.date = val;
      this.updateData();
    },
  },
};
</script>
<style scoped>
/* Remove arrow for input type number */
/* Chrome, Safari, Edge, Opera */
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

/* Firefox */
input[type='number'] {
  -moz-appearance: textfield;
}
/* END Remove arrow for input type number */

.my-link {
  opacity: 0.7;
  transition: opacity 0.2s ease-in-out; /* Optional: Add a smooth transition effect */
}

/* Change the opacity on hover */
.my-link:hover {
  opacity: 1; /* Adjust the opacity value as needed */
  border-bottom-style: solid;
}
</style>
