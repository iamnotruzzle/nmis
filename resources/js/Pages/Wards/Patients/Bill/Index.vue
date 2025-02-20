<template>
  <app-layout>
    <Head title="NMIS - Bill Patient" />

    <Toast />

    <div>
      <!-- patient bills -->
      <div
        class="flex justify-content-center"
        style="height: 85vh; width: 100%"
      >
        <DataTable
          class="p-datatable-sm card"
          style="border-top-right-radius: 0px; border-bottom-right-radius: 0px; width: 90%"
          dataKey="uid"
          v-model:filters="filters"
          :value="billList"
          selectionMode="single"
          rowGroupMode="subheader"
          groupRowsBy="charge_slip_no"
          removableSort
          showGridlines
          scrollable
          scrollHeight="flex"
          :globalFilterFields="['charge_slip_no', 'type_of_charge_description', 'item', 'amount']"
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
                <div class="flex align-items-center">
                  <Button
                    v-if="is_for_discharge !== 'true'"
                    label="CHARGE PATIENT"
                    icon="pi pi-money-bill"
                    iconPos="right"
                    @click="openCreateBillDialog"
                  />
                </div>
              </div>
            </div>
          </template>
          <Column
            field="charge_slip_no"
            header="CHARGE SLIP #"
            sortable
          >
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
            style="width: 30%"
            sortable
          >
            <template #body="{ data }">
              <span> {{ data.item }}</span>
            </template>
          </Column>
          <Column
            field="charge_date_viewer"
            header="DATE"
            sortable
            style="text-align: right"
            :pt="{ headerContent: 'justify-content-end' }"
          >
            <template #body="{ data }">
              {{ data.charge_date_viewer }}
            </template>
          </Column>
          <Column
            field="quantity"
            header="QTY"
            style="text-align: right; width: 5%"
            :pt="{ headerContent: 'justify-content-end' }"
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
            :pt="{ headerContent: 'justify-content-end' }"
          >
            <template #body="{ data }"> {{ data.price }} </template>
          </Column>
          <Column
            field="amount"
            header="AMOUNT"
            sortable
            style="text-align: right; width: 5%"
            :pt="{ headerContent: 'justify-content-end' }"
          >
            <template #body="{ data }"> {{ data.amount }} </template>
          </Column>
          <Column
            field="entry_by"
            header="ENTRY BY"
            sortable
            style="text-align: right"
            :pt="{ headerContent: 'justify-content-end' }"
          >
            <!-- <template #body="{ data }">
              {{ data.charge_slip_no }}
            </template> -->
          </Column>

          <Column
            header="ACTION"
            style="width: 5%"
          >
            <template #body="slotProps">
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
                @click="print(slotProps.data)"
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
        <Button
          style="border-top-left-radius: 0px; border-bottom-left-radius: 0px"
          @click="showStockDialog = true"
          type="button"
          class="border-transparent bg-yellow-500 hover:bg-yellow-300 transition-all"
        >
          <span class="font-bold">INVENTORY</span>
        </Button>
      </div>
    </div>

    <!-- create bill dialog -->
    <Dialog
      v-model:visible="createBillDialog"
      :modal="true"
      class="p-fluid w-7"
      :closeOnEscape="false"
      @hide="whenDialogIsHidden"
    >
      <template #header>
        <div class="text-primary text-xl font-bold">CHARGE PATIENT</div>
      </template>
      <div class="field mb-3">
        <label>Available items</label>
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
            sortable
            style="text-align: right; width: 15%"
            :pt="{ headerContent: 'justify-content-end' }"
          ></Column>
          <Column
            field="price"
            header="PRICE PER UNIT"
            style="text-align: right; width: 15%"
            :pt="{ headerContent: 'justify-content-end' }"
            sortable
          >
            <template #body="{ data }"> ₱ {{ data.price }} </template>
          </Column>
          <Column
            filed="total"
            header="TOTAL"
            style="text-align: right; width: 10%"
            :pt="{ headerContent: 'justify-content-end' }"
            sortable
          >
            <template #body="{ data }"> ₱ {{ data.total }} </template>
          </Column>
          <Column header="">
            <template #body="slotProps">
              <Button
                label="REMOVE"
                icon="pi pi-times"
                severity="danger"
                @click="removeFromToBillContainer(slotProps.data)"
              />
            </template>
          </Column>
        </DataTable>
      </div>

      <template #footer>
        <Button
          :label="!form.processing ? 'CANCEL' : 'CANCEL'"
          icon="pi pi-times"
          :disabled="form.processing"
          severity="danger"
          @click="cancel"
        />

        <Button
          :disabled="itemsToBillList.length == 0 || form.processing"
          :label="!form.processing ? 'CHARGE' : 'CHARGE'"
          :icon="form.processing ? 'pi pi-spin pi-spinner' : 'pi pi-check'"
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
          :label="!form.processing ? 'CANCEL' : 'CANCEL'"
          icon="pi pi-times"
          :disabled="form.processing"
          severity="danger"
          @click="cancel"
        />
        <Button
          :disabled="
            Number(form.upd_QtyToReturn) > Number(form.upd_currentChargeQty) ||
            form.upd_QtyToReturn == null ||
            form.upd_QtyToReturn == 0 ||
            form.upd_QtyToReturn == '' ||
            form.processing
          "
          :label="!form.processing ? 'RETURN' : 'RETURN'"
          :icon="form.processing ? 'pi pi-spin pi-spinner' : 'pi pi-check'"
          severity="warning"
          type="submit"
          @click="submit"
        />
      </template>
    </Dialog>

    <!-- Charge slip -->
    <div
      id="print"
      style="background-color: white; display: none"
    >
      <div
        style="font-family: Arial, sans-serif; width: 100%; display: flex; justify-content: center; align-items: center"
      >
        <div style="padding: 0 2rem; color: #1f2937; margin: 0">
          <div
            style="
              display: flex;
              flex-direction: column;
              justify-content: center;
              align-items: center;
              text-align: center;
            "
          >
            <p style="font-weight: bold; font-size: 1.125rem; margin: 0; padding: 0; text-transform: uppercase">
              {{ printForm.no }}
            </p>
            <p style="font-size: 0.75rem; margin: 0; padding: 0">MMMHMC-A-PHB-QP-005 Form 1 Rev 0 Charge Slip</p>
            <p style="font-size: 0.75rem; margin: 0; padding: 0">MARIANO MARCOS MEMORIAL HOSPITAL and MEDICAL CENTER</p>
            <p style="font-size: 1.125rem; color: #3b82f6; font-weight: bold">CHARGE SLIP</p>
          </div>

          <div
            style="
              display: flex;
              justify-content: space-between;
              width: 100%;
              margin-bottom: 0.25rem;
              font-size: 0.75rem;
            "
          >
            <div>
              <label>Type:</label>
              <span>{{ printForm.type }}</span>
            </div>
            <div>
              <label>No.:</label>
              <span style="font-weight: bold; text-transform: uppercase">{{ printForm.no }}</span>
            </div>
          </div>

          <div
            style="
              display: flex;
              justify-content: space-between;
              width: 100%;
              margin-bottom: 0.25rem;
              font-size: 0.75rem;
            "
          >
            <div>
              <label style="margin-right: 0.5rem">Hospital #:</label>
              <span>{{ printForm.hospital_number }}</span>
            </div>
            <div>
              <label style="margin-right: 0.5rem">Date:</label>
              <span>{{ printForm.date }}</span>
            </div>
          </div>

          <div
            style="display: flex; justify-content: flex-start; width: 100%; margin-bottom: 0.25rem; font-size: 0.75rem"
          >
            <div>
              <label style="margin-right: 0.5rem">Patient Name:</label>
              <span style="text-transform: capitalize; font-weight: 600">{{ printForm.patient_name }}</span>
            </div>
          </div>
          <div
            style="display: flex; justify-content: flex-start; width: 100%; margin-bottom: 0.5rem; font-size: 0.75rem"
          >
            <div>
              <label style="margin-right: 0.5rem">Location:</label>
              <span>{{ printForm.location }}</span>
            </div>
          </div>

          <div style="display: flex; justify-content: center; width: 100%; margin-bottom: 0.25rem">
            <table style="width: 100%; font-size: 0.75rem; border-collapse: collapse">
              <thead>
                <tr>
                  <th style="padding: 0.5rem; text-align: left; border-bottom-style: solid; border-top-style: solid">
                    ITEM
                  </th>
                  <th style="padding: 0.5rem; text-align: right; border-bottom-style: solid; border-top-style: solid">
                    QTY
                  </th>
                  <th style="padding: 0.5rem; text-align: right; border-bottom-style: solid; border-top-style: solid">
                    PRICE
                  </th>
                  <th style="padding: 0.5rem; text-align: right; border-bottom-style: solid; border-top-style: solid">
                    AMOUNT
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="item in printForm.chargedItems"
                  :key="item.id"
                >
                  <td style="padding: 0.5rem; text-align: left; border-bottom-style: solid">
                    {{ item.item }}
                  </td>
                  <td style="padding: 0.5rem; text-align: right; border-bottom-style: solid">
                    {{ item.qty }}
                  </td>
                  <td style="padding: 0.5rem; text-align: right; border-bottom-style: solid">
                    {{ item.price }}
                  </td>
                  <td style="padding: 0.5rem; text-align: right; border-bottom-style: solid">
                    {{ item.amount }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div
            style="
              display: flex;
              justify-content: flex-end;
              font-weight: bold;
              width: 100%;
              margin-bottom: 0.5rem;
              font-size: 0.75rem;
            "
          >
            <span> Total: ₱{{ printForm.total }} </span>
          </div>
          <div
            style="display: flex; justify-content: flex-start; width: 100%; margin-bottom: 0.5rem; font-size: 0.75rem"
          >
            <div>
              <label style="margin-right: 0.5rem; margin-bottom: 0.5rem">Issued by:</label>
              <span style="font-weight: bold">{{ printForm.entry_by }}</span>
            </div>
          </div>
          <div
            style="display: flex; justify-content: flex-start; width: 100%; margin-bottom: 0.5rem; font-size: 0.75rem"
          >
            <div>
              <label style="margin-right: 0.5rem; margin-bottom: 0.5rem">Checked by:</label>
              <span>____________________________</span>
            </div>
          </div>
          <div style="display: flex; justify-content: flex-start; width: 100%; font-size: 0.75rem">
            <div>
              <label style="margin-right: 0.5rem; margin-bottom: 0.5rem">Received by:</label>
              <span>____________________________</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- current stocks -->
    <Dialog
      v-model:visible="showStockDialog"
      :style="{ width: '800px' }"
      header="CURRENT STOCKS"
      :modal="true"
      class="p-fluid"
      @hide="clickOutsideDialog"
      dismissableMask
    >
      <DataTable
        v-model:filters="medicalSuppliesListFilter"
        :value="medicalSuppliesList"
        showGridlines
        removableSort
        class="p-datatable-sm w-auto"
        :globalFilterFields="['cl2desc']"
        paginator
        :rows="10"
        size="large"
      >
        <template #header>
          <!-- <div class="text-2xl text-primary font-bold">CURRENT STOCKS</div> -->

          <div class="p-inputgroup my-3">
            <span class="p-inputgroup-addon">
              <i class="pi pi-search"></i>
            </span>
            <InputText
              id="searchInput"
              v-model="medicalSuppliesListFilter['global'].value"
              class="text-2xl"
              placeholder="Search item"
              type="search"
              autofocus
            />
          </div>
        </template>
        <Column
          field="from"
          header="FROM"
          sortable
        >
          <template #body="{ data }">
            <Tag
              v-if="data.from == 'CSR'"
              value="CSR"
              severity="contrast"
            ></Tag>
            <Tag
              v-else-if="data.from == 'CONSIGNMENT'"
              value="CONSIGNMENT"
              severity="warning"
            ></Tag>
            <Tag
              v-else-if="data.from == 'EXISTING_STOCKS'"
              value="EXISTING STOCKS"
              severity="info"
            ></Tag>
            <Tag
              v-else
              value="MEDICAL GASES"
            ></Tag>
          </template>
        </Column>
        <Column
          field="cl2desc"
          header="ITEM"
          sortable
        >
          <template #body="{ data }">
            <span> {{ data.cl2desc }}</span>
          </template>
        </Column>
        <Column
          header="QTY"
          style="text-align: right"
          :pt="{ headerContent: 'justify-content-end' }"
          sortable
        >
          <template #body="{ data }">
            <p
              v-if="data.quantity <= 10"
              class="text-yellow-500 text-bold"
            >
              {{ data.quantity }}
              <span v-if="data.is_consumable == 'y'"> </span>
            </p>
            <p
              v-else
              class="text-green-500 text-bold"
            >
              {{ data.quantity }}
              <span v-if="data.is_consumable == 'y'"> units</span>
            </p>
          </template>
        </Column>
        <Column
          header="PRICE PER UNIT"
          style="text-align: right"
          :pt="{ headerContent: 'justify-content-end' }"
          sortable
        >
          <template #body="{ data }"> {{ data.price }} </template>
        </Column>
        <Column
          header="EXP. DATE"
          style="text-align: right"
          :pt="{ headerContent: 'justify-content-end' }"
        >
          <template #body="{ data }">
            <!-- {{ data }} -->
            <span v-if="data.is_consumable != null || data.is_consumable == 'y'">NA</span>
            <span v-else>{{ tzone(data.expiration_date) }}</span>
          </template>
        </Column>
      </DataTable>
    </Dialog>
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
import SpeedDial from 'primevue/speeddial';
import Tag from 'primevue/tag';
import Echo from 'laravel-echo';
import moment from 'moment';
import { router } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
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
    SpeedDial,
  },
  props: {
    pat_name: Array,
    // OLD
    // pat_tscode: Object,
    // NEW
    pat_tscode: String,
    pat_enccode: String,
    // patient: String,
    room_bed: String,
    is_for_discharge: String,
    bills: Object,
    medicalSupplies: Object,
    misc: Object,
    canCharge: Boolean,
  },
  data() {
    return {
      domUpdater: null,
      stockBalanceDeclared: false,
      expandedRow: [],
      uid: 0, // Initialize unique ID property
      search: '',
      options: {},
      params: {},
      isUpdate: false,
      createBillDialog: false,
      updateBillDialog: false,
      showStockDialog: false,
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
      isPrinting: false,
      totalAmount: 0,
      medicalSuppliesListFilter: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
        cl2desc: { value: null, matchMode: FilterMatchMode.CONTAINS },
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
        entry_by: null,
        total: null,
      }),
    };
  },
  mounted() {
    window.Echo.channel('charges').listen('.ChargeLogsProcessed', (args) => {
      router.reload({
        onSuccess: () => {
          console.log('Data reloaded successfully');
          this.billList = [];
          this.storeBillsInContainer();
        },
      });
    });

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
      // Add bills (no async here, just a sync operation)
      this.bills.forEach((e) => {
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
          price: Math.round(e.price * 100) / 100,
          amount: (Math.trunc(e.quantity) * Math.round(e.price * 100)) / 100,
          charge_date_viewer: moment(e.charge_date).format('MM-DD-YYYY: hh:mm:ss'),
          charge_date: e.charge_date,
          entry_by: e.entry_by,
        });
      });

      // Sort the list after async operations
      this.billList.sort((a, b) => moment(b.charge_date, 'MM-DD-YYYY') - moment(a.charge_date, 'MM-DD-YYYY'));

      //   console.log(this.billList);
    },
    storeMedicalSuppliesInContainer() {
      let combinedSupplies = [];
      this.medicalSupplies.forEach((med) => {
        // Find if the item with the same cl2desc and price already exists in the combinedSupplies array
        let existingItem = combinedSupplies.find(
          (item) => item.cl2desc === med.cl2desc && Number(item.price) === Number(med.price)
        );

        if (existingItem) {
          // If found, just update the quantity
          existingItem.quantity += med.is_consumable != 'y' ? Number(med.quantity) : Number(med.total_usage);
        } else {
          // If not found, add a new entry
          combinedSupplies.push({
            from: med.from,
            id: med.id,
            is_consumable: med.is_consumable,
            cl2comb: med.cl2comb,
            cl2desc: med.cl2desc,
            uomcode: med.uomcode == null ? null : med.uomcode,
            quantity: med.is_consumable != 'y' ? Number(med.quantity) : Number(med.total_usage),
            average: Number(med.average),
            total_usage: Number(med.total_usage),
            price: Number(med.price),
            expiration_date: med.expiration_date,
          });
        }
      });

      this.medicalSuppliesList = combinedSupplies;
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
    },
    storeItemsInContainer() {
      // medical supplies
      const combinedItems = {};
      this.medicalSupplies.forEach((med) => {
        if (med.price != null) {
          let medQuantity = med.is_consumable != 'y' ? med.quantity : med.total_usage;
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
              itemDesc: '(MEDICAL SUPPLY)' + ' - ' + med.cl2desc,
              unit: med.uomcode == null ? null : med.uomcode,
              totalQuantity: medQuantity,
              prices: [{ id: med.id, price: med.price, quantity: medQuantity, expiryDate: med.expiryDate }],
            };
          }
        }
      });
      this.itemList = Object.values(combinedItems);

      // // misc
      this.misc.forEach((misc) => {
        this.itemList.push({
          id: null,
          typeOfCharge: 'MISC',
          itemCode: misc.hmcode,
          itemDesc: '(MISC)' + ' - ' + misc.hmdesc,
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
      //   console.log('item list', this.itemList);
    },
    /***  new and optimize version */
    medicalSuppliesQtyValidation() {
      if (!this.item) {
        this.itemNotSelected = true;
        this.itemNotSelectedMsg = 'Item not selected.';
        return;
      }

      if (!this.qtyToCharge || this.qtyToCharge <= 0) {
        this.itemNotSelected = true;
        this.itemNotSelectedMsg = 'Please provide quantity.';
        return;
      }

      const { typeOfCharge, itemCode, itemDesc, unit, totalQuantity, prices, price } = this.item;
      const isDrumn = typeOfCharge === 'DRUMN';

      if (isDrumn) {
        const totalBilledQty = this.itemsToBillList
          .filter((e) => e.itemCode === itemCode)
          .reduce((sum, e) => sum + e.qtyToCharge, 0);

        if (totalBilledQty + this.qtyToCharge > totalQuantity) {
          this.itemNotSelected = true;
          this.itemNotSelectedMsg = 'Total quantity exceeds available stock.';
          return;
        }

        if (this.itemsToBillList.some((e) => e.itemCode === itemCode)) {
          this.itemNotSelected = true;
          this.itemNotSelectedMsg = 'Remove all related items first to update the quantity.';
          return;
        }

        let qtyRemaining = this.qtyToCharge;
        const newBillItems = [];

        // Sort by earliest expiry date
        prices.sort((a, b) => new Date(a.expiryDate) - new Date(b.expiryDate));

        for (const priceInfo of prices) {
          if (qtyRemaining <= 0) break;
          const qtyToCharge = Math.min(priceInfo.quantity, qtyRemaining);
          qtyRemaining -= qtyToCharge;

          const existingItem = this.itemsToBillList.find((e) => e.itemCode === itemCode && e.price === priceInfo.price);

          if (existingItem) {
            existingItem.qtyToCharge += qtyToCharge;
            existingItem.total = (existingItem.price * existingItem.qtyToCharge).toFixed(2);
          } else {
            newBillItems.push({
              id: priceInfo.id,
              typeOfCharge,
              itemCode,
              itemDesc,
              unit,
              currentStock: isDrumn ? totalQuantity : 'Infinite',
              qtyToCharge,
              price: priceInfo.price,
              total: (priceInfo.price * qtyToCharge).toFixed(2),
              expiryDate: priceInfo.expiryDate,
            });
          }
        }

        if (qtyRemaining > 0) {
          this.itemNotSelected = true;
          this.itemNotSelectedMsg = 'Not enough quantity available.';
          return;
        }

        this.itemsToBillList = this.itemsToBillList.filter((e) => e.itemCode !== itemCode);
        this.itemsToBillList.push(...newBillItems);
      } else {
        if (this.itemsToBillList.some((e) => e.itemCode === itemCode)) {
          this.itemNotSelected = true;
          this.itemNotSelectedMsg = 'Item is already on the list.';
          return;
        }

        this.itemsToBillList.push({
          id: this.item.id || null,
          typeOfCharge,
          itemCode,
          itemDesc,
          unit,
          currentStock: isDrumn ? totalQuantity : 'Infinite',
          qtyToCharge: this.qtyToCharge,
          price,
          total: (price * this.qtyToCharge).toFixed(2),
        });
      }

      this.itemNotSelected = false;
      this.itemNotSelectedMsg = null;
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
    print(data) {
      setTimeout(() => {
        if (data != null) {
          this.printForm.no = data.charge_slip_no;
          this.printForm.type = 'Ward';
          this.printForm.hospital_number = this.pat_name[0].hpercode;
          this.printForm.date = this.chargeSlipDate(data.charge_date);
          this.printForm.patient_name =
            this.pat_name[0].patlast + ', ' + this.pat_name[0].patfirst + this.pat_name[0].patmiddle;
          this.printForm.location = this.$page.props.auth.user.location.location_name.wardname + ' ' + this.room_bed;
          this.printForm.chargedItems = [];
          this.printForm.entry_by = data.entry_by;
          this.printForm.total = 0;

          this.billList.forEach((e) => {
            if (e.charge_slip_no == this.printForm.no) {
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
                qty: e.quantity,
                price: formattedPrice,
                amount: formattedAmount,
              });

              this.printForm.total += e.amount;
            }
          });

          // Fix floating-point precision by formatting the total to 2 decimal places
          this.printForm.total = this.printForm.total.toFixed(2);

          this.$nextTick(() => {
            // Create a hidden iframe for printing
            const iframe = document.createElement('iframe');
            iframe.style.position = 'absolute';
            iframe.style.top = '-9999px';
            iframe.style.left = '-9999px';
            iframe.style.width = '0';
            iframe.style.height = '0';
            document.body.appendChild(iframe);

            // Write print content into the iframe
            const iframeDoc = iframe.contentWindow.document;
            iframeDoc.open();
            iframeDoc.write(`
                <html>
                <head>
                    <title>Print</title>
                    <style>
                    /* Add your print styles here */
                    </style>
                </head>
                <body>
                    ${document.getElementById('print').innerHTML}
                </body>
                </html>
            `);
            iframeDoc.close();

            // Trigger the print dialog
            iframe.contentWindow.focus();
            iframe.contentWindow.print();

            // Remove the iframe after a delay to ensure proper cleanup
            setTimeout(() => {
              document.body.removeChild(iframe);
            }, 100);
          });
        }
      }, 200); // Slightly longer delay to ensure rendering
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
      // console.log('else');
      if (this.form.processing) {
        return false;
      }

      // set form data
      this.form.enccode = this.pat_enccode;
      this.form.hospitalNumber = this.pat_name[0].hpercode;
      this.form.itemsToBillList = this.itemsToBillList;
      this.form.tscode = this.pat_tscode;

      this.form.post(route('patientcharge.store'), {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
          this.createBillDialog = false;
          if (this.form.isUpdate != true) {
            this.createdMsg();
            this.cancel();
          } else {
            this.updatedMsg();
            this.cancel();
          }
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

      //   console.log(this.$page.props.errors);
    },
    editItem(e) {
      //   console.log('charge', e);

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

      //   console.log('form', this.form);
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

@media print {
  @page {
    margin: 0;
    /* font-size: 50px; */
    /* font-weight: bold; */
  }
}
</style>
