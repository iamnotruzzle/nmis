<template>
  <app-layout>
    <Head title="Template - Bill Patient" />

    <div class="card">
      <Toast />
      <div class="lg:flex">
        <DataTable
          class="p-datatable-sm"
          dataKey="id"
          v-model:filters="filters"
          :value="billList"
          removableSort
          sortField="charge_date"
          :sortOrder="-1"
          paginator
          :rows="15"
          filterDisplay="row"
          showGridlines
        >
          <template #header>
            <span class="text-2xl text-cyan-500 font-bold">{{ patientName }} ( {{ hospitalNumber }} )</span>
            <div class="flex flex-wrap align-items-center justify-content-between gap-2">
              <span class="text-xl text-900 font-bold">Bills</span>
              <div>
                <span class="p-input-icon-left mr-2">
                  <i class="pi pi-search" />
                  <span class="p-input-icon-left">
                    <i class="pi pi-search" />
                    <InputText
                      v-model="filters['global'].value"
                      placeholder="Keyword Search"
                    />
                  </span>
                </span>
                <Button
                  label="Bill patient"
                  icon="pi pi-money-bill"
                  iconPos="right"
                  @click="openCreateBillDialog"
                />
              </div>
            </div>
          </template>
          <Column
            field="charge_slip_no"
            header="Charge slip #"
            sortable
          >
            <template #body="{ data }">
              {{ data.charge_slip_no }}
            </template>
            <template #filter="{ filterModel, filterCallback }">
              <InputText
                v-model="filterModel.value"
                type="text"
                @input="filterCallback()"
                class="p-column-filter"
                placeholder="Search by #"
              />
            </template>
          </Column>
          <Column
            field="type_of_charge_description"
            header="Type of charge"
            sortable
          >
            <template #body="{ data }">
              {{ data.type_of_charge_description }}
            </template>
            <template #filter="{ filterModel, filterCallback }">
              <InputText
                v-model="filterModel.value"
                type="text"
                @input="filterCallback()"
                class="p-column-filter"
                placeholder="Search by type"
              />
            </template>
          </Column>
          <Column
            field="item"
            header="Item"
            sortable
          >
            <template #body="{ data }">
              {{ data.item }}
            </template>
            <template #filter="{ filterModel, filterCallback }">
              <InputText
                v-model="filterModel.value"
                type="text"
                @input="filterCallback()"
                class="p-column-filter"
                placeholder="Search by item"
              />
            </template>
          </Column>
          <Column
            field="charge_date"
            header="Charge date"
            sortable
          >
            <template #body="{ data }">
              {{ tzone(data.charge_date) }}
            </template>
          </Column>
          <Column
            field="quantity"
            header="Quantity"
            sortable
          >
            <template #body="{ data }">
              {{ data.quantity }}
            </template>
          </Column>
          <Column
            field="price"
            header="Price"
            sortable
          >
            <template #body="{ data }">
              {{ data.price }}
            </template>
          </Column>
          <Column
            field="amount"
            header="Amount"
            sortable
          >
            <template #body="{ data }">
              {{ data.amount }}
            </template>
          </Column>
          <template #footer>
            <div class="text-right text-lg text-green-600">Total: ₱ {{ totalAmount.toFixed(2) }}</div>
          </template>
        </DataTable>

        <DataTable
          v-model:filters="medicalSuppliesListFilter"
          :value="medicalSuppliesList"
          scrollable
          scrollHeight="h-full"
          showGridlines
          class="mdmt-4"
        >
          <template #header>
            <div class="text-2xl text-cyan-500 font-bold">INSTOCK</div>
            <div class="p-input-icon-left flex justify-content-end w-full">
              <i class="pi pi-search" />
              <InputText
                v-model="medicalSuppliesListFilter['global'].value"
                placeholder="Item"
                class="w-full"
              />
            </div>
          </template>
          <Column
            field="cl2desc"
            header="Item"
          ></Column>
          <Column
            field="quantity"
            header="Qty"
          >
            <template #body="{ data }">
              <span
                v-if="data.quantity <= 10"
                class="text-yellow-500 text-bold"
                >{{ data.quantity }}
              </span>
              <span
                v-else
                class="text-green-500 text-bold"
                >{{ data.quantity }}
              </span>
            </template>
          </Column>
          <Column
            field="price"
            header="Price"
          >
            <template #body="{ data }"> ₱ {{ data.price }} </template>
          </Column>
        </DataTable>

        <!-- create bill dialog -->
        <Dialog
          v-model:visible="createBillDialog"
          header="CHARGE PATIENT"
          :modal="true"
          class="p-fluid"
          @hide="whenDialogIsHidden"
        >
          <div class="field">
            <label>Item</label>
            <Dropdown
              required="true"
              v-model="item"
              :options="itemList"
              optionLabel="itemDesc"
              class="w-full mb-3"
            />
          </div>
          <div class="field">
            <label for="Item">Quantity</label>
            <InputText
              id="quantity"
              v-model.trim="qtyToCharge"
              required="true"
              autofocus
              type="number"
              :class="{ 'p-invalid': qtyToCharge == '' || item == null }"
              @keyup.enter="fillRequestContainer"
            />
            <small
              class="text-error"
              v-if="itemNotSelected == true"
            >
              {{ itemNotSelectedMsg }}
            </small>
          </div>
          <div class="field mt-8">
            <label class="mr-2 font-bold">ITEMS / SERVICES TO CHARGE</label>

            <DataTable
              v-model:filters="itemsToBillFilter"
              :globalFilterFields="['itemDesc']"
              :value="itemsToBillList"
              tableStyle="min-width: 50rem"
              class="p-datatable-sm"
              paginator
              :rows="7"
            >
              <template #header>
                <div class="flex justify-content-end">
                  <span class="p-input-icon-left">
                    <i class="pi pi-search" />
                    <InputText
                      v-model="itemsToBillFilter['global'].value"
                      placeholder="Search Item"
                    />
                  </span>
                </div>
              </template>
              <Column
                field="itemDesc"
                header="ITEM/SERVICE"
                sortable
              ></Column>
              <Column
                field="currentStock"
                header="Current Stock"
                sortable
              ></Column>
              <Column
                field="qtyToCharge"
                header="Qty to charge"
                sortable
              ></Column>
              <Column
                field="price"
                header="Price per piece"
                sortable
              >
                <template #body="{ data }"> ₱ {{ data.price }} </template>
              </Column>
              <Column
                filed="total"
                header="TOTAL"
                sortable
              >
                <template #body="{ data }"> ₱ {{ data.total }} </template>
              </Column>
              <Column header="">
                <template #body="slotProps">
                  <Button
                    icon="pi pi-times"
                    rounded
                    text
                    severity="danger"
                    @click="removeFromToBillContainer(slotProps.data)"
                  />
                </template>
              </Column>
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
      </div>
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
    Tag,
  },
  props: {
    bills: Object,
    medicalSupplies: Object,
    misc: Object,
  },
  data() {
    return {
      search: '',
      options: {},
      params: {},
      isUpdate: false,
      createBillDialog: false,
      enccode: '',
      patientName: '',
      hospitalNumber: '',
      billList: [],
      medicalSuppliesList: [],
      miscList: [],
      itemList: [],
      itemsToBillList: [],
      item: null, // selected item
      itemDesc: null,
      qtyToCharge: null,
      itemNotSelected: false,
      itemNotSelectedMsg: null,
      totalAmount: 0,
      medicalSuppliesListFilter: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
      filters: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
        charge_slip_no: { value: null, matchMode: FilterMatchMode.CONTAINS },
        // type_of_charge_code: { value: null, matchMode: FilterMatchMode.CONTAINS },
        type_of_charge_description: { value: null, matchMode: FilterMatchMode.CONTAINS },
        item: { value: null, matchMode: FilterMatchMode.CONTAINS },
        quantity: { value: null, matchMode: FilterMatchMode.CONTAINS },
        price: { value: null, matchMode: FilterMatchMode.CONTAINS },
        amount: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
      itemsToBillFilter: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
      form: this.$inertia.form({
        enccode: null,
        hospitalNumber: null,
        itemsToBillList: null,
      }),
    };
  },
  mounted() {
    this.storeBillsInContainer();
    this.getTotalAmount();
    this.storeMedicalSuppliesInContainer();
    this.storeMiscInContainer();
    this.storeItemsInContainer();

    // set patient enccode
    this.enccode = this.bills.admission_date_bill.enccode;
    // set patient name
    this.patientName = this.bills.patlast + ', ' + this.bills.patfirst + ' ' + this.bills.patmiddle;
    // set hospital number
    this.hospitalNumber = this.bills.hpercode;
  },
  methods: {
    tzone(date) {
      return moment.tz(date, 'Asia/Manila').format('LLL');
    },
    getTotalAmount() {
      this.billList.forEach((item) => {
        this.totalAmount += Number(item.amount);
      });
    },
    storeBillsInContainer() {
      this.bills.admission_date_bill.patient_charge.forEach((e) => {
        // only push item when chargcode are drug and meds oxygen or compressed air
        if (e.chargcode == 'DRUMD' || e.chargcode == 'DRUMF') {
          this.billList.push({
            charge_slip_no: e.pcchrgcod,
            type_of_charge_code: e.type_of_charge.chrgcode,
            type_of_charge_description: e.type_of_charge.chrgdesc,
            item: e.type_of_charge.chrgdesc,
            quantity: Math.trunc(e.pchrgqty),
            price: e.pchrgup,
            amount: (Math.trunc(e.pchrgqty) * Math.round(e.pchrgup * 100)) / 100,
            charge_date: e.pcchrgdte,
          });
        }

        // only push item when chargcode are medical supplies or misc
        if (e.chargcode == 'MISC' || e.chargcode == 'DRUMN') {
          this.billList.push({
            charge_slip_no: e.pcchrgcod,
            type_of_charge_code: e.type_of_charge.chrgcode,
            type_of_charge_description: e.type_of_charge.chrgdesc,
            item: e.misc != null ? e.misc.hmdesc : e.item.category.cl1desc + ' ' + e.item.cl2desc,
            quantity: Math.trunc(e.pchrgqty),
            price: e.pchrgup,
            amount: (Math.trunc(e.pchrgqty) * Math.round(e.pchrgup * 100)) / 100,
            charge_date: e.pcchrgdte,
          });
        }
      });
      //   console.log(this.billList);
    },
    storeMedicalSuppliesInContainer() {
      this.medicalSupplies.forEach((med) => {
        this.medicalSuppliesList.push({
          cl2comb: med.cl2comb,
          cl2desc: med.cl2desc,
          uomcode: med.uomcode == null ? null : med.uomcode,
          quantity: med.quantity,
          price: med.price,
        });
      });
      //   console.log('medicalSupplies list container', this.medicalSuppliesList);
    },
    storeMiscInContainer() {
      this.misc.forEach((misc) => {
        this.miscList.push({
          hmcode: misc.hmcode,
          hmdesc: misc.hmdesc,
          hmamt: misc.hmamt,
          uomcode: misc.uomcode == null ? null : misc.uomcode,
        });
      });
      //   console.log('misc list container', this.miscList);
    },
    storeItemsInContainer() {
      // medical supplies
      this.medicalSupplies.forEach((med) => {
        this.itemList.push({
          typeOfCharge: 'DRUMN',
          itemCode: med.cl2comb,
          itemDesc: med.cl2desc,
          unit: med.uomcode == null ? null : med.uomcode,
          quantity: med.quantity,
          price: med.price,
        });
      });

      // misc
      this.misc.forEach((misc) => {
        this.itemList.push({
          typeOfCharge: 'MISC',
          itemCode: misc.hmcode,
          itemDesc: misc.hmdesc,
          unit: misc.uomcode == null ? null : misc.uomcode,
          quantity: 9999,
          price: misc.hmamt,
        });
      });
      //   console.log('item list container', this.itemList);
    },
    // when dialog is hidden, do this function
    whenDialogIsHidden() {
      this.$emit(
        'hide',
        (this.itemsToBillList = []),
        (this.item = null),
        (this.itemDesc = null),
        (this.qtyToCharge = null),
        (this.itemNotSelected = null),
        (this.itemNotSelectedMsg = null),
        this.form.clearErrors(),
        this.form.reset()
      );
    },
    fillRequestContainer() {
      // console.log(this.item);
      // check if no selected item
      if (this.item.typeOfCharge == 'DRUMN' && Number(this.item.quantity) < Number(this.qtyToCharge)) {
        // check if item selected is already on the list
        if (this.itemsToBillList.some((e) => e.itemCode === this.item['itemCode'])) {
          this.itemNotSelected = true;
          this.itemNotSelectedMsg = 'Item is already on the list.';
        } else {
          //   this.stockQtyNotEnough();
          this.itemNotSelected = true;
          this.itemNotSelectedMsg = 'Current stock is not enough.';
        }
      } else {
        if (this.item == null || this.item == '') {
          this.itemNotSelected = true;
          this.itemNotSelectedMsg = 'Item not selected.';
        } else {
          // check if request qty is not provided
          if (this.qtyToCharge == 0 || this.qtyToCharge == null || this.qtyToCharge == '') {
            this.itemNotSelected = true;
            this.itemNotSelectedMsg = 'Please provide quantity.';
          } else {
            // check if item selected is already on the list
            if (this.itemsToBillList.some((e) => e.itemCode === this.item['itemCode'])) {
              this.itemNotSelected = true;
              this.itemNotSelectedMsg = 'Item is already on the list.';
            } else {
              this.itemNotSelected = false;
              this.itemNotSelectedMsg = null;
              this.itemsToBillList.push({
                typeOfCharge: this.item['typeOfCharge'],
                itemCode: this.item['itemCode'],
                itemDesc: this.item['itemDesc'],
                currentStock: this.item['typeOfCharge'] == 'DRUMN' ? this.item['quantity'] : 'Infinite',
                qtyToCharge: this.qtyToCharge,
                price: this.item['price'],
                total: (this.item['price'] * this.qtyToCharge).toFixed(2),
              });
            }
          }
        }
      }
    },
    removeFromToBillContainer(item) {
      this.itemsToBillList.splice(
        this.itemsToBillList.findIndex((e) => e.itemCode === item.itemCode),
        1
      );
    },
    updateData() {
      this.params.enccode = this.enccode;
      this.$inertia.get('patientcharge', this.params, {
        preserveState: true,
        preserveScroll: true,
      });
    },
    // emit close dialog
    openCreateBillDialog() {
      this.form.clearErrors();
      this.form.reset();
      this.createBillDialog = true;
    },
    clickOutsideDialog() {
      this.$emit('hide', this.form.clearErrors(), this.form.reset());
    },
    submit() {
      // set form data
      this.form.enccode = this.enccode;
      this.form.hospitalNumber = this.hospitalNumber;
      this.form.itemsToBillList = this.itemsToBillList;

      this.form.post(route('patientcharge.store'), {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
          this.createBillDialog = false;
          this.cancel();
          this.createdMsg();
        },
        onFinish: (visit) => {
          this.billList = [];
          this.medicalSuppliesList = [];
          this.miscList = [];
          this.itemList = [];
          this.itemsToBillList = [];
          this.storeBillsInContainer();
          this.getTotalAmount();
          this.storeMedicalSuppliesInContainer();
          this.storeMiscInContainer();
          this.storeItemsInContainer();
        },
      });
    },
    cancel() {
      this.itemsToBillList = [];
      this.item = null;
      this.itemDesc = null;
      this.qtyToCharge = null;
      this.itemNotSelected = null;
      this.itemNotSelectedMsg = null;
      this.createBillDialog = false;
      this.form.reset();
      this.form.clearErrors();
    },
    // stockQtyNotEnough() {
    //   this.$toast.add({ severity: 'error', summary: 'Success', detail: 'Current stock is not enough', life: 3000 });
    // },
    createdMsg() {
      this.$toast.add({ severity: 'success', summary: 'Success', detail: 'Successfully charge patient', life: 3000 });
    },
    // updatedMsg() {
    //   this.$toast.add({ severity: 'warn', summary: 'Success', detail: 'Category updated', life: 3000 });
    // },
    // deletedMsg() {
    //   this.$toast.add({ severity: 'error', summary: 'Success', detail: 'Category deleted', life: 3000 });
    // },
  },
  watch: {
    // search: function (val, oldVal) {
    //   this.params.search = val;
    //   //   this.updateData();
    // },
  },
};
</script>
