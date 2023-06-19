<template>
  <app-layout>
    <Head title="Template - Transfer stock" />

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
          field="brand"
          header="BRAND"
          style="min-width: 12rem"
        >
        </Column>
        <Column
          field="item"
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
              @click="editTransferredStock(slotProps.data)"
            />
          </template>
        </Column>
      </DataTable>

      <!-- v-model:filters="filters" -->
      <DataTable
        class="p-datatable-sm mt-8"
        v-model:filters="filters"
        :value="transferredStocksList"
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
            <span class="text-xl text-900 font-bold text-cyan-500 hover:text-cyan-700">TRANSFERRED STOCKS</span>
            <div>
              <span class="p-input-icon-left mr-2">
                <i class="pi pi-search" />
                <InputText
                  v-model="search"
                  placeholder="Search employee id"
                />
              </span>
              <Button
                label="Transfer stock"
                icon="pi pi-plus"
                iconPos="right"
                @click="openTransferStockDialog"
              />
            </div>
          </div>
        </template>
        <template #empty> No data found. </template>
        <template #loading> Loading data. Please wait. </template>
        <Column
          field="ptcode"
          header="PTCODE"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.ptcode }}
          </template>
        </Column>
      </DataTable>

      <!-- create & edit dialog -->
      <Dialog
        v-model:visible="transferStockDialog"
        :style="{ width: '450px' }"
        header="Category Detail"
        :modal="true"
        class="p-fluid"
        @hide="clickOutsideDialog"
        dismissableMask
      >
        <!-- <div class="field">
          <label for="ptcode">Ptcode</label>
          <Dropdown
            v-model.trim="form.ptcode"
            required="true"
            :options="transferredStocksList"
            filter
            optionLabel="ptdesc"
            optionValue="ptcode"
            class="w-full mb-3"
            :class="{ 'p-invalid': form.ptcode == '' }"
          />
          <small
            class="text-error"
            v-if="form.errors.ptcode"
          >
            {{ form.errors.ptcode }}
          </small>
        </div> -->

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
      <!-- <Dialog
        v-model:visible="deleteTransferredStockDialog"
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
            >Are you sure you want to delete <b>{{ form.cl1desc }}</b> ?</span
          >
        </div>
        <template #footer>
          <Button
            label="No"
            icon="pi pi-times"
            class="p-button-text"
            @click="deleteTransferredStockDialog = false"
          />
          <Button
            label="Yes"
            icon="pi pi-check"
            severity="danger"
            text
            @click="deleteCategory"
          />
        </template>
      </Dialog> -->
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
  },
  props: {
    wardStocks: Object,
    transferredStock: Object,
  },
  data() {
    return {
      // paginator
      loading: false,
      totalRecords: null,
      rows: null,
      // end paginator
      isUpdate: false,
      transferStockDialog: false,
      deleteTransferredStockDialog: false,
      search: '',
      options: {},
      params: {},
      currentWardStocksList: [],
      wardStocksList: [],
      filters: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
      wardStocksFilter: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
      form: this.$inertia.form({
        // ptcode: null,
      }),
    };
  },
  // created will be initialize before mounted
  created() {
    this.totalRecords = this.transferredStock.total;
    this.params.page = this.transferredStock.current_page;
    this.rows = this.transferredStock.per_page;
  },
  mounted() {
    console.log(this.wardStocks);
    this.storeWardStockInContainer();
    // this.storeTransferredStockInContainer();

    this.loading = false;
  },
  methods: {
    tzone(date) {
      return moment.tz(date, 'Asia/Manila').format('LL');
    },
    storeTransferredStockInContainer() {
      this.transferredStock.forEach((e) => {
        this.transferredStocksList.push({
          //   ptcode: e.ptcode,
        });
      });
    },
    storeWardStockInContainer() {
      this.wardStocks.forEach((e) => {
        this.wardStocksList.push({
          ward_stock_id: e.id,
          brand: e.brand_details.name,
          item: e.item_details.cl2desc,
          quantity: e.quantity,
          expiration_date: e.expiration_date,
        });
      });
    },
    onPage(event) {
      this.params.page = event.page + 1;
      this.updateData();
    },
    updateData() {
      this.transferredStocksList = [];
      this.loading = true;

      this.$inertia.get('transferstock', this.params, {
        preserveState: true,
        preserveScroll: true,
        onFinish: (visit) => {
          this.totalRecords = this.transferredStock.total;
          this.transferredStocksList = [];
          this.storeTransferredStockInContainer();
          this.loading = false;
        },
      });
    },
    openTransferStockDialog() {
      this.isUpdate = false;
      this.form.clearErrors();
      this.form.reset();
      this.cl1comb = null;
      this.transferStockDialog = true;
    },
    // emit close dialog
    clickOutsideDialog() {
      this.$emit('hide', (this.cl1comb = null), (this.isUpdate = false), this.form.clearErrors(), this.form.reset());
    },
    editTransferredStock(item) {
      this.isUpdate = true;
      this.transferStockDialog = true;
      this.cl1comb = item.cl1comb;
    },
    submit() {
      if (this.isUpdate) {
        this.form.put(route('transferstock.update', this.form.id), {
          preserveScroll: true,
          onSuccess: () => {
            this.cl1comb = null;
            this.transferStockDialog = false;
            this.cancel();
            this.updateData();
            this.updatedMsg();
          },
        });
      } else {
        this.form.post(route('transferstock.store'), {
          preserveScroll: true,
          onSuccess: () => {
            this.cl1comb = null;
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
    updatedMsg() {
      this.$toast.add({ severity: 'warn', summary: 'Success', detail: 'Updated transferred stock.', life: 3000 });
    },
    deletedMsg() {
      this.$toast.add({ severity: 'error', summary: 'Success', detail: 'Delete transferred stock.', life: 3000 });
    },
  },
  watch: {
    search: function (val, oldVal) {
      this.params.search = val;
      this.updateData();
    },
  },
};
</script>
