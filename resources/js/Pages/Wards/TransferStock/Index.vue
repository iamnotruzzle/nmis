<template>
  <app-layout>
    <Head title="NMIS - Transfer stock" />

    <div class="card">
      <Toast />

      <!-- ward stocks -->
      <DataTable
        class="p-datatable-sm"
        dataKey="ward_stock_id"
        v-model:filters="wardStocksFilter"
        :value="wardStocksList"
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
                    v-model="wardStocksFilter['global'].value"
                    placeholder="Search item"
                  />
                </span>
              </span>
            </div>
          </div>
        </template>
        <template #empty> No data found. </template>
        <template #loading> Loading data. Please wait. </template>
        <Column
          field="from"
          header="FROM"
          style="min-width: 12rem"
        >
        </Column>
        <Column
          field="brand_name"
          header="BRAND"
          style="min-width: 12rem"
        >
        </Column>
        <Column
          field="cl2desc"
          header="ITEM"
          style="min-width: 12rem"
        >
        </Column>
        <Column
          field="quantity"
          header="QUANTITY"
          sortable
          style="min-width: 12rem"
        >
        </Column>
        <Column
          header="EXP. DATE"
          sortable
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ tzone(data.expiration_date) }}
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
              @click="transferStock(slotProps.data)"
            />
          </template>
        </Column>
      </DataTable>

      <TabView class="mt-8">
        <TabPanel header="TRANSFERRED STOCKS">
          <DataTable
            class="p-datatable-sm"
            dataKey="ward_stock_id"
            v-model:filters="transferredStocksFilter"
            :value="transferredStocksList"
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
              <div class="flex flex-wrap align-items-center justify-content-end">
                <div>
                  <span class="p-input-icon-left mr-2">
                    <i class="pi pi-search" />
                    <span class="p-input-icon-left">
                      <i class="pi pi-search" />
                      <InputText
                        v-model="transferredStocksFilter['global'].value"
                        placeholder="Search"
                      />
                    </span>
                  </span>
                </div>
              </div>
            </template>
            <template #empty> No data found. </template>
            <template #loading> Loading data. Please wait. </template>
            <Column
              field="brand"
              header="BRAND"
            >
            </Column>
            <Column
              field="item"
              header="ITEM"
            >
            </Column>
            <Column
              field="quantity"
              header="QUANTITY"
            >
            </Column>
            <Column
              field="expiration_date"
              header="EXP. DATE"
            >
            </Column>
            <Column
              field="to"
              header="TO"
            >
            </Column>
            <Column
              field="status"
              header="STATUS"
            >
              <template #body="slotProps">
                <Tag
                  v-if="slotProps.data.status == 'TRANSFERRED'"
                  :value="slotProps.data.status"
                  severity="warning"
                />
                <Tag
                  v-else-if="slotProps.data.status == 'RECEIVED'"
                  :value="slotProps.data.status"
                  severity="success"
                />
                <p v-else></p>
              </template>
            </Column>
          </DataTable>
        </TabPanel>
        <TabPanel header="TO RECEIVE">
          <DataTable
            class="p-datatable-sm"
            dataKey="ward_stock_id"
            v-model:filters="toReceiveFilter"
            :value="toReceiveList"
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
              <div class="flex flex-wrap align-items-center justify-content-end">
                <div>
                  <span class="p-input-icon-left mr-2">
                    <i class="pi pi-search" />
                    <span class="p-input-icon-left">
                      <i class="pi pi-search" />
                      <InputText
                        v-model="toReceiveFilter['global'].value"
                        placeholder="Search"
                      />
                    </span>
                  </span>
                </div>
              </div>
            </template>
            <template #empty> No data found. </template>
            <template #loading> Loading data. Please wait. </template>
            <Column
              field="brand"
              header="BRAND"
            >
            </Column>
            <Column
              field="item"
              header="ITEM"
            >
            </Column>
            <Column
              field="quantity"
              header="QUANTITY"
            >
            </Column>
            <Column
              field="expiration_date"
              header="EXP. DATE"
            >
            </Column>
            <Column
              field="from"
              header="FROM"
            >
            </Column>
            <Column header="ACTION">
              <template #body="slotProps">
                <i
                  v-if="slotProps.data.status != 'RECEIVED'"
                  class="pi pi-check"
                  style="color: skyblue"
                  @click="receivedStock(slotProps)"
                ></i>
              </template>
            </Column>
          </DataTable>
        </TabPanel>
      </TabView>

      <!-- create dialog -->
      <Dialog
        v-model:visible="transferStockDialog"
        :style="{ width: '450px' }"
        header="Transfer stock"
        :modal="true"
        class="p-fluid"
        @hide="clickOutsideDialog"
        dismissableMask
      >
        <div class="field">
          <label for="item">Item</label>
          <InputText
            id="item"
            v-model.trim="form.cl2desc"
            readonly
            class="w-full"
          />
        </div>
        <div class="field">
          <label for="quantity">Quantity</label>
          <InputText
            id="quantity"
            v-model.number="form.quantity"
            class="w-full"
            autofocus
            type="number"
            @keyup.enter="submit"
            :class="{ 'p-invalid': Number(form.quantity) > Number(form.prevQuantity) }"
          />
          <small
            class="text-error"
            v-if="Number(form.quantity) > Number(form.prevQuantity)"
          >
            Current stock quantity is not enough.
          </small>
          <small
            class="text-error"
            v-if="Number(form.quantity) == 0"
          >
            Quantity is required.
          </small>
          <small
            class="text-error"
            v-if="form.errors.quantity"
          >
            {{ form.errors.quantity }}
          </small>
        </div>
        <div class="field">
          <label for="ward">Ward</label>
          <Dropdown
            id="ward"
            v-model.trim="form.to"
            required="true"
            :options="locationsList"
            :virtualScrollerOptions="{ itemSize: 38 }"
            filter
            optionLabel="wardname"
            optionValue="wardcode"
            class="w-full mb-3"
            :class="{ 'p-invalid': form.to == '' }"
          />
          <small
            class="text-error"
            v-if="form.errors.to"
          >
            {{ form.errors.to }}
          </small>
        </div>
        <div class="field">
          <label for="requested_by">Requested by</label>
          <Dropdown
            id="requested_by"
            v-model.trim="form.requested_by"
            required="true"
            :options="$page.props.employees"
            :virtualScrollerOptions="{ itemSize: 38 }"
            filter
            optionLabel="employeeid"
            optionValue="employeeid"
            class="w-full mb-3"
            :class="{ 'p-invalid': form.requested_by == '' }"
          />
          <small
            class="text-error"
            v-if="form.errors.requested_by"
          >
            {{ form.errors.requested_by }}
          </small>
        </div>
        <div class="field">
          <label for="expiration_date">Expiration date</label>
          <InputText
            id="quantity"
            v-model.trim="form.expiration_date"
            class="w-full"
            readonly
          />
        </div>
        <div class="field">
          <label for="remarks">Remarks</label>
          <Textarea
            v-model.trim="form.remarks"
            rows="5"
            autofocus
          />
          <small
            class="text-error"
            v-if="form.remarks == null"
          >
            Remarks is required.
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
            label="Save"
            icon="pi pi-check"
            text
            type="submit"
            :disabled="
              form.processing ||
              form.quantity == null ||
              Number(form.quantity) == 0 ||
              Number(form.quantity) > Number(form.prevQuantity) ||
              form.to == null ||
              form.requested_by == null ||
              form.remarks == null
            "
            @click="submit"
          />
        </template>
      </Dialog>

      <!-- received stock dialog -->
      <Dialog
        v-model:visible="receivedItemDialog"
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
          <span v-if="form"> You're about to receive a transferred stock. Proceed? </span>
        </div>
        <template #footer>
          <Button
            label="No"
            icon="pi pi-times"
            class="p-button-text"
            @click="receivedItemDialog = false"
          />
          <Button
            label="Yes"
            icon="pi pi-check"
            severity="danger"
            text
            :disabled="formReceiveStock.processing"
            @click="updateReceivedStockStatus"
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
import Textarea from 'primevue/textarea';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
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
    Textarea,
    TabView,
    TabPanel,
    Tag,
  },
  props: {
    authWardcode: Object,
    wardStocks: Object,
    wardStocksConsignments: Object,
    transferredStock: Object,
  },
  data() {
    return {
      // paginator
      loading: false,
      rows: null,
      // end paginator
      from: null,
      to: null,
      isUpdate: false,
      transferStockDialog: false,
      receivedItemDialog: false,
      deleteTransferredStockDialog: false,
      search: '',
      options: {},
      params: {},
      locationsList: [],
      wardStocksList: [],
      transferredStocksList: [],
      toReceiveList: [],
      transferredStocksFilter: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
      toReceiveFilter: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
      wardStocksFilter: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
      form: this.$inertia.form({
        ward_stock_id: null,
        to: null,
        requested_by: null,
        brand: null,
        cl2comb: null,
        cl2desc: null,
        quantity: null,
        prevQuantity: null,
        expiration_date: null,
        remarks: null,
      }),
      formReceiveStock: this.$inertia.form({
        id: null,
      }),
    };
  },
  // created will be initialize before mounted
  created() {},
  mounted() {
    window.Echo.channel('issued').listen('ItemIssued', (e) => {
      this.transferredStocksList = [];
      this.loading = true;

      if (e.message == this.$page.props.authWardcode.wardcode) {
        this.$inertia.get('transferstock', this.params, {
          preserveState: true,
          preserveScroll: true,
          onFinish: (visit) => {
            this.wardStocksList = [];
            this.transferredStocksList = [];
            this.toReceiveList = [];
            this.storeWardStockInContainer();
            this.storeTransferredStockInContainer();
            this.loading = false;
          },
        });
      }
    });

    this.storeLocationsInContainer();
    this.storeTransferredStockInContainer();
    this.storeWardStockInContainer();

    this.loading = false;
  },
  methods: {
    tzone(date) {
      return moment.tz(date, 'Asia/Manila').format('MM/DD/YYYY');
    },
    storeLocationsInContainer() {
      this.$page.props.locations.forEach((e) => {
        if (e.wardcode == 'CSR' || e.wardcode == 'ADMIN') {
          return null;
        } else {
          this.locationsList.push({
            wardcode: e.wardcode,
            wardname: e.wardname,
          });
        }
      });
    },
    storeTransferredStockInContainer() {
      if (this.transferredStock.length != 0) {
        this.transferredStock.forEach((e) => {
          let expiration_date = moment.tz(e.ward_stock.expiration_date, 'Asia/Manila').format('MM/DD/YYYY');

          // list of items this auth ward transferred
          if (e.from == this.authWardcode.wardcode) {
            this.transferredStocksList.push({
              id: e.id,
              brand: e.ward_stock.brand_details.name,
              item: e.ward_stock.item_details.cl2desc,
              quantity: e.quantity,
              expiration_date: expiration_date,
              to: e.ward_to.wardname,
              status: e.status,
            });
          }

          // list of items to receive
          if (e.to == this.authWardcode.wardcode) {
            this.toReceiveList.push({
              id: e.id,
              brand: e.ward_stock.brand_details.name,
              item: e.ward_stock.item_details.cl2desc,
              quantity: e.quantity,
              expiration_date: expiration_date,
              from: e.ward_from.wardname,
              status: e.status,
            });
          }
        });
      } else {
        this.transferredStocksList.push({
          id: null,
          brand: null,
          item: null,
          quantity: null,
          expiration_date: null,
          from: null,
        });
      }
    },
    storeWardStockInContainer() {
      this.wardStocks.forEach((e) => {
        // let expiration_date = moment.tz(e.expiration_date, 'Asia/Manila').format('MM/DD/YYYY');

        this.wardStocksList.push({
          ward_stock_id: e.id,
          from: e.from,
          brand_id: e.brand_details.id,
          brand_name: e.brand_details.name,
          cl2comb: e.item_details.cl2comb,
          cl2desc: e.item_details.cl2desc,
          quantity: e.quantity,
          expiration_date: e.expiration_date,
        });
      });

      this.wardStocksConsignments.forEach((e) => {
        // let expiration_date = moment.tz(e.expiration_date, 'Asia/Manila').format('MM/DD/YYYY');

        this.wardStocksList.push({
          ward_stock_id: e.id,
          from: e.from,
          brand_id: e.brand_details.id,
          brand_name: e.brand_details.name,
          cl2comb: e.item_details.cl2comb,
          cl2desc: e.item_details.cl2desc,
          quantity: e.quantity,
          expiration_date: e.expiration_date,
        });
      });
    },
    updateData() {
      this.transferredStocksList = [];
      this.loading = true;

      this.$inertia.get('transferstock', this.params, {
        preserveState: true,
        preserveScroll: true,
        onFinish: (visit) => {
          this.wardStocksList = [];
          this.transferredStocksList = [];
          this.toReceiveList = [];
          this.storeWardStockInContainer();
          this.storeTransferredStockInContainer();
          this.loading = false;
        },
      });
    },
    // emit close dialog
    clickOutsideDialog() {
      this.$emit('hide', (this.cl1comb = null), (this.isUpdate = false), this.form.clearErrors(), this.form.reset());
    },
    transferStock(item) {
      //   console.log(item.quantity);
      this.transferStockDialog = true;
      this.form.ward_stock_id = item.ward_stock_id;
      this.form.cl2desc = item.cl2desc;
      this.form.prevQuantity = item.quantity;
      this.form.expiration_date = item.expiration_date;
    },
    submit() {
      // the form is submitted only if the conditions is met
      if (
        Number(this.form.quantity) <= Number(this.form.prevQuantity) &&
        Number(this.form.quantity) != 0 &&
        Number(this.form.quantity) != null &&
        this.form.to != null &&
        this.form.requested_by != null &&
        this.form.remarks != null
      ) {
        this.form.post(route('transferstock.store'), {
          preserveScroll: true,
          onSuccess: () => {
            this.transferStockDialog = false;
            this.cancel();
            this.updateData();
            this.createdMsg();
          },
        });
      }
    },
    confirmDeleteTransferredStock(item) {
      this.cl1comb = item.cl1comb;
      this.form.cl1desc = item.cl1desc;
      this.deleteTransferredStockDialog = true;
    },
    deleteCategory() {
      this.form.delete(route('transferstock.destroy', this.cl1comb), {
        preserveScroll: true,
        onSuccess: () => {
          this.transferredStocksList = [];
          this.deleteTransferredStockDialog = false;
          this.cl1comb = null;
          this.form.clearErrors();
          this.form.reset();
          this.updateData();
          this.deletedMsg();
          this.storeTransferredStockInContainer();
        },
      });
    },
    cancel() {
      this.cl1comb = null;
      this.isUpdate = false;
      this.transferStockDialog = false;
      this.form.reset();
      this.form.clearErrors();
      this.transferredStocksList = [];
      this.storeTransferredStockInContainer();
    },
    createdMsg() {
      this.$toast.add({ severity: 'success', summary: 'Success', detail: 'Transfer stock successfully.', life: 3000 });
    },
    stockReceivedMsg() {
      this.$toast.add({ severity: 'success', summary: 'Success', detail: 'Stock received.', life: 3000 });
    },
    deletedMsg() {
      this.$toast.add({ severity: 'error', summary: 'Success', detail: 'Delete transferred stock.', life: 3000 });
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
    receivedStock(item) {
      //   console.log(item);
      this.receivedItemDialog = true;
      this.formReceiveStock.id = item.data.id;
    },
    updateReceivedStockStatus() {
      this.formReceiveStock.put(route('transferstock.updatetransferstatus', this.formReceiveStock), {
        preserveScroll: true,
        onSuccess: () => {
          this.receivedItemDialog = false;
          this.cancel();
          this.updateData();
          this.stockReceivedMsg();
        },
      });
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
