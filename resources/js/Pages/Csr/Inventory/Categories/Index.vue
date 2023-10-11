<template>
  <app-layout>
    <Head title="InvenTrackr - Categories" />

    <div class="card">
      <Toast />

      <DataTable
        class="p-datatable-sm"
        dataKey="ptcode"
        v-model:filters="filters"
        v-model:expandedRows="expandedRows"
        :value="mainCategoriesList"
        selectionMode="single"
        lazy
        paginator
        :rows="rows"
        ref="dt"
        :totalRecords="totalRecords"
        showGridlines
        @page="onPage($event)"
        filterDisplay="row"
        :loading="loading"
      >
        <template #header>
          <div class="flex flex-wrap align-items-center justify-content-between gap-2">
            <span class="text-xl text-900 font-bold text-cyan-500 hover:text-cyan-700">CATEGORIES</span>
            <div>
              <span class="p-input-icon-left mr-2">
                <i class="pi pi-search" />
                <InputText
                  v-model="search"
                  placeholder="Search category"
                />
              </span>
              <Button
                label="Add Category"
                icon="pi pi-plus"
                iconPos="right"
                @click="openCreateCategoryDialog"
              />
            </div>
          </div>
        </template>
        <template #empty> No item found. </template>
        <template #loading> Loading item data. Please wait. </template>
        <Column
          expander
          style="width: 5rem"
        />
        <Column
          field="ptcode"
          header="PTCODE"
          style="min-width: 12rem"
        >
          <!-- <template #body="{ data }">
            {{ data.cl2comb }}
          </template> -->
        </Column>
        <Column
          field="ptdesc"
          header="PTDESC"
          style="min-width: 12rem"
        >
        </Column>
        <Column
          field="ptstat"
          header="PTSTAT"
          style="min-width: 12rem"
          :showFilterMenu="false"
        >
          <template #body="{ data }">
            <Tag
              v-if="data.ptstat == 'A'"
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

            <Button
              icon="pi pi-trash"
              rounded
              text
              severity="danger"
              @click="confirmDeleteCategory(slotProps.data)"
            />
          </template>
        </Column>
        <template #expansion="slotProps">
          <div class="max-w-full flex flex-column align-items-center">
            <!-- {{ slotProps.data }} -->

            <div class="flex align-items-center w-full">
              <div class="text-2xl font-bold my-3">
                Sub-categories of <span class="text-cyan-500 hover:text-cyan-700">[ {{ slotProps.data.ptdesc }} ]</span>
              </div>

              <Button
                label="Add sub-category"
                icon="pi pi-plus"
                iconPos="right"
                size="small"
                class="ml-2 my-0"
                @click="openCreateSubCategoryDialog(slotProps.data)"
              />
            </div>

            <DataTable
              :dataKey="slotProps.ptcode"
              :value="slotProps.data.subCategory"
              paginator
              :rows="5"
              class="w-full"
            >
              <Column header="PTCODE">
                <template #body="{ data }"> {{ data.ptcode }} </template>
              </Column>
              <Column header="CL1CODE">
                <template #body="{ data }"> {{ data.cl1code }} </template>
              </Column>
              <Column header="CL1COMB">
                <template #body="{ data }"> {{ data.cl1comb }} </template>
              </Column>
              <Column header="NAME">
                <template #body="{ data }"> {{ data.cl1desc }} </template>
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
                    @click="editSubCategory(slotProps.data)"
                  />

                  <Button
                    icon="pi pi-trash"
                    rounded
                    text
                    severity="danger"
                    @click="confirmDeleteSubCategory(slotProps.data)"
                  />
                </template>
              </Column>
            </DataTable>
          </div>
        </template>
      </DataTable>

      <!-- create & edit dialog -->
      <Dialog
        v-model:visible="createCategoryDialog"
        :style="{ width: '450px' }"
        header="Item Detail"
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
            @keyup.enter="submit"
          />
          <small
            class="text-error"
            v-if="form.errors.ptcode"
          >
            {{ form.errors.ptcode }}
          </small>
        </div>
        <div class="field">
          <label for="ptdesc">Ptdesc</label>
          <InputText
            id="ptdesc"
            v-model.trim="form.ptdesc"
            required="true"
            autofocus
            :class="{ 'p-invalid': form.ptdesc == '' }"
            @keyup.enter="submit"
          />
          <small
            class="text-error"
            v-if="form.errors.ptdesc"
          >
            {{ form.errors.ptdesc }}
          </small>
        </div>
        <div class="field">
          <label for="ptstat">Ptstat</label>
          <Dropdown
            v-model="form.ptstat"
            :options="status"
            optionLabel="name"
            optionValue="code"
            placeholder="Select status"
            class="w-full"
          />
          <small
            class="text-error"
            v-if="form.errors.ptstat"
          >
            {{ form.errors.ptstat }}
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
            >Are you sure you want to delete <b>{{ form.ptdesc }}</b> ?</span
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
            @click="deleteItem"
          />
        </template>
      </Dialog>

      <!-- create & edit sub category dialog -->
      <Dialog
        v-model:visible="createSubCategoryDialog"
        :style="{ width: '450px' }"
        header="Sub-category"
        :modal="true"
        class="p-fluid"
        @hide="clickOutsideSubCategoryDialog"
        dismissableMask
      >
        <div class="field">
          <label for="ptcode">Ptcode</label>
          <InputText
            id="ptcode"
            v-model.trim="formSubCategory.ptcode"
            required="true"
            autofocus
            disabled
          />
          <small
            class="text-error"
            v-if="formSubCategory.errors.ptcode"
          >
            {{ formSubCategory.errors.ptcode }}
          </small>
        </div>
        <div class="field">
          <label for="cl1code">Cl1code</label>
          <InputText
            id="cl1code"
            v-model.trim="formSubCategory.cl1code"
            required="true"
            autofocus
            @keyup.enter="submitSubCategory"
          />
          <small
            class="text-error"
            v-if="formSubCategory.errors.cl1code"
          >
            {{ formSubCategory.errors.cl1code }}
          </small>
        </div>
        <div class="field">
          <label for="cl1desc">Cl1desc</label>
          <InputText
            id="cl1desc"
            v-model.trim="formSubCategory.cl1desc"
            required="true"
            autofocus
            @keyup.enter="submitSubCategory"
          />
          <small
            class="text-error"
            v-if="formSubCategory.errors.cl1desc"
          >
            {{ formSubCategory.errors.cl1desc }}
          </small>
        </div>
        <div class="field">
          <label for="cl1stat">cl1stat</label>
          <Dropdown
            v-model="formSubCategory.cl1stat"
            :options="status"
            optionLabel="name"
            optionValue="code"
            placeholder="Select status"
            class="w-full"
          />
          <small
            class="text-error"
            v-if="formSubCategory.errors.cl1stat"
          >
            {{ formSubCategory.errors.cl1stat }}
          </small>
        </div>

        <template #footer>
          <Button
            label="Cancel"
            icon="pi pi-times"
            severity="danger"
            text
            @click="cancelSubCategory"
          />
          <Button
            v-if="isSubCategoryUpdate == true"
            label="Update"
            icon="pi pi-check"
            severity="warning"
            text
            type="submit"
            :disabled="formSubCategory.processing"
            @click="submitSubCategory"
          />
          <Button
            v-else
            label="Save"
            icon="pi pi-check"
            text
            type="submit"
            :disabled="formSubCategory.processing"
            @click="submitSubCategory"
          />
        </template>
      </Dialog>

      <!-- Delete category confirmation dialog -->
      <Dialog
        v-model:visible="deleteSubCategoryDialog"
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
            Are you sure you want to delete <b>{{ formSubCategory.cl1desc }}</b> ?
          </span>
        </div>
        <template #footer>
          <Button
            label="No"
            icon="pi pi-times"
            class="p-button-text"
            @click="deleteSubCategoryDialog = false"
          />
          <Button
            label="Yes"
            icon="pi pi-check"
            severity="danger"
            text
            @click="deleteSubCategory"
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
    Tag,
  },
  props: {
    mainCategory: Object,
  },
  data() {
    return {
      // data table expand
      expandedRows: [],
      // end data table expand
      // paginator
      loading: false,
      totalRecords: null,
      rows: null,
      // end paginator
      itemId: null,
      isUpdate: false,
      createCategoryDialog: false,
      deleteCategoryDialog: false,
      // categories
      isSubCategoryUpdate: false,
      createSubCategoryDialog: false,
      deleteSubCategoryDialog: false,
      search: '',
      selectedStatus: null,
      options: {},
      params: {},
      mainCategoriesList: [],
      filters: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
      status: [
        { name: 'Active', code: 'A' },
        { name: 'Inactive', code: 'I' },
      ],
      statusFilter: [
        { name: 'NO FILTER', code: null },
        { name: 'Active', code: 'A' },
        { name: 'Inactive', code: 'I' },
      ],
      form: this.$inertia.form({
        ptcode: null,
        ptdesc: null,
        ptstat: null,
        ptstat: null,
      }),
      formSubCategory: this.$inertia.form({
        cl1comb: null,
        ptcode: null,
        cl1code: null,
        cl1desc: null,
        cl1stat: null,
      }),
    };
  },
  // created will be initialize before mounted
  created() {
    this.totalRecords = this.mainCategory.total;
    this.params.page = this.mainCategory.current_page;
    this.rows = this.mainCategory.per_page;
  },
  mounted() {
    // console.log(this.mainCategory);
    this.storeCategoriesInContainer();

    this.loading = false;
  },
  computed: {
    user() {
      return this.$page.props.auth.user;
    },
  },
  methods: {
    tzone(date) {
      return moment.tz(date, 'Asia/Manila').format('LLL');
    },

    storeCategoriesInContainer() {
      this.mainCategory.data.forEach((e) => {
        this.mainCategoriesList.push({
          ptcode: e.ptcode,
          ptdesc: e.ptdesc,
          ptstat: e.ptstat,
          ptlock: e.ptlock,
          subCategory: e.sub_category.length == 0 ? null : e.sub_category,
        });
      });
      //   console.log(this.mainCategoriesList);
    },
    onPage(event) {
      this.params.page = event.page + 1;
      this.updateData();
    },
    updateData() {
      this.mainCategoriesList = [];
      this.loading = true;

      this.$inertia.get('categories', this.params, {
        preserveState: true,
        preserveScroll: true,
        onFinish: (visit) => {
          this.totalRecords = this.mainCategory.total;
          this.mainCategoriesList = [];
          this.storeCategoriesInContainer();
          this.loading = false;
        },
      });
    },
    openCreateCategoryDialog() {
      this.isUpdate = false;
      this.form.clearErrors();
      this.form.reset();
      this.itemId = null;
      this.createCategoryDialog = true;
    },
    // emit close dialog
    clickOutsideDialog() {
      this.$emit('hide', (this.itemId = null), (this.isUpdate = false), this.form.clearErrors(), this.form.reset());
    },
    editItem(item) {
      this.isUpdate = true;
      this.createCategoryDialog = true;
      this.itemId = item.ptcode;
      this.form.ptcode = item.ptcode;
      this.form.ptdesc = item.ptdesc;
      this.form.ptstat = item.ptstat;
    },
    submit() {
      if (this.isUpdate) {
        this.form.put(route('categories.update', this.itemId), {
          preserveScroll: true,
          onSuccess: () => {
            this.itemId = null;
            this.createCategoryDialog = false;
            this.cancel();
            this.updateData();
            this.updatedMsg();
          },
        });
      } else {
        this.form.post(route('categories.store'), {
          preserveScroll: true,
          onSuccess: () => {
            this.createCategoryDialog = false;
            this.cancel();
            this.updateData();
            this.createdMsg();
          },
        });
      }
    },
    confirmDeleteCategory(category) {
      this.itemId = category.ptcode;
      this.form.ptdesc = category.ptdesc;
      this.deleteCategoryDialog = true;
    },
    deleteItem() {
      this.form.delete(route('categories.destroy', this.itemId), {
        preserveScroll: true,
        onSuccess: () => {
          this.mainCategoriesList = [];
          this.deleteCategoryDialog = false;
          this.itemId = null;
          this.form.clearErrors();
          this.form.reset();
          this.updateData();
          this.deletedMsg();
          this.storeCategoriesInContainer();
        },
      });
    },
    cancel() {
      this.itemId = null;
      this.isUpdate = false;
      this.createCategoryDialog = false;
      this.form.reset();
      this.form.clearErrors();
      this.mainCategoriesList = [];
      this.storeCategoriesInContainer();
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
    // ********** sub category
    openCreateSubCategoryDialog(e) {
      this.formSubCategory.ptcode = e.ptcode;
      this.isSubCategoryUpdate = false;
      this.createSubCategoryDialog = true;
    },
    // emit category close dialog
    clickOutsideSubCategoryDialog() {
      this.$emit(
        'hide',
        (this.isSubCategoryUpdate = false),
        this.formSubCategory.clearErrors(),
        this.formSubCategory.reset()
      );
    },
    editSubCategory(e) {
      //   console.log(e);
      this.isSubCategoryUpdate = true;
      this.createSubCategoryDialog = true;
      this.formSubCategory.cl1comb = e.cl1comb;
      this.formSubCategory.ptcode = e.ptcode;
      this.formSubCategory.cl1code = e.cl1code;
      this.formSubCategory.cl1desc = e.cl1desc;
      this.formSubCategory.cl1stat = e.cl1stat;
    },
    submitSubCategory() {
      if (this.isSubCategoryUpdate) {
        this.formSubCategory.put(route('subcategories.update', this.formSubCategory.cl1comb), {
          preserveScroll: true,
          onSuccess: () => {
            this.createSubCategoryDialog = false;
            this.cancelSubCategory();
            this.updateData();
            this.updatedSubCategory();
          },
        });
      } else {
        this.formSubCategory.post(route('subcategories.store'), {
          preserveScroll: true,
          onSuccess: () => {
            this.createCategoryDialog = false;
            this.cancelSubCategory();
            this.updateData();
            this.createdSubCategory();
          },
        });
      }
    },
    confirmDeleteSubCategory(e) {
      this.formSubCategory.cl1comb = e.cl1comb;
      this.formSubCategory.cl1desc = e.cl1desc;
      this.deleteSubCategoryDialog = true;
    },
    deleteSubCategory() {
      this.formSubCategory.delete(route('subcategories.destroy', this.formSubCategory.cl1comb), {
        preserveScroll: true,
        onSuccess: () => {
          this.deleteSubCategoryDialog = false;
          this.formSubCategory.clearErrors();
          this.formSubCategory.reset();
          this.updateData();
          this.deletedSubCategory();
          //   this.storeCategoriesInContainer();
        },
      });
    },
    cancelSubCategory() {
      this.isSubCategoryUpdate = false;
      this.createSubCategoryDialog = false;
      this.formSubCategory.reset();
      this.formSubCategory.clearErrors();
    },
    createdSubCategory() {
      this.$toast.add({ severity: 'success', summary: 'Success', detail: 'Sub-category created', life: 3000 });
    },
    updatedSubCategory() {
      this.$toast.add({ severity: 'warn', summary: 'Success', detail: 'Sub-category updated', life: 3000 });
    },
    deletedSubCategory() {
      this.$toast.add({ severity: 'error', summary: 'Success', detail: 'Sub-category deleted', life: 3000 });
    },
    // ********** end sub category
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
