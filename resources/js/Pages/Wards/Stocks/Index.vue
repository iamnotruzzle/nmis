<template>
  <app-layout>
    <Head title="Template - Stocks" />

    <div class="card">
      <Toast />
      <!--
            data table sort order
            asc = 1
            desc =-1
        -->
      <DataTable
        class="p-datatable-sm"
        v-model:expandedRows="expandedRows"
        v-model:filters="filters"
        :value="requestStockList"
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
            <span class="text-xl text-900 font-bold text-cyan-500 hover:text-cyan-700">REQUESTED STOCKS</span>
            <div>
              <span class="p-input-icon-left mr-2">
                <i class="pi pi-search" />
                <InputText
                  v-model="search"
                  placeholder="Search requested by"
                />
              </span>
              <Button
                label="Request stocks"
                icon="pi pi-plus"
                iconPos="right"
                @click="openCreateRequestStocksDialog"
              />
            </div>
          </div>
        </template>
        <Column
          expander
          style="width: 5rem"
        />
        <template #empty> No requested stock found. </template>
        <template #loading> Loading requested stock data. Please wait. </template>
        <Column
          header="CREATED AT"
          filterField="created_at"
          :showFilterMenu="false"
          style="max-width: 5rem"
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
          field="status"
          header="STATUS"
        >
          <template #body="{ data }">
            <div class="flex justify-content-center align-content-center">
              <Tag
                :value="data.status"
                :severity="getSeverity(data.status)"
                class="mr-4"
              />
              <div>
                <i
                  v-if="data.status == 'FILLED'"
                  class="pi pi-check"
                  style="color: skyblue"
                  @click="editStatus(data)"
                ></i>
              </div>
            </div>
          </template>
        </Column>
        <Column
          field="requested_by"
          header="REQUESTED BY"
        >
          <template #body="{ data }">
            <div class="flex flex-row align-items-center">
              <img
                v-if="data.image != null"
                :src="`/storage/${data.image}`"
                class="w-4rem h-4rem rounded-card"
              />
              <img
                v-else
                src="storage/no_profile.png"
                class="w-10rem h-10rem rounded-card"
              />

              <span class="font-semibold text-xl pl-2">
                {{ data.requested_by }}
              </span>
            </div>
          </template>
        </Column>
        <Column header="ACTION">
          <template #body="slotProps">
            <Button
              v-if="slotProps.data.status == 'REQUESTED'"
              icon="pi pi-pencil"
              class="mr-1"
              rounded
              text
              severity="warning"
              @click="editRequestedStock(slotProps.data)"
            />

            <Button
              v-if="slotProps.data.status == 'REQUESTED'"
              icon="pi pi-trash"
              rounded
              text
              severity="danger"
              @click="confirmDeleteItem(slotProps.data)"
            />
          </template>
        </Column>
        <template #expansion="slotProps">
          <div class="p-3">
            <h5 class="text-cyan-500 hover:text-cyan-700">ITEMS</h5>
            <DataTable
              paginator
              :rows="7"
              :value="slotProps.data.request_stocks_details"
            >
              <Column
                field="item"
                header="ITEM"
              >
                <template #body="{ data }">
                  {{ data.item_details.cl2desc }}
                </template>
              </Column>
              <Column
                field="requested_qty"
                header="REQUESTED QTY"
              ></Column>
              <Column
                field="approved_qty"
                header="APPROVED QTY"
              ></Column>
            </DataTable>
          </div>
        </template>
      </DataTable>

      <!-- current ward stocks -->
      <DataTable
        class="p-datatable-sm mt-8"
        dataKey="id"
        v-model:filters="currentWardStocksFilter"
        :value="currentWardStocksList"
        paginator
        :rows="10"
        :rowsPerPageOptions="[10, 30, 50]"
        removableSort
        sortField="expiration_date"
        :sortOrder="1"
        filterDisplay="row"
        showGridlines
        :loading="loading"
      >
        <template #header>
          <div class="flex flex-wrap align-items-center justify-content-between gap-2">
            <span class="text-xl text-900 font-bold text-cyan-500 hover:text-cyan-700">CURRENT STOCKS</span>
            <div>
              <span class="p-input-icon-left mr-2">
                <i class="pi pi-search" />
                <span class="p-input-icon-left">
                  <i class="pi pi-search" />
                  <InputText
                    v-model="currentWardStocksFilter['global'].value"
                    placeholder="Search item"
                  />
                </span>
              </span>
            </div>
          </div>
        </template>
        <template #empty> No item found. </template>
        <template #loading> Loading item data. Please wait. </template>
        <Column
          field="brand"
          header="BRAND"
          sortable
        >
          <template #body="{ data }">
            {{ data.brand }}
          </template>
        </Column>
        <Column
          field="item"
          header="ITEM"
          sortable
        >
          <template #body="{ data }">
            {{ data.item }}
          </template>
        </Column>
        <Column
          field="quantity"
          header="QUANTITY"
          sortable
        >
          <template #body="{ data }">
            {{ data.quantity }}
          </template>
        </Column>
        <Column
          field="expiration_date"
          header="EXPIRATION DATE"
          sortable
        >
          <template #body="{ data }">
            {{ tzone2(data.expiration_date) }}
          </template>
        </Column>
        <Column header="ACTION">
          <template #body="slotProps">
            <Button
              icon="pi pi-pencil"
              class=""
              rounded
              text
              severity="warning"
              @click="editWardStocks(slotProps.data)"
            />
          </template>
        </Column>
      </DataTable>

      <!-- @hide="clickOutsideDialog" -->
      <!-- create & edit dialog -->
      <Dialog
        v-model:visible="createRequestStocksDialog"
        header="Request stock"
        :modal="true"
        class="p-fluid"
        @hide="whenDialogIsHidden"
      >
        <div class="field">
          <label>Item</label>
          <Dropdown
            required="true"
            v-model="item"
            :options="itemsList"
            filter
            optionLabel="cl2desc"
            class="w-full mb-3"
          />
        </div>
        <div class="field">
          <label for="Item">Quantity</label>
          <InputText
            id="quantity"
            v-model.trim="requested_qty"
            required="true"
            autofocus
            type="number"
            :class="{ 'p-invalid': requested_qty == '' || item == null }"
            @keyup.enter="fillRequestContainer"
          />
          <small
            class="text-error"
            v-if="itemNotSelected == true"
          >
            {{ itemNotSelectedMsg }}
          </small>
        </div>
        <div class="field mt-8">
          <label class="mr-2">Requested stock list</label>
          <i
            class="pi pi-shopping-cart text-blue-500"
            style="font-size: 1.5rem"
          />
          <DataTable
            v-model:filters="requestStockListDetailsFilter"
            :globalFilterFields="['cl2desc']"
            :value="requestStockListDetails"
            tableStyle="min-width: 50rem"
            class="p-datatable-sm"
            paginator
            :rows="7"
          >
            <template #header>
              <div class="flex justify-content-end">
                <span class="p-input-icon-left">
                  <i class="pi pi-search" />
                  <InputText
                    v-model="requestStockListDetailsFilter['global'].value"
                    placeholder="Search Item"
                  />
                </span>
              </div>
            </template>
            <Column
              field="cl2desc"
              header="REQUESTED ITEM"
              sortable
            ></Column>
            <Column
              field="requested_qty"
              header="REQUESTED QTY"
              sortable
            ></Column>
            <Column header="">
              <template #body="slotProps">
                <Button
                  icon="pi pi-times"
                  rounded
                  text
                  severity="danger"
                  @click="removeFromRequestContainer(slotProps.data)"
                />
              </template>
            </Column>
          </DataTable>
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
            :disabled="form.processing || requestStockListDetails == '' || requestStockListDetails == null"
            @click="submit"
          />
          <Button
            v-else
            label="Save"
            icon="pi pi-check"
            text
            type="submit"
            :disabled="form.processing || requestStockListDetails == '' || requestStockListDetails == null"
            @click="submit"
          />
        </template>
      </Dialog>

      <!-- edit status confirmation dialog -->
      <Dialog
        v-model:visible="editStatusDialog"
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
          <span v-if="form">
            Are you sure you want to <b>update</b> the status of this requested stocks to <b>RECEIVED</b>?
          </span>
        </div>
        <template #footer>
          <Button
            label="No"
            icon="pi pi-times"
            class="p-button-text"
            @click="editStatusDialog = false"
          />
          <Button
            label="Yes"
            icon="pi pi-check"
            severity="danger"
            text
            @click="updateStatus"
          />
        </template>
      </Dialog>

      <!-- Delete confirmation dialog -->
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
          <span v-if="form">Are you sure you want to delete this request?</span>
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

      <!-- ward stock dialog -->
      <Dialog
        v-model:visible="editWardStocksDialog"
        header="Update stock"
        :modal="true"
        class="p-fluid w-4"
        @hide="whenDialogIsHidden"
        dismissableMask
      >
        <div class="field">
          <label for="brand">Brand</label>
          <InputText
            id="brand"
            v-model.trim="formWardStocks.brand"
            readonly
            class="w-full"
          />
        </div>
        <div class="field">
          <label for="item">Item</label>
          <InputText
            id="item"
            v-model.trim="formWardStocks.item"
            readonly
            class="w-full"
          />
        </div>
        <div class="field">
          <label for="quantity">Quantity</label>
          <InputText
            id="quantity"
            v-model.trim="formWardStocks.quantity"
            autofocus
            class="w-full"
          />
        </div>
        <div class="field">
          <label for="expiration_date">Expiration date</label>
          <InputText
            id="expiration_date"
            v-model.trim="formWardStocks.expiration_date"
            readonly
            class="w-full"
          />
        </div>
        <div class="field">
          <label for="remarks">Remarks <span class="text-error">(Required)</span></label>
          <TextArea
            v-model.trim="formWardStocks.remarks"
            rows="5"
            autofocus
            :class="{ 'p-invalid': formWardStocks.remarks == '' }"
          />
          <small
            class="text-error"
            v-if="formWardStocks.errors.remarks"
          >
            {{ formWardStocks.errors.remarks }}
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
            label="Update"
            icon="pi pi-check"
            severity="warning"
            text
            type="submit"
            :disabled="
              formWardStocks.processing ||
              formWardStocks.quantity == null ||
              formWardStocks.quantity == '' ||
              Number(formWardStocks.current_quantity) <= Number(formWardStocks.quantity)
            "
            @click="submitEditWardStocks"
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
import TextArea from 'primevue/textarea';
import Tag from 'primevue/tag';
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
    Tag,
    TextArea,
  },
  props: {
    authWardcode: Object,
    items: Object,
    requestedStocks: Object,
    currentWardStocks: Object,
  },
  data() {
    return {
      expandedRows: null,
      // paginator
      loading: false,
      totalRecords: null,
      rows: null,
      // end paginator
      requestStockId: null,
      isUpdate: false,
      createRequestStocksDialog: false,
      editWardStocksDialog: false,
      editStatusDialog: false,
      deleteItemDialog: false,
      search: '',
      options: {},
      params: {},
      from: null,
      to: null,
      itemsList: [],
      requestStockList: [],
      currentWardStocksList: [],
      // stock list details
      requestStockListDetailsFilter: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
      currentWardStocksFilter: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
      requestStockListDetails: [],
      item: null,
      cl2desc: null,
      requested_qty: null,
      approved_qty: null,
      itemNotSelected: false,
      itemNotSelectedMsg: null,
      // end stock list details
      filters: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
      form: this.$inertia.form({
        request_stocks_id: null,
        location: null,
        requested_by: null,
        requestStockListDetails: [],
      }),
      formUpdateStatus: this.$inertia.form({
        request_stock_id: null,
        status: null,
      }),
      formWardStocks: this.$inertia.form({
        ward_stock_id: null,
        brand: null,
        item: null,
        current_quantity: null,
        quantity: null,
        expiration_date: null,
        remarks: null,
      }),
    };
  },
  // created will be initialize before mounted
  created() {
    this.totalRecords = this.requestedStocks.total;
    this.params.page = this.requestedStocks.current_page;
    this.rows = this.requestedStocks.per_page;
  },
  mounted() {
    console.log(this.requestedStocks.data);

    this.storeItemsInController();
    this.storeRequestedStocksInContainer();
    this.storeCurrentWardStocksInContainer();

    this.loading = false;
  },
  computed: {
    user() {
      return this.$page.props.auth.user;
    },
  },
  methods: {
    storeItemsInController() {
      this.items.forEach((e) => {
        this.itemsList.push({
          cl2comb: e.cl2comb,
          cl2desc: e.cl2desc,
        });
      });
    },
    // use storeRequestedStocksInContainer() function so that every time you make
    // server request such as POST, the data in the table
    // is updated
    storeRequestedStocksInContainer() {
      this.requestedStocks.data.forEach((e) => {
        this.requestStockList.push({
          id: e.id,
          status: e.status,
          requested_by:
            e.requested_by_details.firstname +
            ' ' +
            e.requested_by_details.middlename +
            ' ' +
            e.requested_by_details.lastname,
          image: e.requested_by_details.user_account.image,
          created_at: e.created_at,
          request_stocks_details: e.request_stocks_details,
        });
      });
    },
    // store current stocks
    storeCurrentWardStocksInContainer() {
      moment.suppressDeprecationWarnings = true;

      this.currentWardStocks.forEach((e) => {
        let expiration_date = moment.tz(e.expiration_date, 'Asia/Manila').format('MM/DD/YYYY');

        this.currentWardStocksList.push({
          ward_stock_id: e.id,
          brand: e.brand_details.name,
          item: e.item_details.cl2desc,
          quantity: e.quantity,
          expiration_date: expiration_date.toString(),
        });
      });
    },
    tzone(date) {
      return moment.tz(date, 'Asia/Manila').format('LL');
    },
    tzone2(date) {
      return moment.tz(date, 'Asia/Manila').format('L');
    },
    getSeverity(status) {
      switch (status) {
        case 'REQUESTED':
          return 'primary';

        case 'FILLED':
          return 'info';

        case 'RECEIVED':
          return 'success';

        default:
          return null;
      }
    },
    onPage(event) {
      this.params.page = event.page + 1;
      this.updateData();
    },
    updateData() {
      this.usersList = [];
      this.loading = true;

      this.$inertia.get('requeststocks', this.params, {
        preserveState: true,
        preserveScroll: true,
        onFinish: (visit) => {
          this.totalRecords = this.requestedStocks.total;
          this.requestStockList = [];
          this.currentWardStocksList = [];
          this.storeRequestedStocksInContainer();
          this.storeCurrentWardStocksInContainer();
          this.loading = false;
          this.formUpdateStatus.reset();
        },
      });
    },
    openCreateRequestStocksDialog() {
      this.isUpdate = false;
      this.form.clearErrors();
      this.form.reset();
      this.requestStockId = null;
      this.createRequestStocksDialog = true;
    },
    // when dialog is hidden, do this function
    whenDialogIsHidden() {
      this.$emit(
        'hide',
        (this.requestStockId = null),
        (this.isUpdate = false),
        (this.requestStockListDetails = []),
        (this.item = null),
        (this.cl2desc = null),
        (this.requested_qty = null),
        (this.approved_qty = null),
        (this.itemNotSelected = null),
        (this.itemNotSelectedMsg = null),
        this.form.clearErrors(),
        this.form.reset(),
        this.formWardStocks.clearErrors(),
        this.formWardStocks.reset(),
        this.formUpdateStatus.reset()
      );
    },
    fillRequestContainer() {
      // check if no selected item
      if (this.item == null || this.item == '') {
        this.itemNotSelected = true;
        this.itemNotSelectedMsg = 'Item not selected.';
      } else {
        // check if request qty is not provided
        if (this.requested_qty == 0 || this.requested_qty == null || this.requested_qty == '') {
          this.itemNotSelected = true;
          this.itemNotSelectedMsg = 'Please provide quantity.';
        } else {
          // check if item selected is already on the list
          if (this.requestStockListDetails.some((e) => e.cl2comb === this.item['cl2comb'])) {
            this.itemNotSelected = true;
            this.itemNotSelectedMsg = 'Item is already on the list.';
          } else {
            this.itemNotSelected = false;
            this.itemNotSelectedMsg = null;
            this.requestStockListDetails.push({
              cl2comb: this.item['cl2comb'],
              cl2desc: this.item['cl2desc'],
              requested_qty: this.requested_qty,
            });
          }
        }
      }
      //   console.log(this.requestStockListDetails);
    },
    removeFromRequestContainer(item) {
      this.requestStockListDetails.splice(
        this.requestStockListDetails.findIndex((e) => e.cl2comb === item.cl2comb),
        1
      );
    },
    editRequestedStock(item) {
      this.form.request_stocks_id = item.id;

      this.isUpdate = true;
      this.createRequestStocksDialog = true;
      this.requestStockId = item.id;

      item.request_stocks_details.forEach((e) => {
        this.requestStockListDetails.push({
          request_stocks_details_id: e.id,
          cl2comb: e.cl2comb,
          cl2desc: e.item_details.cl2desc,
          requested_qty: e.requested_qty,
        });
      });
    },
    editStatus(item) {
      //   console.log(item);
      this.editStatusDialog = true;
      this.formUpdateStatus.request_stock_id = item.id;
      this.formUpdateStatus.status = 'RECEIVED';
    },
    updateStatus() {
      //   console.log(item);
      //   this.formUpdateStatus.status = item;

      this.formUpdateStatus.put(route('requeststocks.updatedeliverystatus', this.formUpdateStatus), {
        preserveScroll: true,
        onSuccess: () => {
          this.requestStockId = null;
          this.editStatusDialog = false;
          this.cancel();
          this.updateData();
          this.updatedStatusMsg();
        },
      });
    },
    submit() {
      // setup location, requested by and requestStockListDetails before submitting
      this.form.location = this.authWardcode.wardcode;
      this.form.requested_by = this.user.userDetail.employeeid;
      this.form.requestStockListDetails = this.requestStockListDetails;

      if (this.isUpdate) {
        this.form.put(route('requeststocks.update', this.requestStockId), {
          preserveScroll: true,
          onSuccess: () => {
            this.requestStockId = null;
            this.createRequestStocksDialog = false;
            this.cancel();
            this.updateData();
            this.updatedMsg();
          },
        });
      } else {
        this.form.post(route('requeststocks.store'), {
          preserveScroll: true,
          onSuccess: () => {
            this.requestStockId = null;
            this.createRequestStocksDialog = false;
            this.cancel();
            this.updateData();
            this.createdMsg();
          },
        });
      }
    },
    confirmDeleteItem(item) {
      this.requestStockId = item.id;
      this.deleteItemDialog = true;
    },
    deleteItem() {
      this.form.delete(route('requeststocks.destroy', this.requestStockId), {
        preserveScroll: true,
        onSuccess: () => {
          this.requestStockList = [];
          this.deleteItemDialog = false;
          this.requestStockId = null;
          this.form.clearErrors();
          this.form.reset();
          this.updateData();
          this.deletedMsg();
        },
      });
    },
    cancel() {
      this.requestStockId = null;
      this.isUpdate = false;
      this.createRequestStocksDialog = false;
      this.editWardStocksDialog = false;
      this.form.reset();
      this.form.clearErrors();
      this.formWardStocks.reset();
      this.formWardStocks.clearErrors();
    },
    createdMsg() {
      this.$toast.add({ severity: 'success', summary: 'Success', detail: 'Stock request created', life: 3000 });
    },
    updatedMsg() {
      this.$toast.add({ severity: 'warn', summary: 'Success', detail: 'Stock request updated', life: 3000 });
    },
    updatedStatusMsg() {
      this.$toast.add({ severity: 'warn', summary: 'Success', detail: 'Changed requested stocks status', life: 3000 });
    },
    deletedMsg() {
      this.$toast.add({ severity: 'error', summary: 'Success', detail: 'Stock request deleted', life: 3000 });
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
    // ward stocks logs
    editWardStocks(data) {
      //   console.log(data);
      this.editWardStocksDialog = true;

      this.formWardStocks.ward_stock_id = data.ward_stock_id;
      this.formWardStocks.brand = data.brand;
      this.formWardStocks.item = data.item;
      this.formWardStocks.current_quantity = data.quantity;
      this.formWardStocks.quantity = data.quantity;
      this.formWardStocks.expiration_date = data.expiration_date;
    },
    submitEditWardStocks() {
      this.formWardStocks.post(route('wardsstockslogs.store'), {
        preserveScroll: true,
        onSuccess: () => {
          this.editWardStocksDialog = false;
          this.cancel();
          this.updateData();
          this.updatedStockMsg();
        },
      });
    },
    updatedStockMsg() {
      this.$toast.add({ severity: 'warn', summary: 'Success', detail: 'Stock updated', life: 3000 });
    },
    // end ward stocks logs
  },
  watch: {
    search: function (val, oldVal) {
      this.params.search = val;
      this.updateData();
    },
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
.rounded-card {
  border-radius: 50%;
  /* min-height: 100px;
  min-width: 100px; */
}
</style>
