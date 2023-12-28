<template>
  <app-layout>
    <Head title="NMIS - Reports" />

    <div
      class="card"
      style="width: 100%"
    >
      <Toast />

      <!-- :value="balanceContainer" -->
      <DataTable
        class="p-datatable-sm"
        v-model:filters="filters"
        selectionMode="single"
        lazy
        paginator
        :rows="rows"
        ref="dt"
        :totalRecords="totalRecords"
        @page="onPage($event)"
        dataKey="id"
        filterDisplay="row"
        showGridlines
        :loading="loading"
      >
        <template #header>
          <div class="flex flex-wrap align-items-center justify-content-between gap-2">
            <span class="text-xl text-900 font-bold text-cyan-500 hover:text-cyan-700">STOCK BALANCE</span>
            <div>
              <span class="p-input-icon-left mr-2">
                <i class="pi pi-search" />
                <InputText
                  v-model="search"
                  placeholder="Search item"
                />
              </span>

              <Button
                label="Balance"
                icon="pi pi-user-plus"
                iconPos="right"
                @click="openCreateItemDialog"
              />
            </div>
          </div>
        </template>
        <template #empty> No data found. </template>
        <template #loading> Loading data. Please wait. </template>
        <Column
          field="item"
          header="ITEM"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            <!-- {{ data.cl2desc }} -->
          </template>
        </Column>
        <Column
          field="unit_cost"
          header="UNIT COST"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.unit_cost }}
          </template>
        </Column>
        <Column
          field="csr_beg_bal_quantity"
          header="CSR BEG BAL QTY"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.csr_beg_bal_quantity }}
          </template>
        </Column>
        <Column
          field="csr_beg_bal_total_cost"
          header="CSR BEG BAL COST"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.csr_beg_bal_total_cost }}
          </template>
        </Column>
        <Column
          field="wards_beg_bal_quantity"
          header="WARD BEG BAL QTY"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.wards_beg_bal_quantity }}
          </template>
        </Column>
        <Column
          field="wards_beg_bal_total_cost"
          header="WARD BEG BAL COST"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.wards_beg_bal_total_cost }}
          </template>
        </Column>
        <Column
          field="total_beg_bal_total_quantity"
          header="TOTAL BEG BAL QTY"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.total_beg_bal_total_quantity }}
          </template>
        </Column>
        <Column
          field="total_beg_bal_total_cost"
          header="TOTAL BEG BAL COST"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.total_beg_bal_total_cost }}
          </template>
        </Column>
        <Column
          field="supp_issued_to_wards_total_quantity"
          header="SUPP ISSUED QTY"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.supp_issued_to_wards_total_quantity }}
          </template>
        </Column>
        <Column
          field="supp_issued_to_wards_total_cost"
          header="SUPP ISSUED COST"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.supp_issued_to_wards_total_cost }}
          </template>
        </Column>
        <Column
          field="consumption_quantity"
          header="CONSUMP. QTY"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.consumption_quantity }}
          </template>
        </Column>
        <Column
          field="consumption_total_cost"
          header="CONSUMP. COST"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.consumption_total_cost }}
          </template>
        </Column>
        <Column
          field="csr_end_bal_quantity"
          header="CSR END BAL QTY"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.csr_end_bal_quantity }}
          </template>
        </Column>
        <Column
          field="csr_end_bal_total_cost"
          header="CSR END BAL COST"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.csr_end_bal_total_cost }}
          </template>
        </Column>
        <Column
          field="wards_end_bal_quantity"
          header="WARD END BAL QTY"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.wards_end_bal_quantity }}
          </template>
        </Column>
        <Column
          field="wards_end_bal_total_cost"
          header="WARD END BAL COST"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.wards_end_bal_total_cost }}
          </template>
        </Column>
        <Column
          field="total_end_bal_total_quantity"
          header="TOTAL END BAL QTY"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.total_end_bal_total_quantity }}
          </template>
        </Column>
        <Column
          field="total_end_bal_total_cost"
          header="TOTAL END BAL COST"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.total_end_bal_total_cost }}
          </template>
        </Column>
        <Column
          field="entry_by"
          header="ENTRY BY"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.entry_by }}
          </template>
        </Column>
        <Column
          header="CREATED AT"
          filterField="created_at"
          style="min-width: 10rem"
          :showFilterMenu="false"
        >
          <template #body="{ data }">
            {{ tzone(data.created_at) }}
          </template>
          <template #filter="{}">
            <Calendar
              v-model="from"
              dateFormat="mm-dd-yy"
              placeholder="FROM"
              showIcon
              showButtonBar
              :hideOnDateTimeSelect="true"
            />
            <div class="mt-2"></div>
            <Calendar
              v-model="to"
              dateFormat="mm-dd-yy"
              placeholder="TO"
              showIcon
              showButtonBar
              :hideOnDateTimeSelect="true"
            />
          </template>
        </Column>
        <Column
          header="ACTION"
          style="min-width: 12rem"
        >
          <template #body="slotProps">
            <Button
              icon="pi pi-pencil"
              class="mr-1"
              rounded
              text
              severity="warning"
              @click="editItem(slotProps.data)"
            />

            <Button
              icon="pi pi-trash"
              rounded
              text
              severity="danger"
              @click="confirmDeleteItem(slotProps.data)"
            />
          </template>
        </Column>
      </DataTable>

      <Dialog
        v-model:visible="createItemDialog"
        header="Balance"
        :modal="true"
        class="p-fluid"
        @hide="whenDialogIsHidden"
        dismissableMask
      >
        <div class="field">
          <label>Item</label>
          <Dropdown
            v-if="!isUpdate"
            required="true"
            v-model="form.cl2comb"
            :options="itemsList"
            :virtualScrollerOptions="{ itemSize: 38 }"
            filter
            optionLabel="cl2desc"
            optionValue="cl2comb"
            class="w-full"
          />
          <InputText
            v-else
            v-model.trim="cl2desc"
            disabled=""
          />
          <small
            class="text-error"
            v-if="form.errors.cl2comb"
          >
            {{ form.errors.cl2comb }}
          </small>
        </div>
        <div class="field">
          <label>Starting balance</label>
          <InputText
            v-model.trim="form.unit_cost"
            required="true"
            autofocus
            type="number"
            @keyup.enter="submit"
          />
          <small
            class="text-error"
            v-if="form.errors.unit_cost"
          >
            {{ form.errors.unit_cost }}
          </small>
        </div>

        <template #footer>
          <Button
            label="Cancel"
            icon="pi pi-times"
            severity="danger"
            text
            @click="cancel"
          />
          <Button
            v-if="isUpdate == true"
            label="Update"
            icon="pi pi-check"
            text
            type="submit"
            severity="warning"
            :disabled="form.processing"
            @click="submit"
          />
          <Button
            v-else
            label="Save"
            icon="pi pi-check"
            text
            type="submit"
            :disabled="form.processing"
            @click="submit"
          />
        </template>
      </Dialog>

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
import Toast from 'primevue/toast';
import Avatar from 'primevue/avatar';
import Calendar from 'primevue/calendar';
import Dropdown from 'primevue/dropdown';
import AutoComplete from 'primevue/autocomplete';
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
  },
  props: {
    manual_reports: Object,
  },
  data() {
    return {
      // paginator
      loading: false,
      totalRecords: null,
      rows: null,
      // end paginator
      itemId: null,
      isUpdate: false,
      cl2desc: '',
      createItemDialog: false,
      deleteItemDialog: false,
      search: '',
      options: {},
      params: {},
      from: null,
      to: null,
      manual_reportsList: [],
      itemsList: [],
      filters: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
      form: this.$inertia.form({
        id: null,
        cl2comb: null,
        unit_cost: null,
        csr_beg_bal_quantity: null,
        csr_beg_bal_total_cost: null,
        wards_beg_bal_quantity: null,
        wards_beg_bal_total_cost: null,
        total_beg_bal_total_quantity: null,
        total_beg_bal_total_cost: null,
        supp_issued_to_wards_total_quantity: null,
        supp_issued_to_wards_total_cost: null,
        consumption_quantity: null,
        consumption_total_cost: null,
        csr_end_bal_quantity: null,
        csr_end_bal_total_cost: null,
        wards_end_bal_quantity: null,
        wards_end_bal_total_cost: null,
        total_end_bal_total_quantity: null,
        total_end_bal_total_cost: null,
        entry_by: null,
      }),
    };
  },
  // created will be initialize before mounted
  created() {
    this.totalRecords = this.manual_reports.total;
    this.params.page = this.manual_reports.current_page;
    this.rows = this.manual_reports.per_page;
  },
  mounted() {
    // console.log('stock bal', this.locationStockBalance);

    // console.log(this.$page.props.items);

    this.storeManualReportsInContainer();
    this.storeItemsInContainer();

    this.loading = false;

    this.form.entry_by = this.$page.props.auth.user.userDetail.employeeid;
  },
  methods: {
    storeManualReportsInContainer() {
      this.manual_reportsList = []; // reset

      this.manual_reports.forEach((e) => {
        this.manual_reportsList.push({
          cl2comb: e.cl2comb,
          unit_cost: e.unit_cost,
          csr_beg_bal_quantity: e.csr_beg_bal_quantity,
          csr_beg_bal_total_cost: e.csr_beg_bal_total_cost,
          wards_beg_bal_quantity: e.wards_beg_bal_quantity,
          wards_beg_bal_total_cost: e.wards_beg_bal_total_cost,
          total_beg_bal_total_quantity: e.total_beg_bal_total_quantity,
          total_beg_bal_total_cost: e.total_beg_bal_total_cost,
          supp_issued_to_wards_total_quantity: e.supp_issued_to_wards_total_quantity,
          supp_issued_to_wards_total_cost: e.supp_issued_to_wards_total_cost,
          consumption_quantity: e.consumption_quantity,
          consumption_total_cost: e.consumption_total_cost,
          csr_end_bal_quantity: e.csr_end_bal_quantity,
          csr_end_bal_total_cost: e.csr_end_bal_total_cost,
          wards_end_bal_quantity: e.wards_end_bal_quantity,
          wards_end_bal_total_cost: e.wards_end_bal_total_cost,
          total_end_bal_total_quantity: e.total_end_bal_total_quantity,
          total_end_bal_total_cost: e.total_end_bal_total_cost,
          entry_by: e.entry_by,
        });
      });

      this.sortItemsList(this.itemsList, 'cl2desc');
    },
    storeItemsInContainer() {
      this.itemsList = []; // reset

      this.$page.props.items.forEach((e) => {
        this.itemsList.push({
          cl2comb: e.cl2comb,
          cl2desc: e.cl2desc.trim(),
        });
      });

      this.sortItemsList(this.itemsList, 'cl2desc');
    },
    sortItemsList(arr, propertyName, order = 'ascending') {
      const sortedArr = this.itemsList.sort((a, b) => {
        if (a[propertyName] < b[propertyName]) {
          return -1;
        }
        if (a[propertyName] > b[propertyName]) {
          return 1;
        }
        return 0;
      });

      if (order === 'descending') {
        return sortedArr.reverse();
      }

      this.itemsList = sortedArr;
    },
    updateData() {
      this.loading = true;

      this.$inertia.get('csrmanualreports', this.params, {
        preserveState: true,
        preserveScroll: true,
        onFinish: (visit) => {
          //   this.totalRecords = this.users.total;
          this.itemsList = [];
          this.storeItemsInContainer();
          this.loading = false;
        },
      });
    },
    tzone(date) {
      return moment.tz(date, 'Asia/Manila').format('L');
    },
    onPage(event) {
      this.params.page = event.page + 1;
      this.updateData();
    },
    openCreateItemDialog() {
      this.isUpdate = false;
      this.form.clearErrors();
      this.form.reset();
      this.itemId = null;
      this.createItemDialog = true;
      this.form.entry_by = this.$page.props.auth.user.userDetail.employeeid;
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
    editItem(item) {
      //   console.log(item);
      this.isUpdate = true;
      this.createItemDialog = true;
      this.form.id = item.id;
      this.form.cl2comb = item.cl2comb;
      this.cl2desc = item.cl2desc;
      this.form.unit_cost = item.unit_cost;
    },
    submit() {
      if (this.form.processing) {
        return false;
      }

      let id = this.form.id;
      if (this.isUpdate) {
        this.form.put(route('stockbal.update', id), {
          preserveScroll: true,
          onSuccess: () => {
            this.createItemDialog = false;
            this.cancel();
            this.updateData();
            this.updatedMsg();
          },
        });
      } else {
        this.form.post(route('stockbal.store'), {
          preserveScroll: true,
          onSuccess: () => {
            this.createItemDialog = false;
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
      this.form.delete(route('stockbal.destroy', this.form.id), {
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
      this.createItemDialog = false;
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
</style>
