<template>
  <app-layout>
    <Head title="NMIS - Items" />

    <div class="card">
      <Toast />

      <DataTable
        class="p-datatable-sm"
        v-model:expandedRows="expandedRow"
        v-model:filters="filters"
        :value="itemsList"
        paginator
        :rows="20"
        :rowsPerPageOptions="[20, 30, 40]"
        dataKey="cl2comb"
        filterDisplay="row"
        sortField="item"
        :sortOrder="1"
        removableSort
        :globalFilterFields="['cl1comb', 'cl2desc', 'cl2stat']"
        showGridlines
      >
        <template #header>
          <div class="flex flex-wrap align-items-center justify-content-between gap-2">
            <span class="text-xl text-900 font-bold text-primary">ITEMS</span>
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
                label="Add item"
                icon="pi pi-plus"
                iconPos="right"
                @click="openCreateItemDialog"
              />
            </div>
          </div>
        </template>
        <template #empty> No item found. </template>
        <template #loading> Loading item data. Please wait. </template>
        <Column expander />
        <Column
          field="mainCategory"
          header="MAIN CATEGORY"
          :showFilterMenu="false"
          style="width: 10%"
        >
          <template #body="{ data }">
            {{ data.mainCategory }}
          </template>
          <template #filter="{ filterModel, filterCallback }">
            <Dropdown
              v-model="filterModel.value"
              :options="mainCategoryFilter"
              @change="filterCallback()"
              optionLabel="name"
              optionValue="code"
              placeholder="NO FILTER"
              class="w-full"
            />
          </template>
        </Column>
        <Column
          field="cl1comb"
          header="SUB-CATEGORY ID"
          style="width: 10%"
        >
          <template #body="{ data }">
            {{ data.cl1comb }}
          </template>
        </Column>
        <Column
          field="subCategory"
          header="SUB-CATEGORY"
          :showFilterMenu="false"
          style="width: 20%"
        >
          <template #body="{ data }">
            {{ data.subCategory }}
          </template>
        </Column>
        <Column
          field="cl2desc"
          header="DESCRIPTION"
          style="width: 30%"
        >
          <template #body="{ data }">
            {{ data.cl2desc }}
          </template>
        </Column>
        <Column
          field="normal_stock"
          header="NORMAL STOCK"
          style="text-align: right; width: 5%"
        >
          <template #body="{ data }">
            {{ data.normal_stock }}
          </template>
        </Column>
        <Column
          field="alert_stock"
          header="ALERT STOCK"
          style="text-align: right; width: 5%"
        >
          <template #body="{ data }">
            {{ data.alert_stock }}
          </template>
        </Column>
        <Column
          field="critical_stock"
          header="CRITICAL STOCK"
          style="text-align: right; width: 5%"
        >
          <template #body="{ data }">
            {{ data.critical_stock }}
          </template>
        </Column>
        <Column
          header="UNIT"
          style="width: 5%"
        >
          <template #body="{ data }">
            {{ data.uomdesc }}
          </template>
        </Column>
        <Column
          field="cl2stat"
          header="STATUS"
          style="width: 5%"
          :showFilterMenu="false"
        >
          <template #body="{ data }">
            <div class="text-center">
              <Tag
                v-if="data.cl2stat == 'A'"
                value="ACTIVE"
                severity="success"
              />
              <Tag
                v-else
                value="INACTIVE"
                severity="danger"
              />
            </div>
          </template>
          <template #filter="{ filterModel, filterCallback }">
            <Dropdown
              v-model="filterModel.value"
              :options="statusFilter"
              @change="filterCallback()"
              optionLabel="name"
              optionValue="code"
              placeholder="NO FILTER"
              class="w-full"
            />
          </template>
        </Column>
        <Column
          header="ACTION"
          style="width: 5%"
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

            <!-- <Button
              icon="pi pi-trash"
              rounded
              text
              severity="danger"
              @click="confirmDeleteItem(slotProps.data)"
            /> -->
          </template>
        </Column>
        <template #expansion="slotProps">
          <div class="max-w-full flex justify-content-center">
            <div class="w-11 flex flex-column align-items-center">
              <DataTable
                paginator
                :rows="5"
                class="w-8 mt-2"
                showGridlines
                :value="slotProps.data.prices"
                size="small"
                removableSort
              >
                <template #header>
                  <div class="flex justify-content-between w-full">
                    <div class="text-lg font-bold my-3">
                      Prices for <span class="text-primary">[ {{ slotProps.data.cl2desc }} ]</span>
                    </div>

                    <div>
                      <Button
                        label="Add price"
                        icon="pi pi-plus"
                        iconPos="right"
                        size="small"
                        class="ml-2 my-0"
                        @click="openCreateItemPriceDialog(slotProps.data)"
                      />
                    </div>
                  </div>
                </template>
                <Column
                  field="price"
                  header="SELLING PRICE"
                  style="width: 20%"
                >
                </Column>
                <Column
                  field="entry_by"
                  header="ENTRY BY"
                  style="width: 50%"
                >
                </Column>
                <Column
                  field="created_at"
                  header="CREATED AT"
                  style="width: 20%"
                >
                  <template #body="{ data }">
                    {{ tzone(data.created_at) }}
                  </template>
                </Column>
                <Column
                  header="ACTION"
                  style="width: 10%"
                >
                  <template #body="slotProps">
                    <div class="flex flex-row">
                      <Button
                        icon="pi pi-pencil"
                        class="mr-1"
                        rounded
                        text
                        severity="warning"
                        @click="editPrice(slotProps.data)"
                      />

                      <Button
                        icon="pi pi-trash"
                        rounded
                        text
                        severity="danger"
                        @click="confirmDeletePrice(slotProps.data)"
                      />
                    </div>
                  </template>
                </Column>
              </DataTable>

              <div class="w-11 flex flex-column">
                <div class="text-2xl font-bold mt-4 flex justify-content-around align-items-center w-full">
                  <span>Price changes</span>
                  <Dropdown
                    v-model="dateFilter"
                    :options="dateFilterList"
                    optionLabel="name"
                    optionValue="value"
                    class="w-full md:w-14rem"
                  />
                </div>

                <v-chart
                  class="h-30rem w-full ma-0 pa-0"
                  :option="priceChangesOptions(slotProps.data)"
                  autoresize
                />
              </div>
            </div>
          </div>
        </template>
      </DataTable>

      <!-- create & edit dialog -->
      <Dialog
        v-model:visible="createItemDialog"
        :style="{ width: '450px' }"
        :modal="true"
        class="p-fluid"
        @hide="clickOutsideDialog"
        dismissableMask
      >
        <template #header>
          <div class="text-primary text-xl font-bold">ITEM DETAIL</div>
        </template>
        <div class="field">
          <label for="mainCategory">Main category</label>
          <Dropdown
            v-model.trim="form.mainCategory"
            required="true"
            :options="pimsCategoryList"
            :virtualScrollerOptions="{ itemSize: 38 }"
            filter
            optionLabel="categoryname"
            optionValue="catID"
            class="w-full mb-3"
            :class="{ 'p-invalid': form.cl1comb == '' }"
          />
          <small
            class="text-error"
            v-if="form.errors.unit"
          >
          </small>
        </div>
        <div class="field">
          <label for="cl1comb">Sub-Category</label>
          <Dropdown
            v-model.trim="form.cl1comb"
            required="true"
            :options="cl1combsList"
            :virtualScrollerOptions="{ itemSize: 38 }"
            filter
            optionLabel="cl1comb"
            optionValue="cl1comb"
            class="w-full mb-3"
            :class="{ 'p-invalid': form.cl1comb == '' }"
          />
          <small
            class="text-error"
            v-if="form.errors.cl1comb"
          >
            {{ form.errors.cl1comb }}
          </small>
        </div>
        <div class="field">
          <label>Description</label>
          <InputText
            id="Description"
            v-model.trim="form.cl2desc"
            required="true"
            autofocus
            :class="{ 'p-invalid': form.cl2desc == '' }"
            @keyup.enter="submit"
          />
          <small
            class="text-error"
            v-if="form.errors.cl2desc"
          >
            {{ form.errors.cl2desc }}
          </small>
        </div>
        <div class="field">
          <label for="unit">Unit</label>
          <Dropdown
            v-model.trim="form.unit"
            required="true"
            :options="unitsList"
            :virtualScrollerOptions="{ itemSize: 38 }"
            filter
            optionLabel="uomdesc"
            optionValue="uomcode"
            class="w-full mb-3"
            :class="{ 'p-invalid': form.cl1comb == '' }"
          />
          <small
            class="text-error"
            v-if="form.errors.unit"
          >
          </small>
        </div>
        <div class="field">
          <label>Normal stock</label>
          <InputText
            id="Normal stock"
            v-model.trim="form.normal_stock"
            required="true"
            autofocus
            type="number"
            :class="{ 'p-invalid': form.normal_stock == '' }"
            @keyup.enter="submit"
          />
          <small
            class="text-error"
            v-if="form.errors.normal_stock"
          >
            {{ form.errors.normal_stock }}
          </small>
        </div>
        <div class="field">
          <label>Alert stock</label>
          <InputText
            id="Alert stock"
            v-model.trim="form.alert_stock"
            required="true"
            autofocus
            type="number"
            :class="{ 'p-invalid': form.alert_stock == '' }"
            @keyup.enter="submit"
          />
          <small
            class="text-error"
            v-if="form.errors.alert_stock"
          >
            {{ form.errors.alert_stock }}
          </small>
        </div>
        <div class="field">
          <label>Critical stock</label>
          <InputText
            id="Critical stock"
            v-model.trim="form.critical_stock"
            required="true"
            autofocus
            type="number"
            :class="{ 'p-invalid': form.critical_stock == '' }"
            @keyup.enter="submit"
          />
          <small
            class="text-error"
            v-if="form.errors.critical_stock"
          >
            {{ form.errors.critical_stock }}
          </small>
        </div>
        <div class="field">
          <label for="cl2stat">Status</label>
          <Dropdown
            v-model="form.cl2stat"
            :options="cl2stats"
            optionLabel="name"
            optionValue="value"
            class="w-full"
          />
          <small
            class="text-error"
            v-if="form.errors.cl2stat"
          >
            {{ form.errors.cl2stat }}
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
          <span v-if="form"
            >Are you sure you want to delete <b>{{ form.cl2desc }}</b> ?</span
          >
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
            :disabled="form.processing"
            @click="deleteItem"
          />
        </template>
      </Dialog>

      <!-- create & edit price dialog -->
      <Dialog
        v-model:visible="createItemPriceDialog"
        :style="{ width: '450px' }"
        header="Price Detail"
        :modal="true"
        class="p-fluid"
        @hide="clickOutsidePriceDialog"
        dismissableMask
      >
        <div class="field">
          <label for="selling_price">Selling price</label>
          <InputText
            id="selling_price"
            v-model.trim="formPrice.selling_price"
            required="true"
            type="number"
            autofocus
            :class="{ 'p-invalid': formPrice.selling_price == '' }"
            @keyup.enter="submitPrice"
          />
          <small
            class="text-error"
            v-if="formPrice.errors.selling_price"
          >
            {{ formPrice.errors.selling_price }}
          </small>
        </div>
        <template #footer>
          <Button
            label="Cancel"
            icon="pi pi-times"
            severity="danger"
            text
            @click="cancelPrice"
          />
          <Button
            v-if="isPriceUpdate == true"
            label="Update"
            icon="pi pi-check"
            severity="warning"
            text
            type="submit"
            :disabled="formPrice.processing"
            @click="submitPrice"
          />
          <Button
            v-else
            label="Save"
            icon="pi pi-check"
            text
            type="submit"
            :disabled="formPrice.processing"
            @click="submitPrice"
          />
        </template>
      </Dialog>

      <!-- Delete item price confirmation dialog -->
      <Dialog
        v-model:visible="deleteItemPriceDialog"
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
            Are you sure you want to delete <b>₱ {{ formPrice.selling_price }}</b> price ?
          </span>
        </div>
        <template #footer>
          <Button
            label="No"
            icon="pi pi-times"
            class="p-button-text"
            @click="deleteItemPriceDialog = false"
          />
          <Button
            label="Yes"
            icon="pi pi-check"
            severity="danger"
            text
            :disabled="form.processing"
            @click="deletePrice"
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
import InputNumber from 'primevue/inputnumber';
import Tag from 'primevue/tag';
import moment from 'moment';
import { use } from 'echarts/core';
import { CanvasRenderer } from 'echarts/renderers';
import { LineChart } from 'echarts/charts';
import { TitleComponent, TooltipComponent, LegendComponent, GridComponent } from 'echarts/components';
import VChart, { THEME_KEY } from 'vue-echarts';

use([CanvasRenderer, LineChart, TitleComponent, TooltipComponent, LegendComponent, GridComponent]);

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
    InputNumber,
    VChart,
    Tag,
  },
  props: {
    cl1combs: Array,
    pimsCategory: Array,
    units: Array,
    items: Object,
  },
  data() {
    return {
      // data table expand
      expandedRow: [],
      // end data table expand
      // paginator
      loading: false,
      rows: null,
      // end paginator
      entry_by: null,
      authLocation: null,
      itemId: null,
      isUpdate: false,
      createItemDialog: false,
      deleteItemDialog: false,
      // price
      priceId: null,
      isPriceUpdate: false,
      createItemPriceDialog: false,
      deleteItemPriceDialog: false,
      // end price
      dateFilter: 'this year',
      selectedStatus: null,
      statusFilter: [
        { name: 'Active', code: 'A' },
        { name: 'Inactive', code: 'I' },
      ],
      selectedCatID: null,
      mainCategoryFilter: [
        { name: 'Drugs and medicines', code: 'Drugs and medicines' },
        { name: 'IT supplies', code: 'IT supplies' },
        { name: 'Medical supplies', code: 'Medical supplies' },
        { name: 'Office Supplies', code: 'Office Supplies' },
      ],
      selectedCl1comb: null,
      subCategoryFilter: [],
      cl1desc: '',
      itemsList: [],
      cl1combsList: [],
      // TODO add quarterly filter
      pimsCategoryList: [],
      dateFilterList: [
        {
          name: 'yesterday',
          value: 'yesterday',
        },
        {
          name: 'today',
          value: 'today',
        },
        {
          name: 'this week',
          value: 'this week',
        },
        {
          name: 'this month',
          value: 'this month',
        },
        {
          name: 'this year',
          value: 'this year',
        },
      ],
      unitsList: [],
      filters: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
        cl1comb: { value: null, matchMode: FilterMatchMode.CONTAINS },
        cl2desc: { value: null, matchMode: FilterMatchMode.CONTAINS },
        cl2stat: { value: null, matchMode: FilterMatchMode.EQUALS },
        mainCategory: { value: null, matchMode: FilterMatchMode.EQUALS },
      },
      cl2stats: [
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
        cl2comb: null,
        cl1comb: null,
        cl2code: null,
        cl2desc: null,
        unit: null,
        cl2stat: null,
        mainCategory: null,
        normal_stock: null,
        critical_stock: null,
        alert_stock: null,
        entry_by: null,
        location: null,
      }),
      formPrice: this.$inertia.form({
        id: null,
        cl2comb: null,
        selling_price: null,
        entry_by: this.$page.props.auth.user.userDetail.employeeid,
      }),
    };
  },

  // created will be initialize before mounted
  mounted() {
    this.storeCl1combsInContainer();
    this.storeItemInContainer();
    this.storeUnitsInContainer();
    this.storePimsCategoryInContainer();

    this.entry_by = this.$page.props.auth.user.userDetail.employeeid;
    this.authLocation = this.$page.props.auth.user;
    // console.log(this.authLocation.location.wardcode);

    this.loading = false;
  },
  computed: {
    user() {
      return this.$page.props.auth.user;
    },
  },
  methods: {
    tzone(date) {
      return moment.tz(date, 'Asia/Manila').format('L');
    },
    storePimsCategoryInContainer() {
      this.pimsCategory.forEach((e) => {
        this.pimsCategoryList.push({
          catID: e.catID,
          categoryname: e.categoryname,
        });
      });
    },
    storeCl1combsInContainer() {
      this.cl1combs.forEach((e) => {
        this.cl1combsList.push({
          cl1comb: e.cl1comb,
          cl1desc: e.cl1desc,
        });
      });
    },
    storeUnitsInContainer() {
      this.units.forEach((e) => {
        this.unitsList.push({
          uomcode: e.uomcode,
          uomdesc: e.uomdesc,
        });
      });
    },
    // use storeItemInContainer() function so that every time you make
    // server request such as POST, the data in the table
    // is updated
    storeItemInContainer() {
      this.items.forEach((e) => {
        const existingItemIndex = this.itemsList.findIndex((item) => item.cl2comb === e.cl2comb);

        if (existingItemIndex === -1) {
          // Item does not exist, add a new object to the itemsList
          this.itemsList.push({
            cl2comb: e.cl2comb,
            catID: e.catID,
            mainCategory: e.main_category,
            cl1comb: e.cl1comb,
            subCategory: e.sub_category == '' || e.sub_category == null ? null : e.sub_category,
            cl2code: e.cl2code,
            cl2desc: e.item,
            uomcode: e.uomcode,
            uomdesc: e.unit,
            cl2stat: e.cl2stat,
            normal_stock: e.normal_stock,
            alert_stock: e.alert_stock,
            critical_stock: e.critical_stock,
            prices:
              e.price == null
                ? []
                : [
                    {
                      price_id: e.price_id,
                      price: e.price,
                      entry_by:
                        e.entry_by == null
                          ? null
                          : e.entry_by_firstname + ' ' + e.entry_by_middlename + ' ' + e.entry_by_lastname,
                      created_at: e.price_created_at,
                    },
                  ],
          });
        } else {
          // Item already exists, insert the prices into the existing item's prices array
          if (e.price == null) {
            this.itemsList[existingItemIndex].prices.push([]);
          } else {
            this.itemsList[existingItemIndex].prices.push({
              price_id: e.price_id,
              price: e.price,
              entry_by:
                e.entry_by == null
                  ? null
                  : e.entry_by_firstname + ' ' + e.entry_by_middlename + ' ' + e.entry_by_lastname,
              created_at: e.price_created_at,
            });
          }
        }
      });
    },
    priceChangesOptions(data) {
      // console.log('price data', data.prices);
      // sort the date to ascending order

      let result = data.prices;
      let priceDetails = [...result].sort((a, b) =>
        moment(a.created_at, 'DD-MM-YYYY, hh:mm:ss').diff(moment(b.created_at, 'DD-MM-YYYY, hh:mm:ss'))
      );
      //   console.log(priceDetails);
      let option = {
        grid: {
          show: true,
          left: '15%',
          top: '5%',
          right: '15%',
          bottom: '10%',
        },
        tooltip: {
          trigger: 'axis',
          valueFormatter: (value) => '₱ ' + value,
        },
        xAxis: {
          type: 'category',
          //   data: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
          data: [],
        },
        yAxis: {
          type: 'value',
        },
        series: [
          {
            // data: [150, 230, 224, 218, 135, 147, 260],
            data: [],
            type: 'line',
          },
        ],
      };

      moment.suppressDeprecationWarnings = true;
      switch (this.dateFilter) {
        case 'yesterday':
          priceDetails.forEach((e) => {
            if (e.length != 0) {
              let created_at = moment(e.created_at);

              // if created_ate is equal to yesterday
              if (moment(created_at).format('MM/DD/YYYY') === moment().subtract(1, 'days').format('MM/DD/YYYY')) {
                option.xAxis.data.push(moment(e.created_at).format('YYYY-MM-DD, hh:mm'));
                option.series[0].data.push(Number(e.price).toFixed(2));
              }
            } else {
              option.xAxis.data.push(null);
              option.series.data.push(null);
            }
          });
          break;
        case 'today':
          priceDetails.forEach((e) => {
            if (e.length != 0) {
              let created_at = moment(e.created_at).format('MM/DD/YYYY');
              let today = moment().format('MM/DD/YYYY');
              if (moment(created_at).isSame(today)) {
                option.xAxis.data.push(moment(e.created_at).format('YYYY-MM-DD, hh:mm'));
                option.series[0].data.push(Number(e.price).toFixed(2));
              }
            } else {
              option.xAxis.data.push(null);
              option.series.data.push(null);
            }
          });
          break;
        case 'this week':
          priceDetails.forEach((e) => {
            if (e.length != 0) {
              let created_at = moment(e.created_at).format('MM/DD/YYYY');
              if (moment(created_at).week() === moment().week()) {
                option.xAxis.data.push(moment(e.created_at).format('YYYY-MM-DD, hh:mm'));
                option.series[0].data.push(Number(e.price).toFixed(2));
              }
            } else {
              option.xAxis.data.push(null);
              option.series.data.push(null);
            }
          });
          break;
        case 'this month':
          priceDetails.forEach((e) => {
            if (e.length != 0) {
              let created_at = moment(e.created_at).format('MM/DD/YYYY');
              if (moment(created_at).month() === moment().month()) {
                // option.xAxis.data.push(this.tzone(e.created_at));
                option.xAxis.data.push(moment(e.created_at).format('YYYY-MM-DD, hh:mm'));
                option.series[0].data.push(Number(e.price).toFixed(2));
              }
            } else {
              option.xAxis.data.push(null);
              option.series.data.push(null);
            }
          });
          break;
        case 'this year':
          priceDetails.forEach((e) => {
            if (e.length != 0) {
              let created_at = moment(e.created_at).format('LL');
              if (moment(created_at).year() === moment().year()) {
                option.xAxis.data.push(moment(e.created_at).format('YYYY-MM-DD, hh:mm'));
                option.series[0].data.push(Number(e.price).toFixed(2));
              }
            } else {
              option.xAxis.data.push(null);
              option.series.data.push(null);
            }
          });
          break;
        default:
          break;
      }

      return option;
    },
    updateData() {
      this.itemsList = [];
      this.loading = true;

      this.$inertia.get('items', this.params, {
        preserveState: true,
        preserveScroll: true,
        onFinish: (visit) => {
          this.itemsList = [];
          this.expandedRow = [];
          this.storeItemInContainer();
          this.loading = false;
        },
      });
    },
    openCreateItemDialog() {
      this.isUpdate = false;
      this.form.clearErrors();
      this.form.reset();
      this.itemId = null;
      this.createItemDialog = true;
    },
    // emit close dialog
    clickOutsideDialog() {
      this.$emit('hide', (this.itemId = null), (this.isUpdate = false), this.form.clearErrors(), this.form.reset());
    },
    editItem(item) {
      //   console.log(item);
      this.isUpdate = true;
      this.createItemDialog = true;
      this.itemId = item.cl2comb;
      this.form.cl2comb = item.cl2comb;
      this.form.cl1comb = item.cl1comb;
      this.form.cl2code = item.cl2code;
      this.form.cl2desc = item.cl2desc;
      this.form.normal_stock = item.normal_stock;
      this.form.alert_stock = item.alert_stock;
      this.form.critical_stock = item.critical_stock;
      this.form.unit = item.uomcode;
      this.form.cl2stat = item.cl2stat;
      this.form.mainCategory = item.catID;
    },
    submit() {
      if (this.form.processing) {
        return false;
      }

      this.form.entry_by = this.entry_by;
      this.form.location = this.authLocation.location.wardcode;

      if (this.isUpdate) {
        this.form.put(route('items.update', this.itemId), {
          preserveScroll: true,
          onSuccess: () => {
            this.itemId = null;
            this.createItemDialog = false;
            this.cancel();
            this.updateData();
            this.updatedMsg();
          },
        });
      } else {
        this.form.post(route('items.store'), {
          preserveScroll: true,
          onSuccess: () => {
            this.itemId = null;
            this.createItemDialog = false;
            this.cancel();
            this.updateData();
            this.createdMsg();
          },
        });
      }
      //   console.log(this.$page.props.errors);
    },
    confirmDeleteItem(item) {
      this.itemId = item.cl2comb;
      this.form.cl2desc = item.cl2desc;
      this.deleteItemDialog = true;
    },
    deleteItem() {
      this.form.delete(route('items.destroy', this.itemId), {
        preserveScroll: true,
        onSuccess: () => {
          this.itemsList = [];
          this.deleteItemDialog = false;
          this.itemId = null;
          this.form.clearErrors();
          this.form.reset();
          this.updateData();
          this.deletedMsg();
          this.storeItemInContainer();
        },
      });
    },
    cancel() {
      this.itemId = null;
      this.isUpdate = false;
      this.createItemDialog = false;
      this.form.reset();
      this.form.clearErrors();
      this.itemsList = [];
      this.storeItemInContainer();
    },
    createdMsg() {
      this.$toast.add({ severity: 'success', summary: 'Success', detail: 'Item created', life: 3000 });
    },
    updatedMsg() {
      this.$toast.add({ severity: 'warn', summary: 'Success', detail: 'Item updated', life: 3000 });
    },
    deletedMsg() {
      this.$toast.add({ severity: 'error', summary: 'Success', detail: 'Item deleted', life: 3000 });
    },
    // ********** prices
    openCreateItemPriceDialog(item) {
      this.formPrice.id = item.id;
      this.formPrice.cl2comb = item.cl2comb;
      this.isPriceUpdate = false;
      this.priceId = item.id;
      this.createItemPriceDialog = true;
    },
    // emit price close dialog
    clickOutsidePriceDialog() {
      this.$emit(
        'hide',
        (this.priceId = null),
        (this.isPriceUpdate = false),
        this.formPrice.clearErrors(),
        this.formPrice.reset()
      );
    },
    editPrice(item) {
      //   console.log('item', item);
      this.isPriceUpdate = true;
      this.createItemPriceDialog = true;
      this.priceId = item.price_id;
      this.formPrice.id = item.price_id;
      this.formPrice.selling_price = item.price;
    },
    submitPrice() {
      if (this.formPrice.processing) {
        return false;
      }

      if (this.isPriceUpdate) {
        this.formPrice.put(route('itemprices.update', this.priceId), {
          preserveScroll: true,
          onSuccess: () => {
            this.priceId = null;
            this.createItemPriceDialog = false;
            this.cancelPrice();
            this.updateData();
            this.updatedPriceMsg();
          },
        });
      } else {
        this.formPrice.post(route('itemprices.store'), {
          preserveScroll: true,
          onSuccess: () => {
            this.priceId = null;
            this.createItemDialog = false;
            this.cancelPrice();
            this.updateData();
            this.createdPriceMsg();
          },
        });
      }
    },
    confirmDeletePrice(item) {
      this.priceId = item.price_id;
      this.deleteItemPriceDialog = true;
    },
    deletePrice() {
      this.form.delete(route('itemprices.destroy', this.priceId), {
        preserveScroll: true,
        onSuccess: () => {
          this.deleteItemPriceDialog = false;
          this.priceId = null;
          this.formPrice.clearErrors();
          this.formPrice.reset();
          this.updateData();
          this.deletedPriceMsg();
          //   this.storeItemInContainer();
        },
      });
    },
    cancelPrice() {
      this.priceId = null;
      this.isPriceUpdate = false;
      this.createItemPriceDialog = false;
      this.formPrice.reset();
      this.formPrice.clearErrors();
    },
    createdPriceMsg() {
      this.$toast.add({ severity: 'success', summary: 'Success', detail: 'Price created', life: 3000 });
    },
    updatedPriceMsg() {
      this.$toast.add({ severity: 'warn', summary: 'Success', detail: 'Price updated', life: 3000 });
    },
    deletedPriceMsg() {
      this.$toast.add({ severity: 'error', summary: 'Success', detail: 'Price deleted', life: 3000 });
    },
    // ********** end prices
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
