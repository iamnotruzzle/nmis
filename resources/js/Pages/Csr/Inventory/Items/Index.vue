<template>
  <app-layout>
    <Head title="NMIS - Items" />

    <div class="card">
      <Toast />

      <DataTable
        class="p-datatable-sm"
        v-model:filters="filters"
        v-model:expandedRows="expandedRow"
        :value="itemsList"
        paginator
        :rows="20"
        dataKey="cl2comb"
        filterDisplay="row"
        sortField="cl2desc"
        :sortOrder="1"
        showGridlines
        removableSort
      >
        <template #header>
          <div class="flex flex-wrap align-items-center justify-content-between gap-2">
            <span class="text-xl text-900 font-bold text-primary">ITEMS</span>
            <div class="flex">
              <div class="mr-2">
                <!-- <div class="p-inputgroup">
                  <span class="p-inputgroup-addon">
                    <i class="pi pi-search"></i>
                  </span>
                  <InputText
                    id="searchInput"
                    v-model="filters['global'].value"
                    placeholder="Search item"
                  />
                </div> -->
              </div>
              <Button
                v-if="$page.props.auth.user.roles[0] == 'super-admin'"
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
        <Column expander />
        <Column
          field="mainCategory"
          header="MAIN CATEGORY"
          :showFilterMenu="false"
          style="width: 10%"
        >
          <template #body="{ data }">
            {{ data.mainCategory }}
          </template>
          <template #filter="{ filterModel, filterCallback }">
            <Dropdown
              v-model="filterModel.value"
              :options="pimsCategoryList"
              @change="filterCallback()"
              optionLabel="categoryname"
              optionValue="categoryname"
              placeholder="NO FILTER"
            />
          </template>
        </Column>
        <Column
          field="subCategory"
          header="SUB-CATEGORY"
          :showFilterMenu="false"
          sortable
          style="width: 15%"
        >
          <template #body="{ data }">
            {{ data.subCategory }}
          </template>
        </Column>
        <Column
          field="cl2desc"
          header="DESCRIPTION"
          :showFilterMenu="false"
          sortable
        >
          <template #body="{ data }">
            <span> {{ data.cl2desc }}</span>
          </template>
          <template #filter="{ filterModel, filterCallback }">
            <InputText
              id="searchInput"
              ref="searchInput"
              v-model="filters['global'].value"
              placeholder="Search item (ALT + 1)"
              size="large"
            />
          </template>
        </Column>
        <Column
          field="uomdesc"
          header="UNIT"
          :showFilterMenu="false"
          style="width: 5%"
        >
          <template #body="{ data }">
            {{ data.uomdesc }}
          </template>
          <template #filter="{ filterModel, filterCallback }">
            <Dropdown
              v-model="filterModel.value"
              :options="unitsList"
              @change="filterCallback()"
              :virtualScrollerOptions="{ itemSize: 38 }"
              filter
              optionLabel="uomdesc"
              optionValue="uomdesc"
              placeholder="NO FILTER"
            />
          </template>
        </Column>
        <Column
          field="cl2stat"
          header="STATUS"
          :showFilterMenu="false"
          style="width: 2%"
        >
          <template #body="{ data }">
            <div class="text-center">
              <Tag
                v-if="data.cl2stat == 'A'"
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
          <template #filter="{ filterModel, filterCallback }">
            <Dropdown
              v-model="filterModel.value"
              :options="statusFilter"
              @change="filterCallback()"
              optionLabel="name"
              optionValue="code"
              placeholder="NO FILTER"
            >
              <template #option="slotProps">
                <Tag
                  :value="slotProps.option.name"
                  :severity="statusSeverity(slotProps.option)"
                />
              </template>
            </Dropdown>
          </template>
        </Column>
        <Column
          header="ACTION"
          style="width: 5%"
        >
          <template #body="slotProps">
            <div class="flex flex-row justify-content-between align-content-around">
              <Button
                v-tooltip.top="'Update'"
                icon="pi pi-pencil"
                class="mr-2"
                rounded
                severity="warning"
                @click="editItem(slotProps.data)"
              />
              <Button
                v-if="slotProps.data.uomcode == 'box'"
                v-tooltip.top="'Convert'"
                rounded
                severity="success"
                @click="convertItem(slotProps.data)"
              >
                <template #icon>
                  <v-icon name="bi-arrow-left-right"></v-icon>
                </template>
              </Button>
              <Button
                v-if="slotProps.data.uomcode != 'box'"
                icon="pi pi-trash"
                rounded
                severity="danger"
                @click="confirmDeleteItem(slotProps.data)"
              />
            </div>
          </template>
        </Column>
        <template #expansion="slotProps">
          <div class="max-w-full flex justify-content-center">
            <div class="w-11 flex flex-column align-items-center">
              <DataTable
                paginator
                :rows="5"
                class="w-8 mt-2"
                showGridlines
                :value="slotProps.data.prices"
                size="small"
                sortField="created_at"
                :sortOrder="-1"
                removableSort
              >
                <template #header>
                  <div class="w-full flex flex-row justify-content-start">
                    <div class="text-lg font-bold my-3">
                      Prices for <span class="text-primary">[ {{ slotProps.data.cl2desc }} ]</span>
                    </div>
                  </div>
                  <div class="flex justify-content-end">
                    <Button
                      label="Add price"
                      severity="success"
                      icon="pi pi-plus"
                      iconPos="right"
                      @click="openPriceDialog(slotProps.data)"
                    />
                  </div>
                </template>
                <Column
                  field="price_per_unit"
                  header="PRICE PER UNIT"
                  style="width: 20%"
                  sortable
                >
                  <template #body="{ data }">
                    <span class="text-green-500"> {{ data.price_per_unit }}</span>
                  </template>
                </Column>
                <Column
                  field="entry_by"
                  header="ENTRY BY"
                  style="width: 20%"
                >
                </Column>
                <Column
                  field="created_at"
                  header="CREATED AT"
                  style="width: 20%"
                >
                  <template #body="{ data }">
                    <span v-if="data.created_at == null"></span>
                    <span v-else> {{ tzone(data.created_at) }}</span>
                  </template>
                </Column>
              </DataTable>

              <div class="w-11 flex flex-column">
                <div class="text-2xl font-bold mt-4 flex justify-content-around align-items-center w-full">
                  <span>Price changes</span>
                  <Dropdown
                    v-model="dateFilter"
                    :options="dateFilterList"
                    optionLabel="name"
                    optionValue="value"
                    class="w-full md:w-14rem"
                  />
                </div>

                <v-chart
                  class="h-30rem w-full ma-0 pa-0"
                  :option="priceChangesOptions(slotProps.data)"
                  autoresize
                />
              </div>
            </div>
          </div>
        </template>
      </DataTable>

      <!-- edit dialog -->
      <Dialog
        v-model:visible="createItemDialog"
        :style="{ width: '450px' }"
        :modal="true"
        class="p-fluid"
        @hide="clickOutsideDialog"
        dismissableMask
      >
        <template #header>
          <div class="text-primary text-xl font-bold">ITEM DETAIL</div>
        </template>
        <div class="field">
          <label for="mainCategory">Main category</label>
          <Dropdown
            v-model.trim="form.mainCategory"
            required="true"
            :options="pimsCategoryList"
            :virtualScrollerOptions="{ itemSize: 38 }"
            filter
            optionLabel="categoryname"
            optionValue="catID"
            class="w-full mb-3"
            :class="{ 'p-invalid': form.cl1comb == '' }"
          />
          <!-- <InputText
            v-model="form.selectedMainCat"
            readonly
          /> -->
          <small
            class="text-error"
            v-if="form.errors.unit"
          >
          </small>
        </div>
        <div class="field">
          <label for="cl1comb">Sub-Category</label>
          <Dropdown
            v-model.trim="form.cl1comb"
            required="true"
            :options="cl1combsList"
            :virtualScrollerOptions="{ itemSize: 38 }"
            filter
            optionValue="cl1comb"
            optionLabel="cl1desc"
            class="w-full mb-3"
            :class="{ 'p-invalid': form.cl1comb == '' }"
          />
          <!-- <InputText
            v-model="form.selectedSubCategory"
            readonly
          /> -->
          <small
            class="text-error"
            v-if="form.errors.cl1comb"
          >
            {{ form.errors.cl1comb }}
          </small>
        </div>
        <div class="field">
          <label>Item code</label>
          <InputText v-model="form.itemcode" />
          <small
            class="text-error"
            v-if="form.errors.itemcode"
          >
            {{ form.errors.itemcode }}
          </small>
        </div>
        <div class="field">
          <label>Description</label>
          <InputText
            id="Description"
            v-model.trim="form.cl2desc"
            required="true"
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
          <label for="unit">Unit</label>
          <Dropdown
            v-model.trim="form.unit"
            required="true"
            :options="unitsList"
            :virtualScrollerOptions="{ itemSize: 38 }"
            filter
            optionLabel="uomdesc"
            optionValue="uomcode"
            class="w-full mb-3"
            :class="{ 'p-invalid': form.cl1comb == '' }"
          />
          <small
            class="text-error"
            v-if="form.errors.unit"
          >
          </small>
        </div>
        <div class="field">
          <label for="cl2stat">Status</label>
          <Dropdown
            v-model="form.cl2stat"
            :options="cl2stats"
            optionLabel="name"
            optionValue="value"
            class="w-full"
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
      </Dialog>

      <!-- convert dialog -->
      <Dialog
        v-model:visible="convertDialog"
        :style="{ width: '450px' }"
        :modal="true"
        class="p-fluid"
        @hide="clickOutsideDialog"
        dismissableMask
      >
        <template #header>
          <div class="text-primary text-xl font-bold">CONVERT</div>
        </template>
        <div class="field">
          <label for="mainCategory">Main category</label>
          <InputText
            v-model="formConvert.selectedMainCat"
            readonly
          />
        </div>
        <div class="field">
          <label for="cl1comb">Sub-Category</label>
          <InputText
            v-model="formConvert.selectedSubCategory"
            readonly
          />
        </div>
        <div class="field">
          <label>Description</label>
          <InputText
            id="Description"
            v-model.trim="formConvert.cl2desc"
            required="true"
            autofocus
            :class="{ 'p-invalid': formConvert.cl2desc == '' }"
            @keyup.enter="submitConvert"
          />
        </div>
        <div class="field">
          <label for="unit">Unit</label>
          <Dropdown
            v-model.trim="formConvert.unit"
            required="true"
            :options="unitsList"
            :virtualScrollerOptions="{ itemSize: 38 }"
            filter
            optionLabel="uomdesc"
            optionValue="uomcode"
            class="w-full mb-3"
            :class="{ 'p-invalid': formConvert.cl1comb == '' }"
          />
        </div>
        <div class="field">
          <label for="cl2stat">Status</label>
          <Dropdown
            v-model="formConvert.cl2stat"
            :options="cl2stats"
            optionLabel="name"
            optionValue="value"
            class="w-full"
          />
          <small
            class="text-error"
            v-if="formConvert.errors.cl2stat"
          >
            {{ formConvert.errors.cl2stat }}
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
            label="Convert"
            icon="pi pi-check"
            text
            type="submit"
            :disabled="formConvert.processing || formConvert.cl2desc == null || formConvert.cl2desc == ''"
            @click="submitConvert"
          />
        </template>
      </Dialog>

      <!-- price dialog -->
      <Dialog
        v-model:visible="priceDialog"
        :style="{ width: '450px' }"
        :modal="true"
        class="p-fluid"
        @hide="clickOutsideDialog"
        dismissableMask
      >
        <template #header>
          <div class="text-primary text-xl font-bold">PRICE</div>
        </template>
        <div class="field">
          <label>Price per unit</label>
          <InputNumber
            id="Price"
            v-model="formPrice.price_per_unit"
            required="true"
            autofocus
            :class="{ 'p-invalid': formPrice.price_per_unit == '' }"
            :minFractionDigits="1"
            :maxFractionDigits="2"
            @keyup.enter="submitPrice"
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
          <Button
            label="Convert"
            icon="pi pi-check"
            text
            type="submit"
            :disabled="formPrice.processing || formPrice.price_per_unit == null || formPrice.price_per_unit == ''"
            @click="submitPrice"
          />
        </template>
      </Dialog>

      <!-- Delete confirmation dialog -->
      <Dialog
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
          <span v-if="form">Are you sure you want to delete this item?</span>
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
            :disabled="form.processing"
            @click="deleteItem"
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
import Textarea from 'primevue/textarea';
import InputNumber from 'primevue/inputnumber';
import Tag from 'primevue/tag';
import moment from 'moment';
import axios from 'axios';
import { use } from 'echarts/core';
import { CanvasRenderer } from 'echarts/renderers';
import { LineChart } from 'echarts/charts';
import { TitleComponent, TooltipComponent, LegendComponent, GridComponent } from 'echarts/components';
import VChart, { THEME_KEY } from 'vue-echarts';

use([CanvasRenderer, LineChart, TitleComponent, TooltipComponent, LegendComponent, GridComponent]);

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
    VChart,
    Tag,
  },
  props: {
    cl1combs: Array,
    pimsCategory: Array,
    units: Array,
    items: Array,
    prices: Array,
  },
  data() {
    return {
      // paginator
      loading: false,
      totalRecords: null,
      deleteItemDialog: false,
      rows: null,
      params: {},
      // end paginator
      search: null,
      // data table expand
      expandedRow: [],
      // end data table expand
      // paginator
      loading: false,
      rows: null,
      // end paginator
      authLocation: null,
      itemId: null,
      isUpdate: false,
      createItemDialog: false,
      convertDialog: false,
      dateFilter: 'this year',
      status: null,
      statusFilter: [
        { name: 'Active', code: 'A' },
        { name: 'Inactive', code: 'I' },
      ],
      maincat: null,
      mainCategoryFilter: [
        { name: 'Accountable forms', code: 'Accountable forms' },
        { name: 'Drugs and medicines', code: 'Drugs and medicines' },
        { name: 'IT supplies', code: 'IT supplies' },
        { name: 'Medical supplies', code: 'Medical supplies' },
        { name: 'Non-accountable Forms', code: 'Non-accountable Forms' },
        { name: 'Office Supplies', code: 'Office Supplies' },
        { name: 'Other Supplies and Materials', code: 'Other Supplies and Materials' },
      ],
      selectedCl1comb: null,
      subCategoryFilter: [],
      cl1desc: '',
      itemsList: [],
      cl1combsList: [],
      // TODO add quarterly filter
      pimsCategoryList: [],
      dateFilterList: [
        {
          name: 'yesterday',
          value: 'yesterday',
        },
        {
          name: 'today',
          value: 'today',
        },
        {
          name: 'this week',
          value: 'this week',
        },
        {
          name: 'this month',
          value: 'this month',
        },
        {
          name: 'this year',
          value: 'this year',
        },
      ],
      unitsList: [],
      filters: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
        // cl1comb: { value: null, matchMode: FilterMatchMode.CONTAINS },
        // cl2desc: { value: null, matchMode: FilterMatchMode.CONTAINS },
        cl2stat: { value: null, matchMode: FilterMatchMode.EQUALS },
        mainCategory: { value: null, matchMode: FilterMatchMode.EQUALS },
        uomdesc: { value: null, matchMode: FilterMatchMode.EQUALS },
      },
      cl2stats: [
        {
          name: 'ACTIVE',
          value: 'A',
        },
        {
          name: 'INACTIVE',
          value: 'I',
        },
      ],
      form: this.$inertia.form({
        cl2comb: null,
        cl1comb: null,
        cl2code: null,
        cl2desc: null,
        unit: null,
        cl2stat: null,
        mainCategory: null,
        itemcode: null,
        location: null,
        selectedMainCat: null,
        selectedSubCategory: null,
      }),
      formConvert: this.$inertia.form({
        cl2comb: null,
        cl1comb: null,
        cl2code: null,
        cl2desc: null,
        unit: null,
        cl2stat: null,
        mainCategory: null,
        location: null,
        selectedMainCat: null,
        selectedSubCategory: null,
      }),
      priceDialog: false,
      formPrice: this.$inertia.form({
        id: null,
        cl2comb: null,
        price_per_unit: null,
        entry_by: null,
      }),
    };
  },
  // created will be initialize before mounted
  //   created() {
  //     this.totalRecords = this.items.total;
  //     this.params.page = this.items.current_page;
  //     this.rows = this.items.per_page;
  //   },
  // created will be initialize before mounted
  mounted() {
    // console.log(this.$page.props.auth.user.roles[0]);
    this.storeCl1combsInContainer();
    this.storeItemInContainer();
    this.storeUnitsInContainer();
    this.storePimsCategoryInContainer();

    this.authLocation = this.$page.props;
    // console.log(this.authLocation.location.wardcode);

    this.loading = false;
    window.addEventListener('keydown', this.handleAlt1Shortcut);
  },
  beforeDestroy() {
    window.removeEventListener('keydown', this.handleAlt1Shortcut);
  },
  computed: {
    user() {
      return this.$page.props.auth.user;
    },
  },
  methods: {
    handleAlt1Shortcut(event) {
      if (event.altKey && event.key === '1') {
        this.$refs.searchInput.$el.focus();
      }
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
    restrictNonNumeric(event) {
      // Allow: backspace, delete, tab, escape, enter, and . (for decimal point)
      if (
        [46, 8, 9, 27, 13, 110, 190].includes(event.keyCode) ||
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
    statusSeverity(status) {
      //   console.log(status);
      switch (status.code) {
        case 'I':
          return 'danger';

        case 'A':
          return 'success';
      }
    },
    tzone(date) {
      return moment.tz(date, 'Asia/Manila').format('L');
    },
    onPage(event) {
      this.params.page = event.page + 1;
      this.updateData();
    },
    storePimsCategoryInContainer() {
      this.pimsCategory.forEach((e) => {
        this.pimsCategoryList.push({
          catID: e.catID,
          categoryname: e.categoryname,
        });
      });
    },
    storeCl1combsInContainer() {
      this.cl1combs.forEach((e) => {
        this.cl1combsList.push({
          cl1comb: e.cl1comb,
          cl1desc: e.cl1desc,
        });
      });
      // sort
      this.cl1combsList.sort((a, b) => {
        if (a.cl1desc < b.cl1desc) {
          return -1;
        }
        if (a.cl1desc > b.cl1desc) {
          return 1;
        }
        return 0;
      });
    },
    storeUnitsInContainer() {
      this.units.forEach((e) => {
        this.unitsList.push({
          uomcode: e.uomcode,
          uomdesc: e.uomdesc,
        });
      });
    },
    // use storeItemInContainer() function so that every time you make
    // server request such as POST, the data in the table
    // is updated
    storeItemInContainer() {
      // Loop through each item in this.items
      this.items.forEach((item) => {
        // console.log('items', this.items);
        // Find corresponding item in itemsList based on cl2comb
        const matchingItem = this.itemsList.find((listItem) => listItem.cl2comb === item.cl2comb);
        // console.log('match', matchingItem);
        if (matchingItem) {
          // If a matching item is found, iterate through prices array
          this.prices.forEach((price) => {
            // Check if price.cl2comb matches with matchingItem.cl2comb
            if (price.cl2comb === matchingItem.cl2comb) {
              // Push the price object into the prices array of matchingItem
              matchingItem.prices.push({
                id: price.id,
                cl2comb: price.cl2comb,
                price_per_unit: price.price_per_unit,
                ris_no: price.ris_no,
                hospital_price: price.hospital_price,
                acquisition_price: price.acquisition_price,
                itemcode: item.itemcode,
                entry_by: price.entry_by,
                created_at: price.created_at,
              });
            }
          });
        } else {
          // If no matching item is found, create a new item in itemsList with prices array
          this.itemsList.push({
            cl2comb: item.cl2comb,
            catID: item.catID,
            mainCategory: item.main_category,
            cl1comb: item.cl1comb,
            subCategory: item.sub_category,
            cl2code: item.cl2code,
            cl2desc: item.item,
            uomcode: item.uomcode,
            uomdesc: item.unit,
            cl2stat: item.cl2stat,
            itemcode: item.itemcode,
            prices: this.prices
              .filter((price) => price.cl2comb === item.cl2comb)
              .map((filteredPrice) => ({
                id: filteredPrice.id,
                cl2comb: filteredPrice.cl2comb,
                price_per_unit: filteredPrice.price_per_unit,
                ris_no: filteredPrice.ris_no,
                acquisition_price: filteredPrice.acquisition_price,
                hospital_price: filteredPrice.hospital_price,
                entry_by: filteredPrice.firstname + ' ' + filteredPrice.lastname,
                created_at: filteredPrice.created_at,
              })),
          });
        }
      });
    },
    priceChangesOptions(data) {
      // console.log('price data', data.prices);
      // sort the date to ascending order

      let result = data.prices;
      let priceDetails = [...result].sort((a, b) =>
        moment(a.created_at, 'DD-MM-YYYY, hh:mm:ss').diff(moment(b.created_at, 'DD-MM-YYYY, hh:mm:ss'))
      );
      //   console.log(priceDetails);
      let option = {
        grid: {
          show: true,
          left: '15%',
          top: '5%',
          right: '15%',
          bottom: '10%',
        },
        tooltip: {
          trigger: 'axis',
          valueFormatter: (value) => '₱ ' + value,
        },
        xAxis: {
          type: 'category',
          //   data: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
          data: [],
        },
        yAxis: {
          type: 'value',
        },
        series: [
          {
            // data: [150, 230, 224, 218, 135, 147, 260],
            data: [],
            type: 'line',
          },
        ],
      };

      moment.suppressDeprecationWarnings = true;
      switch (this.dateFilter) {
        case 'yesterday':
          priceDetails.forEach((e) => {
            if (e.length != 0) {
              let created_at = moment(e.created_at);

              // if created_ate is equal to yesterday
              if (moment(created_at).format('MM/DD/YYYY') === moment().subtract(1, 'days').format('MM/DD/YYYY')) {
                option.xAxis.data.push(moment(e.created_at).format('YYYY-MM-DD, hh:mm'));
                option.series[0].data.push(Number(e.price_per_unit).toFixed(2));
              }
            } else {
              option.xAxis.data.push(null);
              option.series.data.push(null);
            }
          });
          break;
        case 'today':
          priceDetails.forEach((e) => {
            if (e.length != 0) {
              let created_at = moment(e.created_at).format('MM/DD/YYYY');
              let today = moment().format('MM/DD/YYYY');
              if (moment(created_at).isSame(today)) {
                option.xAxis.data.push(moment(e.created_at).format('YYYY-MM-DD, hh:mm'));
                option.series[0].data.push(Number(e.price_per_unit).toFixed(2));
              }
            } else {
              option.xAxis.data.push(null);
              option.series.data.push(null);
            }
          });
          break;
        case 'this week':
          priceDetails.forEach((e) => {
            if (e.length != 0) {
              let created_at = moment(e.created_at).format('MM/DD/YYYY');
              if (moment(created_at).week() === moment().week()) {
                option.xAxis.data.push(moment(e.created_at).format('YYYY-MM-DD, hh:mm'));
                option.series[0].data.push(Number(e.price_per_unit).toFixed(2));
              }
            } else {
              option.xAxis.data.push(null);
              option.series.data.push(null);
            }
          });
          break;
        case 'this month':
          priceDetails.forEach((e) => {
            if (e.length != 0) {
              let created_at = moment(e.created_at).format('MM/DD/YYYY');
              if (moment(created_at).month() === moment().month()) {
                // option.xAxis.data.push(this.tzone(e.created_at));
                option.xAxis.data.push(moment(e.created_at).format('YYYY-MM-DD, hh:mm'));
                option.series[0].data.push(Number(e.price_per_unit).toFixed(2));
              }
            } else {
              option.xAxis.data.push(null);
              option.series.data.push(null);
            }
          });
          break;
        case 'this year':
          priceDetails.forEach((e) => {
            if (e.length != 0) {
              let created_at = moment(e.created_at).format('LL');
              if (moment(created_at).year() === moment().year()) {
                option.xAxis.data.push(moment(e.created_at).format('YYYY-MM-DD, hh:mm'));
                option.series[0].data.push(Number(e.price_per_unit).toFixed(2));
              }
            } else {
              option.xAxis.data.push(null);
              option.series.data.push(null);
            }
          });
          break;
        default:
          break;
      }

      return option;
    },
    updateData() {
      this.itemsList = [];
      this.loading = true;

      this.$inertia.get('items', this.params, {
        preserveState: true,
        preserveScroll: true,
        onFinish: (visit) => {
          this.totalRecords = this.items.total;
          this.itemsList = [];
          this.expandedRow = [];
          this.storeItemInContainer();
          this.loading = false;
        },
      });
    },
    confirmDeleteItem(item) {
      //   console.log(item.cl2comb);
      this.form.cl2comb = item.cl2comb;
      //   this.itemId = item.id;
      this.deleteItemDialog = true;
    },
    deleteItem() {
      this.form.delete(route('items.destroy', this.form.cl2comb), {
        preserveScroll: true,
        onSuccess: () => {
          this.updateData();
          this.deleteItemDialog = false;
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
    openPriceDialog(item) {
      //   console.log(item);
      this.formPrice.cl2comb = item.cl2comb;
      this.priceDialog = true;
    },
    // emit close dialog
    clickOutsideDialog() {
      this.$emit(
        'hide',
        (this.itemId = null),
        (this.isUpdate = false),
        this.form.clearErrors(),
        this.form.reset(),
        this.formConvert.reset(),
        this.formConvert.clearErrors(),
        this.formPrice.reset(),
        this.formPrice.clearErrors(),
        (this.priceDialog = false)
      );
    },
    editItem(item) {
      //   console.log(item);
      this.isUpdate = true;
      this.createItemDialog = true;
      this.itemId = item.cl2comb;
      this.form.cl2comb = item.cl2comb;
      this.form.cl1comb = item.cl1comb;
      this.form.cl2code = item.cl2code;
      this.form.cl2desc = item.cl2desc;
      this.form.unit = item.uomcode;
      this.form.cl2stat = item.cl2stat;
      this.form.itemcode = item.itemcode;
      this.form.mainCategory = item.catID;
      this.form.selectedMainCat = item.mainCategory;
      this.form.selectedSubCategory = item.subCategory;
    },
    convertItem(item) {
      //   console.log(item);

      this.convertDialog = true;
      this.formConvert.cl2comb = item.cl2comb;
      this.formConvert.cl1comb = item.cl1comb;
      this.formConvert.cl2code = item.cl2code;
      this.formConvert.cl2desc = item.cl2desc;
      this.formConvert.unit = item.uomcode;
      this.formConvert.cl2stat = item.cl2stat;
      this.formConvert.mainCategory = Number(item.catID);
      this.formConvert.selectedMainCat = item.mainCategory;
      this.formConvert.selectedSubCategory = item.subCategory;
    },
    submit() {
      if (this.form.processing) {
        return false;
      }

      //   this.form.location = this.authLocation.location.wardcode;

      if (this.isUpdate) {
        this.form.put(route('items.update', this.itemId), {
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
        this.form.post(route('items.store'), {
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
      //   console.log(this.$page.props.errors);
    },
    submitConvert() {
      //   if (this.formConvert.processing || this.formConvert.cl2desc == null || this.formConvert.cl2desc == '') {
      //     return false;
      //   }

      //   this.formConvert.location = this.authLocation.location.wardcode;

      //   console.log('formConvert', this.formConvert);

      this.formConvert.post(route('csrconvertitem.store'), {
        preserveScroll: true,
        onSuccess: () => {
          //   console.log('DONE');
          this.convertDialog = false;
          this.cancel();
          this.updateData();
          this.createdMsg();
        },
        onError: (error) => {
          console.log(error);
          //   this.convertDialog = false;
          //   this.cancel();
          //   this.updateData();
          //   this.createdMsg();
        },
      });

      //   console.log(this.$page.props.errors);
    },
    submitPrice() {
      if (this.formPrice.processing || this.formPrice.price_per_unit == null || this.formConvert.cl2desc == '') {
        return false;
      }

      this.formPrice.post(route('itemprices.store'), {
        preserveScroll: true,
        onSuccess: () => {
          //   console.log('DONE');
          this.convertDialog = false;
          this.cancel();
          this.updateData();
          this.createdPriceMsg();
        },
      });
    },
    cancel() {
      this.itemId = null;
      this.isUpdate = false;
      this.createItemDialog = false;
      this.convertDialog = false;
      this.priceDialog = false;
      this.deleteItemDialog = false;
      this.form.reset();
      this.form.clearErrors();
      this.formConvert.reset();
      this.formConvert.clearErrors();
      this.formPrice.reset();
      this.formPrice.clearErrors();
      this.itemsList = [];
      this.storeItemInContainer();
    },
    createdMsg() {
      this.$toast.add({ severity: 'success', summary: 'Success', detail: 'Item created', life: 3000 });
    },
    createdPriceMsg() {
      this.$toast.add({ severity: 'success', summary: 'Success', detail: 'Item price created', life: 3000 });
    },
    updatedMsg() {
      this.$toast.add({ severity: 'warn', summary: 'Success', detail: 'Item updated', life: 3000 });
    },
  },
  watch: {
    // search: function (val, oldVal) {
    //   this.params.search = val;
    //   this.updateData();
    // },
    // status: function (val, oldVal) {
    //   this.params.status = val;
    //   this.updateData();
    // },
    // maincat: function (val, oldVal) {
    //   this.params.maincat = val;
    //   this.updateData();
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
