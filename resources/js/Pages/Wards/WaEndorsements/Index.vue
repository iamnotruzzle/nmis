<template>
  <app-layout>
    <Head title="NMIS - Stocks" />

    <div>
      <div
        class="card"
        style="border-top-left-radius: 0; border-top-right-radius: 0"
      >
        <Toast />

        <span class="text-xl text-900 font-bold text-primary">ENDORSEMENTS</span>

        <DataTable
          class="p-datatable-sm"
          v-model:expandedRows="expandedRow"
          v-model:filters="filters"
          :value="endorsementList"
          selectionMode="single"
          lazy
          paginator
          :rows="rows"
          removableSort
          ref="dt"
          :totalRecords="totalRecords"
          @page="onPage($event)"
          dataKey="id"
          filterDisplay="row"
          :loading="loading"
        >
          <template #header>
            <div class="flex flex-wrap align-items-center justify-content-end gap-2">
              <div class="flex">
                <!-- <div class="mr-2">
                  <div class="p-inputgroup">
                    <span class="p-inputgroup-addon">
                      <i class="pi pi-search"></i>
                    </span>
                    <InputText
                      id="searchInput"
                      v-model="search"
                      placeholder="Search"
                    />
                  </div>
                </div> -->
                <Button
                  label="Endorsement"
                  icon="pi pi-plus"
                  class="mr-2"
                  @click="openCreateEndorsementDialog"
                />
              </div>
            </div>
          </template>
          <Column
            expander
            style="width: 5%"
          />
          <template #empty> No endorsement found. </template>
          <template #loading> Loading endorsement data. Please wait. </template>
          <Column
            header="CREATED AT"
            filterField="created_at"
            style="width: 20%"
            :showFilterMenu="false"
          >
            <template #body="{ data }">
              {{ tzone(data.created_at) }}
            </template>
            <template #filter="{}">
              <Calendar
                v-model="from"
                dateFormat="mm-dd-yy"
                placeholder="FROM"
                showIcon
                showButtonBar
                :manualInput="false"
                :hideOnDateTimeSelect="true"
              />
              <div class="mt-2"></div>
              <Calendar
                v-model="to"
                dateFormat="mm-dd-yy"
                placeholder="TO"
                showIcon
                showButtonBar
                :manualInput="false"
                :hideOnDateTimeSelect="true"
              />
            </template>
          </Column>
          <Column
            header="FROM"
            style="width: 30%"
          >
            <template #body="{ data }">
              <div class="flex flex-row align-items-center">
                <span class="font-semibold text-xl pl-3">
                  {{ data.from_user_name }}
                </span>
              </div>
            </template>
          </Column>
          <Column
            header="TO"
            style="width: 30%"
          >
            <template #body="{ data }">
              <div class="flex flex-row align-items-center">
                <span class="font-semibold text-xl pl-3">
                  {{ data.to_user_name }}
                </span>
              </div>
            </template>
          </Column>
          <Column
            header="ACTION"
            style="width: 5%"
          >
            <template #body="slotProps">
              <div class="flex justify-content-around align-content-center">
                <v-icon
                  name="pr-pencil"
                  class="text-yellow-500 text-xl"
                  @click="editEndorsement(slotProps.data)"
                ></v-icon>

                <v-icon
                  v-if="slotProps.data.from_user == $page.props.auth.user.userDetail.employeeid"
                  name="fc-cancel"
                  class="text-red-500 text-xl"
                  @click="confirmCancelItem(slotProps.data)"
                ></v-icon>
              </div>
            </template>
          </Column>
          <template #expansion="slotProps">
            <div class="p-3 w-full flex justify-content-center">
              <DataTable
                paginator
                removableSort
                showGridlines
                :rows="5"
                :value="slotProps.data.endorsementDetails"
              >
                <Column header="TAG">
                  <template #body="{ data }">
                    <span class="font-bold"> {{ data.tag }}</span>
                  </template>
                </Column>
                <Column
                  header="DESCRIPTION"
                  width="70%"
                >
                  <template #body="{ data }">
                    <span> {{ data.description }}</span>
                  </template>
                </Column>
                <Column header="STATUS">
                  <template #body="{ data }">
                    <Tag
                      v-if="data.status == 'CANCELLED'"
                      :value="data.status"
                      severity="danger"
                    />
                    <Tag
                      v-if="data.status == 'PENDING'"
                      :value="data.status"
                      severity="secondary"
                    />
                    <Tag
                      v-if="data.status == 'ONGOING'"
                      :value="data.status"
                      severity="warning"
                    />
                    <Tag
                      v-if="data.status == 'COMPLETED'"
                      :value="data.status"
                      severity="success"
                    />
                  </template>
                </Column>
              </DataTable>
            </div>
          </template>
        </DataTable>

        <!-- @hide="clickOutsideDialog" -->
        <!-- create & edit dialog -->
        <Dialog
          v-model:visible="createEndorsementDialog"
          :modal="true"
          class="p-fluid w-5"
          :closeOnEscape="false"
          @hide="whenDialogIsHidden"
        >
          <template #header>
            <div class="text-primary text-xl font-bold">ENDORSEMENT</div>
          </template>
          <div class="field">
            <label for="to_user">To</label>
            <Dropdown
              id="to_user"
              v-model.trim="form.to_user"
              required="true"
              :options="employeesList"
              :virtualScrollerOptions="{ itemSize: 38 }"
              filter
              optionLabel="name"
              optionValue="employeeid"
              class="w-full mb-3"
              :class="{ 'p-invalid': form.to_user == '' }"
              showClear
            />
            <small
              class="text-error"
              v-if="form.errors.to_user"
            >
              {{ form.errors.to_user }}
            </small>
          </div>

          <div class="border-1 p-2 border-dotted">
            <!-- <h1>LIST OF ENDORSEMENTS</h1> -->

            <Accordion
              :activeIndex="activeAccordionIndex"
              @tab-change="activeAccordionIndex = $event.index"
              expandIcon="pi pi-plus"
              collapseIcon="pi pi-minus"
              class="pa-0 ma-0"
            >
              <AccordionTab
                v-for="(endorse, index) in form.endorsementDetails"
                :key="index"
              >
                <template #header>
                  <div class="flex align-items-center justify-content-between w-full">
                    <div class="text-white uppercase">
                      Item {{ index + 1 }} -
                      <span
                        v-if="endorse.status == 'CANCELLED'"
                        class="text-error"
                      >
                        {{ endorse.tag }}
                      </span>
                      <span
                        v-else-if="endorse.status == 'PENDING'"
                        class="text-color-secondary"
                      >
                        {{ endorse.tag }}
                      </span>
                      <span
                        v-else-if="endorse.status == 'ONGOING'"
                        class="text-yellow-500"
                      >
                        {{ endorse.tag }}
                      </span>
                      <span
                        v-else-if="endorse.status == 'COMPLETED'"
                        class="text-green-500"
                      >
                        {{ endorse.tag }}
                      </span>
                      <span v-else> NO TAG </span>
                    </div>
                    <Tag
                      :value="endorse.status || 'No Status'"
                      :severity="statusSeverity({ code: endorse.status })"
                      rounded
                      class="mr-2"
                    />
                  </div>
                </template>
                <TextArea
                  v-model="endorse.description"
                  rows="6"
                  class="w-full mb-2"
                  placeholder="Enter description"
                />

                <div class="field flex gap-2">
                  <Dropdown
                    v-model="endorse.tag"
                    :options="tagFilter"
                    optionLabel="name"
                    optionValue="code"
                    placeholder="TAG"
                    class="mr-2"
                  >
                    <template #option="slotProps">
                      <Tag :value="slotProps.option.name" />
                    </template>
                  </Dropdown>
                  <Dropdown
                    v-model="endorse.status"
                    :options="statusFilter"
                    optionLabel="name"
                    optionValue="code"
                    placeholder="STATUS"
                  >
                    <template #option="slotProps">
                      <Tag
                        :value="slotProps.option.name"
                        :severity="statusSeverity(slotProps.option)"
                      />
                    </template>
                  </Dropdown>
                </div>

                <Button
                  icon="pi pi-trash"
                  label="Remove"
                  severity="danger"
                  v-if="form.endorsementDetails.length > 1"
                  @click="removeEndorse(index)"
                />
              </AccordionTab>
            </Accordion>

            <Button
              label="Add Endorsement Item"
              icon="pi pi-plus"
              class="mt-3"
              @click="addMore"
            />
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
              v-if="isUpdate == true"
              :disabled="form.processing || form.endorsementDetails.length < 1"
              :label="!form.processing ? 'UPDATE' : 'UPDATE'"
              :icon="form.processing ? 'pi pi-spin pi-spinner' : 'pi pi-check'"
              severity="warning"
              type="submit"
              @click="submit"
            />
            <Button
              v-else
              :label="!form.processing ? 'ENDORSE' : 'ENDORSE'"
              :icon="form.processing ? 'pi pi-spin pi-spinner' : 'pi pi-check'"
              :disabled="
                form.processing ||
                form.to_user == null ||
                form.endorsementDetails.length < 1 ||
                form.endorsementDetails.some((e) => !e.description || !e.tag || !e.status)
              "
              type="submit"
              @click="submit"
            />
          </template>
        </Dialog>

        <!-- Cancel confirmation dialog -->
        <Dialog
          v-model:visible="cancelItemDialog"
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
            <span v-if="form">Are you sure you want to cancel this endorsement?</span>
          </div>
          <template #footer>
            <Button
              label="NO"
              icon="pi pi-times"
              :disabled="form.processing"
              severity="danger"
              @click="cancelItemDialog = false"
            />

            <Button
              :disabled="form.processing"
              :label="!form.processing ? 'YES' : 'YES'"
              :icon="form.processing ? 'pi pi-spin pi-spinner' : 'pi pi-check'"
              type="submit"
              @click="cancelItem"
            />

            <!-- <Button
              label="Yes"
              icon="pi pi-check"
              severity="danger"
              text
              :disabled="form.processing"
              @click="cancelItem"
            /> -->
          </template>
        </Dialog>
      </div>
    </div>
  </app-layout>
</template>

<script>
import { FilterMatchMode } from 'primevue/api';
import { router } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputText from 'primevue/inputtext';
import Accordion from 'primevue/accordion';
import AccordionTab from 'primevue/accordiontab';
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
import axios from 'axios';

export default {
  components: {
    AppLayout,
    Head,
    InputText,
    Accordion,
    AccordionTab,
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
    endorsements: Object,
    employees: Object,
  },
  data() {
    return {
      authWardcode: '',
      expandedRow: [],
      // paginator
      loading: false,
      totalRecords: null,
      rows: null,
      // end paginator,
      countdown: 0,
      endorsement_id: null,
      isUpdate: false,
      createEndorsementDialog: false,
      editStatusDialog: false,
      cancelItemDialog: false,
      search: '',
      selectedItemsUomDesc: null,
      oldQuantity: 0,
      options: {},
      params: {},
      from: null,
      to: null,
      stockBalanceDeclared: false,
      noStockLevel: false,
      employeesList: [],
      // stock list details
      endorsementDetailsFilter: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
      endorsementList: [],
      item: null,
      cl2desc: null,
      requested_qty: null,
      approved_qty: null,
      itemNotSelected: false,
      itemNotSelectedMsg: null,
      // end stock list details
      filters: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
      status: null,
      statusFilter: [
        { name: 'CANCELLED', code: 'CANCELLED' },
        { name: 'PENDING', code: 'PENDING' },
        { name: 'ONGOING', code: 'ONGOING' },
        { name: 'COMPLETED', code: 'COMPLETED' },
      ],
      tag: null,
      tagFilter: [
        { name: 'EQUIPMENT', code: 'EQUIPMENT' },
        { name: 'MEDICAL SUPPLY', code: 'MEDICAL SUPPLY' },
        { name: 'MEDICAL TANK', code: 'MEDICAL TANK' },
        { name: 'JORS', code: 'JORS' },
        { name: 'OTHERS', code: 'OTHERS' },
      ],
      form: this.$inertia.form({
        id: null,
        from_user: null,
        to_user: null,
        wardcode: null,
        // description, tag (Equipment, Medical Supply, JORS, Medical tank, Others), status ('CANCELLED', PENDING, ONGOING COMPLETED),
        endorsementDetails: [],
      }),
      activeAccordionIndex: 0,
      formUpdateStatus: this.$inertia.form({
        request_stock_id: null,
        status: null,
      }),
      previousQty: 0,
      targetItemDesc: null,
    };
  },
  // created will be initialize before mounted
  created() {
    this.totalRecords = this.endorsements.total;
    this.params.page = this.endorsements.current_page;
    this.rows = this.endorsements.per_page;
  },
  mounted() {
    this.authWardcode = this.$page.props.auth.user.location.location_name.wardcode;

    this.storeEndorsementsInContainer();
    this.storeEmployeesInContainer();

    this.loading = false;
  },
  computed: {
    user() {
      return this.$page.props.auth.user;
    },
  },
  methods: {
    addMore() {
      this.form.endorsementDetails.push({
        description: '',
        tag: null,
        status: null,
      });
      // Automatically open the last added item and close others
      this.activeAccordionIndex = this.form.endorsementDetails.length - 1;
    },
    removeEndorse(index) {
      this.form.endorsementDetails.splice(index, 1);
    },
    statusSeverity(status) {
      //   console.log(status);
      switch (status.code) {
        case 'CANCELLED':
          return 'danger';

        case 'PENDING':
          return 'secondary';

        case 'ONGOING':
          return 'warning';

        case 'COMPLETED':
          return 'success';
      }
    },
    storeEmployeesInContainer() {
      this.employees.forEach((e) => {
        // console.log(e);
        this.employeesList.push({
          employeeid: e.employeeid,
          name: '(' + e.employeeid + ') - ' + e.firstname + ' ' + e.lastname,
        });
      });
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
    // use storeEndorsementsInContainer() function so that every time you make
    // server request such as POST, the data in the table
    // is updated
    storeEndorsementsInContainer() {
      this.endorsementList = []; // reset

      this.endorsements.data.forEach((e) => {
        this.endorsementList.push({
          id: e.id,
          from_user: e.from_user.employeeid,
          from_user_name: e.from_user.firstname + ' ' + e.from_user.middlename + ' ' + e.from_user.lastname,
          to_user: e.to_user.employeeid,
          to_user_name: e.to_user.firstname + ' ' + e.to_user.middlename + ' ' + e.to_user.lastname,
          wardcode: e.ward.wardcode,
          wardname: e.ward.wardname,
          created_at: e.created_at,
          endorsementDetails: e.details,
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
      this.loading = true;

      this.$inertia.get('wa-endorse', this.params, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: (visit) => {
          this.totalRecords = this.endorsements.total;
          this.endorsementList = [];
          this.expandedRow = [];
          this.storeEndorsementsInContainer();
          this.loading = false;
          this.formUpdateStatus.reset();
        },
      });
    },
    openCreateEndorsementDialog() {
      this.isUpdate = false;
      this.form.clearErrors();
      this.form.reset();
      this.endorsement_id = null;
      this.createEndorsementDialog = true;
    },
    // when dialog is hidden, do this function
    whenDialogIsHidden() {
      this.$emit(
        'hide',
        (this.noStockLevel = false),
        (this.endorsement_id = null),
        (this.isUpdate = false),
        (this.item = null),
        (this.cl2desc = null),
        (this.requested_qty = null),
        (this.approved_qty = null),
        (this.itemNotSelected = null),
        (this.itemNotSelectedMsg = null),
        (this.targetItemDesc = null),
        (this.selectedItemsUomDesc = ''),
        (this.oldQuantity = 0),
        this.form.clearErrors(),
        this.form.reset(),
        this.formUpdateStatus.reset()
      );
    },
    editEndorsement(item) {
      console.log(item);
      this.form.id = item.id;
      this.form.from_user = item.from_user;
      this.form.to_user = item.to_user;

      this.isUpdate = true;
      this.createEndorsementDialog = true;

      item.endorsementDetails.forEach((e) => {
        this.form.endorsementDetails.push({
          id: e.id,
          endorsement_id: e.endorsement_id,
          tag: e.tag,
          description: e.description,
          status: e.status,
        });
      });
    },
    editStatus(item) {
      this.editStatusDialog = true;
      this.formUpdateStatus.request_stock_id = item.id;
      this.formUpdateStatus.status = 'RECEIVED';
    },
    updateStatus() {
      //   this.formUpdateStatus.put(route('requeststocks.updatedeliverystatus', this.formUpdateStatus), {
      //     preserveScroll: true,
      //     onSuccess: () => {
      //       this.endorsement_id = null;
      //       this.editStatusDialog = false;
      //       this.cancel();
      //       this.updateData();
      //       this.updatedStatusMsg();
      //       this.loading = false;
      //     },
      //   });
    },
    submit() {
      if (this.form.processing) {
        return false;
      }

      // setup location, requested by and endorsementDetails before submitting
      this.form.wardcode = this.authWardcode;
      this.form.from_user = this.user.userDetail.employeeid;

      if (this.isUpdate) {
        this.form.put(route('wa-endorse.update', this.form.id), {
          preserveScroll: true,
          onSuccess: () => {
            this.createEndorsementDialog = false;
            this.cancel();
            this.updateData();
            this.updatedMsg();
            this.loading = false;
          },
        });
      } else {
        this.form.post(route('wa-endorse.store'), {
          preserveScroll: true,
          onSuccess: () => {
            this.createEndorsementDialog = false;
            this.cancel();
            this.updateData();
            this.createdMsg();
            this.loading = false;
          },
        });
      }
    },
    confirmCancelItem(item) {
      this.form.id = item.id;
      this.cancelItemDialog = true;
    },
    cancelItem() {
      this.form.delete(route('wa-endorse.destroy', this.form.id), {
        preserveScroll: true,
        onSuccess: () => {
          this.loading = false;
          this.cancelItemDialog = false;
          this.form.clearErrors();
          this.form.reset();
          this.updateData();
          this.cancelledMsg();
        },
      });
    },
    cancel() {
      this.endorsement_id = null;
      this.isUpdate = false;
      this.createEndorsementDialog = false;
      this.editStatusDialog = false;
      this.noStockLevel = false;
      this.targetItemDesc = null;
      this.oldQuantity = 0;
      this.selectedItemsUomDesc = '';
      this.form.reset();
      this.form.clearErrors();
    },
    convertedMsg() {
      this.$toast.add({ severity: 'success', summary: 'Success', detail: 'Item converted.', life: 3000 });
    },
    createdMsg() {
      this.$toast.add({ severity: 'success', summary: 'Success', detail: 'Endorsement created', life: 3000 });
    },
    updatedMsg() {
      this.$toast.add({ severity: 'warn', summary: 'Success', detail: 'Endorsement updated', life: 3000 });
    },
    updatedStatusMsg() {
      this.$toast.add({ severity: 'warn', summary: 'Success', detail: 'Changed requested stocks status', life: 3000 });
    },
    cancelledMsg() {
      this.$toast.add({ severity: 'error', summary: 'Success', detail: 'Endorsement cancelled.', life: 3000 });
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
    // end ward stocks logs
  },
  watch: {
    createEndorsementDialog(val) {
      if (val) {
        if (this.form.endorsementDetails.length === 0) {
          this.addMore();
        }
      }
    },
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
</style>
