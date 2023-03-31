<template>
  <app-layout>
    <Head title="Template - Users" />

    <div class="card">
      <Toast />

      <!-- v-model:filters="filters" -->
      <DataTable
        class="p-datatable-sm"
        v-model:filters="filters"
        :value="categoriesList"
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
            <span class="text-xl text-900 font-bold">Category</span>
            <div>
              <span class="p-input-icon-left mr-2">
                <i class="pi pi-search" />
                <InputText
                  v-model="search"
                  placeholder="Search category"
                />
              </span>
              <Button
                label="Add category"
                icon="pi pi-plus"
                iconPos="right"
                @click="openCreateItemDialog"
              />
            </div>
          </div>
        </template>
        <template #empty> No category found. </template>
        <template #loading> Loading category data. Please wait. </template>
        <Column
          field="ptcode"
          header="PTCODE"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.ptcode }}
          </template>
        </Column>
        <Column
          field="cl1code"
          header="CL1CODE"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.cl1code }}
          </template>
        </Column>
        <Column
          field="cl1code"
          header="CL1COMB"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.cl1comb }}
          </template>
        </Column>
        <Column
          field="cl1desc"
          header="CL1DESC"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.cl1desc }}
          </template>
        </Column>
        <Column
          field="cl1lock"
          header="CL1LOCK"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.cl1lock }}
          </template>
        </Column>
        <Column
          field="cl1stat"
          header="CL1STAT"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.cl1stat }}
          </template>
        </Column>
        <Column
          field="cl1upsw"
          header="CL1UPSW"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.cl1upsw }}
          </template>
        </Column>
        <Column
          header="Action"
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

      <!-- create & edit dialog -->
      <Dialog
        v-model:visible="createItemDialog"
        :style="{ width: '450px' }"
        header="Category Detail"
        :modal="true"
        class="p-fluid"
        @hide="clickOutsideDialog"
        dismissableMask
      >
        <div class="field">
          <label for="ptcode">Ptcode</label>
          <InputText
            id="ptcode"
            v-model.trim="form.ptcode"
            required="true"
            autofocus
            :class="{ 'p-invalid': form.ptcode == '' }"
          />
          <small
            class="text-error"
            v-if="form.errors.ptcode"
          >
            {{ form.errors.ptcode }}
          </small>
        </div>
        <div class="field">
          <label for="cl1code">Cl1code</label>
          <InputText
            id="cl1code"
            v-model.trim="form.cl1code"
            required="true"
            autofocus
            :class="{ 'p-invalid': form.cl1code == '' }"
          />
          <small
            class="text-error"
            v-if="form.errors.cl1code"
          >
            {{ form.errors.cl1code }}
          </small>
        </div>
        <div class="field">
          <label for="cl1comb">Cl1comb</label>
          <InputText
            id="cl1comb"
            v-model.trim="form.cl1comb"
            required="true"
            autofocus
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
          <label for="cl1desc">Cl1desc</label>
          <InputText
            id="cl1desc"
            v-model.trim="form.cl1desc"
            required="true"
            autofocus
            :class="{ 'p-invalid': form.cl1desc == '' }"
          />
          <small
            class="text-error"
            v-if="form.errors.cl1desc"
          >
            {{ form.errors.cl1desc }}
          </small>
        </div>
        <div class="field">
          <label for="cl1lock">Cl1lock</label>
          <InputText
            id="cl1lock"
            v-model.trim="form.cl1lock"
            required="true"
            autofocus
            :class="{ 'p-invalid': form.cl1lock == '' }"
          />
          <small
            class="text-error"
            v-if="form.errors.cl1lock"
          >
            {{ form.errors.cl1lock }}
          </small>
        </div>
        <div class="field">
          <label for="cl1stat">Cl1stat</label>
          <Dropdown
            v-model="form.cl1stat"
            :options="cl1stats"
            optionLabel="name"
            optionValue="value"
            class="w-full md:w-14rem"
          />
          <small
            class="text-error"
            v-if="form.errors.cl1stat"
          >
            {{ form.errors.cl1stat }}
          </small>
        </div>
        <div class="field">
          <label for="cl1upsw">Cl1upsw</label>
          <InputText
            id="cl1upsw"
            v-model.trim="form.cl1upsw"
            required="true"
            autofocus
            :class="{ 'p-invalid': form.cl1upsw == '' }"
          />
          <small
            class="text-error"
            v-if="form.errors.cl1upsw"
          >
            {{ form.errors.cl1upsw }}
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
            >Are you sure you want to delete <b>{{ form.cl1desc }}</b> ?</span
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
            @click="deleteItem"
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
    categories: Object,
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
      createItemDialog: false,
      deleteItemDialog: false,
      search: '',
      options: {},
      params: {},
      categoriesList: [],
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
        cl1comb: null,
        ptcode: null,
        cl1code: null,
        cl1desc: null,
        cl1lock: null,
        cl1stat: null,
        cl1upsw: null,
      }),
    };
  },
  // created will be initialize before mounted
  created() {
    this.totalRecords = this.categories.total;
    this.params.page = this.categories.current_page;
    this.rows = this.categories.per_page;
  },
  mounted() {
    this.storeCategoryInContainer();
  },
  methods: {
    // use storeCategoryInContainer() function so that every time you make
    // server request such as POST, the data in the table
    // is updated
    storeCategoryInContainer() {
      this.categories.data.forEach((e) => {
        this.categoriesList.push({
          cl1code: e.cl1code,
          cl1comb: e.cl1comb,
          cl1desc: e.cl1desc,
          cl1dtmd: e.cl1dtmd,
          cl1lock: e.cl1lock,
          cl1stat: e.cl1stat,
          cl1upsw: e.cl1upsw,
          ptcode: e.ptcode,
        });
      });
    },
    onPage(event) {
      this.params.page = event.page + 1;
      this.updateData();
    },
    updateData() {
      this.categoriesList = [];
      this.loading = true;

      this.$inertia.get('categories', this.params, {
        preserveState: true,
        preserveScroll: true,
        onFinish: (visit) => {
          this.totalRecords = this.categories.total;
          this.categoriesList = [];
          this.storeCategoryInContainer();
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
      this.$emit('hide', (this.isUpdate = false), this.form.clearErrors(), this.form.reset());
    },
    editItem(item) {
      this.isUpdate = true;
      this.createItemDialog = true;
      this.itemId = item.cl1comb;
      this.form.cl1comb = item.cl1comb;
      this.form.ptcode = item.ptcode;
      this.form.cl1code = item.cl1code;
      this.form.cl1desc = item.cl1desc;
      this.form.cl1lock = item.cl1lock;
      this.form.cl1stat = item.cl1stat;
      this.form.cl1upsw = item.cl1upsw;
    },
    submit() {
      if (this.isUpdate) {
        this.form.put(route('categories.update'), {
          preserveScroll: true,
          onSuccess: () => {
            this.itemId = null;
            this.createItemDialog = false;
            this.cancel();
            // this.updateData(); // orig
            this.updateData();
            this.updatedMsg();
          },
        });
      } else {
        this.form.post(route('categories.store'), {
          preserveScroll: true,
          onSuccess: () => {
            this.itemId = null;
            this.createItemDialog = false;
            this.cancel();
            // this.updateData(); // orig
            this.updateData();
            this.createdMsg();
          },
        });
      }
    },
    confirmDeleteItem(item) {
      this.itemId = item.cl1comb;
      this.form.cl1desc = item.cl1desc;
      this.deleteItemDialog = true;
    },
    deleteItem() {
      this.form.delete(route('categories.destroy', this.itemId), {
        preserveScroll: true,
        onSuccess: () => {
          this.deleteItemDialog = false;
          this.itemId = null;
          this.storeCategoryInContainer();
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
      this.createItemDialog = false;
      this.form.reset();
      this.form.clearErrors();
      this.categoriesList = [];
      this.storeCategoryInContainer();
    },
    createdMsg() {
      this.$toast.add({ severity: 'success', summary: 'Success', detail: 'Category created', life: 3000 });
    },
    updatedMsg() {
      this.$toast.add({ severity: 'warn', summary: 'Success', detail: 'Category updated', life: 3000 });
    },
    deletedMsg() {
      this.$toast.add({ severity: 'error', summary: 'Success', detail: 'Category deleted', life: 3000 });
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
