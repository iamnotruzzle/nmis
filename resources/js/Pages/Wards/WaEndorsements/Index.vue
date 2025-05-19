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
                <div class="mr-2">
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
                </div>
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
            field="status"
            header="STATUS"
            style="text-align: center; width: 10%"
            :pt="{ headerContent: 'justify-content-center' }"
          >
            <template #body="{ data }">
              <div class="flex justify-content-center align-content-center">
                <Tag
                  v-if="data.status == 'PENDING'"
                  :value="data.status"
                  severity="contrast"
                />
                <Tag
                  v-if="data.status == 'ACKNOWLEDGED'"
                  :value="data.status"
                  severity="primary"
                />
                <Tag
                  v-if="data.status == 'FILLED'"
                  :value="data.status"
                  severity="info"
                />
                <Tag
                  v-if="data.status == 'RECEIVED'"
                  :value="data.status"
                  severity="success"
                />
                <Tag
                  v-if="data.status == 'CANCELLED'"
                  :value="data.status"
                  severity="danger"
                />

                <Button
                  v-if="data.status == 'FILLED'"
                  label="RECEIVE"
                  icon="pi pi-check"
                  class="ml-2"
                  severity="success"
                  @click="editStatus(data)"
                />
              </div>
            </template>
          </Column>
          <Column
            field="requested_by"
            header="REQUESTED BY"
            style="width: 30%"
          >
            <template #body="{ data }">
              <div class="flex flex-row align-items-center">
                <!-- <img
                  v-if="data.requested_by_image != null"
                  :src="`storage/${data.requested_by_image}`"
                  class="w-3rem h-3rem rounded-card"
                />
                <img
                  v-else
                  src="images/no_profile.png"
                  class="w-3rem h-3rem rounded-card"
                /> -->

                <span class="font-semibold text-xl pl-3">
                  {{ data.requested_by }}
                </span>
              </div>
            </template>
          </Column>
          <Column
            field="approved_by"
            header="APPROVED BY"
            style="width: 30%"
          >
            <template #body="{ data }">
              <div class="flex flex-row align-items-center">
                <!-- <img
                  v-if="data.approved_by_image != null"
                  :src="`storage/${data.approved_by_image}`"
                  class="w-3rem h-3rem rounded-card"
                />

                <img
                  v-if="data.approved_by != null && data.approved_by_image == null"
                  src="images/no_profile.png"
                  class="w-3rem h-3rem rounded-card"
                /> -->

                <span class="font-semibold text-xl pl-3">
                  {{ data.approved_by }}
                </span>
              </div>
            </template>
          </Column>
          <Column header="PRINT">
            <template #body="slotProps">
              <div class="flex justify-content-around align-content-center">
                <v-icon
                  name="la-receipt-solid"
                  class="text-green-500 text-8xl"
                  @click="print(slotProps.data)"
                ></v-icon>
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
                  v-if="slotProps.data.status == 'PENDING'"
                  name="pr-pencil"
                  class="text-yellow-500 text-xl"
                  @click="editEndorsementedStock(slotProps.data)"
                ></v-icon>

                <v-icon
                  v-if="slotProps.data.status == 'PENDING' || slotProps.data.status == 'ACKNOWLEDGED'"
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
                :value="slotProps.data.request_stocks_details"
              >
                <Column
                  field="item"
                  header="ITEM"
                  style="width: 60%"
                >
                  <template #body="{ data }">
                    <span> {{ data.item_details.cl2desc }}</span>
                  </template>
                </Column>
                <Column
                  header="PENDING QTY"
                  style="text-align: right; width: 10%"
                  :pt="{ headerContent: 'justify-content-end' }"
                >
                  <template #body="{ data }">
                    <span class="text-blue-500">{{ data.requested_qty }} </span>
                  </template>
                </Column>
                <Column
                  field="approved_qty"
                  header="APPROVED QTY"
                  style="text-align: right; width: 10%"
                  :pt="{ headerContent: 'justify-content-end' }"
                >
                  <template #body="{ data }">
                    <span class="text-green-500">{{ data.approved_qty }} </span>
                  </template>
                </Column>
                <Column
                  field="remarks"
                  header="REMARKS"
                  style="width: 20%; text-align: center"
                  :pt="{ headerContent: 'justify-content-center' }"
                ></Column>
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
            <label for="to">To</label>
            <Dropdown
              id="to"
              v-model.trim="form.to"
              required="true"
              :options="employeesList"
              :virtualScrollerOptions="{ itemSize: 38 }"
              filter
              optionLabel="name"
              optionValue="employeeid"
              class="w-full mb-3"
              :class="{ 'p-invalid': form.to == '' }"
              showClear
            />
            <small
              class="text-error"
              v-if="form.errors.to"
            >
              {{ form.errors.to }}
            </small>
          </div>

          <Accordion
            multiple
            :activeIndex="[0]"
            expandIcon="pi pi-plus"
            collapseIcon="pi pi-minus"
            class="pa-0 ma-0"
          >
            <AccordionTab
              v-for="(endorse, index) in form.endorsementDetails"
              :key="index"
              :header="`Item ${index + 1} - ${endorse.tag || 'No Tag'} (${endorse.status || 'No Status'})`"
            >
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
              :disabled="form.processing || endorsementDetails == '' || endorsementDetails == null"
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
              :disabled="form.processing || endorsementDetails == '' || endorsementDetails == null"
              type="submit"
              @click="submit"
            />
          </template>
        </Dialog>

        <!-- edit status confirmation dialog -->
        <Dialog
          v-model:visible="editStatusDialog"
          :style="{ width: '450px' }"
          header=" "
          :modal="true"
          dismissableMask
        >
          <div class="flex align-items-center justify-content-center">
            <i
              class="pi pi-exclamation-triangle mr-3"
              style="font-size: 5rem; color: red"
            />

            <div v-if="form">
              <p class="text-justify text-xl">
                Upon clicking <b class="text-green-500">"Yes,"</b> the items will be added to your inventory, and
                <b class="text-green-500">your name</b> will be recorded as the person who received the items and
                verified that the quantity received is correct. Please call the
                <b class="text-primary">Central Supply Unit</b> if the items or their quantities are incorrect.
              </p>
              <p class="text-justify text-xl">Are you sure you want to receive these items?</p>
            </div>
          </div>
          <template #footer>
            <Button
              :label="!formUpdateStatus.processing ? 'CANCEL' : 'CANCEL'"
              icon="pi pi-times"
              :disabled="formUpdateStatus.processing"
              severity="danger"
              @click="editStatusDialog = false"
            />

            <Button
              :disabled="formUpdateStatus.processing"
              :label="!formUpdateStatus.processing ? 'YES' : 'YES'"
              :icon="formUpdateStatus.processing ? 'pi pi-spin pi-spinner' : 'pi pi-check'"
              severity="success"
              @click="updateStatus"
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
            <span v-if="form">Are you sure you want to cancel this request?</span>
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

      <!-- RIS docs -->
      <div
        id="print"
        style="font-family: Arial, sans-serif; background-color: white; color: black; white; display: none;"
      >
        <div style="font-family: Arial, sans-serif">
          <div>
            <h3 style="font-weight: bold; text-align: center">REQUISITION AND ISSUE SLIP</h3>
          </div>
          <div style="display: flex; width: 100%; font-size: 1rem">
            <div style="width: 50%"><span>Entity Name:</span></div>
            <div style="width: 50%"><span>Fund Cluster:</span></div>
          </div>
          <div style="margin-top: 1rem">
            <h4>MARIANO MARCOS MEMORIAL HOSPITAL AND MEDICAL CENTER</h4>
          </div>
        </div>

        <table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif">
          <tbody>
            <tr>
              <td
                colspan="3"
                style="border: 1px solid black"
              >
                <p><strong>Division: NURSING</strong></p>
              </td>
              <td
                colspan="5"
                style="border: 1px solid black"
              >
                <p><strong>Responsibility Center Code 13-001-14-00017-03-01-06-02</strong></p>
              </td>
            </tr>
            <tr>
              <td
                colspan="3"
                style="border: 1px solid black"
              >
                <p>
                  <strong>Office: {{ printForm.office }}</strong>
                </p>
              </td>
              <td
                colspan="5"
                style="border: 1px solid black"
              >
                <p>
                  <strong>RIS NO.: {{ printForm.ris_no }}</strong>
                </p>
              </td>
            </tr>
            <tr>
              <td
                colspan="3"
                style="border: 1px solid black; text-align: center"
              >
                <p><strong>Requisition</strong></p>
              </td>
              <td
                colspan="2"
                style="border: 1px solid black; text-align: center"
              >
                <p><strong>Stock Available?</strong></p>
              </td>
              <td
                colspan="2"
                style="border: 1px solid black; text-align: center"
              >
                <p><strong>Issue</strong></p>
              </td>
            </tr>
            <tr>
              <td style="border: 1px solid black; text-align: center">
                <p><strong>Stock No.</strong></p>
              </td>
              <td style="border: 1px solid black; text-align: center">
                <p><strong>Description</strong></p>
              </td>
              <td style="border: 1px solid black; text-align: center">
                <p><strong>Quantity</strong></p>
              </td>
              <td style="border: 1px solid black; text-align: center">
                <p><strong>Yes</strong></p>
              </td>
              <td style="border: 1px solid black; text-align: center">
                <p><strong>No</strong></p>
              </td>
              <td style="border: 1px solid black; text-align: center">
                <p><strong>Quantity</strong></p>
              </td>
              <td style="border: 1px solid black; text-align: center">
                <p><strong>Remarks</strong></p>
              </td>
            </tr>
            <tr v-for="(item, index) in this.printForm.items">
              <td style="border: 1px solid black; text-align: center">
                <p>{{ item.stock_no }}</p>
              </td>
              <td style="border: 1px solid black; text-align: center">
                <p>{{ item.description }}</p>
              </td>
              <td style="border: 1px solid black; text-align: center">
                <p>{{ item.req_qty }}</p>
              </td>
              <td style="border: 1px solid black; text-align: center">
                <!-- yes -->
              </td>
              <td style="border: 1px solid black; text-align: center">
                <!-- no -->
              </td>
              <td style="border: 1px solid black; text-align: center">
                <p>{{ item.issue_qty }}</p>
              </td>
              <td style="border: 1px solid black; text-align: center">
                <p>{{ item.remarks }}</p>
              </td>
            </tr>
            <tr>
              <td
                colspan="8"
                style="border: 1px solid black; padding: 10px 0"
              >
                <p>
                  Purpose:
                  <strong>
                    <u>{{ printForm.office }}</u>
                  </strong>
                </p>
              </td>
            </tr>
          </tbody>
        </table>

        <table style="border-collapse: collapse; width: 100%; font-family: Arial, sans-serif">
          <tr>
            <td style="border: 1px solid black">
              <p>&nbsp;</p>
            </td>
            <td
              colspan="2"
              style="border: 1px solid black; text-align: center"
            >
              <p><strong>Endorsemented by:</strong></p>
            </td>
            <td style="border: 1px solid black; text-align: center">
              <p><strong>Approved by:</strong></p>
            </td>
            <td style="border: 1px solid black; text-align: center">
              <p><strong>Issued by:</strong></p>
            </td>
            <td
              colspan="3"
              style="border: 1px solid black; text-align: center"
            >
              <p><strong>Received by:</strong></p>
            </td>
          </tr>
          <tr>
            <td style="border: 1px solid black; padding: 20px 0px">
              <p><strong>Signature:</strong></p>
            </td>
            <td
              colspan="2"
              style="border: 1px solid black; padding: 20px 0px"
            >
              <p>&nbsp;</p>
            </td>
            <td style="border: 1px solid black; padding: 20px 0px">
              <p>&nbsp;</p>
            </td>
            <td style="border: 1px solid black; padding: 20px 0px">
              <p>&nbsp;</p>
            </td>
            <td
              colspan="3"
              style="border: 1px solid black; padding: 20px 0px"
            >
              <p>&nbsp;</p>
            </td>
          </tr>
          <tr>
            <td style="border: 1px solid black">
              <p><strong>Printed name:&nbsp;</strong></p>
            </td>
            <td
              colspan="2"
              style="border: 1px solid black; text-align: center"
            >
              <p>{{ printForm.requested_by }}</p>
            </td>
            <td style="border: 1px solid black; text-align: center">
              <span>Efleda Sarah V. Marders, RN, MAN</span>
            </td>
            <td style="border: 1px solid black; text-align: center">
              <p>David Bagaoisan</p>
            </td>
            <td
              colspan="3"
              style="border: 1px solid black"
            >
              <p>&nbsp;</p>
            </td>
          </tr>
          <tr>
            <td style="border: 1px solid black">
              <p><strong>Designation:</strong></p>
            </td>
            <td
              colspan="2"
              style="border: 1px solid black"
            >
              <p>&nbsp;</p>
            </td>
            <td style="border: 1px solid black; text-align: center">
              <span>CHIEF NURSE VII</span>
            </td>
            <td style="border: 1px solid black">
              <p>&nbsp;</p>
            </td>
            <td
              colspan="3"
              style="border: 1px solid black"
            >
              <p>&nbsp;</p>
            </td>
          </tr>
          <tr>
            <td style="border: 1px solid black">
              <p><strong>Date:</strong></p>
            </td>
            <td
              colspan="2"
              style="border: 1px solid black"
            >
              <p>&nbsp;</p>
            </td>
            <td style="border: 1px solid black">
              <p>&nbsp;</p>
            </td>
            <td style="border: 1px solid black">
              <p>&nbsp;</p>
            </td>
            <td
              colspan="3"
              style="border: 1px solid black"
            >
              <p>&nbsp;</p>
            </td>
          </tr>
        </table>
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
      endorsementDetails: [],
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
        { name: 'MEDICAL MEDICAL TANK', code: 'MEDICAL MEDICAL TANK' },
        { name: 'JORS', code: 'JORS' },
        { name: 'OTHERS', code: 'OTHERS' },
      ],
      form: this.$inertia.form({
        id: null,
        to: null,
        wardcode: null,
        // description, tag (Equipment, Medical Supply, JORS, Medical tank, Others), status ('CANCELLED', PENDING, ONGOING COMPLETED),
        endorsementDetails: [],
      }),
      formUpdateStatus: this.$inertia.form({
        request_stock_id: null,
        status: null,
      }),
      previousQty: 0,
      targetItemDesc: null,
      printForm: this.$inertia.form({
        office: null, // charge_slip_no
        ris_no: null,

        requested_by: null,
        approved_by: null,
      }),
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
    print(data) {
      if (data) {
        // Set up the print form details
        this.printForm.office = data.requested_at;
        this.printForm.requested_by = data.requested_by;
        this.printForm.approved_by = data.approved_by;

        // data.request_stocks_details.forEach((e) => {
        //   this.printForm.items.push({
        //     stock_no: e.issued_item.length === 0 ? '' : e.issued_item[0].id,
        //     description: e.item_details.cl2desc,
        //     req_qty: e.requested_qty,
        //     stock_avail: e.approved_qty != 0 ? 'y' : 'n',
        //     issue_qty: e.approved_qty,
        //     remarks: e.remarks,
        //   });
        // });

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
          from_user: e.from_user,
          to_user: e.to_user,
          //   requested_by: e.requested_by_details.firstname + ' ' + e.requested_by_details.lastname,
          //   approved_by:
          //     e.approved_by_details != null
          //       ? e.approved_by_details.firstname + ' ' + e.approved_by_details.lastname
          //       : null,
          wardcode: e.wardcode,
          created_at: e.created_at,
          request_stocks_details: e.request_stocks_details,
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
        (this.endorsementDetails = []),
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
    fillEndorsementContainer() {
      // check if no selected item
      if (this.item == null || this.item == '') {
        this.itemNotSelected = true;
        this.itemNotSelectedMsg = 'Item not selected.';
      } else {
        // check if request qty is not provided
        if (this.requested_qty == 0 || this.requested_qty == null || this.requested_qty == '') {
          this.itemNotSelected = true;
          this.itemNotSelectedMsg = 'Please provide quantity.';
        } else {
          // check if item selected is already on the list
          if (this.endorsementDetails.some((e) => e.cl2comb === this.item['cl2comb'])) {
            this.itemNotSelected = true;
            this.itemNotSelectedMsg = 'Item is already on the list.';
          } else {
            this.itemNotSelected = false;
            this.itemNotSelectedMsg = null;
            this.endorsementDetails.push({
              cl2comb: this.item['cl2comb'],
              cl2desc: this.item['cl2desc'],
              requested_qty: this.requested_qty,
            });
          }
        }
      }
    },
    removeFromEndorsementContainer(item) {
      this.endorsementDetails.splice(
        this.endorsementDetails.findIndex((e) => e.cl2comb === item.cl2comb),
        1
      );
    },
    editEndorsementedStock(item) {
      this.form.id = item.id;

      this.isUpdate = true;
      this.createEndorsementDialog = true;
      this.endorsement_id = item.id;

      item.request_stocks_details.forEach((e) => {
        this.endorsementDetails.push({
          request_stocks_details_id: e.id,
          cl2comb: e.cl2comb,
          cl2desc: e.item_details.cl2desc,
          requested_qty: e.requested_qty,
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
      this.form.requested_by = this.user.userDetail.employeeid;
      this.form.endorsementDetails = this.endorsementDetails;

      if (this.isUpdate) {
        this.form.put(route('wa-endorse.update', this.endorsement_id), {
          preserveScroll: true,
          onSuccess: () => {
            this.endorsement_id = null;
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
            this.endorsement_id = null;
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
      this.endorsement_id = item.id;
      this.cancelItemDialog = true;
    },
    cancelItem() {
      this.form.delete(route('wa-endorse.destroy', this.endorsement_id), {
        preserveScroll: true,
        onSuccess: () => {
          this.loading = false;
          this.endorsementList = [];
          this.cancelItemDialog = false;
          this.endorsement_id = null;
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
      this.$toast.add({ severity: 'success', summary: 'Success', detail: 'Stock request created', life: 3000 });
    },
    updatedMsg() {
      this.$toast.add({ severity: 'warn', summary: 'Success', detail: 'Stock request updated', life: 3000 });
    },
    updatedStatusMsg() {
      this.$toast.add({ severity: 'warn', summary: 'Success', detail: 'Changed requested stocks status', life: 3000 });
    },
    cancelledMsg() {
      this.$toast.add({ severity: 'error', summary: 'Success', detail: 'Stock request canceld', life: 3000 });
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
