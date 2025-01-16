<template>
  <app-layout>
    <Head title="NMIS - Returned Items" />

    <div
      class="card"
      style="width: 100%"
    >
      <Toast />

      <DataTable
        class="p-datatable-sm"
        v-model:filters="filters"
        :value="returnedItemsList"
        paginator
        :rows="20"
        :rowsPerPageOptions="[20, 30, 40]"
        dataKey="id"
        filterDisplay="row"
        sortField="created_at"
        :sortOrder="-1"
        removableSort
        :globalFilterFields="['ward', 'ris_no', 'item']"
        showGridlines
      >
        <template #header>
          <div class="flex flex-wrap align-items-center justify-content-between gap-2">
            <span class="text-xl text-900 font-bold text-primary">RETURNED ITEMS</span>
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
        <!-- <Column
          field="cl1comb"
          header="ID"
          style="width: 5%"
        >
        </Column> -->
        <Column
          field="ward"
          header="FROM"
          sortable
          :showFilterMenu="false"
        >
          <template #body="{ data }">
            {{ data.ward }}
          </template>

          <template #filter="{ filterModel, filterCallback }">
            <Dropdown
              v-model="filterModel.value"
              :options="locationFilter"
              @change="filterCallback()"
              :virtualScrollerOptions="{ itemSize: 38 }"
              filter
              optionLabel="name"
              optionValue="name"
              placeholder="NO FILTER"
            />
          </template>
        </Column>
        <Column
          field="ris_no"
          header="RIS NO."
        >
        </Column>
        <Column
          field="item"
          header="ITEM"
          sortable
        >
        </Column>
        <Column
          field="quantity"
          header="RETURNED QTY"
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
        <Column
          field="restocked_quantity"
          header="RESTOCKED QTY"
          sortable
          style="width: 5%; text-align: right"
          :pt="{ headerContent: 'justify-content-end' }"
        >
          <template #body="{ data }">
            <p
              class="text-right"
              :class="{ 'text-green-500': data.quantity === data.restocked_quantity }"
            >
              {{ data.restocked_quantity }}
            </p>
          </template>
        </Column>
        <Column
          field="returned_by"
          header="RETURNED BY"
          sortable
          style="text-align: right"
          :pt="{ headerContent: 'justify-content-end' }"
        >
        </Column>
        <Column
          field="remarks"
          header="REMARKS"
          style="width: 10%; text-align: left"
          :pt="{ headerContent: 'justify-content-center' }"
        >
        </Column>
        <Column
          field="created_at"
          header="RETURN DATE"
          :showFilterMenu="false"
          sortable
          style="text-align: center"
          :pt="{ headerContent: 'justify-content-center' }"
        >
          <template #body="{ data }">
            {{ tzone(data.created_at) }}
          </template>
        </Column>
        <Column
          header="ACTION"
          style="text-align: center"
          :pt="{ headerContent: 'justify-content-center' }"
        >
          <template #body="slotProps">
            <Button
              v-if="slotProps.data.quantity > 0"
              label="Add to stock"
              class="mr-1"
              severity="warning"
              @click="addQtyToStock(slotProps.data)"
            />
          </template>
        </Column>
      </DataTable>

      <Dialog
        v-model:visible="addQtyToStockDialog"
        :modal="true"
        class="p-fluid w-3"
        @hide="whenDialogIsHidden"
      >
        <template #header>
          <div class="text-primary text-xl font-bold">RETURN ITEM IN THE CURRENT STOCK</div>
        </template>
        <div class="field flex flex-column">
          <label for="unit">Item</label>
          <TextArea
            v-model.trim="formAddQtyToStock.item"
            readonly
            rows="3"
          />
        </div>
        <div class="field flex flex-column">
          <label for="remarks">
            Remarks from <span class="text-yellow-500 font-semibold">{{ formAddQtyToStock.ward }}</span></label
          >
          <TextArea
            v-model.trim="formAddQtyToStock.remarks"
            rows="10"
            readonly
          />
        </div>
        <div class="field flex flex-column">
          <label>Qty to return</label>
          <InputText
            id="quantity"
            v-model.trim="formAddQtyToStock.quantity"
            required="true"
            autofocus
            class="my-0"
            :class="{
              'p-invalid':
                formAddQtyToStock.processing ||
                formAddQtyToStock.quantity == '' ||
                formAddQtyToStock.quantity == null ||
                Number(formAddQtyToStock.quantity) <= 0 ||
                Number(formAddQtyToStock.quantity) > Number(previousQty),
            }"
            @keydown="restrictNonNumericAndPeriod"
            @keyup.enter="submitReturnToCsr"
            inputId="integeronly"
          />
          <small
            class="text-error"
            v-if="formAddQtyToStock.errors.quantity"
          >
            {{ formAddQtyToStock.errors.quantity }}
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
            label="Save"
            icon="pi pi-check"
            text
            type="submit"
            :disabled="
              formAddQtyToStock.processing ||
              formAddQtyToStock.quantity == null ||
              formAddQtyToStock.quantity <= 0 ||
              Number(formAddQtyToStock.quantity) > Number(previousQty)
            "
            @click="submitReturnToCsr"
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
// import IconField from 'primevue/iconField';
import TextArea from 'primevue/textarea';
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
    TextArea,
  },
  props: {
    returnedItems: Object,
  },
  data() {
    return {
      //   categoryFilter: [
      //     { name: 'Accountable forms', catID: 22 },
      //   ],
      returnedItemsList: [],
      locationFilter: [],
      filters: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
        ward: { value: null, matchMode: FilterMatchMode.CONTAINS },
        ris_no: { value: null, matchMode: FilterMatchMode.CONTAINS },
        item: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
      previousQty: null,
      addQtyToStockDialog: false,
      formAddQtyToStock: this.$inertia.form({
        id: null,
        cl2comb: null,
        item: null,
        quantity: null,
        remarks: null,
        wardcode: null,
        ward: null,
      }),
    };
  },
  mounted() {
    this.storeReturnedItemsInContainer();
    this.storeLocationsInContainer();
  },
  methods: {
    updateData() {
      this.$inertia.get('returneditems', this.params, {
        preserveState: true,
        preserveScroll: true,
        onFinish: (visit) => {
          this.previousQty = null;
          this.addQtyToStockDialog = false;
          this.returnedItemsList = [];
          this.locationFilter = [];
          this.storeReturnedItemsInContainer();
          this.storeLocationsInContainer();
          this.formAddQtyToStock.reset();
        },
      });
    },
    storeReturnedItemsInContainer() {
      this.returnedItemsList = []; // reset

      this.returnedItems.forEach((e) => {
        this.returnedItemsList.push({
          id: e.id,
          ris_no: e.ris_no,
          wardcode: e.wardcode,
          ward: e.ward,
          cl2comb: e.cl2comb,
          item: e.item,
          quantity: e.quantity,
          restocked_quantity: Number(e.restocked_quantity),
          returned_by: e.returned_by,
          remarks: e.remarks,
          created_at: e.created_at,
        });
      });
    },
    storeLocationsInContainer() {
      this.$page.props.locations.forEach((e) => {
        if (e.wardcode != 'CSR' && e.wardcode != 'ADMIN') {
          this.locationFilter.push({
            code: e.wardcode,
            name: e.wardname,
          });
        }
      });

      //   console.log(this.locationsList);
    },
    // ward stocks logs
    addQtyToStock(data) {
      //   console.log(data);

      this.previousQty = data.quantity;

      this.formAddQtyToStock.id = data.id;
      this.formAddQtyToStock.cl2comb = data.cl2comb;
      this.formAddQtyToStock.item = data.item;
      this.formAddQtyToStock.quantity = Number(data.quantity);
      this.formAddQtyToStock.remarks = data.remarks;
      this.formAddQtyToStock.wardcode = data.wardcode;
      this.formAddQtyToStock.ward = data.ward;

      this.addQtyToStockDialog = true;
    },
    submitReturnToCsr() {
      //   console.log(this.previousQty);
      if (
        this.formAddQtyToStock.processing ||
        this.formAddQtyToStock.quantity == null ||
        Number(this.formAddQtyToStock.quantity) <= 0 ||
        Number(this.formAddQtyToStock.quantity) > Number(this.previousQty)
      ) {
        return false;
      }

      this.formAddQtyToStock.post(route('returneditems.store'), {
        preserveScroll: true,
        onSuccess: () => {
          this.addQtyToStockDialog = false;
          this.cancel();
          this.updateData();
          this.qtyAddedMsg();
        },
      });
    },
    qtyAddedMsg() {
      this.$toast.add({ severity: 'success', summary: 'Success', detail: 'Item added to stock', life: 3000 });
    },
    cancel() {
      this.addQtyToStockDialog = false;
      this.formAddQtyToStock.reset();
      this.formAddQtyToStock.clearErrors();
    },
    whenDialogIsHidden() {
      this.$emit(
        'hide',
        (this.addQtyToStockDialog = false),
        this.formAddQtyToStock.reset(),
        this.formAddQtyToStock.clearErrors()
      );
    },
    restrictNonNumericAndPeriod(event) {
      if (
        [46, 8, 9, 27, 13].includes(event.keyCode) ||
        // Allow: Ctrl+A, Command+A
        (event.keyCode === 65 && (event.ctrlKey === true || event.metaKey === true)) ||
        // Allow: Ctrl+C, Command+C
        (event.keyCode === 67 && (event.ctrlKey === true || event.metaKey === true)) ||
        // Allow: Ctrl+V, Command+V
        (event.keyCode === 86 && (event.ctrlKey === true || event.metaKey === true)) ||
        // Allow: Ctrl+X, Command+X
        (event.keyCode === 88 && (event.ctrlKey === true || event.metaKey === true))
      ) {
        // Let it happen, don't do anything
        return;
      }
      // Ensure that it is a number and stop the keypress
      if ((event.shiftKey || event.keyCode < 48 || event.keyCode > 57) && (event.keyCode < 96 || event.keyCode > 105)) {
        event.preventDefault();
      }
    },
    tzone(date) {
      return moment.tz(date, 'Asia/Manila').format('L');
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
