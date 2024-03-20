<template>
  <app-layout>
    <Head title="NMIS - RIS" />

    <div class="card">
      <Toast />

      <div class="mb-2">
        <Link
          href="issueitems"
          class="text-2xl mr-2 my-link"
        >
          DRUG AND MEDS
        </Link>

        <Link
          href="issuetankitems"
          class="text-2xl border-bottom-2 font-semibold"
        >
          TANKS
        </Link>
      </div>

      <DataTable
        class="p-datatable-sm"
        v-model:expandedRows="expandedRow"
        v-model:filters="filters"
        :value="requestStockList"
        selectionMode="single"
        lazy
        paginator
        removableSort
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
            <span class="text-xl text-900 font-bold text-primary">REQUESTS</span>
            <div>
              <div class="p-inputgroup">
                <span class="p-inputgroup-addon">
                  <i class="pi pi-search"></i>
                </span>
                <InputText
                  id="searchInput"
                  v-model="search"
                  placeholder="Search requested at"
                />
              </div>
            </div>
          </div>
        </template>
        <Column expander />
        <template #empty> No requested stock found. </template>
        <template #loading> Loading requested stock data. Please wait. </template>
        <Column
          header="CREATED AT"
          filterField="created_at"
          style="width: 20%"
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
              :manualInput="false"
              :hideOnDateTimeSelect="true"
            />
            <div class="mt-2"></div>
            <Calendar
              v-model="to"
              dateFormat="mm-dd-yy"
              placeholder="TO"
              showIcon
              showButtonBar
              :manualInput="false"
              :hideOnDateTimeSelect="true"
            />
          </template>
        </Column>
        <Column
          field="status"
          header="STATUS"
          filterField="status"
          style="width: 10%"
          :showFilterMenu="false"
        >
          <template #body="{ data }">
            <div class="text-center">
              <Tag
                v-if="data.status == 'PENDING'"
                :value="data.status"
                class="bg-orange-400 text-gray-900"
              />
              <Tag
                v-if="data.status == 'ACKNOWLEDGED'"
                :value="data.status"
                class="bg-yellow-400 text-gray-900"
              />
              <Tag
                v-if="data.status == 'FILLED'"
                :value="data.status"
                class="bg-blue-400"
              />
              <Tag
                v-if="data.status == 'RECEIVED'"
                :value="data.status"
                class="bg-green-400 text-gray-900"
              />
              <Tag
                v-if="data.status == 'CANCELLED'"
                :value="data.status"
                style="background-color: rgb(239, 42, 42); color: rgb(253, 249, 249)"
              />
            </div>
          </template>
          <template #filter="{}">
            <Dropdown
              v-model="selectedStatus"
              :options="status"
              optionLabel="name"
              optionValue="code"
              placeholder="NO FILTER"
              class="w-full"
            />
          </template>
        </Column>
        <Column
          field="requested_by"
          header="PENDING BY"
          style="width: 20%"
        >
          <template #body="{ data }">
            <div class="flex flex-row align-items-center">
              <img
                v-if="data.requested_by_image != null"
                :src="`storage/${data.requested_by_image}`"
                class="w-3rem h-3rem rounded-card"
              />
              <img
                v-else
                src="images/no_profile.png"
                class="w-3rem h-3rem rounded-card"
              />

              <span class="font-semibold text-xl pl-3">
                {{ data.requested_by }}
              </span>
            </div>
          </template>
        </Column>
        <Column
          field="approved_by"
          header="APPROVED BY"
          style="width: 20%"
        >
          <template #body="{ data }">
            <div class="flex flex-row align-items-center">
              <img
                v-if="data.approved_by_image != null"
                :src="`storage/${data.approved_by_image}`"
                class="w-3rem h-3rem rounded-card"
              />

              <img
                v-if="data.approved_by != null && data.approved_by_image == null"
                src="images/no_profile.png"
                class="w-3rem h-3rem rounded-card"
              />

              <span class="font-semibold text-xl pl-3">
                {{ data.approved_by }}
              </span>
            </div>
          </template>
        </Column>
        <Column
          field="requested_at"
          header="PENDING AT"
          style="width: 20%"
        >
          <template #body="{ data }">
            {{ data.requested_at }}
          </template>
        </Column>
        <Column
          header="ACTION"
          style="width: 10%"
        >
          <template #body="slotProps">
            <div class="text-center">
              <Button
                v-if="slotProps.data.status == 'PENDING'"
                icon="pi pi-check"
                class="mr-1"
                rounded
                text
                severity="success"
                @click="editStatus(slotProps.data)"
              />
              <Button
                v-if="slotProps.data.status == 'ACKNOWLEDGED'"
                icon="pi pi-plus"
                class="mr-1"
                rounded
                text
                severity="primary"
                @click="openCreateRequestStocksDialog(slotProps.data)"
              />
              <Button
                v-if="slotProps.data.status == 'FILLED'"
                icon="pi pi-pencil"
                class="mr-1"
                rounded
                text
                severity="warning"
                @click="editRequestedStock(slotProps.data)"
              />
            </div>
          </template>
        </Column>
        <template #expansion="slotProps">
          <div class="p-3">
            <DataTable
              paginator
              showGridlines
              removableSort
              :rows="7"
              :value="slotProps.data.request_stocks_details"
            >
              <template #header>
                <div class="flex flex-wrap align-items-center justify-content-between gap-2">
                  <span class="text-primary">PENDING ITEMS</span>
                  <div class="flex flex-row align-items-center">
                    <a
                      v-if="slotProps.data.status != 'PENDING'"
                      :href="`issuetankitems/issued?from=${params.from}&to=${params.to}
                      &id=${(params.id = slotProps.data.id)}`"
                      target="_blank"
                      class="mr-3"
                    >
                      <i
                        class="pi pi-download"
                        :style="{ color: 'green', 'font-size': '1.2rem' }"
                      ></i>
                    </a>
                  </div>
                </div>
              </template>
              <Column
                field="item"
                header="ITEM"
                style="width: 50%"
              >
                <template #body="{ data }">
                  {{ getTankDesc(data) }}
                  <!-- {{ data.itemcode }} -->
                </template>
              </Column>
              <Column
                field="requested_qty"
                header="PENDING QTY"
                style="text-align: right; width: 20%"
              >
              </Column>
              <Column
                field="approved_qty"
                header="APPROVED QTY"
                style="text-align: right; width: 20%"
              ></Column>
              <Column
                field="remarks"
                header="REMARKS"
                style="width: 10%"
              ></Column>
            </DataTable>
          </div>
        </template>
      </DataTable>

      <!-- @hide="clickOutsideDialog" -->
      <!-- create & edit dialog -->
      <Dialog
        v-model:visible="createRequestStocksDialog"
        :modal="true"
        class="p-fluid"
        showGridlines
        @hide="whenDialogIsHidden"
      >
        <template #header>
          <div class="text-primary text-xl font-bold">REQUESTED STOCKS</div>
        </template>
        <div class="field">
          <DataTable
            v-model:filters="requestStockListDetailsFilter"
            :globalFilterFields="['itemDesc']"
            :value="requestStockListDetails"
            class="p-datatable-sm"
            paginator
            showGridlines
            removableSort
            :rows="7"
          >
            <template #header>
              <div>
                <div class="mr-2">
                  <div class="p-inputgroup">
                    <span class="p-inputgroup-addon">
                      <i class="pi pi-search"></i>
                    </span>
                    <InputText
                      id="searchInput"
                      v-model="requestStockListDetailsFilter['global'].value"
                      placeholder="Search item"
                    />
                  </div>
                </div>
              </div>
            </template>
            <Column
              field="itemDesc"
              header="PENDING ITEM"
              style="width: 50%"
              sortable
            ></Column>
            <Column
              field="requested_qty"
              header="PENDING QTY"
              style="text-align: right; width: 15%"
            ></Column>
            <Column
              field="approved_qty"
              header="APPROVED QTY"
              style="text-align: right; width: 15%"
            >
              <template #body="slotProps">
                <InputText
                  id="quantity"
                  v-model.trim="slotProps.data.approved_qty"
                  required="true"
                  autofocus
                  type="number"
                  @keyup.enter="submit"
                />
              </template>
            </Column>
            <Column
              field="remarks"
              header="REMARKS (OPTIONAL)"
              style="width: 20%"
            >
              <template #body="slotProps">
                <Textarea
                  v-model.trim="slotProps.data.remarks"
                  rows="2"
                  cols="30"
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

      <!-- edit status -->
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
            Are you sure you want to <b>update</b> the status of this requested stocks to <b>ACKNOWLEDGED</b>?
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
            :disabled="formUpdateStatus.processing"
            @click="updateStatus"
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
import Textarea from 'primevue/textarea';
import Tag from 'primevue/tag';
import moment from 'moment';
import Echo from 'laravel-echo';
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
    Textarea,
    Link,
  },
  props: {
    authWardcode: Object,
    tanksList: Object,
    // items: Object,
    requestedStocks: Object,
  },
  data() {
    return {
      expandedRow: [],
      // paginator
      loading: false,
      totalRecords: null,
      rows: null,
      // end paginator
      requestStockId: null,
      isUpdate: false,
      createRequestStocksDialog: false,
      editStatusDialog: false,
      search: '',
      options: {},
      params: {},
      from: null,
      to: null,
      itemsList: [],
      requestStockList: [],
      issuedItemList: [],
      // stock list details
      requestStockListDetailsFilter: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
      issuedItemsFilter: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
      disabled: false,
      app_qty_checker: null,
      requestStockListDetails: [],
      item: null,
      itemDesc: null,
      requested_qty: null,
      approved_qty: null,
      remarks: null,
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
        approved_by: null,
        remarks: null,
        requestStockListDetails: [],
      }),
      formUpdateStatus: this.$inertia.form({
        request_stock_id: null,
      }),
      selectedStatus: null,
      status: [
        { name: 'NO FILTER', code: '' },
        { name: 'PENDING', code: 'PENDING' },
        { name: 'ACKNOWLEDGED', code: 'ACKNOWLEDGED' },
        { name: 'FILLED', code: 'FILLED' },
        { name: 'RECEIVED', code: 'RECEIVED' },
        { name: 'CANCELLED', code: 'CANCELLED' },
      ],
    };
  },
  // created will be initialize before mounted
  created() {
    this.totalRecords = this.requestedStocks.total;
    this.params.page = this.requestedStocks.current_page;
    this.rows = this.requestedStocks.per_page;
  },
  mounted() {
    // console.log(this.requestedStocks);

    window.Echo.channel('requesttank').listen('RequestTankStock', (args) => {
      router.reload({
        onSuccess: (e) => {
          this.requestStockList = [];
          this.storeRequestedStocksInContainer();
        },
      });
    });

    // this.storeItemsInController();
    this.storeRequestedStocksInContainer();

    this.loading = false;
  },
  computed: {
    user() {
      return this.$page.props.auth.user;
    },
  },
  methods: {
    tzone(date) {
      if (date == null || date == '') {
        return null;
      } else {
        return moment.tz(date, 'Asia/Manila').format('L');
      }
    },
    // storeItemsInController() {
    //   this.items.forEach((e) => {
    //     this.itemsList.push({
    //       cl2comb: e.cl2comb,
    //       itemDesc: e.itemDesc,
    //     });
    //   });
    // },
    // use storeRequestedStocksInContainer() function so that every time you make
    // server request such as POST, the data in the table
    // is updated
    storeRequestedStocksInContainer() {
      //   console.log(this.requestedStocks);
      this.requestedStocks.data.forEach((e) => {
        this.requestStockList.push({
          id: e.id,
          status: e.status,
          requested_by: e.requested_by_details.firstname + ' ' + e.requested_by_details.lastname,
          requested_by_image: e.requested_by_details.user_account.image,
          approved_by:
            e.approved_by_details != null
              ? e.approved_by_details.firstname + ' ' + e.approved_by_details.lastname
              : null,
          approved_by_image: e.approved_by_details != null ? e.approved_by_details.user_account.image : null,
          requested_at: e.requested_at_details.wardname,
          created_at: e.created_at,
          request_stocks_details: e.request_stocks_details,
        });
      });
      //   console.log(this.requestStockList);
    },
    getTankDesc(item) {
      //   console.log(item);
      const matchingTank = this.tanksList.find((x) => item.itemcode === x.itemcode);

      return (
        matchingTank.gendesc +
        ' ' +
        matchingTank.dmdnost +
        // ' ' +
        // matchingTank.dmdnnostp +
        ' ' +
        matchingTank.stredesc +
        ' ' +
        matchingTank.formdesc +
        ' ' +
        matchingTank.rtedesc
      );
    },
    // getSeverity(status) {
    //   switch (status) {
    //     case 'PENDING':
    //       return 'primary';

    //     case 'ACKNOWLEDGED':
    //       return 'yellow';

    //     case 'FILLED':
    //       return 'info';

    //     case 'RECEIVED':
    //       return 'success';

    //     default:
    //       return null;
    //   }
    // },
    onPage(event) {
      this.params.page = event.page + 1;
      this.updateData();
    },
    updateData() {
      this.loading = true;

      this.$inertia.get('issuetankitems', this.params, {
        preserveState: true,
        preserveScroll: true,
        onFinish: (visit) => {
          this.totalRecords = this.requestedStocks.total;
          this.requestStockList = [];
          this.expandedRow = [];
          this.storeRequestedStocksInContainer();
          this.loading = false;
        },
      });
    },
    openCreateRequestStocksDialog(item) {
      //   console.log(item);
      this.form.clearErrors();
      this.form.reset();
      this.form.request_stocks_id = item.id;

      this.isUpdate = false;
      this.createRequestStocksDialog = true;
      this.requestStockId = item.id;

      item.request_stocks_details.forEach((e) => {
        const matchingTank = this.tanksList.find((x) => e.itemcode === x.itemcode);

        this.requestStockListDetails.push({
          request_stocks_details_id: e.id,
          itemcode: e.itemcode,
          itemDesc:
            matchingTank.gendesc +
            ' ' +
            matchingTank.dmdnost +
            // ' ' +
            // matchingTank.dmdnnostp +
            ' ' +
            matchingTank.stredesc +
            ' ' +
            matchingTank.formdesc +
            ' ' +
            matchingTank.rtedesc,
          requested_qty: e.requested_qty,
          approved_qty: e.approved_qty,
          remarks: e.remarks,
          //   stock_qty: e.stocks.reduce((accumulator, object) => {
          //     return Number(accumulator) + Number(object.quantity);
          //   }, 0),
        });
      });

      //   console.log(this.requestStockListDetails);
    },
    // when dialog is hidden, do this function
    whenDialogIsHidden() {
      this.$emit(
        'hide',
        (this.requestStockId = null),
        (this.isUpdate = false),
        (this.requestStockListDetails = []),
        (this.item = null),
        (this.itemDesc = null),
        (this.requested_qty = null),
        (this.approved_qty = null),
        (this.remarks = null),
        (this.itemNotSelected = null),
        (this.itemNotSelectedMsg = null),
        (this.issuedItemList = []),
        this.form.clearErrors(),
        this.form.reset()
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
              itemDesc: this.item['itemDesc'],
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

      //   console.log(item);
      this.form.clearErrors();
      this.form.reset();
      this.form.request_stocks_id = item.id;

      this.isUpdate = true;
      this.createRequestStocksDialog = true;
      this.requestStockId = item.id;

      item.request_stocks_details.forEach((e) => {
        const matchingTank = this.tanksList.find((x) => e.itemcode === x.itemcode);

        this.requestStockListDetails.push({
          request_stocks_details_id: e.id,
          itemcode: e.itemcode,
          itemDesc:
            matchingTank.gendesc +
            ' ' +
            matchingTank.dmdnost +
            // ' ' +
            // matchingTank.dmdnnostp +
            ' ' +
            matchingTank.stredesc +
            ' ' +
            matchingTank.formdesc +
            ' ' +
            matchingTank.rtedesc,
          requested_qty: e.requested_qty,
          approved_qty: e.approved_qty,
          remarks: e.remarks,
          //   stock_qty: e.stocks.reduce((accumulator, object) => {
          //     return Number(accumulator) + Number(object.quantity);
          //   }, 0),
        });
      });
    },
    submit() {
      if (this.form.processing) {
        return false;
      }

      // setup approved by and requestStockListDetails before submitting
      this.form.approved_by = this.user.userDetail.employeeid;
      this.form.requestStockListDetails = this.requestStockListDetails;

      // prevents submitting the form using enter key when this.disabled is true or the
      // approved qty <= total stock
      if (this.disabled != true) {
        if (this.isUpdate) {
          this.form.put(route('issuetankitems.update', this.requestStockId), {
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
          this.form.post(route('issuetankitems.store'), {
            preserveScroll: true,
            onSuccess: () => {
              this.requestStockId = null;
              this.createRequestStocksDialog = false;
              this.cancel();
              this.updateData();
              // this.createdMsg();
            },
          });
        }
      }
    },
    cancel() {
      this.requestStockId = null;
      this.isUpdate = false;
      this.createRequestStocksDialog = false;
      this.form.reset();
      this.form.clearErrors();
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

      this.formUpdateStatus.put(route('issuetankitems.acknowledgedrequest', this.formUpdateStatus), {
        preserveScroll: true,
        onSuccess: () => {
          this.requestStockId = null;
          this.editStatusDialog = false;
          this.cancel();
          this.updateData();
        },
      });
    },
    createdMsg() {
      this.$toast.add({ severity: 'success', summary: 'Success', detail: 'Issued item.', life: 3000 });
    },
    updatedMsg() {
      this.$toast.add({ severity: 'warn', summary: 'Success', detail: 'Issued item updated.', life: 3000 });
    },
    qtyIsNotEnough() {
      this.$toast.add({ severity: 'error', summary: 'Failed', detail: 'Stock quantity is not enough.', life: 3000 });
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
    selectedStatus: function (val) {
      //   console.log(val['code']);
      this.params.status = this.selectedStatus;

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

.rounded-card {
  border-radius: 50%;
  /* min-height: 100px;
  min-width: 100px; */
}

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
