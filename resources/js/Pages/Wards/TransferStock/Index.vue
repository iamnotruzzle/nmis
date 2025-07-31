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
        :loading="isWardStockLoading"
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
        <!-- <template #empty> No data found. </template> -->
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
            :loading="isEmployeesLoading"
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
            :loading="isTransferredStockLoading"
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
            <!-- <template #empty> No data found. </template> -->
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
import axios from 'axios';

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
    canTransact: Boolean,
  },
  data() {
    return {
      CACHE_CONFIG: {
        WARD_STOCKS: {
          key: 'wardStocksCache',
          timestamp: 'wardStocksCacheTimestamp',
        },
        TRANSFERRED_STOCKS: {
          key: 'transferredStocksCache',
          timestamp: 'transferredStocksCacheTimestamp',
        },
        EMPLOYEES: {
          key: 'employeesCache',
          timestamp: 'employeesCacheTimestamp',
        },
        WARDS: {
          key: 'wardsCache',
          timestamp: 'wardsCacheTimestamp',
        },
      },
      CACHE_DURATION_MS: 1000 * 60 * 5, // 5 minutes
      // loading states
      isTransferredStockLoading: false,
      isEmployeesLoading: false,
      isWardStockLoading: false,
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
    // window.Echo.channel('issued').listen('ItemIssued', (e) => {
    //   this.transferredStocksList = [];
    //   this.loading = true;

    //   if (e.message == this.this.$page.props.auth.user.location.location_name.wardcode) {
    //     this.$inertia.get('transferstock', this.params, {
    //       preserveState: true,
    //       preserveScroll: true,
    //       onFinish: (visit) => {
    //         this.wardStocksList = [];
    //         this.transferredStocksList = [];
    //         this.toReceiveList = [];
    //         this.fetchWardStocks();
    //         this.loading = false;
    //       },
    //     });
    //   }
    // });

    this.loading = false;

    this.fetchWardStocks();
    this.fetchTransferredStocks();
    this.fetchEmployees();
    this.fetchWards();
  },
  methods: {
    // Generic localStorage cache methods
    getCachedData(cacheType) {
      const config = this.CACHE_CONFIG[cacheType];
      const cached = localStorage.getItem(config.key);
      const timestamp = localStorage.getItem(config.timestamp);

      if (!cached || !timestamp) return null;

      const age = Date.now() - parseInt(timestamp);
      if (age > this.CACHE_DURATION_MS) {
        console.log(`⚠️ Cache expired for ${cacheType}`);
        this.clearCacheData(cacheType);
        return null;
      }

      return JSON.parse(cached);
    },

    setCachedData(cacheType, data) {
      const config = this.CACHE_CONFIG[cacheType];
      localStorage.setItem(config.key, JSON.stringify(data));
      localStorage.setItem(config.timestamp, Date.now().toString());
    },

    clearCacheData(cacheType) {
      const config = this.CACHE_CONFIG[cacheType];
      localStorage.removeItem(config.key);
      localStorage.removeItem(config.timestamp);
    },

    clearAllCaches() {
      Object.keys(this.CACHE_CONFIG).forEach((cacheType) => {
        this.clearCacheData(cacheType);
      });
    },

    // Ward Stocks with localStorage caching
    async fetchWardStocks(forceRefresh = false) {
      this.isWardStockLoading = true;
      this.error = null;

      const cached = this.getCachedData('WARD_STOCKS');

      if (cached && !forceRefresh) {
        // console.log('🟢 Using cached ward stocks from localStorage');
        this.wardStocksList = cached;
        this.isWardStockLoading = false;
        return;
      }

      try {
        const response = await axios.get('getWardStocks');
        this.wardStocksList = response.data;
        this.setCachedData('WARD_STOCKS', response.data);
        // console.log('🔵 Fetched fresh ward stocks and cached to localStorage');
      } catch (err) {
        this.error = err.response?.data ?? err.message;
        console.error('❌ Failed to fetch ward stocks:', this.error);
      } finally {
        this.isWardStockLoading = false;
      }
    },

    // Transferred Stocks with localStorage caching
    async fetchTransferredStocks(forceRefresh = false) {
      this.isTransferredStockLoading = true;
      this.error = null;

      const cached = this.getCachedData('TRANSFERRED_STOCKS');

      if (cached && !forceRefresh) {
        // console.log('🟢 Using cached transferred stocks from localStorage');
        this.transferredStocksList = cached.transferred;
        this.toReceiveList = cached.toReceive;
        this.isTransferredStockLoading = false;
        return;
      }

      this.transferredStocksList = [];
      this.toReceiveList = [];

      try {
        const response = await axios.get('getTransferredStocks');
        // console.log('fetchTransferredStocks data: ', response.data);

        if (response.data.length !== 0) {
          response.data.forEach((e) => {
            let expiration_date = moment.tz(e.ward_stock.expiration_date, 'Asia/Manila').format('MM/DD/YYYY');

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

        // Cache both lists together in localStorage
        const cacheData = {
          transferred: this.transferredStocksList,
          toReceive: this.toReceiveList,
        };
        this.setCachedData('TRANSFERRED_STOCKS', cacheData);
        // console.log('🔵 Fetched fresh transferred stocks and cached to localStorage');
      } catch (err) {
        this.error = err.response?.data ?? err.message;
        console.error('Failed to fetch transferred stocks:', this.error);
      } finally {
        this.isTransferredStockLoading = false;
      }
    },

    // Employees with localStorage caching
    async fetchEmployees(forceRefresh = false) {
      this.isEmployeesLoading = true;
      this.error = null;

      const cached = this.getCachedData('EMPLOYEES');

      if (cached && !forceRefresh) {
        // console.log('🟢 Using cached employees from localStorage');
        this.employeesList = cached;
        this.isEmployeesLoading = false;
        return;
      }

      this.employeesList = [];

      try {
        const response = await axios.get('getEmployees');
        // console.log('fetchEmployees data: ', response.data);

        response.data.forEach((e) => {
          this.employeesList.push({
            employeeid: e.employeeid,
            name: '(' + e.employeeid + ') - ' + e.firstname + ' ' + e.lastname,
          });
        });

        this.setCachedData('EMPLOYEES', this.employeesList);
        // console.log('🔵 Fetched fresh employees and cached to localStorage');
      } catch (err) {
        this.error = err.response?.data ?? err.message;
        console.error('Failed to fetch employees:', this.error);
      } finally {
        this.isEmployeesLoading = false;
      }
    },

    // Wards with localStorage caching
    async fetchWards(forceRefresh = false) {
      this.isWardsLoading = true;
      this.error = null;

      const cached = this.getCachedData('WARDS');

      if (cached && !forceRefresh) {
        // console.log('🟢 Using cached wards from localStorage');
        this.locationsList = cached;
        this.isWardsLoading = false;
        return;
      }

      this.locationsList = [];

      try {
        const response = await axios.get('getWards');
        // console.log('fetchWards data: ', response.data);

        response.data.forEach((e) => {
          if (e.wardcode !== 'CSR' && e.wardcode !== 'ADMIN') {
            this.locationsList.push({
              wardcode: e.wardcode,
              wardname: e.wardname,
            });
          }
        });

        this.setCachedData('WARDS', this.locationsList);
        // console.log('🔵 Fetched fresh wards and cached to localStorage');
      } catch (err) {
        this.error = err.response?.data ?? err.message;
        console.error('Failed to fetch wards:', this.error);
      } finally {
        this.isWardsLoading = false;
      }
    },

    async invalidateAndRefreshWardStocks() {
      this.clearCacheData('WARD_STOCKS');
      await this.fetchWardStocks(true);
    },

    // Method to refresh specific data after POST operations
    async refreshDataAfterPost() {
      console.log('🔄 Refreshing wardStocks, transferredStocks, and toReceiveList after POST');

      // Clear localStorage cache for the three specific datasets
      this.clearCacheData('WARD_STOCKS');
      this.clearCacheData('TRANSFERRED_STOCKS');

      // Fetch fresh data and cache in localStorage
      await Promise.all([this.fetchWardStocks(true), this.fetchTransferredStocks(true)]);
    },

    updateData() {
      this.loading = true;

      this.$inertia.get('transferstock', this.params, {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => {
          this.fetchWardStocks(true);
        },
      });
    },

    submit() {
      if (this.form.processing) {
        return false;
      }

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
            this.createdMsg();

            // Refresh only the data that changes after POST
            this.refreshDataAfterPost();
          },
        });
      }
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
          this.stockReceivedMsg();

          // Refresh only the data that changes after POST
          this.refreshDataAfterPost();
        },
      });
    },

    tzone(date) {
      return moment.tz(date, 'Asia/Manila').format('L');
    },

    // emit close dialog
    clickOutsideDialog() {
      this.$emit('hide', (this.isUpdate = false), this.form.clearErrors(), this.form.reset());
    },
    transferStock(item) {
      this.transferStockDialog = true;
      this.form.ward_stock_id = item.ward_stock_id;
      this.form.cl2desc = item.cl2desc;
      this.form.prevQuantity = item.quantity;
      this.form.expiration_date = item.expiration_date;
    },
    // submit() {
    //   if (this.form.processing) {
    //     return false;
    //   }

    //   // the form is submitted only if the conditions is met
    //   if (
    //     Number(this.form.quantity) <= Number(this.form.prevQuantity) &&
    //     Number(this.form.quantity) != 0 &&
    //     Number(this.form.quantity) != null &&
    //     this.form.to != null &&
    //     this.form.requested_by != null &&
    //     this.form.remarks != null
    //   ) {
    //     this.form.post(route('transferstock.store'), {
    //       preserveScroll: true,
    //       onSuccess: () => {
    //         this.transferStockDialog = false;
    //         this.cancel();
    //         this.updateData();
    //         this.createdMsg();
    //       },
    //     });
    //   }
    // },

    cancel() {
      this.isUpdate = false;
      this.transferStockDialog = false;
      this.form.reset();
      this.form.clearErrors();
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
      this.receivedItemDialog = true;
      this.formReceiveStock.id = item.data.id;
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
