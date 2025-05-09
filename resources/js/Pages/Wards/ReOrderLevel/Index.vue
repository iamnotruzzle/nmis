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
                  label="ADD STOCK LEVEL"
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
            field="reorder_point"
            header="REORDER POINT"
            style="width: 30%"
            sortable
          >
            <template #body="{ data }">
              <span class="text-error font-bold"> {{ data.reorder_point }}</span>
            </template>
          </Column>
          <Column
            field="reorder_quantity"
            header="REORDER QTY"
            style="width: 30%"
            sortable
          >
            <template #body="{ data }">
              <span class="text-green-500 font-bold"> {{ data.reorder_quantity }}</span>
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
                  icon="pi pi-pencil"
                  class="mr-1"
                  rounded
                  text
                  severity="warning"
                  @click="openUpdateStocklevel(slotProps.data)"
                />
              </div>
            </template>
          </Column>
        </DataTable>
        <!-- test -->
      </div>

      <!-- REORDER -->
      <Dialog
        v-model:visible="stockLevelDialog"
        :modal="true"
        class="p-fluid w-4"
        @hide="whenDialogIsHidden"
      >
        <template #header>
          <div class="text-blue-500 text-xl font-bold">STOCK LEVEL</div>
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
            :disabled="isUpdatingStockLevel == true"
          />
        </div>
        <div class="field">
          <label for="unit">Unit</label>
          <InputText
            id="unit"
            v-model.trim="selectedItemsUomDesc"
            readonly
            :disabled="isUpdatingStockLevel == true"
          />
        </div>
        <div class="field">
          <label>Reorder point</label>
          <InputText
            id="reorder_point"
            v-model.trim="formStockLevel.reorder_point"
            required="true"
            autofocus
            :class="{ 'p-invalid': formStockLevel.reorder_point == '' || formStockLevel.reorder_point == null }"
            onkeypress="return event.charCode >= 48 && event.charCode <= 57"
            inputId="integeronly"
          />
          <small
            class="text-error"
            v-if="formStockLevel.errors.reorder_point"
          >
            {{ formStockLevel.errors.reorder_point }}
          </small>
        </div>
        <div class="field">
          <label>Reorder quantity</label>
          <InputText
            id="reorder_quantity"
            v-model.trim="formStockLevel.reorder_quantity"
            required="true"
            autofocus
            :class="{ 'p-invalid': formStockLevel.reorder_quantity == '' || formStockLevel.reorder_quantity == null }"
            onkeypress="return event.charCode >= 48 && event.charCode <= 57"
            inputId="integeronly"
          />
          <small
            class="text-error"
            v-if="formStockLevel.errors.reorder_quantity"
          >
            {{ formStockLevel.errors.reorder_quantity }}
          </small>
        </div>

        <!-- Status -->
        <div class="field">
          <label>Status</label>
          <Dropdown
            required="true"
            v-model="formStockLevel.status"
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
            v-if="formStockLevel.errors.status"
          >
            {{ formStockLevel.errors.status }}
          </small>
        </div>

        <template #footer>
          <Button
            :label="!formStockLevel.processing ? 'CANCEL' : 'CANCEL'"
            icon="pi pi-times"
            :disabled="formStockLevel.processing"
            severity="danger"
            @click="cancel"
          />

          <Button
            v-if="isUpdatingStockLevel == false"
            :disabled="
              formStockLevel.processing ||
              formStockLevel.cl2comb == null ||
              formStockLevel.reorder_point == null ||
              formStockLevel.reorder_quantity == null
            "
            :label="!formStockLevel.processing ? 'SAVE' : 'SAVE'"
            :icon="formStockLevel.processing ? 'pi pi-spin pi-spinner' : 'pi pi-check'"
            severity="info"
            type="submit"
            @click="submitAddReOrderLvl"
          />

          <Button
            v-else
            :disabled="
              formStockLevel.processing ||
              formStockLevel.cl2comb == null ||
              formStockLevel.reorder_point == null ||
              formStockLevel.reorder_quantity == null
            "
            :label="!formStockLevel.processing ? 'UPDATE' : 'UPDATE'"
            :icon="formStockLevel.processing ? 'pi pi-spin pi-spinner' : 'pi pi-check'"
            severity="info"
            type="submit"
            @click="submitAddReOrderLvl"
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
      isUpdatingStockLevel: false,
      stockLevelDialog: false,
      cancelItemDialog: false,
      search: '',
      selectedItemsUomDesc: null,
      options: {},
      params: {},
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
        cl2comb: null,
        reorder_point: null,
        reorder_quantity: null,
        status: null,
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
    statusSeverity(status) {
      //   console.log(status);
      switch (status.code) {
        case 'I':
          return 'danger';

        case 'A':
          return 'success';
      }
    },
    openUpdateStocklevel(data) {
      this.formStockLevel.id = data.id;
      this.formStockLevel.cl2comb = data.cl2comb;
      this.formStockLevel.reorder_point = data.reorder_point;
      this.formStockLevel.reorder_quantity = data.reorder_quantity;
      this.formStockLevel.status = data.status;

      this.isUpdatingStockLevel = true;
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
          reorder_point: e.reorder_point,
          reorder_quantity: e.reorder_quantity,
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
      this.$inertia.get('reorder', this.params, {
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
        (this.isUpdatingStockLevel = false),
        (this.item = null),
        (this.cl2desc = null),
        (this.itemNotSelected = null),
        (this.itemNotSelectedMsg = null),
        (this.selectedItemsUomDesc = ''),
        this.formStockLevel.clearErrors(),
        this.formStockLevel.reset()
      );
    },
    submitAddReOrderLvl() {
      if (
        this.formStockLevel.processing ||
        this.formStockLevel.cl2comb == null ||
        this.formStockLevel.reorder_point == null ||
        this.formStockLevel.reorder_quantity == null
      ) {
        return false;
      }

      if (
        this.formStockLevel.cl2comb != null ||
        this.formStockLevel.cl2comb != '' ||
        this.formStockLevel.reorder_point != null ||
        this.formStockLevel.reorder_point != '' ||
        this.formStockLevel.reorder_quantity != null ||
        this.formStockLevel.reorder_quantity != ''
      ) {
        // check if in update mode
        if (this.isUpdatingStockLevel == false) {
          this.formStockLevel.post(route('reorder.store'), {
            preserveScroll: true,
            onSuccess: (e) => {
              this.formStockLevel.reset();
              this.cancel();
              this.updateData();
              this.createdMsg();
              this.loading = false;
            },
          });
        } else {
          this.formStockLevel.put(route('reorder.update', this.formStockLevel.id), {
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
      this.isUpdatingStockLevel = false;
      this.stockLevelDialog = false;
      this.selectedItemsUomDesc = '';
      this.formStockLevel.reset();
      this.formStockLevel.clearErrors();
    },
    createdMsg() {
      this.$toast.add({ severity: 'success', summary: 'Success', detail: 'STOCK LEVEL created', life: 5000 });
    },
    updateExistingMessage() {
      this.$toast.add({ severity: 'warn', summary: 'Success', detail: 'STOCK LEVEL updated', life: 5000 });
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
      this.$toast.add({ severity: 'warn', summary: 'Success', detail: 'STOCK LEVEL updated', life: 3000 });
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
