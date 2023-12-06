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
        <template #empty> No stock found. </template>
        <template #loading> Loading stock data. Please wait. </template>
        <Column
          field="item"
          header="ITEM"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.cl2desc }}
          </template>
        </Column>
        <Column
          field="ending_balance"
          header="ENDING BALANCE"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.ending_balance }}
          </template>
        </Column>
        <Column
          field="beginning_balance"
          header="STARTING BALANCE"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.beginning_balance }}
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
          field="updated_by"
          header="UPDATED BY"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.updated_by }}
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
          <label>Ending balance</label>
          <InputText
            v-model.trim="form.ending_balance"
            required="true"
            autofocus
            type="number"
            @keyup.enter="submit"
          />
          <small
            class="text-error"
            v-if="form.errors.ending_balance"
          >
            {{ form.errors.ending_balance }}
          </small>
        </div>
        <div class="field">
          <label>Starting balance</label>
          <InputText
            v-model.trim="form.beginning_balance"
            required="true"
            autofocus
            type="number"
            @keyup.enter="submit"
          />
          <small
            class="text-error"
            v-if="form.errors.beginning_balance"
          >
            {{ form.errors.beginning_balance }}
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
    currentStocks: Object,
    locationStockBalance: Object,
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
      itemsList: [],
      balanceContainer: [],
      filters: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
      form: this.$inertia.form({
        id: null,
        location: null,
        cl2comb: null,
        ending_balance: null,
        beginning_balance: null,
        entry_by: null,
      }),
    };
  },
  // created will be initialize before mounted
  created() {
    this.totalRecords = this.locationStockBalance.total;
    this.params.page = this.locationStockBalance.current_page;
    this.rows = this.locationStockBalance.per_page;
  },
  mounted() {
    // console.log('stock bal', this.locationStockBalance);

    this.storeStockBalanceInContainer();
    this.storeItemsInController();

    this.loading = false;

    this.form.location = this.$page.props.auth.user.location.location_name.wardcode;
    this.form.entry_by = this.$page.props.auth.user.userDetail.employeeid;
  },
  methods: {
    storeStockBalanceInContainer() {
      this.locationStockBalance.data.forEach((e) => {
        this.balanceContainer.push({
          id: e.id,
          cl2comb: e.item.cl2comb,
          cl2desc: e.item.cl2desc,
          ending_balance: e.ending_balance,
          beginning_balance: e.beginning_balance,
          entry_by: e.entry_by.firstname + ' ' + e.entry_by.lastname,
          updated_by: e.updated_by == null ? null : e.updated_by.firstname + ' ' + e.updated_by.lastname,
        });
      });
      //   console.log('container', this.reportsContainer);
    },
    storeItemsInController() {
      this.itemsList = []; // reset
      //   this.currentStocks.forEach((e) => {
      //     this.itemsList.push({
      //       cl2comb: e.item_details.cl2comb,
      //       cl2desc: e.item_details.cl2desc,
      //     });
      //   });

      this.currentStocks.forEach((e) => {
        if (e.clsb_cl2comb == null) {
          this.itemsList.push({
            cl2comb: e.hc_cl2comb,
            cl2desc: e.cl2desc,
          });
        }
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
      this.balanceContainer = [];
      this.loading = true;

      this.$inertia.get('stockbal', this.params, {
        preserveState: true,
        preserveScroll: true,
        onFinish: (visit) => {
          //   this.totalRecords = this.users.total;
          this.balanceContainer = [];
          this.storeStockBalanceInContainer();
          this.itemsList = [];
          this.storeItemsInController();
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
      this.form.location = this.$page.props.auth.user.location.location_name.wardcode;
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
        (this.form.location = this.$page.props.auth.user.location.location_name.wardcode),
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
      this.form.ending_balance = item.ending_balance;
      this.form.beginning_balance = item.beginning_balance;
    },
    submit() {
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
        (this.form.location = this.$page.props.auth.user.location.location_name.wardcode),
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
          this.form.location = this.$page.props.auth.user.location.location_name.wardcode;
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
      this.form.location = this.$page.props.auth.user.location.location_name.wardcode;
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
<style scoped></style>
