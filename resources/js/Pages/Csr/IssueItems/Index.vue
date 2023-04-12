<template>
  <app-layout>
    <Head title="Template - Stocks" />

    <div class="card">
      <Toast />

      <!-- v-model:filters="filters" -->
      <DataTable
        class="p-datatable-sm"
        v-model:expandedRowGroups="expandedRowGroups"
        :value="requestStocksList"
        v-model:filters="filters"
        expandableRowGroups
        rowGroupMode="subheader"
        groupRowsBy="requested_at"
        selectionMode="single"
        lazy
        paginator
        :rows="rows"
        ref="dt"
        :totalRecords="totalRecords"
        @page="onPage($event)"
        filterDisplay="row"
        showGridlines
        :loading="loading"
      >
        <template #header>
          <div class="flex flex-wrap align-items-center justify-content-between gap-2">
            <span class="text-xl text-900 font-bold">Requested Stocks</span>
            <div>
              <span class="p-input-icon-left mr-2">
                <i class="pi pi-search" />
                <InputText
                  v-model="search"
                  placeholder="Search item"
                  class="w-30rem"
                />
              </span>
            </div>
          </div>
        </template>
        <template #empty> No request stock found. </template>
        <template #loading> Loading request stock data. Please wait. </template>
        <template #groupheader="slotProps">
          <span class="vertical-align-middle ml-2 font-bold line-height-3">{{ slotProps.data.requested_at }}</span>
        </template>
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
          field="requested_qty"
          header="REQUESTED QTY"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.requested_qty }}
          </template>
        </Column>
        <Column
          field="approved_qty"
          header="APPROVED QTY"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.approved_qty }}
          </template>
        </Column>
        <Column
          field="status"
          header="STATUS"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            <Tag
              :value="data.status"
              :severity="getSeverity(data)"
            />
          </template>
        </Column>
        <Column
          field="requested_by"
          header="REQUESTED BY"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.requested_by }}
          </template>
        </Column>
        <Column
          field="approved_by"
          header="APPROVED BY"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.approved_by }}
          </template>
        </Column>
        <Column
          field="created_at"
          header="CREATED AT"
          style="min-width: 12rem"
          :showFilterMenu="false"
        >
          <template #body="{ data }">
            {{ tzone(data.created_at) }}
          </template>
          <!-- <template #filter="{}">
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
          </template> -->
        </Column>

        <Column
          header="Action"
          style="min-width: 12rem"
        >
          <template #body="slotProps">
            <Button
              icon="pi pi-plus"
              rounded
              text
              severity="info"
              @click="openIssueItemDialog(slotProps.data)"
            />

            <Button
              icon="pi pi-pencil"
              class="mr-1"
              rounded
              text
              severity="warning"
              @click="editIssueItem(slotProps.data)"
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

      <!-- create & edit dialog -->
      <Dialog
        v-model:visible="issueItemDialog"
        :style="{ width: '450px' }"
        header="Stock Detail"
        :modal="true"
        class="p-fluid"
        @hide="clickOutsideDialog"
        dismissableMask
      >
        <div class="field">
          <label for="Item">Item</label>
          <Dropdown
            required="true"
            v-model="form.cl2comb"
            :options="itemsList"
            dataKey="unit"
            optionLabel="cl2desc"
            optionValue="cl2comb"
            class="w-full mb-3"
            disabled
          />
          <small
            class="text-error"
            v-if="form.errors.cl2comb"
          >
          </small>
        </div>
        <div class="field">
          <label for="requested_qty">Requested quantity</label>
          <InputText
            id="requested_qty"
            v-model.trim="form.requested_qty"
            required="true"
            disabled
          />
        </div>
        <div class="field">
          <label for="approved_qty">Approve quantity</label>
          <InputText
            id="approved_qty"
            v-model.trim="form.approved_qty"
            required="true"
            autofocus
            :class="{ 'p-invalid': form.approved_qty == '' }"
            @keyup.enter="submit"
          />
          <small
            class="text-error"
            v-if="form.errors.approved_qty"
          >
            {{ form.errors.approved_qty }}
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
        v-model:visible="deleteRequestStockDialog"
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
            Are you sure you want to delete <b>{{ form.cl2desc }}</b> ?
          </span>
        </div>
        <template #footer>
          <Button
            label="No"
            icon="pi pi-times"
            class="p-button-text"
            @click="deleteRequestStockDialog = false"
          />
          <Button
            label="Yes"
            icon="pi pi-check"
            severity="danger"
            text
            @click="deleteRequestStock"
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
    Tag,
  },
  props: {
    authWardcode: Object,
    items: Object,
    stocks: Object,
    requestStocks: Object,
  },
  data() {
    return {
      expandedRowGroups: null,
      // paginator
      loading: false,
      totalRecords: null,
      rows: null,
      // end paginator
      requestStockId: null,
      isUpdate: false,
      issueItemDialog: false,
      deleteRequestStockDialog: false,
      search: '',
      options: {},
      params: {},
      // manufactured date
      from: null,
      to: null,
      itemsList: [],
      requestStocksList: [],
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
      form: this.$inertia.form({
        cl2comb: null,
        cl2desc: null,
        requested_qty: null,
        approved_qty: null,
        status: null,
        approved_by: this.$page.props.auth.user.userDetail.employeeid,
      }),
    };
  },
  // created will be initialize before mounted
  created() {
    this.totalRecords = this.requestStocks.total;
    this.params.page = this.requestStocks.current_page;
    this.rows = this.requestStocks.per_page;
  },
  mounted() {
    // console.log(this.requestStocks);
    this.storeItemsInController();
    this.storeRequestStocksInContainer();

    this.loading = false;
  },
  methods: {
    getSeverity(item) {
      switch (item.status) {
        case 'REQUESTED':
          return 'primary';

        case 'APPROVED':
          return 'success';

        case 'DELIVERED':
          return 'info';

        default:
          return null;
      }
    },
    tzone(date) {
      if (date == null || date == '') {
        return null;
      } else {
        return moment.tz(date, 'Asia/Manila').format('LLL');
      }
    },
    storeItemsInController() {
      this.items.forEach((e) => {
        this.itemsList.push({
          cl2comb: e.cl2comb,
          cl2desc: e.cl2desc,
        });
      });
    },
    // use storeRequestStocksInContainer() function so that every time you make
    // server request such as POST, the data in the table
    // is updated
    storeRequestStocksInContainer() {
      this.requestStocks.data.forEach((e) => {
        this.requestStocksList.push({
          id: e.id,
          cl2comb: e.cl2comb,
          cl2desc: e.item_detail.cl2desc,
          requested_qty: e.requested_qty,
          status: e.status,
          requested_by: e.requested_by_details.firstname.concat(
            ' ',
            e.requested_by_details.middlename,
            ' ',
            e.requested_by_details.lastname
          ),
          requested_at: e.requested_at_details.wardname,
          approved_by:
            e.approved_by_details === null
              ? ''
              : e.approved_by_details.firstname.concat(
                  ' ',
                  e.approved_by_details.middlename,
                  ' ',
                  e.approved_by_details.lastname
                ),
          created_at: e.created_at,
        });
      });
      //   console.log(this.requestStocksList);
    },
    onPage(event) {
      this.params.page = event.page + 1;
      this.updateData();
    },
    updateData() {
      this.requestStocksList = [];
      this.loading = true;

      this.$inertia.get('issueitems', this.params, {
        preserveState: true,
        preserveScroll: true,
        onFinish: (visit) => {
          this.totalRecords = this.requestStocks.total;
          this.requestStocksList = [];
          this.storeRequestStocksInContainer();
          this.loading = false;
        },
      });
    },
    openIssueItemDialog(item) {
      this.form.clearErrors();
      this.form.reset();
      this.requestStockId = item.id;
      this.form.cl2comb = item.cl2comb;
      this.form.requested_qty = item.requested_qty;
      this.issueItemDialog = true;
    },
    // emit close dialog
    clickOutsideDialog() {
      this.$emit(
        'hide',
        (this.requestStockId = null),
        (this.isUpdate = false),
        this.form.clearErrors(),
        this.form.reset()
      );
    },
    editIssueItem(item) {
      this.isUpdate = true;
      this.issueItemDialog = true;
      this.requestStockId = item.id;
      this.form.cl2comb = item.cl2comb;
      this.form.requested_qty = item.requested_qty;
    },
    submit() {
      if (this.isUpdate) {
        this.form.put(route('issueitems.update', this.requestStockId), {
          preserveScroll: true,
          onSuccess: () => {
            this.requestStockId = null;
            this.issueItemDialog = false;
            this.cancel();
            this.updateData();
            this.updatedMsg();
          },
        });
      } else {
        this.form.post(route('issueitems.store'), {
          preserveScroll: true,
          onSuccess: () => {
            this.requestStockId = null;
            this.issueItemDialog = false;
            this.cancel();
            this.updateData();
            this.createdMsg();
          },
        });
      }
    },
    confirmDeleteItem(item) {
      this.requestStockId = item.id;
      this.form.cl2desc = item.cl2desc;
      this.deleteRequestStockDialog = true;
    },
    deleteRequestStock() {
      this.form.delete(route('issueitems.destroy', this.requestStockId), {
        preserveScroll: true,
        onSuccess: () => {
          this.requestStocksList = [];
          this.deleteRequestStockDialog = false;
          this.requestStockId = null;
          this.form.clearErrors();
          this.form.reset();
          this.updateData();
          this.deletedMsg();
          this.storeRequestStocksInContainer();
        },
      });
    },
    cancel() {
      this.requestStockId = null;
      this.isUpdate = false;
      this.issueItemDialog = false;
      this.form.reset();
      this.form.clearErrors();
      this.requestStocksList = [];
      this.storeRequestStocksInContainer();
    },
    createdMsg() {
      this.$toast.add({ severity: 'success', summary: 'Success', detail: 'Requested stock', life: 3000 });
    },
    updatedMsg() {
      this.$toast.add({ severity: 'warn', summary: 'Success', detail: 'Requested stock updated', life: 3000 });
    },
    deletedMsg() {
      this.$toast.add({ severity: 'error', summary: 'Success', detail: 'Request stock deleted', life: 3000 });
    },
  },
  watch: {
    search: function (val, oldVal) {
      this.params.search = val;
      this.updateData();
    },
    from: function (val) {
      if (val != null) {
        let from = val;
        this.params.from = from;
      } else {
        this.params.from = null;
        this.from = null;
      }
      this.updateData();
    },
    to: function (val) {
      if (val != null) {
        let to = val;
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
