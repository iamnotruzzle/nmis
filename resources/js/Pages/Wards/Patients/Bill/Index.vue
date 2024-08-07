<template>
  <app-layout>
    <Head title="NMIS - Bill Patient" />

    <Toast />

    <!-- main container -->
    <div>
      <!-- patient bills -->
      <div class="card">
        <div>
          <DataTable
            class="p-datatable-sm"
            dataKey="uid"
            v-model:filters="filters"
            :value="billList"
            selectionMode="single"
            rowGroupMode="subheader"
            groupRowsBy="charge_slip_no"
            sortMode="single"
            removableSort
            showGridlines
            scrollable
            scrollHeight="800px"
          >
            <template #header>
              <span class="text-2xl text-primary font-bold">
                {{ pat_name[0].patlast }}, {{ pat_name[0].patfirst }} {{ pat_name[0].patmiddle }}
              </span>
              <span class="text-2xl text-primary font-bold">( {{ pat_name[0].hpercode }} )</span>
              <div class="flex flex-wrap align-items-center justify-content-between gap-2">
                <span class="text-xl text-900 font-bold text-primary">BILLS</span>
                <div class="flex">
                  <div class="mr-2">
                    <div class="p-inputgroup">
                      <span class="p-inputgroup-addon">
                        <i class="pi pi-search"></i>
                      </span>
                      <InputText
                        id="searchInput"
                        v-model="filters['global'].value"
                        size="large"
                        placeholder="Search"
                      />
                    </div>
                  </div>
                  <Button
                    v-if="is_for_discharge != 'true'"
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
              header="CHARGE SLIP #"
              style="width: 10%"
              sortable
            >
              <!-- <template #body="{ data }">
              {{ data.charge_slip_no }}
            </template> -->
            </Column>
            <Column
              field="type_of_charge_description"
              header="TYPE OF CHARGE"
              style="width: 15%"
              sortable
            >
              <template #body="{ data }">
                {{ data.type_of_charge_description }}
              </template>
            </Column>
            <Column
              field="item"
              header="ITEM"
              style="width: 30%"
              sortable
            >
              <template #body="{ data }">
                {{ data.item }}
              </template>
            </Column>
            <Column
              field="charge_date"
              header="CHARGE DATE"
              style="width: 10%"
              sortable
            >
              <template #body="{ data }">
                {{ tzone(data.charge_date) }}
              </template>
            </Column>
            <Column
              field="quantity"
              header="QUANTITY"
              style="text-align: right; width: 10%"
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
              style="text-align: right; width: 10%"
            >
              <template #body="{ data }"> {{ data.price }} </template>
            </Column>
            <Column
              field="amount"
              header="AMOUNT"
              sortable
              style="text-align: right; width: 10%"
            >
              <template #body="{ data }"> {{ data.amount }} </template>
            </Column>

            <Column
              header="ACTION"
              style="width: 5%"
            >
              <template #body="slotProps">
                <!-- only show if the item is charge using this system and not HOMIS -->
                <!-- slotProps.data.is_consumable != 'y' -->
                <div v-if="slotProps.data.charge_log_id != null">
                  <Button
                    icon="pi pi-pencil"
                    class="mr-1"
                    rounded
                    text
                    severity="warning"
                    @click="editItem(slotProps.data)"
                  />
                </div>
              </template>
            </Column>

            <template #groupheader="slotProps">
              <div class="bg-primary-reverse py-3">
                <span class="mr-2">CHARGE SLIP #: </span>
                <span class="mr-4">{{ slotProps.data.charge_slip_no }}</span>
                <v-icon
                  name="la-receipt-solid"
                  scale="1.5"
                  class="pi pi-send text-yellow-500 cursor-pointer"
                  @click="openReceiptDialog(slotProps.data)"
                ></v-icon>
              </div>
            </template>
            <template #groupfooter="slotProps">
              <div class="flex justify-content-end font-bold w-full text-green-400">
                Total: ₱ {{ totalPerChargeSlip(slotProps.data.charge_slip_no) }}
              </div>
            </template>
            <template #footer>
              <div class="text-right text-2xl text-green-600 font-bold">
                Total: ₱ {{ toFixedWithoutRounding(totalAmount) }}
              </div>
            </template>
          </DataTable>

          <!-- create bill dialog -->
          <Dialog
            v-model:visible="createBillDialog"
            :modal="true"
            class="p-fluid w-10"
            :closeOnEscape="false"
            @hide="whenDialogIsHidden"
          >
            <template #header>
              <div class="text-primary text-xl font-bold">CHARGE PATIENT</div>
            </template>
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
                onkeypress="return event.charCode >= 48 && event.charCode <= 57"
                :class="{ 'p-invalid': qtyToCharge == '' || item == null }"
                @keyup.enter="medicalSuppliesQtyValidation"
                inputId="integeronly"
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

              <!-- <p
                class="text-error text-xl font-semibold"
                v-if="stockBalanceDeclared != false"
              >
                {{ $page.props.errors['itemsToBillList.0.itemCode'] }} stock balance has not yet been declared.
              </p> -->

              <p
                class="text-error text-xl font-semibold"
                v-if="stockBalanceDeclared != false"
              >
                [ {{ item.itemDesc }} ] stock balance has not yet been declared.
              </p>

              <DataTable
                v-model:filters="itemsToBillFilter"
                :globalFilterFields="['itemDesc']"
                :value="itemsToBillList"
                tableStyle="min-width: 50rem"
                class="p-datatable-sm w-full"
                paginator
                showGridlines
                removableSort
                :rows="7"
              >
                <template #header>
                  <div class="flex justify-content-end">
                    <div class="p-inputgroup">
                      <span class="p-inputgroup-addon">
                        <i class="pi pi-search"></i>
                      </span>
                      <InputText
                        id="searchInput"
                        v-model="itemsToBillFilter['global'].value"
                        placeholder="Search Item"
                      />
                    </div>
                  </div>
                </template>
                <Column
                  field="itemDesc"
                  header="ITEM/SERVICE"
                  style="width: 50%"
                  sortable
                ></Column>
                <!-- <Column
                  field="currentStock"
                  header="CURRENT STOCK"
                  style="width: 17.5%"
                  sortable
                ></Column> -->
                <Column
                  field="qtyToCharge"
                  header="QTY TO CHARGE"
                  style="width: 15%"
                  sortable
                ></Column>
                <Column
                  field="price"
                  header="PRICE PER UNIT"
                  style="width: 15%"
                  sortable
                >
                  <template #body="{ data }"> ₱ {{ data.price }} </template>
                </Column>
                <Column
                  filed="total"
                  header="TOTAL"
                  style="width: 10%"
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
            header="Return/Cancel"
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
                onkeypress="return event.charCode >= 48 && event.charCode <= 57"
                @keyup.enter="
                  form.processing ||
                  Number(form.upd_QtyToReturn) > Number(form.upd_currentChargeQty) ||
                  form.upd_QtyToReturn == null ||
                  form.upd_QtyToReturn == 0 ||
                  form.upd_QtyToReturn == ''
                    ? ''
                    : submit()
                "
                inputId="integeronly"
              />
              <span
                v-if="Number(form.upd_QtyToReturn) > Number(form.upd_currentChargeQty)"
                class="text-error"
              >
                Return quantity is must be less than of the charged quantity
              </span>
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

      <!-- current stocks -->
      <div class="card">
        <div>
          <DataTable
            v-model:filters="medicalSuppliesListFilter"
            :value="medicalSuppliesList"
            scrollable
            scrollHeight="h-full"
            showGridlines
            removableSort
            class="p-datatable-sm mt-4"
          >
            <template #header>
              <div class="text-2xl text-primary font-bold">INSTOCK</div>

              <div class="p-inputgroup">
                <span class="p-inputgroup-addon">
                  <i class="pi pi-search"></i>
                </span>
                <InputText
                  id="searchInput"
                  v-model="medicalSuppliesListFilter['global'].value"
                  placeholder="Search item"
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
                <p
                  v-if="data.quantity <= 10"
                  class="text-yellow-500 text-bold"
                >
                  {{ data.quantity }}
                  <span v-if="data.is_consumable == 'y'"> pounds</span>
                </p>
                <p
                  v-else
                  class="text-green-500 text-bold"
                >
                  {{ data.quantity }}
                  <span v-if="data.is_consumable == 'y'"> pounds</span>
                </p>
              </template>
            </Column>
            <Column
              field="price"
              header="PRICE PER UNIT"
              style="text-align: right; width: 20%"
            >
              <template #body="{ data }"> ₱ {{ data.price }} </template>
            </Column>
            <Column header="EXP. DATE">
              <template #body="{ data }">
                <!-- {{ data }} -->
                <span v-if="data.is_consumable != null || data.is_consumable == 'y'">NA</span>
                <span v-else>{{ tzone(data.expiration_date) }}</span>
              </template>
            </Column>
          </DataTable>
        </div>
      </div>

      <Dialog
        v-model:visible="receiptDialog"
        :modal="true"
        class="p-fluid w-3"
        :closeOnEscape="true"
        @hide="whenDialogIsHidden"
      >
        <template #header>
          <div class="text-primary text-xl font-bold"></div>
        </template>

        <div id="print">
          <div class="flex flex-column justify-content-center align-items-center text-center">
            <h4 class="font-bold">{{ printForm.no }}</h4>
            <p class="font-semibold">MMMHMC-A-PHB-QP-005 Form 1 Rev 0 Charge Slip</p>
            <p class="font-bold">MARIANO MARCOS MEMORIAL HOSPITAL and MEDICAL CENTER</p>
            <p class="text-2xl text-blue-500 font-bold">CHARGE SLIP</p>
          </div>

          <div class="w-full flex justify-content-center align-content-center">
            <div class="">
              <div class="flex justify-content-between w-full mb-2">
                <div>
                  <label class="mr-2">Type:</label>
                  <span>{{ printForm.type }}</span>
                </div>
                <div>
                  <label class="mr-2">No.:</label>
                  <span class="font-bold">{{ printForm.no }}</span>
                </div>
              </div>

              <div class="flex justify-content-between w-full mb-2">
                <div>
                  <label class="mr-2">Hospital #:</label>
                  <span>{{ printForm.hospital_number }}</span>
                </div>
                <div>
                  <label class="mr-2">Date:</label>
                  <span class="">{{ printForm.date }}</span>
                </div>
              </div>

              <div class="flex justify-content-start w-full mb-2">
                <div>
                  <label class="mr-2">Patient Name:</label>
                  <span class="capitalize font-semibold">{{ printForm.patient_name }}</span>
                </div>
              </div>
              <div class="flex justify-content-start w-full mb-2">
                <div>
                  <label class="mr-2">Location:</label>
                  <span>{{ printForm.location }}</span>
                </div>
              </div>

              <div class="flex justify-content-center w-full mb-2">
                <DataTable
                  :value="printForm.chargedItems"
                  class="w-full"
                >
                  <Column
                    field="item"
                    header="ITEM"
                  ></Column>
                  <Column
                    field="qty"
                    header="QTY"
                  ></Column>
                  <Column
                    field="price"
                    header="PRICE"
                  ></Column>
                  <Column
                    field="amount"
                    header="AMOUNT"
                  ></Column>

                  <template #footer>
                    <div class="flex justify-content-end font-bold w-full">
                      Total: ₱{{
                        printForm.total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
                      }}
                    </div>
                  </template>
                </DataTable>
              </div>

              <div class="flex justify-content-start w-full mb-4">
                <div>
                  <label class="mr-2 mb-2">Issued by:</label>
                  <span class="text-bold">{{
                    this.printForm.issued_by.userDetail.firstname + ' ' + this.printForm.issued_by.userDetail.lastname
                  }}</span>
                </div>
              </div>
              <div class="flex justify-content-start w-full mb-4">
                <div>
                  <label class="mr-2 mb-2">Checked by:</label>
                  <span>____________________________</span>
                </div>
              </div>
              <div class="flex justify-content-start w-full mb-">
                <div>
                  <label class="mr-2 mb-2">Received by:</label>
                  <span>____________________________</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <template #footer>
          <!-- @click="submit" -->
          <Button
            label="Print"
            icon="pi pi-print"
            severity="success"
            @click="print"
          />
        </template>
      </Dialog>
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
import InputNumber from 'primevue/inputnumber';
import Checkbox from 'primevue/checkbox';
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
    InputNumber,
    Checkbox,
  },
  props: {
    pat_name: Array,
    pat_tscode: Object,
    pat_enccode: String,
    // patient: String,
    room_bed: String,
    is_for_discharge: String,
    bills: Object,
    medicalSupplies: Object,
    misc: Object,
  },
  data() {
    return {
      stockBalanceDeclared: false,
      expandedRow: [],
      uid: 0, // Initialize unique ID property
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
      receiptDialog: false,
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
        charge_log_from: null,
      }),
      printForm: this.$inertia.form({
        no: null, // charge_slip_no
        type: null,
        hospital_number: null,
        date: null,
        patient_name: null,
        location: null,
        chargedItems: [{}],
        issued_by: null,
        total: null,
      }),
    };
  },
  // created will be initialize before mounted
  //   created() {
  //     this.totalRecords = this.bills.total;
  //     this.params.page = this.bills.current_page;
  //     this.rows = this.bills.per_page;
  //   },
  mounted() {
    // console.log(this.pat_tscode.tscode);

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
    this.hospitalNumber = this.pat_name[0].hpercode;
  },
  methods: {
    totalPerChargeSlip(charge_slip_no) {
      let total = 0;

      if (this.billList) {
        for (let bill of this.billList) {
          if (bill.charge_slip_no === charge_slip_no) {
            total += bill.amount;
          }
        }
      }

      //   console.log(total);

      //   total.toLocaleString('en-PH', {
      //     style: 'currency',
      //     currency: 'PHP',
      //   });

      return total.toFixed(2);
    },
    tzone(date) {
      if (date == null) {
        return 'NA';
      } else {
        return moment.tz(date, 'Asia/Manila').format('L');
      }
    },
    chargeSlipDate(date) {
      if (date == null) {
        return 'NA';
      } else {
        return moment.tz(date, 'Asia/Manila').format('MM/DD/YYYY hh:mm a');
      }
    },
    getTotalAmount() {
      this.totalAmount = 0;
      this.billList.forEach((item) => {
        this.totalAmount += Number(item.amount);
      });
    },
    toFixedWithoutRounding(number) {
      const factor = Math.pow(10, 2);
      const truncated = Math.floor(number * factor) / factor;
      return truncated.toFixed(2);
    },
    storeBillsInContainer() {
      this.bills.forEach((e) => {
        // console.log(e);

        // only push item when chargcode are medical supplies or misc
        if (e.type_of_charge_code == 'MISC' || e.type_of_charge_code == 'DRUMN') {
          this.billList.push({
            uid: ++this.uid,
            charge_slip_no: e.charge_slip_no,
            type_of_charge_code: e.type_of_charge_code,
            type_of_charge_description: e.type_of_charge_description,
            item: e.misc != null ? e.misc : e.category + ' ' + e.item,
            charge_log_id: e.charge_log_id,
            charge_log_from: e.charge_log_from,
            charge_log_ward_stocks_id: e.charge_log_ward_stocks_id,
            charge_log_quantity: e.charge_log_quantity,
            itemcode: e.itemcode,
            quantity: Math.trunc(e.quantity),
            // price: e.pchrgup,
            price: Math.round(e.price * 100) / 100,
            amount: (Math.trunc(e.quantity) * Math.round(e.price * 100)) / 100,
            charge_date: e.charge_date,
            // patient_charge_logs: e.patient_charge_logs.length == 0 ? null : e.patient_charge_logs,
          });
        } else {
          return null;
        }
      });
      //   console.log('bill list', this.billList);
    },
    storeMedicalSuppliesInContainer() {
      //   let totalPounds = (quantity * average) - totalConsumed;
      this.medicalSupplies.forEach((med) => {
        this.medicalSuppliesList.push({
          id: med.id,
          is_consumable: med.is_consumable,
          cl2comb: med.cl2comb,
          cl2desc: med.cl2desc,
          uomcode: med.uomcode == null ? null : med.uomcode,
          quantity: med.is_consumable != 'y' ? med.quantity : med.total_pounds,
          average: med.average,
          total_pounds: med.total_pounds,
          price: med.price,
          expiration_date: med.expiration_date,
        });
      });
      console.log('medical supplies list', this.medicalSupplies);
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
      const combinedItems = {};
      this.medicalSupplies.forEach((med) => {
        if (med.price != null) {
          let medQuantity = med.is_consumable != 'y' ? med.quantity : med.total_pounds;
          if (combinedItems[med.cl2desc]) {
            combinedItems[med.cl2desc].totalQuantity += medQuantity;
            combinedItems[med.cl2desc].prices.push({
              id: med.id,
              price: med.price,
              quantity: medQuantity,
              expiryDate: med.expiryDate,
            });
          } else {
            combinedItems[med.cl2desc] = {
              id: med.id, // Use the ID of the first encountered item
              is_consumable: med.is_consumable,
              typeOfCharge: 'DRUMN',
              itemCode: med.cl2comb,
              itemDesc: med.cl2desc,
              unit: med.uomcode == null ? null : med.uomcode,
              totalQuantity: medQuantity,
              prices: [{ id: med.id, price: med.price, quantity: medQuantity, expiryDate: med.expiryDate }],
            };
          }
        }
      });
      this.itemList = Object.values(combinedItems);

      // misc
      this.misc.forEach((misc) => {
        this.itemList.push({
          id: null,
          typeOfCharge: 'MISC',
          itemCode: misc.hmcode,
          itemDesc: misc.hmdesc,
          unit: misc.uomcode == null ? null : misc.uomcode,
          quantity: 99999,
          price: misc.hmamt,
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
    medicalSuppliesQtyValidation() {
      console.log(this.item);

      if (this.item.typeOfCharge == 'DRUMN') {
        // Check if an item is selected
        if (this.item == null || this.item == '') {
          this.itemNotSelected = true;
          this.itemNotSelectedMsg = 'Item not selected.';
          return;
        }

        // Check if a quantity to charge is provided
        if (this.qtyToCharge == 0 || this.qtyToCharge == null || this.qtyToCharge == '') {
          this.itemNotSelected = true;
          this.itemNotSelectedMsg = 'Please provide quantity.';
          return;
        }

        // Calculate the total quantity already billed for this item
        const totalBilledQty = this.itemsToBillList
          .filter((e) => e.itemCode === this.item['itemCode'])
          .reduce((sum, e) => sum + e.qtyToCharge, 0);

        // Check if adding the new quantity exceeds the total available quantity
        if (totalBilledQty + this.qtyToCharge > this.item.totalQuantity) {
          this.itemNotSelected = true;
          this.itemNotSelectedMsg = 'Total quantity exceeds available stock.';
          return;
        }

        // Check if there are related items in the itemsToBillList
        const relatedItemsExist = this.itemsToBillList.some((e) => e.itemCode === this.item['itemCode']);
        if (relatedItemsExist) {
          this.itemNotSelected = true;
          this.itemNotSelectedMsg = 'Remove all related items first to update the quantity.';
          return;
        }

        let qtyRemaining = this.qtyToCharge;
        const newBillItems = [];

        // Sort the prices array to prioritize near-expiry items first
        this.item.prices.sort((a, b) => new Date(a.expiryDate) - new Date(b.expiryDate));

        for (const priceInfo of this.item.prices) {
          if (qtyRemaining <= 0) break;
          const qtyToCharge = Math.min(priceInfo.quantity, qtyRemaining);
          qtyRemaining -= qtyToCharge;

          const existingItem = this.itemsToBillList.find(
            (e) => e.itemCode === this.item['itemCode'] && e.price === priceInfo.price
          );

          if (existingItem) {
            existingItem.qtyToCharge += qtyToCharge;
            existingItem.total = (existingItem.price * existingItem.qtyToCharge).toFixed(2);
          } else {
            newBillItems.push({
              id: priceInfo.id, // Use the original ID from the prices array
              typeOfCharge: this.item['typeOfCharge'],
              itemCode: this.item['itemCode'],
              itemDesc: this.item['itemDesc'],
              unit: this.item['unit'],
              currentStock: this.item['typeOfCharge'] == 'DRUMN' ? this.item['totalQuantity'] : 'Infinite',
              qtyToCharge,
              price: priceInfo.price,
              total: (priceInfo.price * qtyToCharge).toFixed(2),
              expiryDate: priceInfo.expiryDate, // Include expiry date if needed for further processing
            });
          }
        }

        // Handle case where not enough quantity was available
        if (qtyRemaining > 0) {
          this.itemNotSelected = true;
          this.itemNotSelectedMsg = 'Not enough quantity available.';
        } else {
          // Ensure there are no items with the same names already in the list before pushing new items
          this.itemsToBillList = this.itemsToBillList.filter((e) => e.itemCode !== this.item['itemCode']);
          this.itemsToBillList.push(...newBillItems);
          this.itemNotSelected = false;
          this.itemNotSelectedMsg = null;
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
                id: this.item['id'] != null ? this.item['id'] : null,
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
        this.form.reset(),
        this.printForm.reset()
      );
    },
    openReceiptDialog(data) {
      console.log('data', data);

      this.printForm.no = data.charge_slip_no;
      this.printForm.type = 'Ward';
      this.printForm.hospital_number = this.pat_name[0].hpercode;
      this.printForm.date = this.chargeSlipDate(data.charge_date);
      this.printForm.patient_name =
        this.pat_name[0].patlast + ', ' + this.pat_name[0].patfirst + this.pat_name[0].patmiddle;
      this.printForm.location = this.$page.props.auth.user.location.location_name.wardname + ' ' + this.room_bed;
      this.printForm.chargedItems = [];
      this.printForm.issued_by = this.$page.props.auth.user;
      this.printForm.total = 0;

      this.billList.forEach((e) => {
        if (e.charge_slip_no == this.printForm.no) {
          // Format price and amount with commas and two decimal places
          const formattedPrice = e.price.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
          });
          const formattedAmount = e.amount.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
          });

          this.printForm.chargedItems.push({
            item: e.item,
            qty: e.charge_log_quantity,
            price: formattedPrice,
            amount: formattedAmount,
          });

          // Add the amount to the total, keeping it as a number for calculation
          this.printForm.total += e.amount;
        }
      });

      this.receiptDialog = true;
    },
    print() {
      const printContents = document.getElementById('print').innerHTML;
      const originalContents = document.body.innerHTML;

      // Replace the current page content with the print content
      document.body.innerHTML = printContents;

      // Trigger the print dialog
      window.print();

      // After printing, restore the original content and redirect to the previous page
      document.body.innerHTML = originalContents;

      // Get the current URL with its parameters
      const currentUrl = window.location.href;

      // Redirect to the current URL
      window.location.href = currentUrl;
    },
    updateData() {
      this.params.enccode = this.enccode;
      this.billList = [];
      this.$inertia.get('patientcharge', this.params, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: (visit) => {
          this.billList = [];
          //   this.expandedRow = [];
          this.storeBillsInContainer();
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
      if (this.form.charge_log_from == 'MEDICAL GASES') {
        if (
          this.form.processing ||
          Number(this.form.upd_QtyToReturn) > Number(this.form.upd_currentChargeQty) ||
          this.form.upd_QtyToReturn == null ||
          this.form.upd_QtyToReturn == 0 ||
          this.form.upd_QtyToReturn == ''
        ) {
          return false;
        }

        // set form data
        this.form.enccode = this.pat_enccode;
        this.form.hospitalNumber = this.pat_name[0].hpercode;
        this.form.itemsToBillList = this.itemsToBillList;
        this.form.tscode = this.pat_tscode.tscode;

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
              this.storeBillsInContainer();
              this.getTotalAmount();
              this.storeMedicalSuppliesInContainer();
              this.storeMiscInContainer();
              this.storeItemsInContainer();
            }
          },
        });
      } else {
        if (this.form.processing) {
          return false;
        }

        // set form data
        this.form.enccode = this.pat_enccode;
        this.form.hospitalNumber = this.pat_name[0].hpercode;
        this.form.itemsToBillList = this.itemsToBillList;
        this.form.tscode = this.pat_tscode.tscode;

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
              this.storeBillsInContainer();
              this.getTotalAmount();
              this.storeMedicalSuppliesInContainer();
              this.storeMiscInContainer();
              this.storeItemsInContainer();
            }
          },
        });
      }

      //   console.log(this.$page.props.errors);
    },
    editItem(e) {
      console.log('charge', e);

      this.form.isUpdate = true;
      this.form.upd_id = e.charge_log_id;
      this.form.upd_ward_stocks_id = e.charge_log_ward_stocks_id;
      this.form.upd_enccode = this.pat_enccode;
      this.form.upd_hospitalNumber = this.pat_name[0].hpercode;
      this.form.upd_charge_slip_no = e.charge_slip_no;
      this.form.upd_itemcode = e.itemcode;
      this.form.upd_item_desc = e.item;
      this.form.upd_type_of_charge_code = e.type_of_charge_code;
      this.form.upd_currentChargeQty = e.quantity;
      this.form.upd_QtyToReturn = e.quantity;
      this.form.upd_price = e.price;
      this.form.upd_pcchrgdte = e.charge_date;
      this.form.charge_log_from = e.charge_log_from;
      this.updateBillDialog = true;

      console.log('form', this.form);
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
      this.printForm.reset();
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
