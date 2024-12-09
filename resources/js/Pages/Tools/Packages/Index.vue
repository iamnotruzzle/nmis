<template>
  <app-layout>
    <Head title="NMIS - Wards Inventory" />

    <Toast />

    <div style="width: 100%">
      <div
        class="card"
        style="width: 100%"
      >
        <Button
          label="Create package"
          icon="pi pi-plus"
          iconPos="right"
          @click="openCreatePackageDialog"
        />

        <!-- <DataTable
          class="p-datatable-sm w-full"
          v-model:filters="filters"
          :value="itemsList"
          paginator
          :rows="20"
          :rowsPerPageOptions="[20, 30, 40]"
          dataKey="id"
          sortField="item_desc"
          :sortOrder="1"
          removableSort
          :globalFilterFields="['item_desc']"
          showGridlines
        >
          <template #header>
            <div class="flex flex-wrap align-items-center justify-content-between gap-2">
              <span class="text-xl text-900 font-bold text-primary">CSR INVENTORY</span>
              <div class="flex">
                <div class="mr-2">
                  <div class="p-inputgroup">
                    <span class="p-inputgroup-addon">
                      <i class="pi pi-search"></i>
                    </span>
                    <InputText
                      id="searchInput"
                      v-model="filters['global'].value"
                      placeholder="Search"
                    />
                  </div>
                </div>
              </div>
            </div>
          </template>
          <template #empty> No data found. </template>
          <template #loading> Loading data. Please wait. </template>
          <Column
            field="item_desc"
            header="ITEM"
            sortable
          >
          </Column>
          <Column
            field="quantity"
            header="QUANTITY"
            sortable
            style="width: 5%; text-align: right"
            :pt="{ headerContent: 'justify-content-end' }"
          >
            <template #body="{ data }">
              <p class="text-right">
                {{ data.quantity }}
              </p>
            </template>
          </Column>
        </DataTable> -->

        <Dialog
          v-model:visible="createPackageDialog"
          :modal="true"
          :style="{ width: '400px' }"
          class="p-fluid"
          @hide="whenDialogIsHidden"
        >
          <template #header>
            <div class="text-primary text-xl font-bold">Packages</div>
          </template>

          <div class="field">
            <label for="description">Package Description</label>
            <InputText
              id="description"
              v-model="packageDescription"
              required
            />
          </div>

          <div class="field">
            <label for="status">Status</label>
            <Dropdown
              id="status"
              v-model="packageStatus"
              :options="statusOptions"
              optionLabel="label"
              required
            />
          </div>

          <div class="field">
            <h4>Package Items</h4>
            <div>
              <label for="item">Select Item</label>
              <Dropdown
                id="item"
                v-model="selectedItem"
                :options="itemsList"
                optionLabel="cl2desc"
                required
              />
            </div>
          </div>

          <div class="field">
            <label for="quantity">Quantity</label>
            <InputNumber
              id="quantity"
              v-model="itemQuantity"
              required
            />
          </div>

          <Button
            label="Add Item"
            @click="addItem"
          />

          <div>
            <h5>Items in Package</h5>
            <DataTable :value="packageItems">
              <Column
                field="cl2desc"
                header="Item Description"
              />
              <Column
                field="quantity"
                header="Quantity"
              />
              <Column
                header="Actions"
                :body="actionTemplate"
              />
            </DataTable>
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
      </div>
    </div>
  </app-layout>
</template>

<script>
import { FilterMatchMode } from 'primevue/api';
import { router } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputText from 'primevue/inputtext';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import FileUpload from 'primevue/fileupload';
import Toast from 'primevue/toast';
import Dropdown from 'primevue/dropdown';
import InputNumber from 'primevue/inputnumber';
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
    DataTable,
    Button,
    Dialog,
    FileUpload,
    Toast,
    Dropdown,
    Tag,
    Link,
    IconField,
    InputNumber,
  },
  props: {
    items: Object,
  },
  data() {
    return {
      itemsList: [],
      isUpdate: false,
      createPackageDialog: false,

      form: this.$inertia.form({
        description: null,
        cl2comb: null,
        quantity: null,
      }),
    };
  },
  mounted() {
    this.storeItemsInContainer();
  },
  methods: {
    storeItemsInContainer() {
      this.itemsList = []; // reset

      this.items.forEach((e) => {
        this.itemsList.push({
          cl2comb: e.cl2comb,
          cl2desc: e.cl2desc,
        });
      });

      console.log('item list', this.itemsList);
    },
    openCreatePackageDialog() {
      this.isUpdate = false;
      this.form.clearErrors();
      this.form.reset();
      this.createPackageDialog = true;
    },
    cancel() {
      this.isUpdate = false;
      this.createPackageDialog = false;
      this.form.reset();
      this.form.clearErrors();
    },
    whenDialogIsHidden() {
      this.$emit('hide', (this.isUpdate = false), this.form.clearErrors(), this.form.reset());
    },
    updateData() {
      this.$inertia.get('package', this.params, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: (visit) => {
          this.itemsList = [];
          this.storeItemsInContainer();
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
