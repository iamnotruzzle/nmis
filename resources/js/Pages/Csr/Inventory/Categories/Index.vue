<template>
  <app-layout>
    <Head title="InvenTrackr - Items" />

    <div class="card">
      <Toast />

      <DataTable
        class="p-datatable-sm"
        dataKey="cl2comb"
        v-model:filters="filters"
        v-model:expandedRows="expandedRows"
        :value="mainCategoriesList"
        selectionMode="single"
        lazy
        paginator
        :rows="rows"
        ref="dt"
        :totalRecords="totalRecords"
        @page="onPage($event)"
        filterDisplay="row"
        :loading="loading"
      >
        <template #header>
          <div class="flex flex-wrap align-items-center justify-content-between gap-2">
            <span class="text-xl text-900 font-bold text-cyan-500 hover:text-cyan-700">Categories</span>
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
              @click="confirmDeleteItem(slotProps.data)"
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
                label="Add price"
                icon="pi pi-plus"
                iconPos="right"
                size="small"
                class="ml-2 my-0"
                @click="openCreateItemPriceDialog(slotProps.data)"
              />
            </div>

            <DataTable
              :dataKey="slotProps.ptcode"
              :value="slotProps.data.subCategory"
              paginator
              :rows="5"
              class="w-full"
            >
              <Column header="NAME">
                <template #body="{ data }"> {{ data.cl1desc }} </template>
              </Column>
              <Column
                header="ACTION"
                style="min-width: 12rem"
              >
                <!-- <template #body="slotProps">
                  <Button
                    icon="pi pi-pencil"
                    class="mr-1"
                    rounded
                    text
                    severity="warning"
                    @click="editPrice(slotProps.data)"
                  />

                  <Button
                    icon="pi pi-trash"
                    rounded
                    text
                    severity="danger"
                    @click="confirmDeletePrice(slotProps.data)"
                  />
                </template> -->
              </Column>
            </DataTable>
          </div>
        </template>
      </DataTable>

      <!-- create & edit dialog -->
      <!-- <Dialog
        v-model:visible="createItemDialog"
        :style="{ width: '450px' }"
        header="Item Detail"
        :modal="true"
        class="p-fluid"
        @hide="clickOutsideDialog"
        dismissableMask
      >
        <div class="field">
          <label for="cl1comb">Category</label>
          <Dropdown
            v-model.trim="form.cl1comb"
            required="true"
            :options="cl1combsList"
            filter
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
      </Dialog> -->

      <!-- Delete confirmation dialog -->
      <!-- <Dialog
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
      </Dialog> -->

      <!-- create & edit price dialog -->
      <!-- <Dialog
        v-model:visible="createItemPriceDialog"
        :style="{ width: '450px' }"
        header="Price Detail"
        :modal="true"
        class="p-fluid"
        @hide="clickOutsidePriceDialog"
        dismissableMask
      >
        <div class="field">
          <label for="selling_price">Selling price</label>
          <InputText
            id="selling_price"
            v-model.trim="formPrice.selling_price"
            required="true"
            type="number"
            autofocus
            :class="{ 'p-invalid': formPrice.selling_price == '' }"
            @keyup.enter="submitPrice"
          />
          <small
            class="text-error"
            v-if="formPrice.errors.selling_price"
          >
            {{ formPrice.errors.selling_price }}
          </small>
        </div>
        <template #footer>
          <Button
            label="Cancel"
            icon="pi pi-times"
            severity="danger"
            text
            @click="cancelPrice"
          />
          <Button
            v-if="isPriceUpdate == true"
            label="Update"
            icon="pi pi-check"
            severity="warning"
            text
            type="submit"
            :disabled="formPrice.processing"
            @click="submitPrice"
          />
          <Button
            v-else
            label="Save"
            icon="pi pi-check"
            text
            type="submit"
            :disabled="formPrice.processing"
            @click="submitPrice"
          />
        </template>
      </Dialog> -->

      <!-- Delete item price confirmation dialog -->
      <!-- <Dialog
        v-model:visible="deleteItemPriceDialog"
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
            Are you sure you want to delete <b>₱ {{ formPrice.selling_price }}</b> price ?
          </span>
        </div>
        <template #footer>
          <Button
            label="No"
            icon="pi pi-times"
            class="p-button-text"
            @click="deleteItemPriceDialog = false"
          />
          <Button
            label="Yes"
            icon="pi pi-check"
            severity="danger"
            text
            @click="deletePrice"
          />
        </template>
      </Dialog> -->
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
  },
  props: {
    mainCategory: Array,
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
      createItemDialog: false,
      deleteItemDialog: false,
      // price
      priceId: null,
      isPriceUpdate: false,
      createItemPriceDialog: false,
      deleteItemPriceDialog: false,
      search: '',
      options: {},
      params: {},
      mainCategoriesList: [],
      filters: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
      form: this.$inertia.form({
        ptdesc: null,
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
    this.stormCategoriesInContainer();

    // console.log(this.items);

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

    stormCategoriesInContainer() {
      this.mainCategory.data.forEach((e) => {
        this.mainCategoriesList.push({
          ptcode: e.ptcode,
          ptdesc: e.ptdesc,
          ptstat: e.ptstat,
          ptlock: e.ptlock,
          subCategory: e.sub_category.length == 0 ? null : e.sub_category,
        });
      });
      console.log(this.mainCategoriesList);
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
          this.totalRecords = this.categories.total;
          this.mainCategoriesList = [];
          this.stormCategoriesInContainer();
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
        this.form.put(route('categories.update', this.itemId), {
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
        this.form.post(route('categories.store'), {
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
      this.form.delete(route('categories.destroy', this.itemId), {
        preserveScroll: true,
        onSuccess: () => {
          this.mainCategoriesList = [];
          this.deleteItemDialog = false;
          this.itemId = null;
          this.form.clearErrors();
          this.form.reset();
          this.updateData();
          this.deletedMsg();
          this.stormCategoriesInContainer();
        },
      });
    },
    cancel() {
      this.itemId = null;
      this.isUpdate = false;
      this.createItemDialog = false;
      this.form.reset();
      this.form.clearErrors();
      this.mainCategoriesList = [];
      this.stormCategoriesInContainer();
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
    // ********** prices
    openCreateItemPriceDialog(item) {
      this.formPrice.id = item.id;
      this.formPrice.cl2comb = item.cl2comb;
      this.isPriceUpdate = false;
      this.priceId = item.id;
      this.createItemPriceDialog = true;
    },
    // emit price close dialog
    clickOutsidePriceDialog() {
      this.$emit(
        'hide',
        (this.priceId = null),
        (this.isPriceUpdate = false),
        this.formPrice.clearErrors(),
        this.formPrice.reset()
      );
    },
    editPrice(item) {
      this.isPriceUpdate = true;
      this.createItemPriceDialog = true;
      this.priceId = item.id;
      this.formPrice.id = item.id;
      this.formPrice.cl2comb = item.cl2comb;
      this.formPrice.selling_price = item.selling_price;
    },
    submitPrice() {
      if (this.isPriceUpdate) {
        this.formPrice.put(route('itemprices.update', this.priceId), {
          preserveScroll: true,
          onSuccess: () => {
            this.priceId = null;
            this.createItemPriceDialog = false;
            this.cancelPrice();
            this.updateData();
            this.updatedPriceMsg();
          },
        });
      } else {
        this.formPrice.post(route('itemprices.store'), {
          preserveScroll: true,
          onSuccess: () => {
            this.priceId = null;
            this.createItemDialog = false;
            this.cancelPrice();
            this.updateData();
            this.createdPriceMsg();
          },
        });
      }
    },
    confirmDeletePrice(item) {
      this.priceId = item.id;
      this.formPrice.selling_price = item.selling_price;
      this.deleteItemPriceDialog = true;
    },
    deletePrice() {
      this.form.delete(route('itemprices.destroy', this.priceId), {
        preserveScroll: true,
        onSuccess: () => {
          this.deleteItemPriceDialog = false;
          this.priceId = null;
          this.formPrice.clearErrors();
          this.formPrice.reset();
          this.updateData();
          this.deletedPriceMsg();
          //   this.stormCategoriesInContainer();
        },
      });
    },
    cancelPrice() {
      this.priceId = null;
      this.isPriceUpdate = false;
      this.createItemPriceDialog = false;
      this.formPrice.reset();
      this.formPrice.clearErrors();
    },
    createdPriceMsg() {
      this.$toast.add({ severity: 'success', summary: 'Success', detail: 'Price created', life: 3000 });
    },
    updatedPriceMsg() {
      this.$toast.add({ severity: 'warn', summary: 'Success', detail: 'Price updated', life: 3000 });
    },
    deletedPriceMsg() {
      this.$toast.add({ severity: 'error', summary: 'Success', detail: 'Price deleted', life: 3000 });
    },
    // ********** end prices
  },
  watch: {
    search: function (val, oldVal) {
      this.params.search = val;
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
