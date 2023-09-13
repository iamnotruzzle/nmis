<template>
  <app-layout>
    <Head title="InvenTrackr - Reports" />

    <div
      class="card"
      style="width: 100%"
    >
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
            {{ data.item }}
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
      >
        <div class="field">
          <label>Item</label>
          <Dropdown
            required="true"
            v-model="form.cl2comb"
            :options="itemsList"
            filter
            optionLabel="cl2desc"
            class="w-full mb-3"
          />
        </div>
        <div class="field">
          <label>Ending balance</label>
          <InputText
            v-model.trim="form.ending_balance"
            required="true"
            autofocus
            type="number"
            :class="{ 'p-invalid': form.ending_balance }"
          />
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
            label="Save"
            icon="pi pi-check"
            text
            type="submit"
            :disabled="form.processing"
            @click="submit"
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
    currentWardStocks: Object,
    locationStockBalance: Object,
    currentWardStocks: Array,
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
        cl2comb: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
      form: this.$inertia.form({
        location: null,
        cl2comb: null,
        ending_balance: null,
        starting_balance: null,
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
    console.log('stock bal', this.locationStockBalance);

    this.storeStockBalanceInContainer();
    this.storeItemsInController();

    this.loading = false;

    this.form.location = this.$page.props.auth.user.location.location_name.wardcode;
    this.form.entry_by = this.$page.props.auth.user.userDetail.employeeid;
  },
  methods: {
    storeStockBalanceInContainer() {
      //   this.locationStockBalance.forEach((e) => {
      //     this.balanceContainer.push({
      //       cl2comb: e.cl2comb,
      //     });
      //   });
      //   console.log('container', this.reportsContainer);
    },
    storeItemsInController() {
      this.itemsList = []; // reset
      this.currentWardStocks.forEach((e) => {
        this.itemsList.push({
          cl2comb: e.item_details.cl2comb,
          cl2desc: e.item_details.cl2desc,
        });
      });
    },
    updateData() {
      this.reportsContainer = [];
      this.loading = true;

      this.$inertia.get('stockbal', this.params, {
        preserveState: true,
        preserveScroll: true,
        onFinish: (visit) => {
          //   this.totalRecords = this.users.total;
          this.reportsContainer = [];
          this.storeStockBalanceInContainer();
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
    },
    clickOutsideDialog() {
      this.$emit('hide', (this.itemId = null), (this.isUpdate = false), this.form.clearErrors(), this.form.reset());
    },
    editItem(item) {
      //   console.log(item);

      this.isUpdate = true;
      this.createItemDialog = true;
      this.itemId = item.id;
      this.form.role = item.role;
      this.form.designation = item.designation;
      this.form.employeeid = item.employeeid;
      this.form.password = item.password;
    },
    submit() {
      if (this.isUpdate) {
        this.form.put(route('stockbal.update'), {
          preserveScroll: true,
          onSuccess: () => {
            this.itemId = null;
            this.createItemDialog = false;
            this.cancel();
            this.updateData();
            this.createdMsg();
          },
        });
      } else {
        this.form.post(route('stockbal.store'), {
          preserveScroll: true,
          onSuccess: () => {
            this.itemId = null;
            this.createItemDialog = false;
            this.cancel();
            this.updateData();
            this.createdMsg();
          },
        });
      }
    },
    whenDialogIsHidden() {
      this.$emit('hide', this.form.clearErrors(), this.form.reset());
    },
    confirmDeleteItem(item) {
      this.itemId = item.id;
      this.deleteItemDialog = true;
    },
    deleteItem() {
      this.form.delete(route('stockbal.destroy', this.itemId), {
        preserveScroll: true,
        onSuccess: () => {
          this.balanceContainer = [];
          this.deleteItemDialog = false;
          this.itemId = null;
          this.form.clearErrors();
          this.form.reset();
          this.updateData();
          this.deletedMsg();
          this.storeUserInContainer();
        },
      });
    },
    cancel() {
      this.itemId = null;
      this.isUpdate = false;
      this.createItemDialog = false;
      this.form.reset();
      this.form.clearErrors();
    },
    createdMsg() {
      this.$toast.add({ severity: 'success', summary: 'Success', detail: 'Account created', life: 3000 });
    },
    updatedMsg() {
      this.$toast.add({ severity: 'warn', summary: 'Success', detail: 'Account updated', life: 3000 });
    },
    deletedMsg() {
      this.$toast.add({ severity: 'error', summary: 'Success', detail: 'Account deleted', life: 3000 });
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
