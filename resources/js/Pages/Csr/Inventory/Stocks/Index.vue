<template>
  <app-layout>
    <Head title="Template - Stocks" />

    <div class="card">
      <Toast />

      <!-- v-model:filters="filters" -->
      <DataTable
        class="p-datatable-sm"
        v-model:filters="filters"
        :value="stocksList"
        selectionMode="single"
        lazy
        paginator
        :rows="rows"
        ref="dt"
        :totalRecords="totalRecords"
        @page="onPage($event)"
        dataKey="cl1comb"
        filterDisplay="row"
        showGridlines
        :loading="loading"
      >
        <template #header>
          <div class="flex flex-wrap align-items-center justify-content-between gap-2">
            <span class="text-xl text-900 font-bold">Stocks</span>
            <div>
              <span class="p-input-icon-left mr-2">
                <i class="pi pi-search" />
                <InputText
                  v-model="search"
                  placeholder="Search item"
                />
              </span>
              <Button
                label="Add stocks"
                icon="pi pi-plus"
                iconPos="right"
                @click="openCreateItemDialog"
              />
            </div>
          </div>
        </template>
        <template #empty> No stock found. </template>
        <template #loading> Loading stock data. Please wait. </template>
        <Column
          field="batch_no"
          header="BATCH NO."
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.batch_no }}
          </template>
        </Column>
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
          field="quantity"
          header="QUANTITY"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.quantity }}
          </template>
        </Column>
        <Column
          field="manufactured_date"
          header="MANUFACTURED DATE"
          style="min-width: 12rem"
          :showFilterMenu="false"
        >
          <template #body="{ data }">
            {{ tzone(data.manufactured_date) }}
          </template>
          <template #filter="{}">
            <Calendar
              v-model="from_md"
              dateFormat="mm-dd-yy"
              placeholder="FROM"
              showIcon
              showButtonBar
              :hideOnDateTimeSelect="true"
            />
            <div class="mt-2"></div>
            <Calendar
              v-model="to_md"
              dateFormat="mm-dd-yy"
              placeholder="TO"
              showIcon
              showButtonBar
              :hideOnDateTimeSelect="true"
            />
          </template>
        </Column>
        <Column
          field="delivered_date"
          header="DELIVERY DATE"
          style="min-width: 12rem"
          :showFilterMenu="false"
        >
          <template #body="{ data }">
            {{ tzone(data.delivered_date) }}
          </template>
          <template #filter="{}">
            <Calendar
              v-model="from_dd"
              dateFormat="mm-dd-yy"
              placeholder="FROM"
              showIcon
              showButtonBar
              :hideOnDateTimeSelect="true"
            />
            <div class="mt-2"></div>
            <Calendar
              v-model="to_dd"
              dateFormat="mm-dd-yy"
              placeholder="TO"
              showIcon
              showButtonBar
              :hideOnDateTimeSelect="true"
            />
          </template>
        </Column>
        <Column
          field="expiration_date"
          header="EXPIRATION DATE"
          style="min-width: 12rem"
          :showFilterMenu="false"
        >
          <template #body="{ data }">
            {{ tzone(data.expiration_date) }}
          </template>
          <template #filter="{}">
            <Calendar
              v-model="from_ed"
              dateFormat="mm-dd-yy"
              placeholder="FROM"
              showIcon
              showButtonBar
              :hideOnDateTimeSelect="true"
            />
            <div class="mt-2"></div>
            <Calendar
              v-model="to_ed"
              dateFormat="mm-dd-yy"
              placeholder="TO"
              showIcon
              showButtonBar
              :hideOnDateTimeSelect="true"
            />
          </template>
        </Column>
        <Column
          header="Action"
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

      <!-- create & edit dialog -->
      <Dialog
        v-model:visible="createStockDialog"
        :style="{ width: '450px' }"
        header="Stock Detail"
        :modal="true"
        class="p-fluid"
        @hide="clickOutsideDialog"
        dismissableMask
      >
        <div class="field">
          <label for="batch_no">Batch no.</label>
          <InputText
            id="batch_no"
            v-model.trim="form.batch_no"
            required="true"
            autofocus
            :class="{ 'p-invalid': form.batch_no == '' }"
            @keyup.enter="submit"
          />
          <small
            class="text-error"
            v-if="form.errors.batch_no"
          >
            {{ form.errors.batch_no }}
          </small>
        </div>
        <div class="field">
          <label for="Item">Item</label>
          <Dropdown
            required="true"
            v-model="form.cl2comb"
            :options="itemsList"
            dataKey="unit"
            optionLabel="cl2desc"
            optionValue="cl2comb"
            class="w-full mb-3"
            :class="{ 'p-invalid': form.cl2comb == '' }"
          />
          <small
            class="text-error"
            v-if="form.errors.cl2comb"
          >
          </small>
        </div>
        <div class="field">
          <label for="quantity">Quantity</label>
          <InputText
            id="quantity"
            v-model.trim="form.quantity"
            required="true"
            type="number"
            autofocus
            :class="{ 'p-invalid': form.quantity == '' }"
            @keyup.enter="submit"
          />
          <small
            class="text-error"
            v-if="form.errors.quantity"
          >
            {{ form.errors.quantity }}
          </small>
        </div>
        <div class="field">
          <label for="manufactured_date">Manufactured date</label>
          <Calendar
            v-model="form.manufactured_date"
            dateFormat="mm-dd-yy"
            showIcon
            showButtonBar
            showTime
            hourFormat="12"
            :hideOnDateTimeSelect="true"
          />
          <small
            class="text-error"
            v-if="form.errors.manufactured_date"
          >
            {{ form.errors.manufactured_date }}
          </small>
        </div>
        <div class="field">
          <label for="delivered_date">Delivered date</label>
          <Calendar
            v-model="form.delivered_date"
            dateFormat="mm-dd-yy"
            showIcon
            showButtonBar
            showTime
            hourFormat="12"
            :hideOnDateTimeSelect="true"
          />
          <small
            class="text-error"
            v-if="form.errors.delivered_date"
          >
            {{ form.errors.delivered_date }}
          </small>
        </div>
        <div class="field">
          <label for="expiration_date">Expiration date</label>
          <Calendar
            v-model="form.expiration_date"
            dateFormat="mm-dd-yy"
            showIcon
            showButtonBar
            showTime
            hourFormat="12"
            :hideOnDateTimeSelect="true"
          />
          <small
            class="text-error"
            v-if="form.errors.expiration_date"
          >
            {{ form.errors.expiration_date }}
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
            severity="warning"
            text
            type="submit"
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

      <!-- Delete confirmation dialog -->
      <Dialog
        v-model:visible="deleteStockDialog"
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
            >Are you sure you want to delete <b>{{ form.cl2desc }}</b> with batch number
            <b>{{ form.batch_no }}</b> ?</span
          >
        </div>
        <template #footer>
          <Button
            label="No"
            icon="pi pi-times"
            class="p-button-text"
            @click="deleteStockDialog = false"
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
import moment from 'moment';

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
  },
  props: {
    items: Object,
    stocks: Object,
  },
  data() {
    return {
      // paginator
      loading: false,
      totalRecords: null,
      rows: null,
      // end paginator
      stockId: null,
      isUpdate: false,
      createStockDialog: false,
      deleteStockDialog: false,
      search: '',
      options: {},
      params: {},
      // manufactured date
      from_md: null,
      to_md: null,
      // delivered date
      from_dd: null,
      to_dd: null,
      // expiration date
      from_ed: null,
      to_ed: null,
      itemsList: [],
      stocksList: [],
      filters: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
      cl1stats: [
        {
          name: 'ACTIVE',
          value: 'A',
        },
        {
          name: 'INACTIVE',
          value: 'I',
        },
      ],
      form: this.$inertia.form({
        batch_no: null,
        cl2comb: null,
        cl2desc: null,
        quantity: null,
        manufactured_date: null,
        delivered_date: null,
        expiration_date: null,
      }),
    };
  },
  // created will be initialize before mounted
  created() {
    this.totalRecords = this.stocks.total;
    this.params.page = this.stocks.current_page;
    this.rows = this.stocks.per_page;
  },
  mounted() {
    // console.log(this.items);
    this.storeItemsInController();
    this.storeStocksInContainer();

    this.loading = false;
  },
  methods: {
    tzone(date) {
      if (date == null || date == '') {
        return null;
      } else {
        return moment.tz(date, 'Asia/Manila').format('LLL');
      }
    },
    storeItemsInController() {
      this.items.forEach((e) => {
        this.itemsList.push({
          cl2comb: e.cl2comb,
          cl2desc: e.cl2desc,
        });
      });
    },
    // use storeStocksInContainer() function so that every time you make
    // server request such as POST, the data in the table
    // is updated
    storeStocksInContainer() {
      this.stocks.data.forEach((e) => {
        this.stocksList.push({
          id: e.id,
          batch_no: e.batch_no,
          cl2comb: e.cl2comb,
          cl2desc: e.item_detail.cl2desc,
          quantity: e.quantity,
          manufactured_date: e.manufactured_date === null ? '' : e.manufactured_date,
          delivered_date: e.delivered_date === null ? '' : e.delivered_date,
          expiration_date: e.expiration_date === null ? '' : e.expiration_date,
        });
      });
    },
    onPage(event) {
      this.params.page = event.page + 1;
      this.updateData();
    },
    updateData() {
      this.stocksList = [];
      this.loading = true;

      this.$inertia.get('csrstocks', this.params, {
        preserveState: true,
        preserveScroll: true,
        onFinish: (visit) => {
          this.totalRecords = this.stocks.total;
          this.stocksList = [];
          this.storeStocksInContainer();
          this.loading = false;
        },
      });
    },
    openCreateItemDialog() {
      this.isUpdate = false;
      this.form.clearErrors();
      this.form.reset();
      this.stockId = null;
      this.createStockDialog = true;
    },
    // emit close dialog
    clickOutsideDialog() {
      this.$emit('hide', (this.stockId = null), (this.isUpdate = false), this.form.clearErrors(), this.form.reset());
    },
    editItem(item) {
      this.isUpdate = true;
      this.createStockDialog = true;
      this.stockId = item.id;
      this.form.batch_no = item.batch_no;
      this.form.cl2comb = item.cl2comb;
      this.form.quantity = item.quantity;
      this.form.manufactured_date = item.manufactured_date;
      this.form.delivered_date = item.delivered_date;
      this.form.expiration_date = item.expiration_date;
    },
    submit() {
      if (this.isUpdate) {
        this.form.put(route('csrstocks.update', this.stockId), {
          preserveScroll: true,
          onSuccess: () => {
            this.stockId = null;
            this.createStockDialog = false;
            this.cancel();
            this.updateData();
            this.updatedMsg();
          },
        });
      } else {
        this.form.post(route('csrstocks.store'), {
          preserveScroll: true,
          onSuccess: () => {
            this.stockId = null;
            this.createStockDialog = false;
            this.cancel();
            this.updateData();
            this.createdMsg();
          },
        });
      }
    },
    confirmDeleteItem(item) {
      this.stockId = item.id;
      this.form.batch_no = item.batch_no;
      this.form.cl2desc = item.cl2desc;
      this.deleteStockDialog = true;
    },
    deleteItem() {
      this.form.delete(route('csrstocks.destroy', this.stockId), {
        preserveScroll: true,
        onSuccess: () => {
          this.stocksList = [];
          this.deleteStockDialog = false;
          this.stockId = null;
          this.form.clearErrors();
          this.form.reset();
          this.updateData();
          this.deletedMsg();
          this.storeStocksInContainer();
        },
      });
    },
    cancel() {
      this.stockId = null;
      this.isUpdate = false;
      this.createStockDialog = false;
      this.form.reset();
      this.form.clearErrors();
      this.stocksList = [];
      this.storeStocksInContainer();
    },
    createdMsg() {
      this.$toast.add({ severity: 'success', summary: 'Success', detail: 'Stock created', life: 3000 });
    },
    updatedMsg() {
      this.$toast.add({ severity: 'warn', summary: 'Success', detail: 'Stock updated', life: 3000 });
    },
    deletedMsg() {
      this.$toast.add({ severity: 'error', summary: 'Success', detail: 'Stock deleted', life: 3000 });
    },
  },
  watch: {
    search: function (val, oldVal) {
      this.params.search = val;
      this.updateData();
    },
    from_md: function (val) {
      if (val != null) {
        let from_md = val;
        this.params.from_md = from_md;
      } else {
        this.params.from_md = null;
        this.from_md = null;
      }
      this.updateData();
    },
    to_md: function (val) {
      if (val != null) {
        let to_md = val;
        this.params.to_md = to_md;
      } else {
        this.params.to_md = null;
        this.to_md = null;
      }
      this.updateData();
    },
    from_dd: function (val) {
      if (val != null) {
        let from_dd = val;
        this.params.from_dd = from_dd;
      } else {
        this.params.from_dd = null;
        this.from_dd = null;
      }
      this.updateData();
    },
    to_dd: function (val) {
      if (val != null) {
        let to_dd = val;
        this.params.to_dd = to_dd;
      } else {
        this.params.to_dd = null;
        this.to_dd = null;
      }
      this.updateData();
    },
    from_ed: function (val) {
      if (val != null) {
        let from_ed = val;
        this.params.from_ed = from_ed;
      } else {
        this.params.from_ed = null;
        this.from_ed = null;
      }
      this.updateData();
    },
    to_ed: function (val) {
      if (val != null) {
        let to_ed = val;
        this.params.to_ed = to_ed;
      } else {
        this.params.to_ed = null;
        this.to_ed = null;
      }
      this.updateData();
    },
  },
};
</script>

<style>
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
