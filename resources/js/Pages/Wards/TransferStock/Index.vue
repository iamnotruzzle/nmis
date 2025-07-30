<template>
  <app-layout>
    <Head title="NMIS - Transfer stock" />

    <div class="card">
      <Toast />

      <span class="text-xl text-900 font-bold text-primary">CURRENT STOCKS</span>

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
          <div class="flex flex-wrap align-items-center justify-content-end gap-2">
            <div class="flex">
              <div class="mr-2">
                <div class="p-inputgroup">
                  <span class="p-inputgroup-addon">
                    <i class="pi pi-search"></i>
                  </span>
                  <InputText
                    id="searchInput"
                    v-model="wardStocksFilter['global'].value"
                    placeholder="Search item"
                  />
                </div>
              </div>
            </div>
          </div>
        </template>
        <template #empty> No data found. </template>
        <template #loading> Loading data. Please wait. </template>
        <Column
          field="from"
          header="FROM"
          style="width: 20%"
        >
        </Column>
        <Column
          field="cl2desc"
          header="ITEM"
          style="width: 30%"
        >
        </Column>
        <Column
          field="quantity"
          header="QUANTITY"
          sortable
          style="width: 5%; text-align: right"
          :pt="{ headerContent: 'justify-content-end' }"
        >
        </Column>
        <Column
          header="EXP. DATE"
          sortable
          style="width: 15%; text-align: right"
          :pt="{ headerContent: 'justify-content-end' }"
        >
          <template #body="{ data }">
            {{ tzone(data.expiration_date) }}
          </template>
        </Column>
        <Column
          header="ACTION"
          style="width: 10%; text-align: center"
          :pt="{ headerContent: 'justify-content-center' }"
        >
          <template #body="slotProps">
            <Button
              :disabled="canTransact == false"
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
            @keyup.enter="submit"
            :class="{ 'p-invalid': Number(form.quantity) > Number(form.prevQuantity) }"
            onkeypress="return event.charCode >= 48 && event.charCode <= 57"
            inputId="integeronly"
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
            :options="employeesList"
            :virtualScrollerOptions="{ itemSize: 38 }"
            filter
            optionLabel="name"
            optionValue="employeeid"
            class="w-full mb-3"
            :class="{ 'p-invalid': form.requested_by == '' }"
            showClear
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
            :label="!form.processing ? 'CANCEL' : 'CANCEL'"
            icon="pi pi-times"
            :disabled="form.processing"
            severity="danger"
            @click="cancel"
          />

          <Button
            :disabled="form.processing"
            :label="!form.processing ? 'SAVE' : 'SAVE'"
            :icon="form.processing ? 'pi pi-spin pi-spinner' : 'pi pi-check'"
            type="submit"
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
            :label="!formReceiveStock.processing ? 'NO' : 'NO'"
            icon="pi pi-times"
            :disabled="formReceiveStock.processing"
            severity="danger"
            @click="receivedItemDialog = false"
          />

          <Button
            :disabled="formReceiveStock.processing"
            :label="!formReceiveStock.processing ? 'YES' : 'YES'"
            :icon="formReceiveStock.processing ? 'pi pi-spin pi-spinner' : 'pi pi-check'"
            @click="updateReceivedStockStatus"
          />
        </template>
      </Dialog>
    </div>

    <div class="card">
      <TabView>
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
                <div class="flex">
                  <div class="mr-2">
                    <div class="p-inputgroup">
                      <span class="p-inputgroup-addon">
                        <i class="pi pi-search"></i>
                      </span>
                      <InputText
                        id="searchInput"
                        v-model="transferredStocksFilter['global'].value"
                        placeholder="Search"
                      />
                    </div>
                  </div>
                </div>
              </div>
            </template>
            <template #empty> No data found. </template>
            <template #loading> Loading data. Please wait. </template>
            <Column
              field="item"
              header="ITEM"
              style="width: 30%"
            >
            </Column>
            <Column
              field="quantity"
              header="QUANTITY"
              style="width: 10%; text-align: right"
              :pt="{ headerContent: 'justify-content-end' }"
            >
            </Column>
            <Column
              field="expiration_date"
              header="EXP. DATE"
              style="width: 10%; text-align: right"
              :pt="{ headerContent: 'justify-content-end' }"
            >
            </Column>
            <Column
              field="to"
              header="TO"
              style="width: 20%; text-align: right"
              :pt="{ headerContent: 'justify-content-end' }"
            >
            </Column>
            <Column
              field="status"
              header="STATUS"
              style="width: 10%; text-align: center"
              :pt="{ headerContent: 'justify-content-center' }"
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
                  <div class="flex">
                    <div class="mr-2">
                      <div class="p-inputgroup">
                        <span class="p-inputgroup-addon">
                          <i class="pi pi-search"></i>
                        </span>
                        <InputText
                          id="searchInput"
                          v-model="toReceiveFilter['global'].value"
                          placeholder="Search"
                        />
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </template>
            <template #empty> No data found. </template>
            <template #loading> Loading data. Please wait. </template>
            <Column
              field="item"
              header="ITEM"
              style="width: 30%"
            >
            </Column>
            <Column
              field="quantity"
              header="QUANTITY"
              style="width: 10%; text-align: right"
              :pt="{ headerContent: 'justify-content-end' }"
            >
            </Column>
            <Column
              field="expiration_date"
              header="EXP. DATE"
              style="width: 10%; text-align: right"
              :pt="{ headerContent: 'justify-content-end' }"
            >
            </Column>
            <Column
              field="from"
              header="FROM"
              style="width: 20%; text-align: right"
              :pt="{ headerContent: 'justify-content-end' }"
            >
            </Column>
            <Column
              header="ACTION"
              style="width: 10%; text-align: center"
              :pt="{ headerContent: 'justify-content-center' }"
            >
              <template #body="slotProps">
                <div>
                  <Button
                    :disabled="canTransact == false"
                    v-if="slotProps.data.status != 'RECEIVED'"
                    label="RECEIVE"
                    :icon="form.processing ? 'pi pi-spin pi-spinner' : 'pi pi-check'"
                    @click="receivedStock(slotProps)"
                  />
                </div>
              </template>
            </Column>
          </DataTable>
        </TabPanel>
      </TabView>
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
import InputNumber from 'primevue/inputnumber';
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
    InputNumber,
    Tag,
  },
  props: {
    authWardcode: Object,
    wardStocks: Object,
    wardStocks2: Object,
    // wardStocksMedicalGasess: Object,
    transferredStock: Object,
    employees: Object,
    canTransact: Boolean,
    locations: Object,
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
      employeesList: [],
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

      if (e.message == this.this.$page.props.auth.user.location.location_name.wardcode) {
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
    this.storeEmployeesInContainer();

    this.loading = false;
  },
  methods: {
    tzone(date) {
      return moment.tz(date, 'Asia/Manila').format('L');
    },
    storeLocationsInContainer() {
      this.locations.forEach((e) => {
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
    storeEmployeesInContainer() {
      this.employees.forEach((e) => {
        // console.log(e);
        this.employeesList.push({
          employeeid: e.employeeid,
          name: '(' + e.employeeid + ') - ' + e.firstname + ' ' + e.lastname,
        });
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
          item: null,
          quantity: null,
          expiration_date: null,
          from: null,
        });
      }
    },
    storeWardStockInContainer() {
      // FROM CSR
      this.wardStocks.forEach((e) => {
        // let expiration_date = moment.tz(e.expiration_date, 'Asia/Manila').format('MM/DD/YYYY');

        this.wardStocksList.push({
          ward_stock_id: e.id,
          from: e.from,
          cl2comb: e.item_details.cl2comb,
          cl2desc: e.item_details.cl2desc,
          quantity: e.quantity,
          expiration_date: e.expiration_date,
        });
      });

      // FROM TRANSFERRED STOCKS (WARD)
      this.wardStocks2.forEach((e) => {
        // let expiration_date = moment.tz(e.expiration_date, 'Asia/Manila').format('MM/DD/YYYY');

        this.wardStocksList.push({
          ward_stock_id: e.id,
          from: e.from,
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
      if (this.form.processing) {
        return false;
      }

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
      if (this.form.processing) {
        return false;
      }

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
      if (this.formReceiveStock.processing) {
        return false;
      }

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
