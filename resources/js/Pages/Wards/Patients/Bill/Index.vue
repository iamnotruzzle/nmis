<template>
  <app-layout>
    <Head title="Template - Bill Patient" />

    <div class="card">
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
          :value="medicalSuppliesLisk"
          scrollable
          scrollHeight="h-full"
          showGridlines
          class="mdmt-4"
        >
          <template #header>
            <div class="text-2xl text-cyan-500 font-bold mb-2">INSTOCK</div>
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
      medicalSuppliesLisk: [],
      miscList: [],
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
      form: this.$inertia.form({
        password: null,
      }),
    };
  },
  mounted() {
    console.log(this.misc);
    this.storeBillsInContainer();
    this.getTotalAmount();
    this.storeCurrentStockInContainer();
    this.storeMiscInContainer();

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
    storeCurrentStockInContainer() {
      this.medicalSupplies.forEach((med) => {
        this.medicalSuppliesLisk.push({
          cl2comb: med.cl2comb,
          cl2desc: med.cl2desc,
          uomcode: med.uomcode == null ? null : med.uomcode,
          quantity: med.quantity,
          price: med.price,
        });
      });
      //   console.log('container', this.medicalSuppliesLisk);
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
      console.log('container', this.miscList);
    },
    // updateData() {
    //   this.categoriesList = [];
    //   this.loading = true;

    //   this.$inertia.get('patientcharge', this.params, {
    //     preserveState: true,
    //     preserveScroll: true,
    //     onFinish: (visit) => {
    //       this.billList = [];
    //       this.storeBillsInContainer();
    //     },
    //   });
    // },
    // emit close dialog
    openCreateBillDialog() {
      this.isUpdate = false;
      this.form.clearErrors();
      this.form.reset();
      this.createBillDialog = true;
    },
    clickOutsideDialog() {
      this.$emit('hide', this.form.clearErrors(), this.form.reset());
    },
    // submit() {
    //   if (this.isUpdate) {
    //     this.form.put(route('patientcharge.update', this.enccode), {
    //       preserveScroll: true,
    //       onSuccess: () => {
    //         this.createCategoryDialog = false;
    //         this.cancel();
    //         this.updateData();
    //         this.updatedMsg();
    //       },
    //     });
    //   } else {
    //     this.form.post(route('patientcharge.store'), {
    //       preserveScroll: true,
    //       onSuccess: () => {
    //         this.createCategoryDialog = false;
    //         this.cancel();
    //         this.updateData();
    //         this.createdMsg();
    //       },
    //     });
    //   }
    // },
    cancel() {
      this.cl1comb = null;
      this.isUpdate = false;
      this.createCategoryDialog = false;
      this.form.reset();
      this.form.clearErrors();
      this.categoriesList = [];
      this.storeProcTypesInContainer();
    },
    // createdMsg() {
    //   this.$toast.add({ severity: 'success', summary: 'Success', detail: 'Category created', life: 3000 });
    // },
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
