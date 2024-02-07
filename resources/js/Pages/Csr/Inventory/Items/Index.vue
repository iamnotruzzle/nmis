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
        selectionMode="single"
        lazy
        paginator
        :rows="rows"
        ref="dt"
        :totalRecords="totalRecords"
        @page="onPage($event)"
        @row-click="setExpandedRow"
        dataKey="cl2comb"
        filterDisplay="row"
        showGridlines
        :loading="loading"
      >
        <template #header>
          <div class="flex flex-wrap align-items-center justify-content-between gap-2">
            <span class="text-xl text-900 font-bold text-cyan-500 hover:text-cyan-700">ITEMS</span>
            <div>
              <span class="p-input-icon-left mr-2">
                <i class="pi pi-search" />
                <InputText
                  v-model="search"
                  placeholder="Search item"
                />
              </span>
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
          field="cl2comb"
          header="CL2 COMBINATION"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.cl2comb }}
          </template>
        </Column>
        <Column
          field="cl1comb"
          header="CL1 COMBINATION"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.cl1comb }}
          </template>
        </Column>
        <Column
          field="cl2code"
          header="CL2 CODE"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.cl2code }}
          </template>
        </Column>
        <Column
          field="cl2desc"
          header="CL2 DESCRIPTION"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.cl2desc }}
          </template>
        </Column>
        <Column
          field="unit"
          header="UNIT"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.uomcode }}
          </template>
        </Column>
        <Column
          field="cl2stat"
          header="STATUS"
          style="min-width: 12rem"
          :showFilterMenu="false"
        >
          <template #body="{ data }">
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
          </template>
          <template #filter="{}">
            <Dropdown
              v-model="selectedStatus"
              :options="statusFilter"
              optionLabel="name"
              optionValue="code"
              placeholder="NO FILTER"
              class="w-full"
            />
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
          <div class="max-w-full flex flex-column align-items-center">
            <!-- {{ slotProps.data }} -->

            <div class="flex align-items-center w-full">
              <div class="text-2xl font-bold my-3">
                Prices for <span class="text-cyan-500 hover:text-cyan-700">[ {{ slotProps.data.cl2desc }} ]</span>
              </div>

              <Button
                label="Add price"
                icon="pi pi-plus"
                iconPos="right"
                size="small"
                class="ml-2 my-0"
                @click="openCreateItemPriceDialog(slotProps.data)"
              />
            </div>

            <DataTable
              paginator
              :rows="5"
              class="w-full"
              :value="expandedRow[0].prices"
            >
              <Column
                field="selling_price"
                header="SELLING PRICE"
              >
              </Column>
              <Column header="ENTRY BY">
                <template #body="{ data }">
                  <span v-if="data.user_detail === null"></span>
                  <span v-else>
                    {{ data.user_detail.firstname }} {{ data.user_detail.middlename }} {{ data.user_detail.lastname }}
                    {{ data.user_detail.empsuffix }}
                  </span>
                </template>
              </Column>
              <Column
                field="created_at"
                header="CREATED AT"
              >
                <template #body="{ data }">
                  {{ tzone(data.created_at) }}
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
                    @click="editPrice(slotProps.data)"
                  />

                  <Button
                    icon="pi pi-trash"
                    rounded
                    text
                    severity="danger"
                    @click="confirmDeletePrice(slotProps.data)"
                  />
                </template>
              </Column>
            </DataTable>

            <div class="w-11 flex flex-column">
              <div class="text-2xl font-bold mt-4 flex justify-content-around align-items-center">
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
        </template>
      </DataTable>

      <!-- create & edit dialog -->
      <Dialog
        v-model:visible="createItemDialog"
        :style="{ width: '450px' }"
        header="Item Detail"
        :modal="true"
        class="p-fluid"
        @hide="clickOutsideDialog"
        dismissableMask
      >
        <div class="field">
          <label for="cl1comb">Category</label>
          <Dropdown
            v-model.trim="form.cl1comb"
            required="true"
            :options="cl1combsList"
            :virtualScrollerOptions="{ itemSize: 38 }"
            filter
            optionLabel="cl1desc"
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
        <div
          v-if="isUpdate == false"
          class="field"
        >
          <label for="cl2code">Cl2 code</label>
          <InputText
            id="cl2code"
            v-model.trim="form.cl2code"
            required="true"
            autofocus
            :class="{ 'p-invalid': form.cl2code == '' }"
            @keyup.enter="submit"
          />
          <small
            class="text-error"
            v-if="form.errors.cl2code"
          >
            {{ form.errors.cl2code }}
          </small>
        </div>
        <div class="field">
          <label for="cl2desc">Cl2 description</label>
          <Textarea
            id="cl1code"
            v-model.trim="form.cl2desc"
            required="true"
            rows="5"
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
            required="true"
            v-model="form.unit"
            :options="unitsList"
            dataKey="unit"
            filter
            optionLabel="uomdesc"
            optionValue="uomdesc"
            class="w-full mb-3"
            :class="{ 'p-invalid': form.unit == '' }"
          />
          <small
            class="text-error"
            v-if="form.errors.unit"
          >
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
      totalRecords: null,
      rows: null,
      // end paginator
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
      dateFilter: 'NO FILTER',
      selectedStatus: null,
      statusFilter: [
        { name: 'NO FILTER', code: null },
        { name: 'Active', code: 'A' },
        { name: 'Inactive', code: 'I' },
      ],
      search: '',
      options: {},
      params: {},
      itemsList: [],
      cl1combsList: [],
      // TODO add quarterly filter
      dateFilterList: [
        {
          name: 'NO FILTER',
          value: 'NO FILTER',
        },
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
  created() {
    this.totalRecords = this.items.total;
    this.params.page = this.items.current_page;
    this.rows = this.items.per_page;
  },
  mounted() {
    // console.log(this.$page.props.auth.user.roles);

    this.storeCl1combsInContainer();
    this.storeItemInContainer();
    this.storeUnitsInContainer();

    // console.log(this.items);

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
    storeCl1combsInContainer() {
      this.cl1combs.forEach((e) => {
        this.cl1combsList.push({
          cl1comb: e.cl1comb,
          cl1desc: e.cl1desc,
        });
      });
    },
    setExpandedRow($event) {
      // Check if row expanded before click or not
      const isExpanded = this.expandedRow.find((p) => p.cl2comb === $event.data.cl2comb);

      if (isExpanded?.cl2comb) this.expandedRow = [];
      else this.expandedRow = [$event.data];
      //   console.log(this.expandedRow);
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
      this.items.data.forEach((e) => {
        // console.log(e);
        this.itemsList.push({
          cl2comb: e.cl2comb,
          cl1comb: e.cl1comb,
          cl2code: e.cl2code,
          cl2desc: e.cl2desc,
          uomcode: e.unit === null ? '' : e.unit.uomdesc,
          cl2stat: e.cl2stat,
          pharmaceutical: e.pharmaceutical,
          prices: e.prices.length === 0 ? [] : e.prices,
        });
      });
    },
    priceChangesOptions(data) {
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
        case 'NO FILTER':
          priceDetails.forEach((e) => {
            if (e.selling_price.length != 0) {
              option.xAxis.data.push(moment(e.created_at).format('YYYY-MM-DD, hh:mm'));
              option.series[0].data.push(Number(e.selling_price).toFixed(2));
            } else {
              option.xAxis.data.push(null);
              option.series.data.push(null);
            }
          });
          break;
        case 'yesterday':
          priceDetails.forEach((e) => {
            if (e.selling_price.length != 0) {
              let created_at = moment(e.created_at);

              // if created_ate is equal to yesterday
              if (moment(created_at).format('MM/DD/YYYY') === moment().subtract(1, 'days').format('MM/DD/YYYY')) {
                option.xAxis.data.push(moment(e.created_at).format('YYYY-MM-DD, hh:mm'));
                option.series[0].data.push(Number(e.selling_price).toFixed(2));
              }
            } else {
              option.xAxis.data.push(null);
              option.series.data.push(null);
            }
          });
          break;
        case 'today':
          priceDetails.forEach((e) => {
            if (e.selling_price.length != 0) {
              let created_at = moment(e.created_at).format('MM/DD/YYYY');
              let today = moment().format('MM/DD/YYYY');
              if (moment(created_at).isSame(today)) {
                option.xAxis.data.push(moment(e.created_at).format('YYYY-MM-DD, hh:mm'));
                option.series[0].data.push(Number(e.selling_price).toFixed(2));
              }
            } else {
              option.xAxis.data.push(null);
              option.series.data.push(null);
            }
          });
          break;
        case 'this week':
          priceDetails.forEach((e) => {
            if (e.selling_price.length != 0) {
              let created_at = moment(e.created_at).format('MM/DD/YYYY');
              if (moment(created_at).week() === moment().week()) {
                option.xAxis.data.push(moment(e.created_at).format('YYYY-MM-DD, hh:mm'));
                option.series[0].data.push(Number(e.selling_price).toFixed(2));
              }
            } else {
              option.xAxis.data.push(null);
              option.series.data.push(null);
            }
          });
          break;
        case 'this month':
          priceDetails.forEach((e) => {
            if (e.selling_price.length != 0) {
              let created_at = moment(e.created_at).format('MM/DD/YYYY');
              if (moment(created_at).month() === moment().month()) {
                // option.xAxis.data.push(this.tzone(e.created_at));
                option.xAxis.data.push(moment(e.created_at).format('YYYY-MM-DD, hh:mm'));
                option.series[0].data.push(Number(e.selling_price).toFixed(2));
              }
            } else {
              option.xAxis.data.push(null);
              option.series.data.push(null);
            }
          });
          break;
        case 'this year':
          priceDetails.forEach((e) => {
            if (e.selling_price.length != 0) {
              let created_at = moment(e.created_at).format('LL');
              if (moment(created_at).year() === moment().year()) {
                option.xAxis.data.push(moment(e.created_at).format('YYYY-MM-DD, hh:mm'));
                option.series[0].data.push(Number(e.selling_price).toFixed(2));
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
    onPage(event) {
      this.params.page = event.page + 1;
      this.updateData();
    },
    updateData() {
      this.itemsList = [];
      this.loading = true;

      this.$inertia.get('items', this.params, {
        preserveState: true,
        preserveScroll: true,
        onFinish: (visit) => {
          this.totalRecords = this.items.total;
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
      //   console.log(item.cl2comb);
      this.isUpdate = true;
      this.createItemDialog = true;
      this.itemId = item.cl2comb;
      this.form.cl2comb = item.cl2comb;
      this.form.cl1comb = item.cl1comb;
      this.form.cl2code = item.cl2code;
      this.form.cl2desc = item.cl2desc;
      this.form.unit = item.uomcode;
      this.form.cl2stat = item.cl2stat;
    },
    submit() {
      if (this.form.processing) {
        return false;
      }

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
      this.isPriceUpdate = true;
      this.createItemPriceDialog = true;
      this.priceId = item.id;
      this.formPrice.id = item.id;
      this.formPrice.cl2comb = item.cl2comb;
      this.formPrice.selling_price = item.selling_price;
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
      this.priceId = item.id;
      this.formPrice.selling_price = item.selling_price;
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
  watch: {
    search: function (val, oldVal) {
      this.params.search = val;
      this.updateData();
    },
    // uncomment watcher for dateFIlter if not working
    // dateFilter: function (val, oldVal) {
    //   // this watches the dateFilter property value that will filter
    //   // the price changes
    // },
    selectedStatus: function (val) {
      //   console.log(val['code']);
      this.params.status = this.selectedStatus;

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
