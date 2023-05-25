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
          <Column
            header="Action"
            style="min-width: 12rem"
          >
            <!-- @click="editItem(slotProps.data)" -->
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
                @click=""
              />
            </template>
          </Column>
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
              filter
              placeholder="Select a Item"
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
              @keyup.enter="medicalSuppliesQtyValidation"
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
              :disabled="itemsToBillList.length == 0 || form.processing"
              label="Charge"
              icon="pi pi-check"
              text
              type="submit"
              @click="submit"
            />
          </template>
        </Dialog>

        <!-- update bill dialog -->
        <Dialog
          v-model:visible="updateBillDialog"
          :style="{ width: '450px' }"
          header="Charge Detail"
          :modal="true"
          class="p-fluid"
          @hide="clickOutsideDialog"
          dismissableMask
        >
          <div class="field">
            <label for="item">ITEM / SERVICE</label>
            <InputText
              id="item"
              v-model.trim="form.upd_item"
              disabled
            />
          </div>

          <div class="field">
            <label for="qtyToCharge">Quantity</label>
            <InputText
              id="qtyToCharge"
              v-model.trim="form.upd_qtyToCharge"
              required="true"
              type="number"
              autofocus
              :class="{ 'p-invalid': form.upd_qtyToCharge == '' }"
              @keyup.enter="submit"
            />
            <small
              class="text-error"
              v-if="itemNotSelected == true"
            >
              {{ itemNotSelectedMsg }}
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
              :disabled="updateQtyNotEnough == true || form.upd_qtyToCharge == 0 || form.upd_qtyToCharge == ''"
              label="Update"
              icon="pi pi-check"
              severity="warning"
              text
              type="submit"
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
  },
  props: {
    bills: Object,
    medicalSupplies: Object,
    misc: Object,
    tanks: Object,
  },
  data() {
    return {
      search: '',
      options: {},
      params: {},
      isUpdate: false,
      createBillDialog: false,
      updateBillDialog: false,
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
      updateQtyNotEnough: false,
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
        isUpdate: false,
        enccode: null,
        hospitalNumber: null,
        itemsToBillList: null,
        upd_charge_slip_no: null,
        upd_item: null,
        upd_itemcode: null,
        upd_type_of_charge_code: null,
        upd_qtyToCharge: null,
        upd_price: null,
        upd_total: null,
        upd_charge_date: null,
      }),
    };
  },
  mounted() {
    console.log(this.bills);
    this.storeBillsInContainer();
    this.getTotalAmount();
    this.storeMedicalSuppliesInContainer();
    this.storeMiscInContainer();
    this.storeItemsInContainer();
    // console.log('tanks', this.$page.props.tanks);

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
        // only push item when chargcode are drug and meds oxygen, compressed air and carbon dioxide
        if (e.chargcode == 'DRUMD') {
          this.tanks.forEach((t) => {
            if (e.itemcode == t.itemcode && e.uomcode == t.unitcode) {
              this.billList.push({
                charge_slip_no: e.pcchrgcod,
                type_of_charge_code: e.type_of_charge.chrgcode,
                type_of_charge_description: e.type_of_charge.chrgdesc,
                // item: e.type_of_charge.chrgdesc,
                item: t.itemDesc,
                itemcode: e.itemcode,
                quantity: Math.trunc(e.pchrgqty),
                price: e.pchrgup,
                amount: (Math.trunc(e.pchrgqty) * Math.round(e.pchrgup * 100)) / 100,
                charge_date: e.pcchrgdte,
              });
            }
          });
        }
        // only push item when chargcode are medical supplies or misc
        else if (e.chargcode == 'MISC' || e.chargcode == 'DRUMN') {
          this.billList.push({
            charge_slip_no: e.pcchrgcod,
            type_of_charge_code: e.type_of_charge.chrgcode,
            type_of_charge_description: e.type_of_charge.chrgdesc,
            item: e.misc != null ? e.misc.hmdesc : e.item.category.cl1desc + ' ' + e.item.cl2desc,
            itemcode: e.itemcode,
            quantity: Math.trunc(e.pchrgqty),
            price: e.pchrgup,
            amount: (Math.trunc(e.pchrgqty) * Math.round(e.pchrgup * 100)) / 100,
            charge_date: e.pcchrgdte,
          });
        } else {
          return null;
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
          quantity: 99999,
          price: misc.hmamt,
        });
      });

      // oxygen, compressed air, carbon dioxide
      this.$page.props.tanks.forEach((tank) => {
        this.itemList.push({
          typeOfCharge: 'DRUMD',
          itemCode: tank.itemcode,
          itemDesc: tank.itemDesc,
          unit: tank.unitcode,
          quantity: 99999,
          price: tank.price,
        });
      });
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
        (this.form.isUpdate = false),
        this.form.clearErrors(),
        this.form.reset()
      );
    },
    medicalSuppliesQtyValidation() {
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
                unit: this.item['unit'],
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
    editItem(item) {
      //   console.log(item);
      this.form.isUpdate = true;
      this.form.enccode = this.enccode;
      this.form.hospitalNumber = this.hospitalNumber;
      this.form.upd_item = item.item;
      this.form.upd_charge_slip_no = item.charge_slip_no;
      this.form.upd_itemcode = item.itemcode;
      this.form.upd_type_of_charge_code = item.type_of_charge_code;
      this.form.upd_qtyToCharge = item.quantity;
      this.form.upd_price = item.price;
      this.form.upd_total = item.amount;
      this.form.upd_charge_date = item.charge_date;
      this.updateBillDialog = true;
    },
    cancel() {
      this.itemsToBillList = [];
      this.item = null;
      this.itemDesc = null;
      this.qtyToCharge = null;
      this.itemNotSelected = null;
      this.itemNotSelectedMsg = null;
      this.createBillDialog = false;
      this.updateBillDialog = false;
      this.form.isUpdate = false;
      this.form.reset();
      this.form.clearErrors();
    },
    createdMsg() {
      this.$toast.add({ severity: 'success', summary: 'Success', detail: 'Successfully charge patient', life: 3000 });
    },
    updatedMsg() {
      this.$toast.add({ severity: 'warn', summary: 'Success', detail: 'Charge updated', life: 3000 });
    },
    // deletedMsg() {
    //   this.$toast.add({ severity: 'error', summary: 'Success', detail: 'Category deleted', life: 3000 });
    // },
  },
  watch: {
    'form.upd_qtyToCharge': function (val, oldVal) {
      let currentStockQty = undefined;

      if (this.form.upd_type_of_charge_code == 'DRUMN') {
        this.medicalSuppliesList.forEach((e) => {
          if (e.cl2comb == this.form.upd_itemcode) {
            currentStockQty = e.quantity;
          }
        });

        this.billList.forEach((e) => {
          if (e.itemcode == this.form.upd_itemcode && this.form.upd_charge_date == e.charge_date) {
            currentStockQty = Number(currentStockQty) + Number(e.quantity);
          }
        });

        if (Number(this.form.upd_qtyToCharge) > currentStockQty) {
          this.itemNotSelected = true;
          this.itemNotSelectedMsg = 'Current stock is not enough.';
          this.updateQtyNotEnough = true;
        } else {
          this.itemNotSelected = false;
          this.updateQtyNotEnough = false;
        }
      } else {
        this.itemNotSelected = false;
      }
    },
  },
};
</script>
