<template>
  <app-layout>
    <Head title="Template - Items" />

    <div class="card">
      <Toast />

      <DataTable
        class="p-datatable-sm"
        v-model:filters="filters"
        :value="itemsList"
        selectionMode="single"
        lazy
        paginator
        :rows="rows"
        ref="dt"
        :totalRecords="totalRecords"
        @page="onPage($event)"
        dataKey="cl2comb"
        filterDisplay="row"
        showGridlines
        :loading="loading"
      >
        <template #header>
          <div class="flex flex-wrap align-items-center justify-content-between gap-2">
            <span class="text-xl text-900 font-bold">Items</span>
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
        <Column
          field="cl2comb"
          header="CL2COMB"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.cl2comb }}
          </template>
        </Column>
        <Column
          field="cl1comb"
          header="CL1COMB"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.cl1comb }}
          </template>
        </Column>
        <Column
          field="cl2code"
          header="CL2CODE"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.cl2code }}
          </template>
        </Column>
        <Column
          field="cl2desc"
          header="CL2DESC"
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
        >
          <template #body="{ data }">
            {{ data.cl2stat }}
          </template>
        </Column>
        <Column
          field="cl2lock"
          header="CL2LOCK"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.cl2lock }}
          </template>
        </Column>
        <Column
          field="cl2upsw"
          header="CL2UPSW"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.cl2upsw }}
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
        header="Item Detail"
        :modal="true"
        class="p-fluid"
        @hide="clickOutsideDialog"
        dismissableMask
      >
        <div class="field">
          <label for="cl1comb">Cl1comb</label>
          <Dropdown
            v-model.trim="form.cl1comb"
            required="true"
            :options="cl1combsList"
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
        <div class="field">
          <label for="cl2code">Cl2code</label>
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
          <label for="cl2desc">Cl2desc</label>
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
          <label for="unit">UNIT</label>
          <Dropdown
            required="true"
            v-model="form.unit"
            :options="unitsList"
            dataKey="unit"
            optionLabel="uomdesc"
            optionValue="uomdesc"
            class="w-full mb-3"
            :class="{ 'p-invalid': form.unit == '' }"
          />
          <small
            class="text-error"
            v-if="form.errors.unit"
          >
            {{ form.errors.unit }}
          </small>
        </div>
        <div class="field">
          <label for="cl2upsw">Cl2upsw</label>
          <InputText
            id="cl2upsw"
            v-model.trim="form.cl2upsw"
            required="true"
            autofocus
            :class="{ 'p-invalid': form.cl2upsw == '' }"
            @keyup.enter="submit"
          />
          <small
            class="text-error"
            v-if="form.errors.cl2upsw"
          >
            {{ form.errors.cl2upsw }}
          </small>
        </div>
        <div class="field">
          <label for="cl2stat">Cl2stat</label>
          <Dropdown
            v-model="form.cl2stat"
            :options="cl2stats"
            optionLabel="name"
            optionValue="value"
            class="w-full md:w-14rem"
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
import Textarea from 'primevue/textarea';

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
  },
  props: {
    cl1combs: Array,
    units: Array,
    items: Object,
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
      itemsList: [],
      cl1combsList: [],
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
        cl2upsw: null,
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
    this.storeCl1combsInContainer();
    this.storeItemInContainer();
    this.storeUnitsInContainer();

    this.loading = false;
  },
  methods: {
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
      this.items.data.forEach((e) => {
        this.itemsList.push({
          cl2comb: e.cl2comb,
          cl1comb: e.cl1comb,
          cl2code: e.cl2code,
          cl2desc: e.cl2desc,
          uomcode: e.unit === null ? '' : e.unit.uomdesc,
          cl2stat: e.cl2stat,
          cl2lock: e.cl2lock,
          cl2upsw: e.cl2upsw,
          pharmaceutical: e.pharmaceutical,
        });
      });
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
      this.isUpdate = true;
      this.createItemDialog = true;
      this.itemId = item.cl2comb;
      this.form.cl2comb = item.cl2comb;
      this.form.cl1comb = item.cl1comb;
      this.form.cl2code = item.cl2code;
      this.form.cl2desc = item.cl2desc;
      this.form.unit = item.uomcode;
      this.form.cl2stat = item.cl2stat;
      this.form.cl2upsw = item.cl2upsw;
    },
    submit() {
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
  },
  watch: {
    search: function (val, oldVal) {
      this.params.search = val;
      this.updateData();
    },
  },
};
</script>
