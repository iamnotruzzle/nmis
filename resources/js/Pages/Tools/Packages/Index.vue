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
          class="p-fluid"
          :style="{ width: '1000px' }"
          @hide="whenDialogIsHidden"
        >
          <template #header>
            <div class="text-primary text-xl font-bold">PACKAGE DETAILS</div>
          </template>

          <div class="grid">
            <!-- Packages -->
            <div class="col-4">
              <div class="text-primary text-xl font-bold mb-2">PACKAGES</div>
              <div class="field">
                <label for="description">Package Description</label>
                <InputText
                  id="description"
                  v-model="description"
                  required
                />
              </div>

              <!-- Status -->
              <div class="field">
                <label>Status</label>
                <Dropdown
                  required="true"
                  v-model="status"
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
            </div>

            <!-- Package item -->
            <div class="col-8">
              <div class="text-primary text-xl font-bold mb-2">PACKAGE ITEMS</div>

              <div class="field w-full">
                <label for="item">Select Item</label>
                <Dropdown
                  v-model="selectedItem"
                  required="true"
                  :options="itemsList"
                  :virtualScrollerOptions="{ itemSize: 38 }"
                  filter
                  optionLabel="cl2desc"
                  class="w-full mb-3"
                  :class="{ 'p-invalid': form.cl2comb == '' }"
                  style="width: 100%"
                />
              </div>

              <div class="field">
                <div>
                  <label for="quantity">Quantity</label>
                  <InputNumber
                    class="mt-2"
                    id="quantity"
                    v-model="quantity"
                    inputId="minmax"
                    :min="0"
                    required
                  />
                </div>

                <Button
                  class="mt-2 p-3"
                  label="ADD ITEM"
                  icon="pi pi-plus"
                  @click="addItem"
                />
              </div>

              <div>
                <h5 class="mb-2">Items in Package</h5>
                <DataTable
                  :value="packageItems"
                  showGridlines
                >
                  <Column
                    field="cl2desc"
                    header="Item Description"
                  />
                  <Column
                    field="quantity"
                    header="Quantity"
                  />
                  <Column header="Actions">
                    <template #body="slotProps">
                      <div class="flex justify-content-center align-center">
                        <Button
                          icon="pi pi-times"
                          text
                          rounded
                          severity="danger"
                          @click="removeItem(slotProps.data)"
                        />
                      </div>
                    </template>
                  </Column>
                </DataTable>
              </div>
            </div>
          </div>

          <template #footer>
            <Button
              label="Cancel"
              icon="pi pi-times"
              severity="danger"
              text
              @click="cancel"
            />
            <!-- <Button
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
            /> -->
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
      description: '',
      status: null,
      selectedItem: null,
      quantity: 0,
      packageItems: [],
      statusList: [
        {
          name: 'Active',
          code: 'A',
        },
        {
          name: 'Inactive',
          code: 'I',
        },
      ],

      form: this.$inertia.form({}),
    };
  },
  mounted() {
    this.storeItemsInContainer();
  },
  methods: {
    statusSeverity(status) {
      //   console.log(status);
      switch (status.code) {
        case 'I':
          return 'danger';

        case 'A':
          return 'success';
      }
    },
    storeItemsInContainer() {
      this.itemsList = []; // reset

      this.itemsList = this.items.map((e) => ({
        cl2comb: e.cl2comb,
        cl2desc: e.cl2desc,
      }));

      //   console.log('item list', this.itemsList);
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
      this.description = '';
      this.status = null;
      this.selectedItem = null;
      this.quantity = 0;
      this.packageItems = [];
      this.form.reset();
      this.form.clearErrors();
    },
    whenDialogIsHidden() {
      this.$emit(
        'hide',
        (this.isUpdate = false),
        (this.createPackageDialog = false),
        (this.description = ''),
        (this.status = null),
        (this.selectedItem = null),
        (this.quantity = 0),
        (this.packageItems = []),
        this.form.clearErrors(),
        this.form.reset()
      );
    },
    addItem() {
      if (this.selectedItem && this.quantity > 0) {
        // Check for duplicates
        const isDuplicate = this.packageItems.some((item) => item.cl2comb === this.selectedItem.cl2comb);

        if (isDuplicate) {
          alert('This item is already in the package, remove it first to update the quantity.');
          return;
        }

        // Add item if no duplicates
        this.packageItems.push({
          cl2comb: this.selectedItem.cl2comb,
          cl2desc: this.selectedItem.cl2desc,
          quantity: this.quantity,
        });

        // Reset fields
        this.selectedItem = null;
        this.quantity = 0;
      } else {
        alert('Please select an item and enter a valid quantity.');
      }
    },
    removeItem(item) {
      this.packageItems = this.packageItems.filter((i) => i.cl2comb !== item.cl2comb);
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
