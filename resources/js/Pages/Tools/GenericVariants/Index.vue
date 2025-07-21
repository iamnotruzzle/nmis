<template>
  <AppLayout>
    <Head title="Generic Variants" />

    <Toast />

    <div class="card w-full">
      <DataTable
        class="p-datatable-sm"
        v-model:filters="itemsWithGenericDescFilter"
        :value="variants"
        paginator
        :rows="20"
        dataKey="id"
        sortField="generic_desc"
        :sortOrder="1"
        showGridlines
        removableSort
        rowGroupMode="rowspan"
        groupRowsBy="generic_desc"
      >
        <template #header>
          <div class="flex flex-wrap align-items-center justify-content-between gap-2">
            <span class="text-xl text-900 font-bold text-primary">GENERIC VARIANT</span>
            <div class="flex">
              <div class="mr-2">
                <div class="p-inputgroup">
                  <span class="p-inputgroup-addon">
                    <i class="pi pi-search"></i>
                  </span>
                  <InputText
                    id="searchInput"
                    v-model="itemsWithGenericDescFilter['global'].value"
                    placeholder="Search item"
                  />
                </div>
              </div>
              <Button
                label="ADD STOCK LEVEL"
                icon="pi pi-plus"
                iconPos="right"
                @click="openCreateDialog"
              />
            </div>
          </div>
        </template>

        <Column
          header="#"
          headerStyle="width:3rem"
        >
          <template #body="slotProps">
            {{ slotProps.index + 1 }}
          </template>
        </Column>

        <Column
          field="generic_desc"
          header="GENERIC"
          sortable
        />
        <Column
          field="variant_desc"
          header="VARIANT"
          sortable
        />

        <Column
          header="ACTION"
          style="width: 5%"
        >
          <template #body="slotProps">
            <div class="flex justify-content-center">
              <Button
                v-tooltip.top="'Update'"
                icon="pi pi-pencil"
                class="mr-2"
                rounded
                severity="warning"
                @click="editItem(slotProps.data)"
              />
              <Button
                v-tooltip.top="'Delete'"
                icon="pi pi-trash"
                rounded
                severity="danger"
                @click="confirmDeleteVariant(slotProps.data)"
              />
            </div>
          </template>
        </Column>

        <template #empty> No variants found. </template>
        <template #loading> Loading variants... </template>
      </DataTable>

      <Dialog
        v-model:visible="variantDialog"
        :modal="true"
        class="p-fluid"
        :style="{ width: '800px' }"
        @hide="whenDialogIsHidden"
      >
        <template #header>
          <div class="text-primary text-xl font-bold">
            {{ isUpdate ? 'UPDATE VARIANT' : 'CREATE VARIANT' }}
          </div>
        </template>

        <div class="grid">
          <div class="col-12">
            <div class="field w-full">
              <label for="generic_cl2comb">Generic</label>
              <Dropdown
                v-model="form.generic_cl2comb"
                :options="items"
                optionLabel="cl2desc"
                optionValue="cl2comb"
                placeholder="Select Generic"
                :virtualScrollerOptions="{ itemSize: 38 }"
                class="w-full"
                :filter="true"
                filterPlaceholder="Search generic"
                style="width: 100%"
              />
              <small
                class="text-error"
                v-if="form.errors.generic_cl2comb"
              >
                {{ form.errors.generic_cl2comb }}
              </small>
            </div>
          </div>

          <div class="col-12">
            <div class="field w-full">
              <label for="variant_cl2comb">Variant</label>
              <Dropdown
                v-model="form.variant_cl2comb"
                :options="items"
                optionLabel="cl2desc"
                optionValue="cl2comb"
                placeholder="Select Variant"
                :virtualScrollerOptions="{ itemSize: 38 }"
                class="w-full"
                :filter="true"
                filterPlaceholder="Search variant"
                style="width: 100%"
              />
              <small
                class="text-error"
                v-if="form.errors.variant_cl2comb"
              >
                {{ form.errors.variant_cl2comb }}
              </small>
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
            :label="isUpdate ? 'Update' : 'Save'"
            icon="pi pi-check"
            text
            type="submit"
            :disabled="form.processing"
            :severity="isUpdate ? 'warning' : 'success'"
            @click="submit"
          />
        </template>
      </Dialog>

      <Dialog
        v-model:visible="deleteDialog"
        :style="{ width: '450px' }"
        header="Confirm"
        :modal="true"
      >
        <div class="flex align-items-center justify-content-center">
          <i
            class="pi pi-exclamation-triangle mr-3"
            style="font-size: 2rem"
          />
          <span v-if="variantToDelete"
            >Are you sure you want to delete the variant <b>{{ variantToDelete.variant_desc }}</b
            >?</span
          >
        </div>
        <template #footer>
          <Button
            label="No"
            icon="pi pi-times"
            text
            @click="deleteDialog = false"
          />
          <Button
            label="Yes"
            icon="pi pi-check"
            text
            severity="danger"
            @click="deleteVariant"
          />
        </template>
      </Dialog>
    </div>
  </AppLayout>
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
import Toast from 'primevue/toast';
import Dropdown from 'primevue/dropdown';
import Tag from 'primevue/tag';

export default {
  components: {
    AppLayout,
    Head,
    InputText,
    Column,
    DataTable,
    Button,
    Dialog,
    Toast,
    Dropdown,
    Tag,
  },
  props: {
    variants: Array,
    items: Array,
  },
  data() {
    return {
      filters: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
        generic_desc: { value: null, matchMode: FilterMatchMode.CONTAINS },
        variant_desc: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
      itemsWithGenericDescFilter: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
      variantDialog: false,
      deleteDialog: false,
      isUpdate: false,
      variantToDelete: null,
      form: this.$inertia.form({
        id: null,
        generic_cl2comb: '',
        variant_cl2comb: '',
      }),
    };
  },
  methods: {
    openCreateDialog() {
      this.resetForm();
      this.variantDialog = true;
    },
    cancel() {
      this.resetForm();
      this.variantDialog = false;
    },
    whenDialogIsHidden() {
      this.resetForm();
      this.variantDialog = false;
    },
    editItem(item) {
      this.isUpdate = true;
      this.variantDialog = true;
      this.form.id = item.id;
      this.form.generic_cl2comb = item.generic_cl2comb;
      this.form.variant_cl2comb = item.variant_cl2comb;
    },
    resetForm() {
      this.form.reset();
      this.form.clearErrors();
      this.isUpdate = false;
    },
    submit() {
      if (this.isUpdate) {
        this.form.put(route('generic-variants.update', this.form.id), {
          preserveScroll: true,
          onSuccess: () => {
            this.variantDialog = false;
            this.resetForm();
            this.successMsg('Variant updated successfully.');
          },
        });
      } else {
        this.form.post(route('generic-variants.store'), {
          preserveScroll: true,
          onSuccess: () => {
            this.variantDialog = false;
            this.resetForm();
            this.successMsg('Variant created successfully.');
          },
        });
      }
    },
    confirmDeleteVariant(variant) {
      this.variantToDelete = variant;
      this.deleteDialog = true;
    },
    deleteVariant() {
      router.delete(route('generic-variants.destroy', this.variantToDelete.id), {
        preserveScroll: true,
        onSuccess: () => {
          this.deleteDialog = false;
          this.successMsg('Variant deleted successfully.');
        },
      });
    },
    successMsg(message) {
      this.$toast.add({ severity: 'success', summary: 'Success', detail: message, life: 3000 });
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
