<template>
  <app-layout>
    <Head title="NMIS - Dashboard" />

    <div class="surface-ground">
      <Toast />

      <div class="grid">
        <div class="col-12 md:col-4 lg:col-4">
          <div class="surface-card shadow-2 p-3 border-round">
            <div class="mb-3">
              <div class="flex justify-content-between">
                <div>
                  <span class="block text-xl text-900 font-bold">PENDING</span>
                </div>

                <!-- a -->

                <Link href="issueitems?page=1&status=PENDING">
                  <div
                    class="flex align-items-center justify-content-center bg-blue-100 border-round"
                    style="width: 3rem; height: 3rem"
                  >
                    <v-icon
                      name="bi-cart"
                      class="pi pi-send text-blue-500 text-xl"
                    ></v-icon>
                  </div>
                </Link>
              </div>
            </div>
            <div class="flex justify-content-center">
              <span class="text-5xl font-bold text-blue-500">{{ pending_requests_container }}</span>
            </div>
          </div>
        </div>
        <div class="col-12 md:col-4 lg:col-4">
          <div class="surface-card shadow-2 p-3 border-round">
            <div class="mb-3">
              <div class="flex justify-content-between">
                <div>
                  <span class="block text-xl text-900 font-bold">CANCELLED</span>
                </div>

                <Link href="issueitems?page=1&status=CANCELLED">
                  <div
                    class="flex align-items-center justify-content-center bg-orange-100 border-round"
                    style="width: 3rem; height: 3rem"
                  >
                    <v-icon
                      name="fc-cancel"
                      class="pi pi-send text-orange-500 text-xl"
                    ></v-icon>
                  </div>
                </Link>
              </div>
            </div>
            <div class="flex justify-content-center">
              <span class="text-5xl font-bold text-orange-500">{{ cancelled_requests_container }}</span>
            </div>
          </div>
        </div>
        <div class="col-12 md:col-4 lg:col-4">
          <div class="surface-card shadow-2 p-3 border-round">
            <div class="mb-3">
              <div class="flex justify-content-between">
                <div>
                  <span class="block text-xl text-900 font-bold">COMPLETED</span>
                </div>

                <Link href="issueitems?page=1&status=RECEIVED">
                  <div
                    class="flex align-items-center justify-content-center bg-green-100 border-round"
                    style="width: 3rem; height: 3rem"
                  >
                    <v-icon
                      name="bi-check-lg"
                      class="pi pi-send text-green-500 text-xl"
                    ></v-icon>
                  </div>
                </Link>
              </div>
            </div>
            <div class="flex justify-content-center">
              <span class="text-5xl font-bold text-green-500">{{ completed_requests_container }}</span>
            </div>
          </div>
        </div>
        <!--  -->
      </div>
      <div class="my-2"></div>
      <div class="grid">
        <div class="col-12 md:col-12 lg:col-8">
          <div class="surface-card shadow-2 p-3 border-round">
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
                  <span class="text-xl text-900 font-bold text-cyan-500 hover:text-cyan-700 mr-2">DELIVERIES</span>

                  <div>
                    <span class="p-input-icon-left mr-2">
                      <i class="pi pi-search" />
                      <InputText
                        v-model="search"
                        placeholder="Search item"
                      />
                    </span>
                    <Button
                      label="Add deliveries"
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
                field="ris_no"
                header="RIS NO."
              >
              </Column>
              <Column
                field="temp_ris_no"
                header="TEMPORARY NO."
              >
              </Column>
              <Column
                field="suppname"
                header="SUPPLIER"
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
                field="uomdesc"
                header="Unit"
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
                <template #body="{ data }">
                  <span
                    v-if="data.quantity > 30"
                    class="text-green-500"
                    >{{ data.quantity }}</span
                  >
                  <span
                    v-else
                    class="text-yellow-500"
                    >{{ data.quantity }}</span
                  >
                </template>
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
                  <div class="flex flex-row m-0 p-0">
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
                  </div>
                </template>
              </Column>
            </DataTable>
          </div>
        </div>
        <div class="col-12 md:col-12 lg:col-4">
          <div class="surface-card shadow-2 p-3 border-round">
            <div class="mb-3">
              <div class="flex justify-content-between">
                <span class="block text-xl text-900 font-bold">About to expire</span>
                <Link href="csrstocks">
                  <div
                    class="flex align-items-center justify-content-center bg-purple-100 border-round"
                    style="width: 2.5rem; height: 2.5rem"
                  >
                    <v-icon
                      name="md-newreleases-outlined"
                      class="text-purple-500 text-xl"
                    ></v-icon>
                  </div>
                </Link>
              </div>
            </div>
            <DataTable
              :value="about_to_expire_container"
              showGridlines
              class="p-datatable-sm"
              scrollable
              scrollHeight="500px"
              tableStyle="min-height: h-full;"
            >
              <template #header>
                <div class="flex justify-content-start">
                  <p class="text-xl text-purple-500 font-semibold">{{ currentMonth }}</p>
                </div>
              </template>
              <Column
                field="item"
                header="ITEM"
                style="width: 80%"
              ></Column>
              <Column
                field="expiration_date"
                header="EXP. DATE"
                style="width: 20%"
              >
                <template #body="{ data }">
                  {{ tzone(data.expiration_date) }}
                </template>
              </Column>
            </DataTable>
          </div>
        </div>
      </div>

      <Dialog
        v-model:visible="createStockDialog"
        :style="{ width: '450px' }"
        header="Delivery Detail"
        :modal="true"
        class="p-fluid"
        @hide="clickOutsideDialog"
      >
        <div class="field">
          <label for="ris_no">RIS no.</label>
          <InputText
            id="ris_no"
            v-model.trim="form.ris_no"
            required="true"
            autofocus
            :class="{ 'p-invalid': form.ris_no == '' }"
            @keyup.enter="submit"
          />
          <!-- <small
            class="text-error"
            v-if="form.errors.ris_no"
          >
            {{ form.errors.ris_no }}
          </small> -->
        </div>
        <div class="field">
          <label for="Item">Supplier</label>
          <Dropdown
            required="true"
            v-model="form.suppcode"
            :options="suppliersList"
            :virtualScrollerOptions="{ itemSize: 38 }"
            filter
            dataKey="suppcode"
            optionLabel="suppname"
            optionValue="suppcode"
            class="w-full"
            :class="{ 'p-invalid': form.suppcode == '' }"
          />
        </div>
        <div class="field">
          <label for="fundSource">Fund source</label>
          <Dropdown
            id="fundSource"
            required="true"
            v-model="form.fund_source"
            :options="fundSourceList"
            :virtualScrollerOptions="{ itemSize: 38 }"
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
            :virtualScrollerOptions="{ itemSize: 38 }"
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
          <label for="unit">Unit</label>
          <InputText
            id="unit"
            v-model.trim="selectedItemsUomDesc"
            readonly
          />
        </div>
        <div class="field">
          <label for="brand">Brand</label>
          <Dropdown
            required="true"
            v-model="form.brand"
            :options="brandDropDownList"
            :virtualScrollerOptions="{ itemSize: 38 }"
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
            >Are you sure you want to delete <b>{{ form.cl2desc }}</b> with RIS number <b>{{ form.ris_no }}</b> ?</span
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
            :disabled="form.remarks == '' || form.remarks == null || form.processing"
            @click="deleteItem"
          />
        </template>
      </Dialog>
    </div>
  </app-layout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import { FilterMatchMode } from 'primevue/api';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dropdown from 'primevue/dropdown';
import Tag from 'primevue/tag';
import Textarea from 'primevue/textarea';
import Toast from 'primevue/toast';
import Avatar from 'primevue/avatar';
import Calendar from 'primevue/calendar';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import moment, { now } from 'moment';
import { use } from 'echarts/core';
import { CanvasRenderer } from 'echarts/renderers';
import { PieChart, BarChart } from 'echarts/charts';
import { TitleComponent, TooltipComponent, LegendComponent, GridComponent } from 'echarts/components';
import VChart, { THEME_KEY } from 'vue-echarts';
import Echo from 'laravel-echo';

use([CanvasRenderer, PieChart, BarChart, TitleComponent, TooltipComponent, LegendComponent, GridComponent]);

export default {
  components: {
    AppLayout,
    Head,
    Link,
    DataTable,
    Column,
    VChart,
    Dropdown,
    Tag,
    Textarea,
    Toast,
    Avatar,
    Calendar,
    Button,
    Dialog,
    InputText,
  },
  props: {
    pending_requests: Number,
    cancelled_requests: Number,
    completed_requests: Number,
    about_to_expire: Object,
    items: Object,
    stocks: Object,
    brands: Object,
    typeOfCharge: Object,
    fundSource: Object,
    suppliers: Object,
  },
  data() {
    return {
      minimumDate: null,
      // paginator
      loading: false,
      totalRecords: null,
      rows: null,
      // end paginator
      pending_requests_container: null,
      cancelled_requests_container: null,
      completed_requests_container: null,
      about_to_expire_container: [],
      currentMonth: null,
      stockId: null,
      isUpdate: false,
      search: '',
      options: {},
      params: {},
      totalRecords: null,
      rows: null,
      createStockDialog: false,
      deleteStockDialog: false,
      itemsList: [],
      fundSourceList: [],
      suppliersList: [],
      brandsList: [],
      brandDropDownList: [],
      stocksList: [],
      // manufactured date
      from_md: null,
      to_md: null,
      // delivered date
      from_dd: null,
      to_dd: null,
      // expiration date
      from_ed: null,
      to_ed: null,
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
      selectedItemsUomDesc: null,
      form: this.$inertia.form({
        stockId: null,
        ris_no: null,
        suppcode: null,
        fund_source: null,
        cl2comb: null,
        uomcode: null,
        brand: null,
        cl2desc: null,
        quantity: null,
        manufactured_date: null,
        delivered_date: null,
        expiration_date: null,
        remarks: null,
      }),
    };
  },
  created() {
    this.totalRecords = this.stocks.total;
    this.params.page = this.stocks.current_page;
    this.rows = this.stocks.per_page;
  },
  mounted() {
    window.Echo.channel('request').listen('RequestStock', (args) => {
      router.reload({
        onSuccess: (e) => {
          this.pending_requests_container = null;
          this.cancelled_requests_container = null;
          this.completed_requests_container = null;
          this.about_to_expire_container = [];

          this.storePendingRequests();
          this.storeCancelledRequests();
          this.storeCompletedRequests();
          this.storeAboutToExpiredInContainer();
          this.getCurrentMonth();
        },
      });
    });

    this.setMinimumDate();
    this.storeFundSourceInContainer();
    this.storeItemsInContainer();
    this.storeStocksInContainer();
    this.storeActiveBrandsInContainer();
    this.storeSuppliersInContainer();

    this.storePendingRequests();
    this.storeCancelledRequests();
    this.storeCompletedRequests();
    this.storeAboutToExpiredInContainer();
    this.getCurrentMonth();
  },
  methods: {
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
    storeSuppliersInContainer() {
      this.suppliers.forEach((e) => {
        this.suppliersList.push({
          suppcode: e.suppcode,
          suppname: e.suppname,
        });
      });
    },
    storeFundSourceInContainer() {
      this.typeOfCharge.forEach((e) => {
        this.fundSourceList.push({
          chrgcode: e.chrgcode,
          chrgdesc: e.chrgdesc,
          bentypcod: e.bentypcod,
          chrgtable: e.chrgtable,
        });
      });

      this.fundSource.forEach((e) => {
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
          uomcode: e.unit == null ? null : e.unit.uomcode,
          uomdesc: e.unit == null ? null : e.unit.uomdesc,
        });
      });
    },
    storeStocksInContainer() {
      //   console.log(this.stocks.data);
      this.stocks.data.forEach((e) => {
        this.stocksList.push({
          id: e.id,
          ris_no: e.ris_no == null ? null : e.ris_no,
          temp_ris_no: e.temp_ris_no == null ? null : e.temp_ris_no,
          suppcode: e.suppcode,
          suppname: e.supplier_detail.suppname,
          chrgcode: e.type_of_charge === null ? e.fund_source.fsid : e.type_of_charge.chrgcode,
          chrgdesc: e.type_of_charge === null ? e.fund_source.fsName : e.type_of_charge.chrgdesc,
          cl2comb: e.cl2comb,
          cl2desc: e.item_detail.cl2desc,
          uomcode: e.unit == null ? null : e.unit.uomcode,
          uomdesc: e.unit == null ? null : e.unit.uomdesc,
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

      this.$inertia.get('csrdashboard', this.params, {
        preserveState: true,
        preserveScroll: true,
        onFinish: (visit) => {
          this.totalRecords = this.stocks.total;
          this.stocksList = [];
          this.brandDropDownList = [];
          this.storeStocksInContainer();
          this.storeActiveBrandsInContainer();
          this.storeAboutToExpiredInContainer();
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
    clickOutsideDialog() {
      this.$emit(
        'hide',
        (this.stockId = null),
        (this.isUpdate = false),
        (this.isUpdateBrand = false),
        this.form.clearErrors(),
        this.form.reset()
      );
    },
    editItem(item) {
      //   console.log(item);
      this.isUpdate = true;
      this.createStockDialog = true;
      this.stockId = item.id;
      this.form.ris_no = item.ris_no;
      this.form.suppcode = item.suppcode;
      this.form.fund_source = item.chrgcode;
      this.form.cl2comb = item.cl2comb;
      this.form.uomcode = item.uomcode;
      this.form.brand = item.brand_id;
      this.form.quantity = item.quantity;
      this.form.manufactured_date = item.manufactured_date;
      this.form.delivered_date = item.delivered_date;
      this.form.expiration_date = item.expiration_date;
    },
    submit() {
      if (this.form.processing) {
        return false;
      }

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
      this.form.ris_no = item.ris_no;
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
      this.stocksList = [];
      this.storeStocksInContainer();
    },
    createdMsg() {
      this.$toast.add({ severity: 'success', summary: 'Success', detail: 'Delivery created', life: 3000 });
    },
    updatedMsg() {
      this.$toast.add({ severity: 'warn', summary: 'Success', detail: 'Delivery updated', life: 3000 });
    },
    deletedMsg() {
      this.$toast.add({ severity: 'error', summary: 'Success', detail: 'Delivery deleted', life: 3000 });
    },
    storeActiveBrandsInContainer() {
      this.brandDropDownList = this.brands.filter((item) => item.status === 'A');
    },
    storePendingRequests() {
      this.pending_requests_container = this.pending_requests;

      //   console.log(this.pending_requests);
    },
    storeCancelledRequests() {
      this.cancelled_requests_container = this.cancelled_requests;
    },
    storeCompletedRequests() {
      this.completed_requests_container = this.completed_requests;
    },
    storeAboutToExpiredInContainer() {
      this.about_to_expire_container = [];

      this.about_to_expire.forEach((e) => {
        this.about_to_expire_container.push({
          item: e.item_detail.cl2desc,
          expiration_date: e.expiration_date,
        });
      });
    },
    getCurrentMonth() {
      this.currentMonth = moment().format('MMMM');
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
    'form.cl2comb': function (val) {
      this.selectedItemsUomDesc = null;

      this.itemsList.forEach((e) => {
        if (e.cl2comb == val) {
          if (e.uomdesc != null) {
            this.selectedItemsUomDesc = e.uomdesc;
            this.form.uomcode = e.uomcode;
          } else {
            this.selectedItemsUomDesc = null;
          }
        }
      });
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
