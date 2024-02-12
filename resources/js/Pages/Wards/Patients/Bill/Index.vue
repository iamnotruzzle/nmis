<template>
  <app-layout>
    <Head title="NMIS - Bill Patient" />

    <div class="card">
      <Toast />

      <div>
        <DataTable
          class="p-datatable-sm"
          dataKey="charge_slip_no"
          v-model:filters="filters"
          v-model:expandedRows="expandedRow"
          @row-click="setExpandedRow"
          :value="billList"
          selectionMode="single"
          removableSort
          filterDisplay="row"
          showGridlines
          ref="dt"
          lazy
          paginator
          :rows="rows"
          :totalRecords="totalRecords"
          @page="onPage($event)"
        >
          <template #header>
            <span class="text-2xl text-cyan-500 font-bold">{{ patientName }} ( {{ hospitalNumber }} )</span>
            <div class="flex flex-wrap align-items-center justify-content-between gap-2">
              <span class="text-xl text-900 font-bold text-cyan-500 hover:text-cyan-700">BILLS</span>
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
          <Column expander />
          <Column
            field="charge_slip_no"
            header="CHARGE SLIP #"
            sortable
          >
            <template #body="{ data }">
              {{ data.charge_slip_no }}
            </template>
          </Column>
          <Column
            field="type_of_charge_description"
            header="TYPE OF CHARGE"
            sortable
          >
            <template #body="{ data }">
              {{ data.type_of_charge_description }}
            </template>
          </Column>
          <Column
            field="item"
            header="ITEM"
            sortable
          >
            <template #body="{ data }">
              {{ data.item }}
            </template>
          </Column>
          <Column
            field="charge_date"
            header="CHARGE DATE"
            sortable
          >
            <template #body="{ data }">
              {{ tzone(data.charge_date) }}
            </template>
          </Column>
          <Column
            field="quantity"
            header="QUANTITY"
            sortable
          >
            <template #body="{ data }">
              {{ data.quantity }}
            </template>
          </Column>
          <Column
            field="price"
            header="PRICE"
            sortable
            style="text-align: right"
          >
            <template #body="{ data }">
              {{ convertToPHCurrency(data.price) }}
            </template>
          </Column>
          <Column
            field="amount"
            header="AMOUNT"
            sortable
            style="text-align: right"
          >
            <template #body="{ data }">
              {{ convertToPHCurrency(data.amount) }}
            </template>
          </Column>
          <template #footer>
            <!-- <div class="text-right text-lg text-green-600">Total: ₱ {{ totalAmount.toFixed(2) }}</div> -->
            <div class="text-right text-lg text-green-600">Total: {{ convertToPHCurrency(totalAmount) }}</div>
          </template>
          <template #expansion="slotProps">
            <!-- Charge for medical supplies -->
            <!-- v-if="slotProps.data.patient_charge_logs != null" -->
            <div
              class="p-3"
              v-if="slotProps.data.patient_charge_logs != null"
            >
              <div class="flex flex-wrap align-items-center justify-content-between gap-2">
                <h5>
                  <span class="text-cyan-500 hover:text-cyan-700">ITEMS DISPENSED</span>
                </h5>
              </div>
              <!-- {{ slotProps.data }} -->
              <DataTable
                :value="slotProps.data.patient_charge_logs"
                paginator
                :rows="5"
              >
                <Column header="BRAND">
                  <template #body="{ data }">
                    <!-- {{ data }} -->
                    <!-- {{ data.brand_details.name }} -->
                    <span v-if="data.brand_details != null">{{ data.brand_details.name }}</span>
                    <span v-else>NA</span>
                  </template>
                </Column>
                <Column header="EXP. DATE">
                  <template #body="{ data }">
                    {{ tzone(data.expiration_date) }}
                  </template>
                </Column>
                <Column header="QUANTITY">
                  <template #body="{ data }">
                    {{ data.quantity }}
                  </template>
                </Column>
                <Column
                  header="ACTION"
                  style="min-width: 12rem"
                >
                  <template #body="chargeLogs">
                    <Button
                      icon="pi pi-pencil"
                      class="mr-1"
                      rounded
                      text
                      severity="warning"
                      @click="editItem(slotProps, chargeLogs)"
                    />
                  </template>
                </Column>
              </DataTable>
            </div>
          </template>
        </DataTable>

        <!-- current stocks -->
        <DataTable
          v-model:filters="medicalSuppliesListFilter"
          :value="medicalSuppliesList"
          scrollable
          scrollHeight="h-full"
          showGridlines
          class="p-datatable-sm mt-4"
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
            header="ITEM"
            style="width: 60%"
          ></Column>
          <Column
            field="quantity"
            header="QUANTITY"
            style="width: 20%"
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
            header="PRICE"
            style="text-align: right; width: 20%"
          >
            <template #body="{ data }"> ₱ {{ data.price }} </template>
          </Column>
        </DataTable>

        <!-- create bill dialog -->
        <Dialog
          v-model:visible="createBillDialog"
          header="CHARGE PATIENT"
          :modal="true"
          class="p-fluid w-5"
          @hide="whenDialogIsHidden"
        >
          <div class="field mb-3">
            <label>Item</label>
            <Dropdown
              required="true"
              v-model="item"
              :options="itemList"
              :virtualScrollerOptions="{ itemSize: 38 }"
              filter
              placeholder="Select a Item"
              optionLabel="itemDesc"
              class="w-full"
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
              onkeypress="return event.charCode >= 48 && event.charCode <= 57"
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
          <div class="field mt-4">
            <label class="mr-2 font-bold">ITEMS / SERVICES TO CHARGE</label>

            <p
              class="text-error text-xl font-semibold"
              v-if="stockBalanceDeclared != false"
            >
              {{ getTankDesc($page.props.errors['itemsToBillList.0.itemCode']) }} stock balance has not yet been
              declared.
            </p>
            <!-- <p
              class="text-error text-xl font-semibold"
              v-if="stockBalanceDeclared != false"
            >
              {{ $page.props.errors['itemsToBillList.0.itemCode'].toUpperCase() }}
            </p> -->

            <DataTable
              v-model:filters="itemsToBillFilter"
              :globalFilterFields="['itemDesc']"
              :value="itemsToBillList"
              tableStyle="min-width: 50rem"
              class="p-datatable-sm w-full"
              paginator
              showGridlines
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
                header="CURRENT STOCK"
                sortable
              ></Column>
              <Column
                field="qtyToCharge"
                header="QTY TO CHARGE"
                sortable
              ></Column>
              <Column
                field="price"
                header="PRICE PER PC."
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
            <label
              for="item"
              class="font-bold text-cyan-500"
            >
              ITEM / SERVICE
            </label>
            <div>
              <span
                class="text-xl text-900"
                v-text="form.upd_item_desc"
              ></span>
            </div>
          </div>

          <div class="field mt-4">
            <label
              for="quantity"
              class="font-bold text-cyan-500"
            >
              Qty to return
            </label>
            <InputText
              id="quantity"
              v-model.trim="form.upd_QtyToReturn"
              required="true"
              autofocus
              type="number"
              onkeypress="return event.charCode >= 48 && event.charCode <= 57"
              @keyup.enter="
                Number(form.upd_QtyToReturn) > Number(form.upd_currentChargeQty) ||
                form.upd_QtyToReturn == null ||
                form.upd_QtyToReturn == 0 ||
                form.upd_QtyToReturn == ''
                  ? ''
                  : submit()
              "
            />
          </div>

          <template #footer>
            <Button
              label="Cancel"
              icon="pi pi-times"
              severity="danger"
              text
              @click="cancel"
            />
            <!-- form.upd_QtyToReturn == null ||
                form.upd_QtyToReturn == 0 ||
                form.upd_QtyToReturn == '' -->
            <Button
              :disabled="
                Number(form.upd_QtyToReturn) > Number(form.upd_currentChargeQty) ||
                form.upd_QtyToReturn == null ||
                form.upd_QtyToReturn == 0 ||
                form.upd_QtyToReturn == '' ||
                form.processing
              "
              label="Return"
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
    pat_enccode: String,
    bills: Object,
    medicalSupplies: Object,
    misc: Object,
    // tanks: Object,
  },
  data() {
    return {
      // paginator
      loading: false,
      totalRecords: null,
      rows: null,
      // end paginator
      stockBalanceDeclared: false,
      expandedRow: [],
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
        // for storing
        isUpdate: false,
        enccode: null,
        hospitalNumber: null,
        itemsToBillList: null,
        // for updating
        upd_id: null,
        upd_ward_stocks_id: null,
        upd_enccode: null,
        upd_hospitalNumber: null,
        upd_charge_slip_no: null,
        upd_item_desc: null,
        upd_itemcode: null,
        upd_type_of_charge_code: null,
        upd_currentChargeQty: null,
        upd_QtyToReturn: null,
        upd_price: null,
        upd_pcchrgdte: null,
        tscode: null,
      }),
    };
  },
  // created will be initialize before mounted
  created() {
    this.totalRecords = this.bills.total;
    this.params.page = this.bills.current_page;
    this.rows = this.bills.per_page;
  },
  mounted() {
    // console.log('bills', this.bills);

    this.storeBillsInContainer();
    this.getTotalAmount();
    this.storeMedicalSuppliesInContainer();
    this.storeMiscInContainer();
    this.storeItemsInContainer();

    // set patient enccode
    this.enccode = this.pat_enccode;
    // set patient name
    this.patientName = this.bills.patlast + ', ' + this.bills.patfirst + ' ' + this.bills.patmiddle;
    // set hospital number
    this.hospitalNumber = this.bills.hpercode;
  },
  methods: {
    onPage(event) {
      this.params.page = event.page + 1;
      this.updateData();
    },
    convertToPHCurrency(e) {
      const formatted = e.toLocaleString('en-PH', {
        style: 'currency',
        currency: 'PHP',
      });

      return formatted;
    },
    tzone(date) {
      if (date == null) {
        return 'NA';
      } else {
        return moment.tz(date, 'Asia/Manila').format('L');
      }
    },
    setExpandedRow($event) {
      // Check if row expanded before click or not
      const isExpanded = this.expandedRow.find((p) => p.charge_slip_no === $event.data.charge_slip_no);
      if (isExpanded?.charge_slip_no) this.expandedRow = [];
      else this.expandedRow = [$event.data];
      //   console.log(this.expandedRow);
    },
    getTotalAmount() {
      this.totalAmount = 0;
      this.billList.forEach((item) => {
        this.totalAmount += Number(item.amount);
      });
    },
    storeBillsInContainer() {
      let uid = 0;
      this.bills.data.forEach((e) => {
        // console.log(e);
        // only push item when chargcode are drug and meds oxygen, compressed air and carbon dioxide
        if (e.chargcode == 'DRUMD') {
          const matchingTank = this.$page.props.tanksList.find((x) => e.itemcode === x.itemcode);

          if (e.itemcode == matchingTank.itemcode && e.uomcode == matchingTank.unitcode) {
            this.billList.push({
              // uid: Number(uid) + 1,
              charge_slip_no: e.pcchrgcod,
              type_of_charge_code: e.type_of_charge.chrgcode,
              type_of_charge_description: e.type_of_charge.chrgdesc,
              // item: e.type_of_charge.chrgdesc,
              item:
                matchingTank.gendesc +
                ' ' +
                matchingTank.dmdnost +
                ' ' +
                matchingTank.stredesc +
                ' ' +
                matchingTank.formdesc +
                ' ' +
                matchingTank.rtedesc,
              itemcode: e.itemcode,
              quantity: Math.trunc(e.pchrgqty),
              // price: e.pchrgup,
              price: Math.round(e.pchrgup * 100) / 100,
              amount: (Math.trunc(e.pchrgqty) * Math.round(e.pchrgup * 100)) / 100,
              charge_date: e.pcchrgdte,
              patient_charge_logs: e.patient_charge_logs.length == 0 ? null : e.patient_charge_logs,
            });
          }
        }
        // only push item when chargcode are medical supplies or misc
        else if (e.chargcode == 'MISC' || e.chargcode == 'DRUMN') {
          this.billList.push({
            // uid: Number(uid) + 1,
            charge_slip_no: e.pcchrgcod,
            type_of_charge_code: e.type_of_charge.chrgcode,
            type_of_charge_description: e.type_of_charge.chrgdesc,
            item: e.misc != null ? e.misc.hmdesc : e.item.category.cl1desc + ' ' + e.item.cl2desc,
            itemcode: e.itemcode,
            quantity: Math.trunc(e.pchrgqty),
            // price: e.pchrgup,
            price: Math.round(e.pchrgup * 100) / 100,
            amount: (Math.trunc(e.pchrgqty) * Math.round(e.pchrgup * 100)) / 100,
            charge_date: e.pcchrgdte,
            patient_charge_logs: e.patient_charge_logs.length == 0 ? null : e.patient_charge_logs,
          });
        } else {
          return null;
        }
      });
      //   console.log('bill list', this.billList);
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
        if (med.price != null) {
          this.itemList.push({
            typeOfCharge: 'DRUMN',
            itemCode: med.cl2comb,
            itemDesc: med.cl2desc,
            unit: med.uomcode == null ? null : med.uomcode,
            quantity: med.quantity,
            price: med.price,
          });
        }
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
      this.$page.props.tanksList.forEach((tank) => {
        this.itemList.push({
          typeOfCharge: 'DRUMD',
          itemCode: tank.itemcode,
          itemDesc:
            tank.gendesc +
            ' ' +
            tank.dmdnost +
            // ' ' +
            // tank.dmdnnostp +
            ' ' +
            tank.stredesc +
            ' ' +
            tank.formdesc +
            ' ' +
            tank.rtedesc,
          unit: tank.unitcode,
          quantity: 99999,
          price: tank.price,
        });
      });

      this.itemList.sort((a, b) => {
        let cl2descA = a.itemDesc;
        let cl2descB = b.itemDesc;

        if (cl2descA < cl2descB) {
          return -1;
        }
        if (cl2descA > cl2descB) {
          return 1;
        }
        return 0;
      });
    },
    // when dialog is hidden, do this function
    whenDialogIsHidden() {
      this.$emit(
        'hide',
        (this.stockBalanceDeclared = false),
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
      this.billList = [];
      this.$inertia.get('patientcharge', this.params, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: (visit) => {
          this.totalRecords = this.bills.total;
          this.billList = [];
          //   this.expandedRow = [];
          this.storeBillsInContainer();
          this.loading = false;
        },
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
      if (this.form.processing) {
        return false;
      }

      // set form data
      this.form.enccode = this.enccode;
      this.form.hospitalNumber = this.hospitalNumber;
      this.form.itemsToBillList = this.itemsToBillList;
      this.form.tscode = this.bills.admission_date_bill.tscode;

      this.form.post(route('patientcharge.store'), {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
          this.createBillDialog = false;
          this.cancel();
          this.createdMsg();
        },
        onError: (errors) => {
          this.stockBalanceDeclared = true;
        },
        onFinish: (visit) => {
          //   console.log('object');
          if (this.stockBalanceDeclared != true) {
            this.billList = [];
            this.medicalSuppliesList = [];
            this.miscList = [];
            this.itemList = [];
            this.itemsToBillList = [];
            // this.storeBillsInContainer();
            this.getTotalAmount();
            this.storeMedicalSuppliesInContainer();
            this.storeMiscInContainer();
            this.storeItemsInContainer();
          }
        },
      });
      //   console.log(this.$page.props.errors);
    },
    getTankDesc(item) {
      //   console.log(item);
      const matchingMedSupply = this.$page.props.medSupplies.find((x) => item === x.cl2comb);
      const matchingTank = this.$page.props.tanksList.find((x) => item === x.itemcode);
      //   console.log(matchingMedSupply);

      if (matchingMedSupply != null) {
        return matchingMedSupply.cl2desc;
      } else if (matchingTank != null) {
        return (
          matchingTank.gendesc +
          ' ' +
          matchingTank.dmdnost +
          //   ' ' +
          //   matchingTank.dmdnnostp +
          ' ' +
          matchingTank.stredesc +
          ' ' +
          matchingTank.formdesc +
          ' ' +
          matchingTank.rtedesc
        );
      } else {
        return null;
      }
    },
    editItem(charge, chargeLogs) {
      //   console.log('charge', charge.data.item);
      //   console.log('chargeLogs', chargeLogs.data);
      this.form.isUpdate = true;
      this.form.upd_id = chargeLogs.data.id;
      this.form.upd_ward_stocks_id = chargeLogs.data.ward_stocks_id;
      this.form.upd_enccode = chargeLogs.data.enccode;
      this.form.upd_hospitalNumber = chargeLogs.data.acctno;
      this.form.upd_charge_slip_no = charge.data.charge_slip_no;
      this.form.upd_itemcode = chargeLogs.data.itemcode;
      this.form.upd_item_desc = charge.data.item;
      this.form.upd_type_of_charge_code = charge.data.type_of_charge_code;
      this.form.upd_currentChargeQty = chargeLogs.data.quantity;
      this.form.upd_price = chargeLogs.data.price_per_piece;
      this.form.upd_pcchrgdte = chargeLogs.data.pcchrgdte;
      this.updateBillDialog = true;
      //   console.log(this.form.upd_currentChargeQty);
    },
    cancel() {
      this.stockBalanceDeclared = false;
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
