<template>
  <app-layout>
    <Head title="InvenTrackr - Stocks" />

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
            <div>
              <span class="text-xl text-900 font-bold text-cyan-500 hover:text-cyan-700 mr-2">STOCKS</span>
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
        >
        </Column>
        <Column
          field="chrgdesc"
          header="FUND SOURCE"
        >
        </Column>
        <Column
          field="cl2desc"
          header="ITEM"
        >
        </Column>
        <Column
          field="brand_name"
          header="BRAND"
        >
        </Column>
        <Column
          field="quantity"
          header="QTY"
        >
        </Column>
        <Column
          field="manufactured_date"
          header="MFD. DATE"
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
          header="DD. DATE"
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
              placeholder="TO"
              showIcon
              showButtonBar
              :hideOnDateTimeSelect="true"
            />
          </template>
        </Column>
        <Column
          field="expiration_date"
          header="EXP. DATE"
          :showFilterMenu="false"
        >
          <template #body="{ data }">
            <div class="flex flex-column">
              <div>
                {{ tzone(data.expiration_date) }}
              </div>

              <div class="mt-2">
                <span class="text-lg text-error">{{ checkIfAboutToExpire(data.expiration_date) }}</span>
              </div>
            </div>
          </template>
          <template #filter="{}">
            <Calendar
              v-model="from_ed"
              placeholder="FROM"
              showIcon
              showButtonBar
              :hideOnDateTimeSelect="true"
            />
            <div class="mt-2"></div>
            <Calendar
              v-model="to_ed"
              placeholder="TO"
              showIcon
              showButtonBar
              :hideOnDateTimeSelect="true"
            />
          </template>
        </Column>
        <Column header="STATUS">
          <template #body="slotProps">
            <div class="flex flex-column">
              <div>
                <Tag
                  v-if="slotProps.data.quantity > 30"
                  value="INSTOCK"
                  severity="success"
                />
                <Tag
                  v-else-if="slotProps.data.quantity >= 1 && slotProps.data.quantity <= 30"
                  value="LOWSTOCK"
                  severity="warning"
                />
                <Tag
                  v-else
                  value="OUTOFSTOCK"
                  severity="danger"
                />
              </div>
            </div>
          </template>
        </Column>
        <Column header="ACTION">
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
          <label for="fundSource">Fund source</label>
          <Dropdown
            id="fundSource"
            required="true"
            v-model="form.fund_source"
            :options="fundSourceList"
            filter
            showClear
            dataKey="chrgcode"
            optionLabel="chrgdesc"
            optionValue="chrgcode"
            class="w-full"
            :class="{ 'p-invalid': form.fund_source == '' }"
          />
          <small
            class="text-error"
            v-if="form.errors.fund_source"
          >
            {{ form.errors.fund_source }}
          </small>
        </div>
        <div class="field">
          <label for="Item">Item</label>
          <Dropdown
            required="true"
            v-model="form.cl2comb"
            :options="itemsList"
            filter
            dataKey="unit"
            optionLabel="cl2desc"
            optionValue="cl2comb"
            class="w-full"
            :class="{ 'p-invalid': form.cl2comb == '' }"
          />
          <small
            class="text-error"
            v-if="form.errors.cl2comb"
          >
            The item field is required.
          </small>
        </div>
        <div class="field">
          <label for="brand">Brand</label>
          <Dropdown
            required="true"
            v-model="form.brand"
            :options="brandDropDownList"
            filter
            showClear
            dataKey="id"
            optionLabel="name"
            optionValue="id"
            class="w-full mb-3"
            :class="{ 'p-invalid': form.brand == '' }"
          />
          <small
            class="text-error"
            v-if="form.errors.brand"
          >
            {{ form.errors.brand }}
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
            :minDate="minimumDate"
            :hideOnDateTimeSelect="true"
          />
          <small
            class="text-error"
            v-if="form.errors.expiration_date"
          >
            {{ form.errors.expiration_date }}
          </small>
        </div>

        <div
          v-if="isUpdate == true"
          class="field"
        >
          <label for="remarks">Remarks <span class="text-error">(Required)</span></label>
          <Textarea
            v-model.trim="form.remarks"
            rows="5"
            autofocus
            :class="{ 'p-invalid': form.remarks == '' }"
          />
          <small
            class="text-error"
            v-if="form.errors.remarks"
          >
            {{ form.errors.remarks }}
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
            :disabled="form.processing || form.remarks == '' || form.remarks == null"
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

        <div class="field mt-5 flex flex-column">
          <label for="remarks">REMARKS <span class="text-error">(REQUIRED)</span></label>
          <Textarea
            v-model.trim="form.remarks"
            rows="5"
            autofocus
          />
          <small
            class="text-error"
            v-if="form.errors.remarks"
          >
            {{ form.errors.remarks }}
          </small>
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
            :disabled="form.remarks == '' || form.remarks == null"
            @click="deleteItem"
          />
        </template>
      </Dialog>

      <!-- brand -->
      <DataTable
        class="p-datatable-sm mt-8"
        dataKey="id"
        v-model:filters="brandFilters"
        :value="brandsList"
        paginator
        :rows="10"
        :rowsPerPageOptions="[10, 20, 30]"
        removableSort
        sortField="name"
        :sortOrder="1"
        filterDisplay="row"
        showGridlines
      >
        <template #header>
          <div class="flex flex-wrap align-items-center justify-content-between gap-2">
            <span class="text-xl text-900 font-bold text-cyan-500 hover:text-cyan-700">BRANDS</span>
            <div>
              <span class="p-input-icon-left mr-2">
                <i class="pi pi-search" />
                <span class="p-input-icon-left">
                  <i class="pi pi-search" />
                  <InputText
                    v-model="brandFilters['global'].value"
                    placeholder="Search brand"
                  />
                </span>
              </span>
              <Button
                label="Add brand"
                icon="pi pi-plus"
                iconPos="right"
                @click="openCreateBrandDialog"
              />
            </div>
          </div>
        </template>
        <template #empty> No brand found. </template>
        <template #loading> Loading brand data. Please wait. </template>
        <Column
          field="name"
          header="NAME"
          sortable
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.name }}
          </template>
        </Column>
        <Column
          field="status"
          header="STATUS"
          sortable
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            <Tag
              v-if="data.status == 'A'"
              value="ACTIVE"
              severity="success"
            />
            <Tag
              v-else
              value="INACTIVE"
              severity="danger"
            />
          </template>
        </Column>
        <Column
          header="ACTION"
          style="min-width: 12rem"
        >
          <template #body="slotProps">
            <Button
              v-if="slotProps.data.name != 'NO BRAND'"
              icon="pi pi-pencil"
              class="mr-1"
              rounded
              text
              severity="warning"
              @click="editBrand(slotProps.data)"
            />

            <!-- <Button
              icon="pi pi-trash"
              rounded
              text
              severity="danger"
              @click="confirmDeleteItem(slotProps.data)"
            /> -->
          </template>
        </Column>
      </DataTable>

      <!-- create & edit dialog -->
      <Dialog
        v-model:visible="createBrandDialog"
        :style="{ width: '450px' }"
        header="Stock Detail"
        :modal="true"
        class="p-fluid"
        @hide="clickOutsideDialog"
        dismissableMask
      >
        <div class="field">
          <label for="brand_name">Brand name</label>
          <InputText
            id="brand_name"
            v-model.trim="formBrand.name"
            required="true"
            autofocus
            :class="{ 'p-invalid': formBrand.name == '' }"
          />
          <small
            class="text-error"
            v-if="formBrand.errors.name"
          >
            {{ formBrand.errors.name }}
          </small>
        </div>

        <div class="field">
          <label for="status">Status</label>
          <Dropdown
            v-model="formBrand.status"
            :options="brandStatus"
            optionLabel="name"
            optionValue="value"
            class="w-full md:w-14rem"
          />
          <small
            class="text-error"
            v-if="formBrand.errors.status"
          >
            {{ formBrand.errors.status }}
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
            v-if="isUpdateBrand == true"
            label="Update"
            icon="pi pi-check"
            severity="warning"
            text
            type="submit"
            :disabled="formBrand.processing"
            @click="submitBrand"
          />
          <Button
            v-else
            label="Save"
            icon="pi pi-check"
            text
            type="submit"
            :disabled="formBrand.processing"
            @click="submitBrand"
          />
        </template>
      </Dialog>
      <!-- end brand -->
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
import Tag from 'primevue/tag';
import Textarea from 'primevue/textarea';

import moment, { now } from 'moment';

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
    Textarea,
  },
  props: {
    items: Object,
    stocks: Object,
    brands: Object,
  },
  data() {
    return {
      minimumDate: null,
      // paginator
      loading: false,
      totalRecords: null,
      rows: null,
      // end paginator
      stockId: null,
      isUpdate: false,
      isUpdateBrand: false,
      createStockDialog: false,
      createBrandDialog: false,
      deleteStockDialog: false,
      deleteBrandDialog: false,
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
      fundSourceList: [],
      itemsList: [],
      brandsList: [],
      brandDropDownList: [],
      stocksList: [],
      filters: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
      brandFilters: {
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
        fund_source: null,
        cl2comb: null,
        brand: null,
        cl2desc: null,
        quantity: null,
        manufactured_date: null,
        delivered_date: null,
        expiration_date: null,
        remarks: null,
      }),
      formBrand: this.$inertia.form({
        id: null,
        name: null,
        status: null,
      }),
      brandStatus: [
        {
          name: 'ACTIVE',
          value: 'A',
        },
        {
          name: 'INACTIVE',
          value: 'I',
        },
      ],
    };
  },
  // created will be initialize before mounted
  created() {
    this.totalRecords = this.stocks.total;
    this.params.page = this.stocks.current_page;
    this.rows = this.stocks.per_page;
  },
  mounted() {
    // console.log(this.stocks);

    this.setMinimumDate();
    this.storeFundSourceInContainer();
    this.storeItemsInContainer();
    this.storeStocksInContainer();
    this.storeBrandsInContainer();
    this.storeActiveBrandsInContainer();

    this.loading = false;
  },
  methods: {
    generateReport() {
      this.$inertia.get('csrstocks/export/');
    },
    tzone(date) {
      if (date == null || date == '') {
        return null;
      } else {
        return moment.tz(date, 'Asia/Manila').format('LL');
      }
    },
    checkIfAboutToExpire(date) {
      let current_date = moment.tz(moment(), 'Asia/Manila');
      let exp_date = moment.tz(date, 'Asia/Manila');

      // adding +1 to include the starting date
      let date_diff = exp_date.diff(current_date, 'days') + 1;

      //   console.log(current_date.format('MM-DD-YY') == exp_date.format('MM-DD-YY'));

      if (
        current_date.format('MM-DD-YY') == exp_date.format('MM-DD-YY') ||
        exp_date.format('MM-DD-YY') < current_date.format('MM-DD-YY')
      ) {
        return 'Item has expired.';
      } else if (date_diff == 1) {
        return date_diff + ' day remaining.';
      } else {
        return date_diff + ' days remaining.';
      }
    },
    setMinimumDate() {
      this.minimumDate = new Date();
      let returnVal = 0;
      let dateToday = new Date();
      let getDate = dateToday.getDate();
      let getHour = dateToday.getHours();

      if (getHour >= 12 && getDate == 1) {
        this.minimumDate.setDate(dateToday.getDate() + 14);
      } else if (getHour >= 12 && getDate == 15) {
        this.minimumDate.setMonth(dateToday.getMonth() + 1, 1);
      } else if (getHour < 12 && getDate == 13) {
        this.minimumDate.setMonth(dateToday.getMonth() + 1, 1);
      } else {
      }
    },
    storeFundSourceInContainer() {
      this.$page.props.typeOfCharge.forEach((e) => {
        this.fundSourceList.push({
          chrgcode: e.chrgcode,
          chrgdesc: e.chrgdesc,
          bentypcod: e.bentypcod,
          chrgtable: e.chrgtable,
        });
      });

      this.$page.props.fundSource.forEach((e) => {
        this.fundSourceList.push({
          chrgcode: e.fsid,
          chrgdesc: e.fsName,
          bentypcod: null,
          chrgtable: null,
        });
      });
    },
    storeItemsInContainer() {
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
          chrgcode: e.type_of_charge === null ? e.fund_source.fsid : e.type_of_charge.chrgcode,
          chrgdesc: e.type_of_charge === null ? e.fund_source.fsName : e.type_of_charge.chrgdesc,
          cl2comb: e.cl2comb,
          cl2desc: e.item_detail.cl2desc,
          brand_id: e.brand_detail.id,
          brand_name: e.brand_detail.name,
          quantity: e.quantity,
          manufactured_date: e.manufactured_date === null ? '' : e.manufactured_date,
          delivered_date: e.delivered_date === null ? '' : e.delivered_date,
          expiration_date: e.expiration_date === null ? '' : e.expiration_date,
        });
      });
      //   console.log(this.stocks);
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
          this.brandsList = [];
          this.brandDropDownList = [];
          this.storeStocksInContainer();
          this.storeBrandsInContainer();
          this.storeActiveBrandsInContainer();
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
      this.$emit(
        'hide',
        (this.stockId = null),
        (this.isUpdate = false),
        (this.isUpdateBrand = false),
        this.form.clearErrors(),
        this.form.reset(),
        this.formBrand.clearErrors(),
        this.formBrand.reset()
      );
    },
    editItem(item) {
      // console.log(item);
      this.isUpdate = true;
      this.createStockDialog = true;
      this.stockId = item.id;
      this.form.batch_no = item.batch_no;
      this.form.fund_source = item.chrgcode;
      this.form.cl2comb = item.cl2comb;
      this.form.brand = item.brand_id;
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
      this.isUpdateBrand = false;
      this.createStockDialog = false;
      this.createBrandDialog = false;
      this.form.reset();
      this.form.clearErrors();
      this.formBrand.reset();
      this.formBrand.clearErrors();
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
    // brand
    storeBrandsInContainer() {
      this.brands.forEach((e) => {
        this.brandsList.push({
          id: e.id,
          name: e.name,
          status: e.status,
        });
      });
    },
    // filtered list for dropdown, only show brand that is active
    storeActiveBrandsInContainer() {
      this.brandDropDownList = this.brands.filter((item) => item.status === 'A');
    },
    openCreateBrandDialog() {
      this.isUpdateBrand = false;
      this.formBrand.clearErrors();
      this.formBrand.reset();
      this.createBrandDialog = true;
    },
    editBrand(brand) {
      this.isUpdateBrand = true;
      this.createBrandDialog = true;
      this.formBrand.id = brand.id;
      this.formBrand.name = brand.name;
      this.formBrand.status = brand.status;
    },
    submitBrand() {
      if (this.isUpdateBrand) {
        this.formBrand.put(route('brands.update', this.formBrand.id), {
          preserveScroll: true,
          onSuccess: () => {
            this.createBrandDialog = false;
            this.cancel();
            this.updateData();
            this.updatedBrandMessage();
          },
        });
      } else {
        this.formBrand.post(route('brands.store'), {
          preserveScroll: true,
          onSuccess: () => {
            this.createBrandDialog = false;
            this.cancel();
            this.updateData();
            this.createdBrandMessage();
          },
        });
      }
    },
    createdBrandMessage() {
      this.$toast.add({ severity: 'success', summary: 'Success', detail: 'Brand created', life: 3000 });
    },
    updatedBrandMessage() {
      this.$toast.add({ severity: 'warn', summary: 'Success', detail: 'Brand updated', life: 3000 });
    },
    deleteBrandMessage() {
      this.$toast.add({ severity: 'error', summary: 'Success', detail: 'Brand deleted', life: 3000 });
    },
    // end brand
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
