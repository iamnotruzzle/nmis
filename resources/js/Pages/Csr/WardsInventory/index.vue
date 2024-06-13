<template>
  <app-layout>
    <Head title="NMIS - Sub-categories" />

    <div
      class="card"
      style="width: 100%"
    >
      <Toast />

      <DataTable
        class="p-datatable-sm"
        v-model:filters="filters"
        :value="csrSuppliesSubCategoryList"
        paginator
        :rows="20"
        :rowsPerPageOptions="[20, 30, 40]"
        dataKey="cl1comb"
        filterDisplay="row"
        sortField="categoryname"
        :sortOrder="1"
        removableSort
        :globalFilterFields="['cl1comb', 'cl1desc', 'cl1stat']"
        showGridlines
      >
        <template #header>
          <div class="flex flex-wrap align-items-center justify-content-between gap-2">
            <span class="text-xl text-900 font-bold text-primary">CSR SUPPLIES SUB-CATEGORIES</span>
            <div class="flex">
              <div class="mr-2">
                <div class="p-inputgroup">
                  <span class="p-inputgroup-addon">
                    <i class="pi pi-search"></i>
                  </span>
                  <InputText
                    id="searchInput"
                    v-model="filters['global'].value"
                    placeholder="Search sub-category"
                  />
                </div>
              </div>

              <Button
                v-if="$page.props.auth.user.roles[0] == 'super-admin'"
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
          style="width: 5%"
        >
          <template #body="{ data }">
            {{ data.cl1comb }}
          </template>
        </Column>
        <Column
          field="categoryname"
          header="CATEGORY"
          :showFilterMenu="false"
          style="width: 5%"
        >
          <template #body="{ data }">
            {{ data.categoryname }}
          </template>

          <template #filter="{ filterModel, filterCallback }">
            <Dropdown
              v-model="filterModel.value"
              :options="categoryFilter"
              @change="filterCallback()"
              optionLabel="name"
              optionValue="name"
              placeholder="NO FILTER"
            />
          </template>
        </Column>
        <Column
          field="cl1desc"
          header="DESCRIPTION"
          sortable
          style="width: 30%"
        >
          <template #body="{ data }">
            {{ data.cl1desc }}
          </template>
        </Column>
        <Column
          field="cl1stat"
          header="STATUS"
          :showFilterMenu="false"
          style="width: 5%"
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
          <template #filter="{ filterModel, filterCallback }">
            <Dropdown
              v-model="filterModel.value"
              :options="statusFilter"
              @change="filterCallback()"
              optionLabel="name"
              optionValue="code"
              placeholder="NO FILTER"
              class="w-full"
            >
              <template #option="slotProps">
                <Tag
                  :value="slotProps.option.name"
                  :severity="statusSeverity(slotProps.option)"
                />
              </template>
            </Dropdown>
          </template>
        </Column>
        <!-- <Column
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
        </Column> -->
      </DataTable>

      <Dialog
        v-model:visible="createDataDialog"
        :modal="true"
        :style="{ width: '400px' }"
        class="p-fluid"
        @hide="whenDialogIsHidden"
      >
        <template #header>
          <div class="text-primary text-xl font-bold">SUB-CATEGORY</div>
        </template>
        <!-- category -->
        <div class="field">
          <label>Category</label>
          <Dropdown
            required="true"
            v-model="form.category"
            :options="categoryList"
            optionLabel="name"
            optionValue="catID"
            class="w-full"
            autofocus
          />
          <small
            class="text-error"
            v-if="form.errors.category"
          >
            {{ form.errors.category }}
          </small>
        </div>
        <!-- description -->
        <div class="field">
          <label>Description</label>
          <InputText
            id="Description"
            v-model.trim="form.description"
            required="true"
            :class="{ 'p-invalid': form.description == '' }"
            @keyup.enter="submit"
          />
          <small
            class="text-error"
            v-if="form.errors.description"
          >
            {{ form.errors.description }}
          </small>
        </div>
        <!-- Status -->
        <div class="field">
          <label>Status</label>
          <Dropdown
            required="true"
            v-model="form.status"
            :options="statusList"
            optionLabel="name"
            optionValue="code"
            class="w-full"
          >
            <template #option="slotProps">
              <Tag
                :value="slotProps.option.name"
                :severity="statusSeverity(slotProps.option)"
              />
            </template>
          </Dropdown>
          <small
            class="text-error"
            v-if="form.errors.status"
          >
            {{ form.errors.status }}
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

      <!-- <Dialog
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
      </Dialog> -->
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
import IconField from 'primevue/iconField';
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
    IconField,
  },
  props: {
    csrSuppliesSubCategory: Object,
  },
  data() {
    return {
      statusFilter: [
        { name: 'Active', code: 'A' },
        { name: 'Inactive', code: 'I' },
      ],
      categoryFilter: [
        { name: 'Accountable forms', catID: 22 },
        { name: 'Drugs and medicines', catID: 9 },
        { name: 'IT supplies', catID: 3 },
        { name: 'Medical supplies', catID: 1 },
        { name: 'Non-accountable Forms', catID: 25 },
        { name: 'Office Supplies', catID: 2 },
        { name: 'Other Supplies and Materials', catID: 28 },
      ],
      csrSuppliesSubCategoryList: [],
      filters: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
        cl1comb: { value: null, matchMode: FilterMatchMode.CONTAINS },
        categoryname: { value: null, matchMode: FilterMatchMode.EQUALS },
        cl1desc: { value: null, matchMode: FilterMatchMode.CONTAINS },
        cl1stat: { value: null, matchMode: FilterMatchMode.EQUALS },
      },
      form: this.$inertia.form({
        cl1comb: null,
        description: null,
        status: null,
        category: null,
      }),
    };
  },
  mounted() {
    this.storeSubCategoryInContainer();
  },
  methods: {
    storeSubCategoryInContainer() {
      this.csrSuppliesSubCategoryList = []; // reset

      this.csrSuppliesSubCategory.forEach((e) => {
        this.csrSuppliesSubCategoryList.push({
          cl1comb: e.cl1comb,
          catID: e.catID,
          categoryname: e.categoryname,
          cl1desc: e.cl1desc,
          cl1stat: e.cl1stat,
        });
      });
    },
    updateData() {
      this.$inertia.get('wardsinv', this.params, {
        preserveState: true,
        preserveScroll: true,
        onFinish: (visit) => {
          this.storeSubCategoryInContainer();
        },
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
