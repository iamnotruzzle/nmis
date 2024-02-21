<template>
  <app-layout>
    <Head title="NMIS - Sub-categories" />

    <div
      class="card"
      style="width: 100%"
    >
      <Toast />

      <!-- :value="balanceContainer" -->
      <DataTable
        class="p-datatable-sm"
        v-model:filters="filters"
        :value="csrSuppliesSubCategoryList"
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
            <span class="text-xl text-900 font-bold text-cyan-500 hover:text-cyan-700"
              >CSR SUPPLIES SUB-CATEGORIES</span
            >
            <div>
              <span class="p-input-icon-left mr-2">
                <i class="pi pi-search" />
                <InputText
                  v-model="search"
                  placeholder="Search sub-category"
                />
              </span>

              <Button
                label="Add sub-category"
                icon="pi pi-plus"
                iconPos="right"
                @click="openCreateDataDialog"
              />
            </div>
          </div>
        </template>
        <template #empty> No data found. </template>
        <template #loading> Loading data. Please wait. </template>
        <Column
          field="cl1comb"
          header="ID"
          style="width: 20%"
        >
          <template #body="{ data }">
            {{ data.cl1comb }}
          </template>
        </Column>
        <Column
          field="cl1desc"
          header="DESCRIPTION"
          style="width: 50%"
        >
          <template #body="{ data }">
            {{ data.cl1desc }}
          </template>
        </Column>
        <Column
          field="cl1stat"
          header="STATUS"
          :showFilterMenu="false"
          style="width: 20%"
        >
          <template #body="{ data }">
            <div class="text-center">
              <Tag
                v-if="data.cl1stat == 'A'"
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
          style="width: 30%"
        >
          <template #body="slotProps">
            <Button
              icon="pi pi-pencil"
              class="mr-1"
              rounded
              text
              severity="warning"
              @click="editSubCategory(slotProps.data)"
            />

            <Button
              icon="pi pi-trash"
              rounded
              text
              severity="danger"
              @click="confirmDeleteCategory(slotProps.data)"
            />
          </template>
        </Column>
      </DataTable>

      <Dialog
        v-model:visible="createDataDialog"
        header="SUB-CATEGORY"
        :modal="true"
        :style="{ width: '850px' }"
        class="p-fluid"
        @hide="whenDialogIsHidden"
      >
        <!-- ITEM -->
        <div class="field">
          <label>Item</label>
          <!-- <Dropdown
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
          </small> -->
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
        v-model:visible="deleteCategoryDialog"
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
            >Are you sure you want to delete <b>{{ form.cl1desc }} </b> ?</span
          >
        </div>
        <template #footer>
          <Button
            label="No"
            icon="pi pi-times"
            class="p-button-text"
            @click="deleteCategoryDialog = false"
          />
          <Button
            label="Yes"
            icon="pi pi-check"
            severity="danger"
            text
            :disabled="form.processing"
            @click="deleteCategory"
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
    csrSuppliesSubCategory: Object,
  },
  data() {
    return {
      // paginator
      loading: false,
      totalRecords: null,
      rows: null,
      // end paginator
      isUpdate: false,
      createDataDialog: false,
      deleteCategoryDialog: false,
      selectedStatus: null,
      search: '',
      options: {},
      params: {},
      from: null,
      to: null,
      statusFilter: [
        { name: 'NO FILTER', code: null },
        { name: 'Active', code: 'A' },
        { name: 'Inactive', code: 'I' },
      ],
      csrSuppliesSubCategoryList: [],
      filters: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
      form: this.$inertia.form({
        cl1comb: null,
        cl1desc: null,
        cl1stat: null,
      }),
    };
  },
  // created will be initialize before mounted
  created() {
    this.totalRecords = this.csrSuppliesSubCategory.total;
    this.params.page = this.csrSuppliesSubCategory.current_page;
    this.rows = this.csrSuppliesSubCategory.per_page;
  },
  mounted() {
    console.log(this.csrSuppliesSubCategory);
    this.storeSubCategoryInContainer();

    this.loading = false;
  },
  methods: {
    storeSubCategoryInContainer() {
      this.csrSuppliesSubCategoryList = []; // reset

      this.csrSuppliesSubCategory.data.forEach((e) => {
        this.csrSuppliesSubCategoryList.push({
          cl1comb: e.cl1comb,
          cl1desc: e.cl1desc,
          cl1stat: e.cl1stat,
        });
      });
    },
    updateData() {
      this.loading = true;

      this.$inertia.get('categories', this.params, {
        preserveState: true,
        preserveScroll: true,
        onFinish: (visit) => {
          this.totalRecords = this.csrSuppliesSubCategory.total;
          this.storeSubCategoryInContainer();
          this.loading = false;
        },
      });
    },
    onPage(event) {
      this.params.page = event.page + 1;
      this.updateData();
    },
    openCreateDataDialog() {
      this.isUpdate = false;
      this.form.clearErrors();
      this.form.reset();
      this.createDataDialog = true;
    },
    clickOutsideDialog() {
      this.$emit('hide', (this.isUpdate = false), this.form.clearErrors(), this.form.reset());
    },
    editSubCategory(item) {
      //   console.log(item);
      this.isUpdate = true;
      this.createDataDialog = true;
      this.form.id = item.id;
    },
    submit() {
      if (this.form.processing) {
        return false;
      }

      if (this.isUpdate) {
        this.form.put(route('categories.update', this.form.cl1comb), {
          preserveScroll: true,
          onSuccess: () => {
            this.createDataDialog = false;
            this.storeSubCategoryInContainer();
            this.cancel();
            this.updateData();
            this.updatedMsg();
          },
        });
      } else {
        this.form.post(route('categories.store'), {
          preserveScroll: true,
          onSuccess: () => {
            this.createDataDialog = false;
            this.storeSubCategoryInContainer();
            this.cancel();
            this.updateData();
            this.createdMsg();
          },
        });
      }
    },
    whenDialogIsHidden() {
      this.$emit('hide', (this.isUpdate = false), this.form.clearErrors(), this.form.reset());
    },
    confirmDeleteCategory(item) {
      this.deleteCategoryDialog = true;
    },
    deleteCategory() {
      this.form.delete(route('categories.destroy', this.form.cl1comb), {
        preserveScroll: true,
        onSuccess: () => {
          this.balanceContainer = [];
          this.deleteCategoryDialog = false;
          this.form.clearErrors();
          this.form.reset();
          this.updateData();
          this.deletedMsg();
        },
      });
    },
    cancel() {
      this.isUpdate = false;
      this.createDataDialog = false;
      this.form.reset();
      this.form.clearErrors();
    },
    createdMsg() {
      this.$toast.add({
        severity: 'success',
        summary: 'Success',
        detail: 'SubCategory created',
        life: 3000,
      });
    },
    updatedMsg() {
      this.$toast.add({
        severity: 'warn',
        summary: 'Success',
        detail: 'SubCategory updated',
        life: 3000,
      });
    },
    deletedMsg() {
      this.$toast.add({
        severity: 'error',
        summary: 'Success',
        detail: 'SubCategory deleted',
        life: 3000,
      });
    },
  },
  watch: {
    search: function (val, oldVal) {
      this.params.search = val;
      this.updateData();
    },
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
