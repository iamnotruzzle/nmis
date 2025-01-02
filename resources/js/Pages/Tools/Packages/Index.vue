<template>
  <app-layout>
    <Head title="NMIS - Wards Inventory" />

    <Toast />

    <div style="width: 100%">
      <div
        class="card"
        style="width: 100%"
      >
        <DataTable
          class="p-datatable-sm"
          v-model:filters="filters"
          v-model:expandedRows="expandedRow"
          :value="packagesList"
          paginator
          :rows="20"
          dataKey="id"
          filterDisplay="row"
          sortField="description"
          :sortOrder="1"
          showGridlines
          removableSort
        >
          <template #header>
            <div class="flex flex-wrap align-items-center justify-content-between gap-2">
              <span class="text-xl text-900 font-bold text-primary">PACKAGES</span>
              <div class="flex">
                <div class="mr-2">
                  <Button
                    label="Create package"
                    icon="pi pi-plus"
                    iconPos="right"
                    @click="openCreatePackageDialog"
                  />
                </div>
              </div>
            </div>
          </template>
          <template #empty> No item found. </template>
          <template #loading> Loading item data. Please wait. </template>
          <Column expander />
          <Column
            field="description"
            header="PACKAGE"
            :showFilterMenu="false"
            sortable
          >
            <template #body="{ data }">
              {{ data.description }}
            </template>
          </Column>
          <Column
            field="status"
            header="STATUS"
            :showFilterMenu="false"
            sortable=""
          >
            <template #body="{ data }">
              <div class="text-center">
                <Tag
                  v-if="data.status == 'A'"
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
          </Column>

          <!-- <Column
            header="ACTION"
            style="width: 5%"
          >
            <template #body="slotProps">
              <div class="flex flex-row justify-content-between align-content-around">
                <Button
                  v-tooltip.top="'Update'"
                  icon="pi pi-pencil"
                  class="mr-2"
                  rounded
                  severity="warning"
                  @click="editItem(slotProps.data)"
                />
                <Button
                  v-if="slotProps.data.uomcode == 'box'"
                  v-tooltip.top="'Convert'"
                  rounded
                  severity="success"
                  @click="convertItem(slotProps.data)"
                >
                  <template #icon>
                    <v-icon name="bi-arrow-left-right"></v-icon>
                  </template>
                </Button>
                <Button
                  v-if="slotProps.data.uomcode != 'box'"
                  icon="pi pi-trash"
                  rounded
                  severity="danger"
                  @click="confirmDeleteItem(slotProps.data)"
                />
              </div>
            </template>
          </Column> -->
          <template #expansion="slotProps">
            <div class="max-w-full flex justify-content-center">
              <div class="w-11 flex flex-column align-items-center">
                <DataTable
                  paginator
                  :rows="5"
                  class="w-8 mt-2"
                  showGridlines
                  :value="slotProps.data.package_details"
                  size="small"
                  sortField="created_at"
                  :sortOrder="-1"
                  removableSort
                >
                  <template #header>
                    <div class="w-full flex flex-row justify-content-start">
                      <div class="text-lg font-bold my-3">
                        Package details for <span class="text-primary">[ {{ slotProps.data.description }} ]</span>
                      </div>
                    </div>
                  </template>
                  <Column
                    header="ITEM"
                    style="width: 20%"
                    sortable
                  >
                    <template #body="{ data }">
                      <span class="text-green-500"> {{ data.cl2desc }}</span>
                    </template>
                  </Column>
                  <Column
                    header="QUANTITY"
                    style="width: 20%"
                  >
                    <template #body="{ data }">
                      <span> {{ data.quantity }}</span>
                    </template>
                  </Column>
                </DataTable>
              </div>
            </div>
          </template>
        </DataTable>

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
import moment, { updateLocale } from 'moment';
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
    packages: Array,
  },
  data() {
    return {
      itemsList: [],
      packagesList: [],
      filters: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
        description: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
      expandedRow: [],
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

      form: this.$inertia.form({
        description: '',
        status: null,
        packageItems: null,
      }),
    };
  },
  mounted() {
    this.storeItemsInContainer();
    this.storePackagesInContainer();
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
    },
    storePackagesInContainer() {
      this.packagesList = []; // reset

      this.packagesList = this.packages.reduce((acc, curr) => {
        const existingPackage = acc.find((p) => p.id === curr.id);

        if (existingPackage) {
          existingPackage.package_details.push({
            cl2comb: curr.cl2comb,
            cl2desc: curr.cl2desc,
            quantity: curr.quantity,
          });
        } else {
          acc.push({
            id: curr.id,
            description: curr.description,
            status: curr.status,
            package_details: [
              {
                cl2comb: curr.cl2comb,
                cl2desc: curr.cl2desc,
                quantity: curr.quantity,
              },
            ],
          });
        }

        return acc;
      }, []);

      //   console.log(this.packagesList);
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
    submit() {
      // initialize value
      this.form.description = this.description;
      this.form.status = this.status;
      this.form.packageItems = this.packageItems;

      if (this.form.processing) {
        return false;
      }

      if (this.isUpdate) {
        this.form.put(route('packages.update', this.itemId), {
          preserveScroll: true,
          onSuccess: () => {
            this.createPackageDialog = false;
            this.cancel();
            this.updatedMsg();
          },
        });
      } else {
        this.form.post(route('packages.store'), {
          preserveScroll: true,
          onSuccess: () => {
            this.createPackageDialog = false;
            this.cancel();
            this.storeItemsInContainer();
            this.createdMsg();
          },
        });
      }
      //   console.log(this.$page.props.errors);
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
    createdMsg() {
      this.$toast.add({ severity: 'success', summary: 'Success', detail: 'Package created.', life: 3000 });
    },
    updatedMsg() {
      this.$toast.add({ severity: 'info', summary: 'Updated', detail: 'Package updated.', life: 3000 });
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
