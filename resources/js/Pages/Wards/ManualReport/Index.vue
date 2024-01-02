<template>
  <app-layout>
    <Head title="NMIS - Reports" />

    <div
      class="card"
      style="width: 100%"
    >
      <Toast />

      <!-- :value="balanceContainer" -->
      <DataTable
        class="p-datatable-sm"
        v-model:filters="filters"
        :value="manual_reportsList"
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
            <span class="text-xl text-900 font-bold text-cyan-500 hover:text-cyan-700">REPORT</span>
            <div>
              <span class="p-input-icon-left mr-2">
                <i class="pi pi-search" />
                <InputText
                  v-model="search"
                  placeholder="Search item"
                />
              </span>

              <Button
                label="Add"
                icon="pi pi-plus"
                iconPos="right"
                @click="openCreateDataDialog"
              />

              <!-- <i
                v-if="from == null || to == null"
                class="pi pi-file-excel"
                :style="{ color: 'gray', 'font-size': '2rem' }"
              ></i>
              <a
                v-else
                :href="`csrmanualreports/export?from=${params.from}&to=${params.to}`"
                target="_blank"
              >
                <i
                  class="pi pi-file-excel"
                  :style="{ color: 'green', 'font-size': '2rem' }"
                ></i>
              </a> -->
            </div>
          </div>
        </template>
        <template #empty> No data found. </template>
        <template #loading> Loading data. Please wait. </template>
        <Column
          field="item"
          header="ITEM"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.cl2desc }}
          </template>
        </Column>
        <Column
          field="uomdesc"
          header="Unit"
        ></Column>
        <Column
          field="esti_budg_unit_cost"
          header="UNIT COST"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.esti_budg_unit_cost }}
          </template>
        </Column>
        <Column
          field="beginning_balance"
          header="BEGINNING BALANCE"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.beginning_balance }}
          </template>
        </Column>
        <Column
          field="received_from_csr"
          header="REC. FROM CSR"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.received_from_csr }}
          </template>
        </Column>
        <Column
          field="total_stock"
          header="TOTAL STOCK"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.total_stock }}
          </template>
        </Column>
        <Column
          field="consumption_surgery"
          header="SURGERY"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.consumption_surgery }}
          </template>
        </Column>
        <Column
          field="consumption_ob_gyne"
          header="OB-GYNE"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.consumption_ob_gyne }}
          </template>
        </Column>
        <Column
          field="consumption_ortho"
          header="ORTHO"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.consumption_ortho }}
          </template>
        </Column>
        <Column
          field="consumption_pedia"
          header="PEDIA"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.consumption_pedia }}
          </template>
        </Column>
        <Column
          field="consumption_optha"
          header="OPTHA"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.consumption_optha }}
          </template>
        </Column>
        <Column
          field="consumption_ent"
          header="ENT"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.consumption_ent }}
          </template>
        </Column>
        <Column
          field="total_consumption_quantity"
          header="TOTAL CONSUMPTION QTY"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.total_consumption_quantity }}
          </template>
        </Column>
        <Column
          field="total_consumption_cost"
          header="TOTAL CONSUMPTION COST"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.total_consumption_cost }}
          </template>
        </Column>
        <Column
          field="ending_balance"
          header="END. BAL"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.ending_balance }}
          </template>
        </Column>
        <Column
          field="actual_inventory"
          header="ACTUAL INVENTORY"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.actual_inventory }}
          </template>
        </Column>
        <Column
          field="entry_by"
          header="ENTRY BY"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.entry_by }}
          </template>
        </Column>
        <Column
          header="CREATED AT"
          filterField="created_at"
          style="min-width: 10rem"
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

            <Button
              icon="pi pi-trash"
              rounded
              text
              severity="danger"
              @click="confirmDeleteItem(slotProps.data)"
            />
          </template>
        </Column>
      </DataTable>

      <Dialog
        v-model:visible="createDataDialog"
        header="REPORT"
        :modal="true"
        :style="{ width: '850px' }"
        class="p-fluid"
        @hide="whenDialogIsHidden"
      >
        <!-- ITEM -->
        <div class="field">
          <label>Item</label>
          <Dropdown
            required="true"
            v-model="form.cl2comb"
            :options="itemsList"
            :virtualScrollerOptions="{ itemSize: 38 }"
            filter
            optionLabel="cl2desc"
            optionValue="cl2comb"
            class="w-full"
          />
          <small
            class="text-error"
            v-if="form.errors.cl2comb"
          >
            {{ form.errors.cl2comb }}
          </small>
        </div>

        <!-- unit -->
        <div class="field">
          <label for="unit">Unit</label>
          <InputText
            id="unit"
            v-model.trim="selectedItemsUomDesc"
            readonly
          />
        </div>

        <!-- UNIT COST -->
        <div class="field">
          <label>Unit cost</label>
          <InputText
            v-model.trim="form.esti_budg_unit_cost"
            required="true"
            autofocus
            type="number"
          />
          <small
            class="text-error"
            v-if="form.errors.esti_budg_unit_cost"
          >
            {{ form.errors.esti_budg_unit_cost }}
          </small>
        </div>

        <!-- Beginning balance -->
        <div class="field">
          <label>Beginning balance</label>
          <InputText
            v-model.trim="form.beginning_balance"
            required="true"
            autofocus
            type="number"
          />
          <small
            class="text-error"
            v-if="form.errors.beginning_balance"
          >
            {{ form.errors.beginning_balance }}
          </small>
        </div>

        <!-- RECEIVED FROM CSR -->
        <div class="field">
          <label>RECEIVED FROM CSR</label>
          <InputText
            v-model.trim="form.received_from_csr"
            required="true"
            autofocus
            type="number"
          />
          <small
            class="text-error"
            v-if="form.errors.received_from_csr"
          >
            {{ form.errors.received_from_csr }}
          </small>
        </div>

        <!-- TOTAL STOCK -->
        <div class="field">
          <label>TOTAL STOCK</label>
          <InputText
            v-model.trim="form.total_stock"
            required="true"
            autofocus
            type="number"
          />
          <small
            class="text-error"
            v-if="form.errors.total_stock"
          >
            {{ form.errors.total_stock }}
          </small>
        </div>

        <!-- CONSUMPTION -->
        <div class="flex flex-column border-1 border-round">
          <h3 class="text-center border-bottom-1 p-0 m-0">CONSUMPTION</h3>
          <div class="flex justify-content-between px-2">
            <!-- csr beg bal -->
            <div class="flex w-full">
              <div class="w-full mt-3">
                <div class="field">
                  <label>Surgery</label>
                  <InputText
                    v-model.trim="form.consumption_surgery"
                    required="true"
                    autofocus
                    type="number"
                  />
                  <small
                    class="text-error"
                    v-if="form.errors.consumption_surgery"
                  >
                    {{ form.errors.consumption_surgery }}
                  </small>
                </div>
                <div class="field">
                  <label>OB-GYNE</label>
                  <InputText
                    v-model.trim="form.consumption_ob_gyne"
                    required="true"
                    autofocus
                    type="number"
                  />
                  <small
                    class="text-error"
                    v-if="form.errors.consumption_ob_gyne"
                  >
                    {{ form.errors.consumption_ob_gyne }}
                  </small>
                </div>
                <div class="field">
                  <label>Ortho</label>
                  <InputText
                    v-model.trim="form.consumption_ortho"
                    required="true"
                    autofocus
                    type="number"
                  />
                  <small
                    class="text-error"
                    v-if="form.errors.consumption_ortho"
                  >
                    {{ form.errors.consumption_ortho }}
                  </small>
                </div>
              </div>
            </div>
            <div class="border-1 mx-2"></div>
            <!-- ward beg bal -->
            <div class="flex w-full">
              <div class="w-full mt-3">
                <div class="field">
                  <label>Pedia</label>
                  <InputText
                    v-model.trim="form.consumption_pedia"
                    required="true"
                    autofocus
                    type="number"
                  />
                  <small
                    class="text-error"
                    v-if="form.errors.consumption_pedia"
                  >
                    {{ form.errors.consumption_pedia }}
                  </small>
                </div>
                <div class="field">
                  <label>Optha</label>
                  <InputText
                    v-model.trim="form.consumption_optha"
                    required="true"
                    autofocus
                    type="number"
                  />
                  <small
                    class="text-error"
                    v-if="form.errors.consumption_optha"
                  >
                    {{ form.errors.consumption_optha }}
                  </small>
                </div>
                <div class="field">
                  <label>ENT</label>
                  <InputText
                    v-model.trim="form.consumption_ent"
                    required="true"
                    autofocus
                    type="number"
                  />
                  <small
                    class="text-error"
                    v-if="form.errors.consumption_ent"
                  >
                    {{ form.errors.consumption_ent }}
                  </small>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Total Consumption quantity -->
        <div class="field">
          <label>Total Consumption quantity</label>
          <InputText
            v-model.trim="form.total_consumption_quantity"
            required="true"
            autofocus
            type="number"
          />
          <small
            class="text-error"
            v-if="form.errors.total_consumption_quantity"
          >
            {{ form.errors.total_consumption_quantity }}
          </small>
        </div>

        <!-- Total Consumption cost -->
        <div class="field">
          <label>Total Consumption cost</label>
          <InputText
            v-model.trim="form.total_consumption_cost"
            required="true"
            autofocus
            type="number"
          />
          <small
            class="text-error"
            v-if="form.errors.total_consumption_cost"
          >
            {{ form.errors.total_consumption_cost }}
          </small>
        </div>

        <!-- Ending balance -->
        <div class="field">
          <label>Ending balance</label>
          <InputText
            v-model.trim="form.ending_balance"
            required="true"
            autofocus
            type="number"
          />
          <small
            class="text-error"
            v-if="form.errors.ending_balance"
          >
            {{ form.errors.ending_balance }}
          </small>
        </div>

        <!-- Actual inventory -->
        <div class="field">
          <label>Actual inventory</label>
          <InputText
            v-model.trim="form.actual_inventory"
            required="true"
            autofocus
            type="number"
          />
          <small
            class="text-error"
            v-if="form.errors.actual_inventory"
          >
            {{ form.errors.actual_inventory }}
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
            text
            type="submit"
            severity="warning"
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
            >Are you sure you want to delete <b>{{ form.cl2desc }} </b> ?</span
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
import Tag from 'primevue/tag';
import moment from 'moment';
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
    Link,
  },
  props: {
    manual_reports: Object,
  },
  data() {
    return {
      // paginator
      loading: false,
      totalRecords: null,
      rows: null,
      // end paginator
      itemId: null,
      isUpdate: false,
      cl2desc: '',
      createDataDialog: false,
      deleteItemDialog: false,
      search: '',
      options: {},
      params: {},
      from: null,
      to: null,
      manual_reportsList: [],
      itemsList: [],
      filters: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
      selectedItemsUomDesc: null,
      form: this.$inertia.form({
        id: null,
        cl2comb: null,
        uomcode: null,
        esti_budg_unit_cost: null,
        beginning_balance: null,
        received_from_csr: null,
        total_stock: null,
        consumption_surgery: null,
        consumption_ob_gyne: null,
        consumption_ortho: null,
        consumption_pedia: null,
        consumption_optha: null,
        consumption_ent: null,
        total_consumption_quantity: null,
        total_consumption_cost: null,
        ending_balance: null,
        actual_inventory: null,
        wardcode: null,
        entry_by: null,
      }),
    };
  },
  // created will be initialize before mounted
  created() {
    this.totalRecords = this.manual_reports.total;
    this.params.page = this.manual_reports.current_page;
    this.rows = this.manual_reports.per_page;
  },
  mounted() {
    // console.log('stock bal', this.locationStockBalance);

    // console.log('manual', this.manual_reports.data);

    this.storeManualReportsInContainer();
    this.storeItemsInContainer();

    this.loading = false;

    this.form.entry_by = this.$page.props.auth.user.userDetail.employeeid;
  },
  methods: {
    storeManualReportsInContainer() {
      this.manual_reportsList = []; // reset

      this.manual_reports.data.forEach((e) => {
        this.manual_reportsList.push({
          id: e.id,
          cl2comb: e.cl2comb,
          cl2desc: e.item_description.cl2desc.trim(),
          uomcode: e.unit == null ? null : e.unit.uomcode,
          uomdesc: e.unit == null ? null : e.unit.uomdesc,
          esti_budg_unit_cost: e.esti_budg_unit_cost,
          beginning_balance: e.beginning_balance,
          received_from_csr: e.received_from_csr,
          total_stock: e.total_stock,
          consumption_surgery: e.consumption_surgery,
          consumption_ob_gyne: e.consumption_ob_gyne,
          consumption_ortho: e.consumption_ortho,
          consumption_pedia: e.consumption_pedia,
          consumption_optha: e.consumption_optha,
          consumption_ent: e.consumption_ent,
          total_consumption_quantity: e.total_consumption_quantity,
          total_consumption_cost: e.total_consumption_cost,
          ending_balance: e.ending_balance,
          actual_inventory: e.actual_inventory,
          entry_by: e.entry_by.firstname + ' ' + e.entry_by.lastname,
          created_by: e.created_by,
        });
      });

      this.sortItemsList(this.itemsList, 'cl2desc');
    },
    storeItemsInContainer() {
      this.itemsList = []; // reset

      this.$page.props.items.forEach((e) => {
        this.itemsList.push({
          cl2comb: e.cl2comb,
          cl2desc: e.cl2desc.trim(),
          uomcode: e.unit == null ? null : e.unit.uomcode,
          uomdesc: e.unit == null ? null : e.unit.uomdesc,
        });
      });

      this.sortItemsList(this.itemsList, 'cl2desc');
    },
    sortItemsList(arr, propertyName, order = 'ascending') {
      const sortedArr = this.itemsList.sort((a, b) => {
        if (a[propertyName] < b[propertyName]) {
          return -1;
        }
        if (a[propertyName] > b[propertyName]) {
          return 1;
        }
        return 0;
      });

      if (order === 'descending') {
        return sortedArr.reverse();
      }

      this.itemsList = sortedArr;
    },
    updateData() {
      this.loading = true;

      this.$inertia.get('wardsmanualreports', this.params, {
        preserveState: true,
        preserveScroll: true,
        onFinish: (visit) => {
          //   this.totalRecords = this.users.total;
          this.itemsList = [];
          this.storeManualReportsInContainer();
          this.storeItemsInContainer();
          this.loading = false;
        },
      });
    },
    tzone(date) {
      return moment.tz(date, 'Asia/Manila').format('L');
    },
    onPage(event) {
      this.params.page = event.page + 1;
      this.updateData();
    },
    openCreateDataDialog() {
      this.isUpdate = false;
      this.form.clearErrors();
      this.form.reset();
      this.itemId = null;
      this.createDataDialog = true;
      this.form.wardcode = this.$page.props.auth.user.location.wardcode;
      this.form.entry_by = this.$page.props.auth.user.userDetail.employeeid;
    },
    clickOutsideDialog() {
      this.$emit(
        'hide',
        (this.itemId = null),
        (this.isUpdate = false),
        (this.cl2desc = ''),
        this.form.clearErrors(),
        this.form.reset(),
        (this.form.entry_by = this.$page.props.auth.user.userDetail.employeeid)
      );
    },
    editItem(item) {
      //   console.log(item);
      this.isUpdate = true;
      this.createDataDialog = true;
      this.form.id = item.id;
      this.form.cl2comb = item.cl2comb;
      this.form.uomcode = item.uomcode;
      this.form.esti_budg_unit_cost = item.esti_budg_unit_cost;
      this.form.beginning_balance = item.beginning_balance;
      this.form.received_from_csr = item.received_from_csr;
      this.form.total_stock = item.total_stock;
      this.form.consumption_surgery = item.consumption_surgery;
      this.form.consumption_ob_gyne = item.consumption_ob_gyne;
      this.form.consumption_ortho = item.consumption_ortho;
      this.form.consumption_pedia = item.consumption_pedia;
      this.form.consumption_optha = item.consumption_optha;
      this.form.consumption_ent = item.consumption_ent;
      this.form.total_consumption_quantity = item.total_consumption_quantity;
      this.form.total_consumption_cost = item.total_consumption_cost;
      this.form.ending_balance = item.ending_balance;
      this.form.actual_inventory = item.actual_inventory;
    },
    submit() {
      if (this.form.processing) {
        return false;
      }

      if (this.isUpdate) {
        this.form.put(route('wardsmanualreports.update', this.form.id), {
          preserveScroll: true,
          onSuccess: () => {
            this.createDataDialog = false;
            this.cancel();
            this.updateData();
            this.updatedMsg();
          },
        });
      } else {
        this.form.post(route('wardsmanualreports.store'), {
          preserveScroll: true,
          onSuccess: () => {
            this.createDataDialog = false;
            this.cancel();
            this.updateData();
            this.createdMsg();
          },
        });
      }
    },
    whenDialogIsHidden() {
      this.$emit(
        'hide',
        (this.isUpdate = false),
        this.form.clearErrors(),
        this.form.reset(),
        (this.form.entry_by = this.$page.props.auth.user.userDetail.employeeid)
      );
    },
    confirmDeleteItem(item) {
      this.itemId = item.id;
      this.deleteItemDialog = true;
    },
    confirmDeleteItem(item) {
      this.form.id = item.id;
      this.form.cl2desc = item.cl2desc;
      this.deleteItemDialog = true;
    },
    deleteItem() {
      this.form.delete(route('wardsmanualreports.destroy', this.form.id), {
        preserveScroll: true,
        onSuccess: () => {
          this.balanceContainer = [];
          this.deleteItemDialog = false;
          this.form.clearErrors();
          this.form.reset();
          this.updateData();
          this.deletedMsg();
        },
      });
    },
    cancel() {
      this.itemId = null;
      this.isUpdate = false;
      this.createDataDialog = false;
      this.form.reset();
      this.form.clearErrors();
      this.form.entry_by = this.$page.props.auth.user.userDetail.employeeid;
    },
    createdMsg() {
      this.$toast.add({
        severity: 'success',
        summary: 'Success',
        detail: 'Report created',
        life: 3000,
      });
    },
    updatedMsg() {
      this.$toast.add({
        severity: 'warn',
        summary: 'Success',
        detail: 'Report updated',
        life: 3000,
      });
    },
    deletedMsg() {
      this.$toast.add({
        severity: 'error',
        summary: 'Success',
        detail: 'Report deleted',
        life: 3000,
      });
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
        let from = moment(val).format('LL');
        // console.log('from', from);
        this.params.from = from;
      } else {
        this.params.from = null;
        this.from = null;
      }
      this.updateData();
    },
    to: function (val) {
      if (val != null) {
        let to = moment(val).add(1, 'd').format('LL');
        // console.log('to', to);
        this.params.to = to;
      } else {
        this.params.to = null;
        this.to = null;
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
