<template>
  <app-layout>
    <Head title="NMIS - Stocks" />

    <div class="card">
      <Toast />
      <!--
            data table sort order
            asc = 1
            desc =-1
        -->

      <div class="mb-2">
        <Link
          href="requeststocks"
          class="text-2xl mr-2 my-link"
        >
          DRUG AND MEDS
        </Link>

        <Link
          href="requesttankstocks"
          class="text-2xl border-bottom-2 font-semibold"
        >
          TANKS
        </Link>
      </div>

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
            <span class="text-xl text-900 font-bold text-cyan-500 hover:text-cyan-700">PENDING STOCKS</span>
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
                v-if="data.status == 'PENDING'"
                :value="data.status"
              />
              <Tag
                v-if="data.status == 'ACKNOWLEDGED'"
                :value="data.status"
                class="bg-yellow-400"
              />
              <Tag
                v-if="data.status == 'FILLED'"
                :value="data.status"
                class="bg-blue-400"
              />
              <Tag
                v-if="data.status == 'RECEIVED'"
                :value="data.status"
                class="bg-green-400"
              />
              <Tag
                v-if="data.status == 'CANCELLED'"
                :value="data.status"
                style="background-color: rgb(239, 42, 42); color: rgb(253, 249, 249)"
              />
              <div>
                <i
                  v-if="data.status == 'FILLED'"
                  class="pi pi-check ml-3"
                  style="color: skyblue"
                  @click="editStatus(data)"
                ></i>
              </div>
            </div>
          </template>
        </Column>
        <Column
          field="requested_by"
          header="PENDING BY"
        >
          <template #body="{ data }">
            <div class="flex flex-row align-items-center">
              <img
                v-if="data.requested_by_image != null"
                :src="`storage/${data.requested_by_image}`"
                class="w-4rem h-4rem rounded-card"
              />
              <img
                v-else
                src="images/no_profile.png"
                class="w-4rem h-4rem rounded-card"
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
        >
          <template #body="{ data }">
            <div class="flex flex-row align-items-center">
              <img
                v-if="data.approved_by_image != null"
                :src="`storage/${data.approved_by_image}`"
                class="w-4rem h-4rem rounded-card"
              />

              <img
                v-if="data.approved_by != null && data.approved_by_image == null"
                src="images/no_profile.png"
                class="w-4rem h-4rem rounded-card"
              />

              <span class="font-semibold text-xl pl-3">
                {{ data.approved_by }}
              </span>
            </div>
          </template>
        </Column>
        <Column header="ACTION">
          <template #body="slotProps">
            <div class="flex justify-content-around align-content-center">
              <v-icon
                v-if="slotProps.data.status == 'PENDING'"
                name="pr-pencil"
                class="text-yellow-500 text-xl"
                @click="editRequestedStock(slotProps.data)"
              ></v-icon>

              <!-- <Button
              v-if="slotProps.data.status == 'PENDING'"
              icon="fc fc-cancel"
              rounded
              text
              severity="danger"
              @click="confirmCancelItem(slotProps.data)"
            /> -->
              <v-icon
                v-if="slotProps.data.status == 'PENDING' || slotProps.data.status == 'ACKNOWLEDGED'"
                name="fc-cancel"
                class="text-red-500 text-xl"
                @click="confirmCancelItem(slotProps.data)"
              ></v-icon>
            </div>
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
                  {{ getTankDesc(data) }}
                </template>
              </Column>
              <Column
                field="requested_qty"
                header="PENDING QTY"
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
                <InputText
                  v-model="currentWardStocksFilter['global'].value"
                  placeholder="Search item"
                />
              </span>
            </div>
          </div>
        </template>
        <template #empty> No item found. </template>
        <template #loading> Loading item data. Please wait. </template>
        <Column
          field="from"
          header="FROM"
          sortable
        >
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
          field="unit"
          header="UNIT"
          sortable
        >
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
            <div class="flex flex-column">
              <div>
                {{ tzone(data.expiration_date) }}
              </div>

              <div class="mays-2">
                <span
                  :class="
                    checkIfAboutToExpire(data.expiration_date) != 'Item has expired.'
                      ? 'text-lg text-green-500'
                      : 'text-lg text-error'
                  "
                >
                  {{ checkIfAboutToExpire(data.expiration_date) }}
                </span>
              </div>
            </div>
          </template>
        </Column>
        <Column header="ACTION">
          <template #body="slotProps">
            <div class="flex justify-content-center">
              <Button
                rounded
                text
                severity="warning"
                @click="editWardStocks(slotProps.data)"
              >
                <template #default="">
                  <v-icon
                    name="pr-pencil"
                    class="text-yellow-500"
                  ></v-icon>
                </template>
              </Button>
            </div>
          </template>
        </Column>
      </DataTable>

      <!-- @hide="clickOutsideDialog" -->
      <!-- create & edit dialog -->
      <Dialog
        v-model:visible="createRequestStocksDialog"
        header="Request stock"
        :modal="true"
        class="p-fluid w-5"
        @hide="whenDialogIsHidden"
      >
        <div class="field">
          <label>Item</label>
          <Dropdown
            required="true"
            v-model="item"
            :options="itemsList"
            :virtualScrollerOptions="{ itemSize: 38 }"
            filter
            optionLabel="itemDesc"
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
        <div class="field mt-4">
          <label class="mr-2">Requested stock list</label>
          <i
            class="pi pi-shopping-cart text-blue-500"
            style="font-size: 1.5rem"
          />
          <DataTable
            v-model:filters="requestStockListDetailsFilter"
            :globalFilterFields="['itemDesc']"
            :value="requestStockListDetails"
            tableStyle="min-width: 50rem"
            class="p-datatable-sm w-full"
            paginator
            showGridlines
            :rows="5"
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
              field="itemDesc"
              header="PENDING ITEM"
              sortable
            ></Column>
            <Column
              field="requested_qty"
              header="PENDING QTY"
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
            :disabled="formUpdateStatus.processing"
            @click="updateStatus"
          />
        </template>
      </Dialog>

      <!-- Cancel confirmation dialog -->
      <Dialog
        v-model:visible="cancelItemDialog"
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
          <span v-if="form">Are you sure you want to cancel this request?</span>
        </div>
        <template #footer>
          <Button
            label="No"
            icon="pi pi-times"
            class="p-button-text"
            @click="cancelItemDialog = false"
          />
          <Button
            label="Yes"
            icon="pi pi-check"
            severity="danger"
            text
            :disabled="form.processing"
            @click="cancelItem"
          />
        </template>
      </Dialog>

      <!-- update ward stock dialog -->
      <Dialog
        v-model:visible="editWardStocksDialog"
        header="Update stock"
        :modal="true"
        class="p-fluid w-4"
        @hide="whenDialogIsHidden"
        dismissableMask
      >
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
              Number(formWardStocks.current_quantity) < Number(formWardStocks.quantity)
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
import NProgress from 'nprogress';
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
    TextArea,
    Link,
  },
  props: {
    authWardcode: Object,
    items: Object,
    requestedStocks: Object,
    currentWardStocks: Object,
    currentWardStocks2: Object,
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
      cancelItemDialog: false,
      search: '',
      selectedItemsUomDesc: null,
      oldQuantity: 0,
      options: {},
      params: {},
      from: null,
      to: null,
      stockBalanceDeclared: false,
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
      itemDesc: null,
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
        item: null,
        current_quantity: null,
        quantity: null,
        expiration_date: null,
        remarks: null,
      }),
      targetItemDesc: null,
    };
  },
  // created will be initialize before mounted
  created() {
    this.totalRecords = this.requestedStocks.total;
    this.params.page = this.requestedStocks.current_page;
    this.rows = this.requestedStocks.per_page;
  },
  mounted() {
    window.Echo.channel('issued').listen('ItemIssued', (args) => {
      if (args.message[0] == this.$page.props.authWardcode.wardcode) {
        router.reload({
          onSuccess: (e) => {
            this.requestStockList = [];
            this.storeRequestedStocksInContainer();
            this.createRequestStocksDialog = false;
          },
        });
      }
    });

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
      //   console.log(this.$page.props.tanksList);

      this.itemsList = []; // reset
      this.$page.props.tanksList.forEach((e) => {
        const matchingTank = this.$page.props.tanksList.find((x) => e.itemcode === x.itemcode);

        this.itemsList.push({
          itemcode: e.itemcode,
          itemDesc: matchingTank ? matchingTank.itemDesc : null,
          unitcode: e.unit == null ? null : e.unit.unitcode,
          //   uomdesc: e.unit == null ? null : e.unit.uomdesc,
        });
      });

      this.itemsList.sort((a, b) => {
        const itemDescA = a.itemDesc || ''; // Handle cases where itemDesc might be null
        const itemDescB = b.itemDesc || '';

        // Use localeCompare for case-insensitive string comparison
        return itemDescA.localeCompare(itemDescB);
      });
    },
    // use storeRequestedStocksInContainer() function so that every time you make
    // server request such as POST, the data in the table
    // is updated
    storeRequestedStocksInContainer() {
      console.log(this.requestedStocks.data);
      this.requestStockList = []; // reset

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
          created_at: e.created_at,
          request_stocks_details: e.request_stocks_details,
        });
      });
    },
    getTankDesc(item) {
      const matchingTank = this.$page.props.tanksList.find((x) => item.itemcode === x.itemcode);

      return matchingTank.itemDesc;
    },
    // store current stocks
    storeCurrentWardStocksInContainer() {
      this.currentWardStocksList = []; // reset

      moment.suppressDeprecationWarnings = true;

      //   this.currentWardStocks.forEach((e) => {
      //     let expiration_date = moment.tz(e.expiration_date, 'Asia/Manila').format('MM/DD/YYYY');

      //     this.currentWardStocksList.push({
      //       from: e.from,
      //       ward_stock_id: e.id,
      //       itemcode: e.item_details.itemcode,
      //       item: e.item_details.itemDesc,
      //       unit: e.unit_of_measurement == null ? null : e.unit_of_measurement.uomdesc,
      //       quantity: e.quantity,
      //       expiration_date: expiration_date.toString(),
      //     });
      //   });

      //   this.currentWardStocks2.forEach((e) => {
      //     let expiration_date = moment.tz(e.expiration_date, 'Asia/Manila').format('MM/DD/YYYY');

      //     this.currentWardStocksList.push({
      //       from: e.from,
      //       ward_stock_id: e.id,
      //       itemcode: e.item_details.itemcode,
      //       item: e.item_details.itemDesc,
      //       unit: e.unit_of_measurement == null ? null : e.unit_of_measurement.uomdesc,
      //       quantity: e.quantity,
      //       expiration_date: expiration_date.toString(),
      //     });
      //   });
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

      //    exp_date.format('MM-DD-YY') < current_date.format('MM-DD-YY')
      if (
        current_date.format('MM-DD-YY') == exp_date.format('MM-DD-YY') ||
        Date.parse(exp_date) < Date.parse(current_date)
      ) {
        return 'Item has expired.';
      } else if (date_diff == 1) {
        return date_diff + ' day remaining.';
      } else {
        return date_diff + ' days remaining.';
      }
    }, // },
    onPage(event) {
      this.params.page = event.page + 1;
      this.updateData();
    },
    updateData() {
      this.loading = true;

      this.$inertia.get('requesttankstocks', this.params, {
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
        (this.itemDesc = null),
        (this.requested_qty = null),
        (this.approved_qty = null),
        (this.itemNotSelected = null),
        (this.itemNotSelectedMsg = null),
        (this.targetItemDesc = null),
        (this.oldQuantity = 0),
        this.form.clearErrors(),
        this.form.reset(),
        this.formWardStocks.clearErrors(),
        this.formWardStocks.reset(),
        this.formUpdateStatus.reset()
      );
    },
    fillRequestContainer() {
      console.log(this.requestStockListDetails);

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
          if (this.requestStockListDetails.some((e) => e.itemcode === this.item['itemcode'])) {
            this.itemNotSelected = true;
            this.itemNotSelectedMsg = 'Item is already on the list.';
          } else {
            this.itemNotSelected = false;
            this.itemNotSelectedMsg = null;
            this.requestStockListDetails.push({
              itemcode: this.item['itemcode'],
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
        this.requestStockListDetails.findIndex((e) => e.itemcode === item.itemcode),
        1
      );
    },
    editRequestedStock(item) {
      this.form.request_stocks_id = item.id;

      this.isUpdate = true;
      this.createRequestStocksDialog = true;
      this.requestStockId = item.id;

      item.request_stocks_details.forEach((e) => {
        // console.log(e);
        const matchingTank = this.$page.props.tanksList.find((x) => e.itemcode === x.itemcode);

        this.requestStockListDetails.push({
          request_stocks_details_id: e.id,
          itemcode: e.itemcode,
          itemDesc: matchingTank ? matchingTank.itemDesc : null,
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
      if (this.form.processing) {
        return false;
      }

      // setup location, requested by and requestStockListDetails before submitting
      this.form.location = this.authWardcode.wardcode;
      this.form.requested_by = this.user.userDetail.employeeid;
      this.form.requestStockListDetails = this.requestStockListDetails;

      if (this.isUpdate) {
        this.form.put(route('requesttankstocks.update', this.requestStockId), {
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
        this.form.post(route('requesttankstocks.store'), {
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
    confirmCancelItem(item) {
      //   console.log(item);
      this.requestStockId = item.id;
      this.cancelItemDialog = true;
    },
    cancelItem() {
      this.form.delete(route('requesttankstocks.destroy', this.requestStockId), {
        preserveScroll: true,
        onSuccess: () => {
          this.requestStockList = [];
          this.cancelItemDialog = false;
          this.requestStockId = null;
          this.form.clearErrors();
          this.form.reset();
          this.updateData();
          this.cancelledMsg();
        },
      });
    },
    cancel() {
      this.requestStockId = null;
      this.isUpdate = false;
      this.createRequestStocksDialog = false;
      this.editWardStocksDialog = false;
      this.targetItemDesc = null;
      this.oldQuantity = 0;
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
    cancelledMsg() {
      this.$toast.add({ severity: 'error', summary: 'Success', detail: 'Stock request canceld', life: 3000 });
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

.form-container {
  display: flex;
  justify-content: space-around;
  align-items: center;
  /* padding: 20px; */
}

.form-side {
  flex: 1;
  /* margin-right: 20px; */
}

.p-field {
  margin-bottom: 20px;
}

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

#toItem {
  width: 250px;
  overflow: hidden;
  white-space: pre;
  text-overflow: ellipsis;
  -webkit-appearance: none;
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
