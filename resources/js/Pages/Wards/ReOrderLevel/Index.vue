<template>
  <app-layout>
    <Head title="NMIS - STOCK LEVEL" />

    <div>
      <div class="card">
        <Toast />
        <!-- current ward stocks -->
        <span class="text-xl text-900 font-bold text-primary">STOCK LEVEL</span>

        <DataTable
          class="p-datatable-sm"
          dataKey="id"
          v-model:filters="itemsWithReOrderLevelFilter"
          :value="itemsWithReOrderLevelList"
          paginator
          :rows="20"
          :rowsPerPageOptions="[20, 30, 50]"
          removableSort
          sortField="created_at"
          :sortOrder="1"
          showGridlines
          :loading="loading"
        >
          <template #header>
            <div class="flex flex-wrap align-items-center justify-content-end gap-2">
              <div class="flex">
                <div class="mr-2">
                  <div class="p-inputgroup">
                    <span class="p-inputgroup-addon">
                      <i class="pi pi-search"></i>
                    </span>
                    <InputText
                      id="searchInput"
                      v-model="itemsWithReOrderLevelFilter['global'].value"
                      placeholder="Search item"
                    />
                  </div>
                </div>
                <Button
                  label="STOCK LEVEL"
                  icon="pi pi-plus"
                  iconPos="right"
                  @click="openstockLevelDialog"
                />
              </div>
            </div>
          </template>
          <template #empty> No item found. </template>
          <template #loading> Loading item data. Please wait. </template>
          <Column
            field="cl2desc"
            header="ITEM"
            style="width: 30%"
            sortable
          >
            <template #body="{ data }">
              <span> {{ data.cl2desc }}</span>
            </template>
          </Column>
          <Column
            field="uomdesc"
            header="UNIT"
            style="width: 30%"
            sortable
          >
            <template #body="{ data }">
              <span> {{ data.uomdesc }}</span>
            </template>
          </Column>
          <Column
            field="reorder_level_qty"
            header="REORDER LEVEL"
            style="width: 30%"
            sortable
          >
            <template #body="{ data }">
              <span> {{ data.reorder_level_qty }}</span>
            </template>
          </Column>
          <Column
            field="status"
            header="STATUS"
            style="width: 30%"
            sortable
          >
            <template #body="{ data }">
              <span v-if="data.status == A"> ACTIVE </span>
              <span> INACTIVE</span>
            </template>
          </Column>
          <Column
            field="created_by_name"
            header="CREATED BY"
            style="width: 30%"
            sortable
          >
            <template #body="{ data }">
              <span> {{ data.created_by_name }}</span>
            </template>
          </Column>
          <Column
            field="updated_by_name"
            header="UPDATED BY"
            style="width: 30%"
            sortable
          >
            <template #body="{ data }">
              <span> {{ data.updated_by_name }}</span>
            </template>
          </Column>
          <Column
            field="created_at"
            header="CREATED AT"
            style="text-align: right; width: 15%"
            :pt="{ headerContent: 'justify-content-end' }"
            sortable
          >
            <template #body="{ data }">
              <div>
                {{ tzone(data.created_at) }}
              </div>
            </template>
          </Column>
          <Column
            header="ACTION"
            style="width: 10%"
            :pt="{ headerContent: 'justify-content-center' }"
          >
            <template #body="slotProps">
              <div class="flex justify-content-center">
                <Button
                  v-if="slotProps.data.from == 'EXISTING_STOCKS'"
                  label="UPDATE"
                  severity="info"
                  @click="openUpdateStocklevel(slotProps.data)"
                />
              </div>
            </template>
          </Column>
        </DataTable>
        <!-- test -->
      </div>

      <!-- Existing -->
      <Dialog
        v-model:visible="stockLevelDialog"
        :modal="true"
        class="p-fluid w-4"
        @hide="whenDialogIsHidden"
      >
        <template #header>
          <div class="text-blue-500 text-xl font-bold">EXISTING STOCK</div>
        </template>
        <div class="field">
          <label>Items</label>
          <Dropdown
            required="true"
            v-model="formStockLevel.cl2comb"
            :options="itemsList"
            :virtualScrollerOptions="{ itemSize: 38 }"
            filter
            optionValue="cl2comb"
            optionLabel="cl2desc"
            class="w-full mb-3"
            :disabled="isUpdateExisting == true"
          />
        </div>
        <div class="field">
          <label for="unit">Unit</label>
          <InputText
            id="unit"
            v-model.trim="selectedItemsUomDesc"
            readonly
            :disabled="isUpdateExisting == true"
          />
        </div>
        <div class="field">
          <label>Quantity</label>
          <InputText
            id="quantity"
            v-model.trim="formStockLevel.quantity"
            required="true"
            autofocus
            :class="{ 'p-invalid': formStockLevel.quantity == '' || formStockLevel.quantity == null }"
            onkeypress="return event.charCode >= 48 && event.charCode <= 57"
            inputId="integeronly"
          />
          <small
            class="text-error"
            v-if="formStockLevel.errors.quantity"
          >
            {{ formStockLevel.errors.quantity }}
          </small>
        </div>

        <template #footer>
          <Button
            :label="!formStockLevel.processing ? 'CANCEL' : 'CANCEL'"
            icon="pi pi-times"
            :disabled="formStockLevel.processing || formStockLevel.cl2comb == null || formStockLevel.quantity == null"
            severity="danger"
            @click="cancel"
          />

          <Button
            v-if="isUpdateExisting == false"
            :disabled="formStockLevel.processing || formStockLevel.cl2comb == null || formStockLevel.quantity == null"
            :label="!formStockLevel.processing ? 'SAVE' : 'SAVE'"
            :icon="formStockLevel.processing ? 'pi pi-spin pi-spinner' : 'pi pi-check'"
            severity="info"
            type="submit"
            @click="submitExisting"
          />

          <Button
            v-else
            :disabled="formStockLevel.processing || formStockLevel.cl2comb == null || formStockLevel.quantity == null"
            :label="!formStockLevel.processing ? 'UPDATE' : 'UPDATE'"
            :icon="formStockLevel.processing ? 'pi pi-spin pi-spinner' : 'pi pi-check'"
            severity="info"
            type="submit"
            @click="submitExisting"
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
import InputNumber from 'primevue/inputnumber';
import TextArea from 'primevue/textarea';
import Tag from 'primevue/tag';
import moment from 'moment';
import Echo from 'laravel-echo';
import { Link } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';

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
    TextArea,
    Link,
    InputNumber,
  },
  props: {
    items: Object,
    itemsWithReOrderLevel: Array,
  },
  data() {
    return {
      authWardcode: '',
      // paginator
      loading: false,
      totalRecords: null,
      rows: null,
      // end paginator,
      isUpdate: false,
      isUpdateExisting: false,
      stockLevelDialog: false,
      cancelItemDialog: false,
      search: '',
      selectedItemsUomDesc: null,
      oldQuantity: 0,
      options: {},
      params: {},
      from: null,
      to: null,
      stockBalanceDeclared: false,
      itemsList: [],
      itemsWithReOrderLevelList: [],
      itemsWithReOrderLevelFilter: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
      formStockLevel: this.$inertia.form({
        id: null,
        authLocation: null,
        fund_source: null,
        cl2comb: null,
        uomcode: null,
        quantity: null,
        prev_quantity: null,
        delivered_date: null,
      }),
    };
  },
  mounted() {
    this.authWardcode = this.$page.props.auth.user.location.location_name.wardcode;

    this.storeItemsInController();
    this.storeItemsWithReOrderLevelInContainer();

    this.loading = false;
  },
  computed: {
    user() {
      return this.$page.props.auth.user;
    },
  },
  methods: {
    openUpdateStocklevel(data) {
      this.formStockLevel.id = data.ward_stock_id;
      this.formStockLevel.cl2comb = data.cl2comb;
      this.formStockLevel.quantity = data.quantity;

      this.isUpdateExisting = true;
      this.stockLevelDialog = true;
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
    storeItemsInController() {
      this.itemsList = []; // reset
      this.items.forEach((e) => {
        this.itemsList.push({
          cl2comb: e.cl2comb,
          cl2desc: e.cl2desc,
          uomcode: e.uomcode,
          uomdesc: e.uomdesc,
        });
      });
    },
    storeItemsWithReOrderLevelInContainer() {
      this.itemsWithReOrderLevelList = []; // reset

      moment.suppressDeprecationWarnings = true;

      this.itemsWithReOrderLevel.forEach((e) => {
        let expiration_date = moment.tz(e.expiration_date, 'Asia/Manila').format('MM/DD/YYYY');

        this.itemsWithReOrderLevelList.push({
          id: e.id,
          cl2comb: e.cl2comb,
          cl2desc: e.cl2desc,
          uomcode: e.uomcode,
          uomdesc: e.uomdesc,
          reorder_level_qty: e.reorder_level_qty,
          status: e.status,
          wardcode: e.wardcode,
          wardname: e.wardname,
          created_by_name: e.created_by_name,
          updated_by_name: e.updated_by_name,
          created_at: e.created_at,
        });
      });
    },
    tzone(date) {
      if (date == null || date == '') {
        return null;
      } else {
        return moment.tz(date, 'Asia/Manila').format('L');
      }
    },
    checkIfAboutToExpire(date) {
      let current_date = moment.tz(moment(), 'Asia/Manila');
      let exp_date = moment.tz(date, 'Asia/Manila');

      // adding +1 to include the starting date
      let date_diff = exp_date.diff(current_date, 'days') + 1;

      if (
        current_date.format('MM-DD-YY') == exp_date.format('MM-DD-YY') ||
        Date.parse(exp_date) < Date.parse(current_date)
      ) {
        return 'Item has expired.';
      } else if (date_diff == 1) {
        return date_diff + ' day remaining.';
      } else {
        return date_diff + ' days remaining.';
      }
    }, // },
    onPage(event) {
      this.params.page = event.page + 1;
      this.updateData();
    },
    updateData() {
      this.$inertia.get('wardinv', this.params, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: (visit) => {
          this.cancel();
          this.storeItemsInController();
          this.storeItemsWithReOrderLevelInContainer();
        },
      });
    },
    openstockLevelDialog() {
      this.formStockLevel.clearErrors();
      this.formStockLevel.reset();
      this.stockLevelDialog = true;
    },
    // when dialog is hidden, do this function
    whenDialogIsHidden() {
      this.$emit(
        'hide',
        (this.isUpdate = false),
        (this.isUpdateExisting = false),
        (this.item = null),
        (this.cl2desc = null),
        (this.approved_qty = null),
        (this.itemNotSelected = null),
        (this.itemNotSelectedMsg = null),
        (this.selectedItemsUomDesc = ''),
        (this.oldQuantity = 0),
        this.formStockLevel.clearErrors(),
        this.formStockLevel.reset()
      );
    },
    submitExisting() {
      if (
        this.formStockLevel.processing ||
        this.formStockLevel.cl2comb == null ||
        this.formStockLevel.quantity == null
      ) {
        return false;
      }

      this.formStockLevel.authLocation = this.authWardcode;
      if (
        this.formStockLevel.cl2comb != null ||
        this.formStockLevel.cl2comb != '' ||
        this.formStockLevel.quantity != null ||
        this.formStockLevel.quantity != ''
      ) {
        // check if in update mode
        if (this.isUpdateExisting == false) {
          this.formStockLevel.post(route('existingstock.store'), {
            preserveScroll: true,
            onSuccess: (e) => {
              //   console.log(this.$page.props);
              if (e.props.flash.noItemPrice == 0) {
                // this.cancel();
                this.noItemPriceMsg();
              } else {
                this.formStockLevel.reset();
                this.cancel();
                this.updateData();
                this.createdMsg();
                this.loading = false;
              }
            },
          });
        } else {
          this.formStockLevel.put(route('existingstock.update', this.formStockLevel.id), {
            preserveScroll: true,
            onSuccess: () => {
              this.formStockLevel.reset();
              this.cancel();
              this.updateData();
              this.updateExistingMessage();
            },
          });
        }
      }
    },
    cancel() {
      this.isUpdate = false;
      this.isUpdateExisting = false;
      this.stockLevelDialog = false;
      this.oldQuantity = 0;
      this.selectedItemsUomDesc = '';
      this.formStockLevel.reset();
      this.formStockLevel.clearErrors();
    },
    createdMsg() {
      this.$toast.add({ severity: 'success', summary: 'Success', detail: 'Stock created', life: 5000 });
    },
    updateExistingMessage() {
      this.$toast.add({ severity: 'warn', summary: 'Success', detail: 'Stock updated', life: 5000 });
    },
    getLocalDateString(utcStr) {
      const date = new Date(utcStr);
      return (
        date.getFullYear() +
        '-' +
        String(date.getMonth() + 1).padStart(2, '0') +
        '-' +
        String(date.getDate()).padStart(2, '0') +
        ' ' +
        String(date.getHours()).padStart(2, '0') +
        ':' +
        String(date.getMinutes()).padStart(2, '0')
      );
    },
    updatedStockMsg() {
      this.$toast.add({ severity: 'warn', summary: 'Success', detail: 'Stock updated', life: 3000 });
    },
  },
  watch: {
    search: function (val, oldVal) {
      this.params.search = val;
      this.updateData();
    },
    from: function (val) {
      if (val != null) {
        let from = this.getLocalDateString(val);
        this.params.from = from;
      } else {
        this.params.from = null;
        this.from = null;
      }
      this.updateData();
    },
    to: function (val) {
      if (val != null) {
        let to = this.getLocalDateString(val);
        this.params.to = to;
      } else {
        this.params.to = null;
        this.to = null;
      }
      this.updateData();
    },
    'formStockLevel.cl2comb': function (val) {
      this.selectedItemsUomDesc = null;

      this.itemsList.forEach((e) => {
        if (e.cl2comb == val) {
          if (e.uomdesc != null || e.uomdesc == '') {
            this.selectedItemsUomDesc = e.uomdesc;
            this.formStockLevel.uomcode = e.uomcode;
          } else {
            this.selectedItemsUomDesc = null;
          }
        }
      });
    },
  },
};
</script>

<style scoped>
.rounded-card {
  border-radius: 50%;
  /* min-height: 100px;
  min-width: 100px; */
}

.form-container {
  display: flex;
  justify-content: space-around;
  align-items: center;
  /* padding: 20px; */
}

.form-side {
  flex: 1;
  /* margin-right: 20px; */
}

.p-field {
  margin-bottom: 20px;
}

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

#toItem {
  width: 250px;
  overflow: hidden;
  white-space: pre;
  text-overflow: ellipsis;
  -webkit-appearance: none;
}

.my-link {
  opacity: 0.7;
  transition: opacity 0.2s ease-in-out; /* Optional: Add a smooth transition effect */
}

/* Change the opacity on hover */
.my-link:hover {
  opacity: 1; /* Adjust the opacity value as needed */
  border-bottom-style: solid;
}

.button-link-current {
  display: inline-block;
  padding: 0.5rem 1rem;
  border: 1px solid #818cf8;
  background-color: #818cf8;
  color: #fff;
  text-decoration: none;
  text-align: center;
  /* border-radius: 4px; */
  transition: background-color 0.3s ease;
}
.button-link {
  display: inline-block;
  padding: 0.5rem 1rem;
  border: 1px solid #818cf8;
  /* background-color: #818cf8; */
  color: #fff;
  text-decoration: none;
  text-align: center;
  /* border-radius: 4px; */
  transition: background-color 0.3s ease;
}

.button-link-current:hover {
  background-color: #5561d7;
}
.button-link:hover {
  background-color: #5561d7;
  color: white !important;
}
</style>
