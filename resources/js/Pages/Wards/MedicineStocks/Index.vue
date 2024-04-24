<template>
  <app-layout>
    <Head title="NMIS - Stocks" />

    <div>
      <div class="">
        <Link
          href="requeststocks"
          class="button-link"
        >
          OTHER ITEMS
        </Link>

        <Link
          href="requestmedsstocks"
          class="button-link-current"
        >
          MEDICINE
        </Link>

        <Link
          href="requesttankstocks"
          class="button-link"
        >
          TANKS
        </Link>
      </div>

      <div style="background-color: #818cf8; width: 100%; height: 4px"></div>

      <div
        class="card"
        style="border-top-left-radius: 0; border-top-right-radius: 0"
      >
        <Toast />

        <span class="text-xl text-900 font-bold text-primary">REQUESTED STOCKS</span>

        <DataTable
          :value="medsRequestList"
          rowGroupMode="subheader"
          groupRowsBy="reference_id"
          sortMode="single"
          sortField="reference_id"
          :sortOrder="1"
          paginator
          showGridlines
          :rows="10"
        >
          <template #header>
            <div class="flex flex-wrap align-items-center justify-content-end gap-2">
              <div class="flex">
                <div class="mr-2">
                  <div class="p-inputgroup">
                    <span class="p-inputgroup-addon">
                      <i class="pi pi-search"></i>
                    </span>
                    <InputText
                      id="searchInput"
                      v-model="filters['global'].value"
                      placeholder="Search item"
                    />
                  </div>
                </div>
                <Button
                  label="Request medicine"
                  icon="pi pi-plus"
                  iconPos="right"
                  @click="openCreateRequestStocksDialog"
                />
              </div>
            </div>
          </template>
          <template #empty> No requested stock found. </template>
          <template #loading> Loading requested stock data. Please wait. </template>
          <Column
            field="reference_id"
            header="REFERENCE ID"
          >
          </Column>
          <Column
            field="fsName"
            header="FUND SOURCE"
          >
            <template #body="slotProps">
              <span v-if="slotProps.data.fsid != null">{{ slotProps.data.fsName }}</span>
              <Button
                v-else
                label="Fund source"
                icon="pi pi-plus"
                iconPos="right"
                @click="openUpdateFundSource(slotProps.data)"
              ></Button>
            </template>
          </Column>
          <Column
            field="name"
            header="ITEM"
          >
          </Column>
          <Column
            field="requested_qty"
            header="REQUESTED QTY."
          >
          </Column>
          <Column
            field="approved_qty"
            header="APPROVED QTY."
          >
          </Column>
          <Column
            field="status"
            header="STATUS"
          >
            <template #body="{ data }">
              <div class="flex justify-content-start align-content-center">
                <Tag
                  v-if="data.status == 'PENDING'"
                  :value="data.status"
                  severity="contrast"
                />
                <Tag
                  v-if="data.status == 'FILLED'"
                  :value="data.status"
                  severity="info"
                />
                <Tag
                  v-if="data.status == 'RECEIVED'"
                  :value="data.status"
                  severity="success"
                />
                <Tag
                  v-if="data.status == 'CANCELLED'"
                  :value="data.status"
                  severity="danger"
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
            filterField="expiration_date"
            header="EXP. DATE"
            :showFilterMenu="false"
          >
            <template #body="{ data }">
              {{ tzone(data.expiration_date) }}
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
            header="REQUESTED AT"
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
            field="remarks"
            header="REMARKS"
          >
          </Column>
          <Column
            header="ACTION"
            style="width: 5%"
          >
            <template #body="slotProps">
              <div class="flex justify-content-around align-content-center">
                <v-icon
                  v-if="slotProps.data.status == 'PENDING'"
                  name="pr-pencil"
                  class="text-yellow-500 text-xl"
                  @click="editRequestedStock(slotProps.data)"
                ></v-icon>

                <v-icon
                  v-if="slotProps.data.status == 'PENDING' || slotProps.data.status == 'ACKNOWLEDGED'"
                  name="fc-cancel"
                  class="text-red-500 text-xl"
                  @click="confirmCancelItem(slotProps.data)"
                ></v-icon>
              </div>
            </template>
          </Column>
          <template #groupheader="slotProps">
            <div class="bg-primary-reverse py-3">
              <span class="mr-2">CHARGE SLIP #: </span>
              <span>{{ slotProps.data.reference_id }}</span>
            </div>
          </template>
        </DataTable>

        <!-- dialog to update fund source -->
        <Dialog
          v-model:visible="openFundSourceDialog"
          :modal="true"
          class="p-fluid w-3"
          @hide="whenDialogIsHidden"
        >
          <template #header>
            <div class="text-primary text-xl font-bold">UPDATE FUND SOURCE</div>
          </template>

          <label for="role">Fund source</label>
          <Dropdown
            required="true"
            v-model="selectedFundSource"
            :options="fundSourceList"
            :virtualScrollerOptions="{ itemSize: 38 }"
            filter
            optionValue="fsId"
            optionLabel="fsName"
            class="w-full"
          />

          <template #footer>
            <Button
              label="Save"
              icon="pi pi-check"
              text
              type="submit"
              :disabled="formUpdateFundSource.processing"
              @click="submitUpdateFundSource"
            />
          </template>
        </Dialog>

        <!-- @hide="clickOutsideDialog" -->
        <!-- create & edit dialog -->
        <Dialog
          v-model:visible="createRequestStocksDialog"
          :modal="true"
          class="p-fluid w-5"
          @hide="whenDialogIsHidden"
        >
          <template #header>
            <div class="text-primary text-xl font-bold">REQUEST STOCK</div>
          </template>
          <div class="field">
            <label>Item</label>
            <Dropdown
              required="true"
              v-model="item"
              :options="medicinesList"
              :virtualScrollerOptions="{ itemSize: 38 }"
              filter
              optionLabel="name"
              class="w-full mb-3"
            />
          </div>
          <div class="field">
            <label for="Item">Quantity</label>
            <InputNumber
              id="quantity"
              v-model.trim="requested_qty"
              required="true"
              autofocus
              :class="{ 'p-invalid': requested_qty == '' || item == null }"
              @keyup.enter="fillRequestContainer"
              inputId="integeronly"
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
              :globalFilterFields="['name']"
              :value="requestStockListDetails"
              tableStyle="min-width: 50rem"
              class="p-datatable-sm w-full"
              paginator
              removableSort
              showGridlines
              :rows="5"
            >
              <template #header>
                <div class="flex justify-content-end">
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
              </template>
              <Column
                field="name"
                header="PENDING ITEM"
                style="width: 70%"
                sortable
              ></Column>
              <Column
                field="requested_qty"
                header="PENDING QTY"
                style="width: 20%"
                sortable
              ></Column>
              <Column
                header=""
                style="width: 10%"
              >
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
            <label for="quantity">Deduct from Stock</label>
            <InputText
              id="quantity"
              v-model.trim="formWardStocks.quantity"
              autofocus
              class="w-full"
            />
            <small
              class="text-error"
              v-if="Number(formWardStocks.current_quantity) < Number(formWardStocks.quantity)"
            >
              Input must be less than the current stock quantity.
            </small>
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
                formWardStocks.quantity == 0 ||
                Number(formWardStocks.current_quantity) < Number(formWardStocks.quantity)
              "
              @click="submitEditWardStocks"
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
      </div>

      <div class="card">
        <span class="text-xl text-900 font-bold text-primary">CURRENT STOCKS</span>

        <!-- current ward stocks -->
        <DataTable
          class="p-datatable-sm"
          dataKey="id"
          v-model:filters="currentWardStocksFilter"
          :value="currentWardStocksList"
          paginator
          :rows="10"
          :rowsPerPageOptions="[10, 30, 50]"
          removableSort
          sortField="expiration_date"
          :sortOrder="1"
          showGridlines
          :loading="loading"
        >
          <template #header>
            <div class="flex flex-wrap align-items-center justify-content-end gap-2">
              <div class="flex">
                <div class="mr-2">
                  <div class="p-inputgroup">
                    <span class="p-inputgroup-addon">
                      <i class="pi pi-search"></i>
                    </span>
                    <InputText
                      id="searchInput"
                      v-model="currentWardStocksFilter['global'].value"
                      placeholder="Search item"
                    />
                  </div>
                </div>
              </div>
            </div>
          </template>
          <template #empty> No item found. </template>
          <template #loading> Loading item data. Please wait. </template>
          <Column
            field="from"
            header="FROM"
            style="width: 20%"
            sortable
          >
          </Column>
          <Column
            field="item"
            header="ITEM"
            style="width: 30%"
            sortable
          >
            <template #body="{ data }">
              {{ data.item }}
            </template>
          </Column>
          <Column
            field="unit"
            header="UNIT"
            style="width: 10%"
            sortable
          >
          </Column>
          <Column
            field="quantity"
            header="QUANTITY"
            style="width: 5%"
            sortable
          >
            <template #body="{ data }">
              {{ data.quantity }}
            </template>
          </Column>
          <Column
            field="expiration_date"
            header="EXPIRATION DATE"
            style="width: 15%"
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
          <Column
            header="ACTION"
            style="width: 10%"
          >
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
      </div>
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
import InputNumber from 'primevue/inputnumber';
import TextArea from 'primevue/textarea';
import Tag from 'primevue/tag';
import moment from 'moment';
import NProgress from 'nprogress';
import Echo from 'laravel-echo';
import axios from 'axios';
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
    InputNumber,
  },
  props: {
    authWardcode: Object,
    medicines: Object,
    requestedStocks: Object,
    currentWardStocks: Object,
    fundSource: Object,
    medsRequest: Object,
  },
  data() {
    return {
      editingRows: [],
      selectedFundSource: null,
      openFundSourceDialog: false,

      //
      expandedRow: [],
      // paginator
      loading: false,
      totalRecords: null,
      rows: null,
      // end paginator
      editStatusDialog: false,
      requestStockId: null,
      isUpdate: false,
      createRequestStocksDialog: false,
      editWardStocksDialog: false,
      cancelItemDialog: false,
      insertFundSourceDialog: false,
      search: '',
      selectedItemsUomDesc: null,
      oldQuantity: 0,
      options: {},
      params: {},
      from: null,
      to: null,
      stockBalanceDeclared: false,
      medicinesList: [],
      medsRequestList: [],
      requestStockList: [],
      medsToReceiveList: [],
      currentWardStocksList: [],
      // stock list details
      requestStockListDetailsFilter: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
      currentWardStocksFilter: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
      requestStockListDetails: [],
      fundSourceList: [],
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
        reference_id: null,
        requestStockListDetails: [],
      }),
      formUpdateStatus: this.$inertia.form({
        reference_id: null,
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
      formUpdateFundSource: this.$inertia.form({
        itemId: null,
        fsId: null,
      }),
      targetItemDesc: null,
    };
  },
  // created will be initialize before mounted
  created() {
    this.totalRecords = this.medsRequest.total;
    this.params.page = this.medsRequest.current_page;
    this.rows = this.medsRequest.per_page;
  },
  mounted() {
    this.storeFundSourceInContainer();
    this.storeMedicinesInContainer();
    this.storeMedsRequestInContainer();
    // this.storeCurrentWardStocksInContainer();

    this.loading = false;
  },
  computed: {
    user() {
      return this.$page.props.auth.user;
    },
  },
  methods: {
    openUpdateFundSource(item) {
      console.log(item);
      this.selectedFundSource = Number(item.fsId);
      this.formUpdateFundSource.itemId = item.id;

      this.openFundSourceDialog = true;
    },
    submitUpdateFundSource() {
      this.formUpdateFundSource.fsId = this.selectedFundSource;

      this.formUpdateFundSource.post(route('updatereqmedsfs.store'), {
        preserveScroll: true,
        onSuccess: () => {
          this.cancel();
          this.updateData();
          this.updatedFundSource();
          this.openFundSourceDialog = false;
          this.selectedFundSource = null;
        },
      });
    },
    storeMedsRequestInContainer() {
      //   console.log(this.medsRequest);

      // Iterate through this.medsRequest
      this.medsRequest.forEach((e) => {
        const removeUnderscore = e.drug_concat.replace(/_/g, '');
        this.medsRequestList.push({
          id: e.id,
          reference_id: e.reference_id,
          dmdprdte: e.dmdprdte,
          dmdcomb: e.dmdcomb,
          dmdctr: e.dmdctr,
          fsid: e.fsid,
          fsName: e.fsName,
          name: removeUnderscore,
          selling_price: e.selling_price,
          requested_qty: e.requested_qty,
          approved_qty: e.approved_qty,
          expiration_date: e.expiration_date,
          wardcode: e.wardcode,
          status: e.status,
          created_at: e.created_at,
          remarks: e.remarks,
        });
      });
    },
    storeFundSourceInContainer() {
      this.fundSource.forEach((e) => {
        this.fundSourceList.push({
          fsId: e.fsid,
          fsName: e.fsName,
        });
      });
    },
    storeMedicinesInContainer() {
      //   console.log(this.medicines);
      this.medicinesList = []; // reset
      this.medicines.forEach((e) => {
        this.medicinesList.push({
          dmdcomb: e.dmdcomb,
          dmdctr: e.dmdctr,
          name: e.drug_concat.join(''),
        });
      });
    },
    // store current stocks
    // storeCurrentWardStocksInContainer() {
    //   this.currentWardStocksList = []; // reset

    //   moment.suppressDeprecationWarnings = true;

    //   this.currentWardStocks.forEach((e) => {
    //     let expiration_date = moment.tz(e.expiration_date, 'Asia/Manila').format('MM/DD/YYYY');

    //     this.currentWardStocksList.push({
    //       from: e.from,
    //       ward_stock_id: e.id,
    //       cl2comb: e.item_details.cl2comb,
    //       item: e.item_details.cl2desc,
    //       unit: e.unit_of_measurement == null ? null : e.unit_of_measurement.uomdesc,
    //       quantity: e.quantity,
    //       expiration_date: expiration_date.toString(),
    //     });
    //   });
    // },
    tzone(date) {
      if (date == null || date == '') {
        return null;
      } else {
        return moment.tz(date, 'Asia/Manila').format('L');
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
      this.$inertia.get('requestmedsstocks', this.params, {
        preserveState: true,
        preserveScroll: true,
        onFinish: (visit) => {
          // this.totalRecords = this.requestedStocks.total;

          this.medsRequestList = [];
          this.currentWardStocksList = [];
          this.storeFundSourceInContainer();
          this.storeMedicinesInContainer();
          this.storeMedsRequestInContainer();
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
        (this.targetItemDesc = null),
        (this.oldQuantity = 0),
        this.form.clearErrors(),
        this.form.reset(),
        this.formWardStocks.clearErrors(),
        this.formWardStocks.reset()
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
          if (this.requestStockListDetails.some((e) => e.dmdcomb === this.item['dmdcomb'])) {
            this.itemNotSelected = true;
            this.itemNotSelectedMsg = 'Item is already on the list.';
          } else {
            // console.log(this.item);
            this.itemNotSelected = false;
            this.itemNotSelectedMsg = null;
            this.requestStockListDetails.push({
              dmdcomb: this.item['dmdcomb'],
              dmdctr: this.item['dmdctr'],
              name: this.item['name'],
              requested_qty: this.requested_qty,
            });
          }
        }
      }
      //   console.log(this.requestStockListDetails);
    },
    removeFromRequestContainer(item) {
      this.requestStockListDetails.splice(
        this.requestStockListDetails.findIndex((e) => e.dmdcomb === item.dmdcomb),
        1
      );
    },
    editRequestedStock(item) {
      console.log(item);
      this.form.reference_id = item.reference_id;

      this.isUpdate = true;
      this.createRequestStocksDialog = true;
      this.requestStockId = item.reference_id;

      //   item.request_details.forEach((e) => {
      //     this.requestStockListDetails.push({
      //       id: e.id,
      //       dmdcomb: e.dmdcomb,
      //       dmdctr: e.dmdctr,
      //       name: e.name,
      //       requested_qty: e.requested_qty,
      //     });
      //   });
    },
    editStatus(item) {
      console.log(item);

      //   this.medsToReceiveList = item;

      //   item.request_details.forEach((e) => {
      //     this.requestStockListDetails.push({
      //       id: e.id,
      //       dmdcomb: e.dmdcomb,
      //       dmdctr: e.dmdctr,
      //       name: e.name,
      //       requested_qty: e.requested_qty,
      //     });
      //   });

      this.editStatusDialog = true;
      this.formUpdateStatus.reference_id = item.reference_id;
      this.formUpdateStatus.status = 'RECEIVED';
    },
    updateStatus() {
      //   console.log(item);
      //   this.formUpdateStatus.status = item;

      this.formUpdateStatus.put(route('requestmedsstocks.updatedeliverystatus', this.formUpdateStatus.reference_id), {
        preserveScroll: true,
        onSuccess: () => {
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

      this.form.requestStockListDetails = this.requestStockListDetails;

      let reference_id = this.form.reference_id;

      if (this.isUpdate) {
        this.form.put(route('requestmedsstocks.update', reference_id), {
          preserveScroll: true,
          onSuccess: () => {
            this.createRequestStocksDialog = false;
            this.cancel();
            this.updateData();
            this.updatedMsg();
          },
        });
      } else {
        this.form.post(route('requestmedsstocks.store'), {
          preserveScroll: true,
          onSuccess: () => {
            this.createRequestStocksDialog = false;
            this.cancel();
            this.updateData();
            this.createdMsg();
          },
        });
      }
    },
    confirmCancelItem(item) {
      this.form.reference_id = item.reference_id;
      this.cancelItemDialog = true;
    },
    cancelItem() {
      //   let reference_id = this.form.reference_id;

      this.form.delete(route('requestmedsstocks.destroy', this.form.reference_id), {
        preserveScroll: true,
        onSuccess: () => {
          this.cancelItemDialog = false;
          this.cancel();
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
    cancelledMsg() {
      this.$toast.add({ severity: 'error', summary: 'Success', detail: 'Stock request canceld', life: 3000 });
    },
    updatedStatusMsg() {
      this.$toast.add({ severity: 'warn', summary: 'Success', detail: 'Changed requested stocks status', life: 3000 });
    },
    updatedFundSource() {
      this.$toast.add({ severity: 'warn', summary: 'Success', detail: 'Item fund source updated', life: 3000 });
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
    // search: function (val, oldVal) {
    //   this.params.search = val;
    //   this.updateData();
    // },
    // from: function (val) {
    //   if (val != null) {
    //     let from = this.getLocalDateString(val);
    //     this.params.from = from;
    //   } else {
    //     this.params.from = null;
    //     this.from = null;
    //   }
    //   this.updateData();
    // },
    // to: function (val) {
    //   if (val != null) {
    //     let to = this.getLocalDateString(val);
    //     this.params.to = to;
    //   } else {
    //     this.params.to = null;
    //     this.to = null;
    //   }
    //   this.updateData();
    // },
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

.button-link-current {
  display: inline-block;
  padding: 0.5rem 1rem;
  border: 1px solid #818cf8;
  background-color: #818cf8;
  color: #fff;
  text-decoration: none;
  text-align: center;
  /* border-radius: 4px; */
  transition: background-color 0.3s ease;
}
.button-link {
  display: inline-block;
  padding: 0.5rem 1rem;
  border: 1px solid #818cf8;
  /* background-color: #818cf8; */
  color: #fff;
  text-decoration: none;
  text-align: center;
  /* border-radius: 4px; */
  transition: background-color 0.3s ease;
}

.button-link-current:hover {
  background-color: #5561d7;
}
.button-link:hover {
  background-color: #5561d7;
  color: white !important;
}
</style>
