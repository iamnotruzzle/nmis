<template>
  <app-layout>
    <Head title="NMIS - Stocks" />

    <div>
      <div
        class="card"
        style="border-top-left-radius: 0; border-top-right-radius: 0"
      >
        <Toast />

        <span class="text-xl text-900 font-bold text-primary">REQUESTED STOCKS</span>

        <DataTable
          class="p-datatable-sm"
          v-model:expandedRows="expandedRow"
          v-model:filters="filters"
          :value="requestStockList"
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
                      placeholder="Search item"
                    />
                  </div>
                </div>
                <Button
                  :disabled="canTransact == false"
                  label="Request stocks"
                  icon="pi pi-plus"
                  class="mr-2"
                  @click="openCreateRequestStocksDialog"
                />
                <Button
                  :disabled="canTransact == false"
                  label="REORDER"
                  :icon="formReorder.processing ? 'pi pi-spin pi-spinner' : 'pi pi-plus'"
                  type="submit"
                  severity="info"
                  @click="reorder"
                />
              </div>
            </div>
          </template>
          <Column
            expander
            style="width: 5%"
          />
          <template #empty> No requested stock found. </template>
          <template #loading> Loading requested stock data. Please wait. </template>
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
            :showFilterMenu="false"
          >
            <template #body="{ data }">
              <div class="flex flex-column justify-content-center align-content-center">
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
                  :disabled="canTransact == false"
                  v-if="data.status == 'FILLED'"
                  label="RECEIVE"
                  icon="pi pi-check"
                  class="mt-2"
                  severity="success"
                  @click="editStatus(data)"
                />
              </div>
            </template>
            <template #filter="{}">
              <Dropdown
                v-model="status"
                :options="statusList"
                optionLabel="status"
                placeholder="FILTER"
                optionValue="code"
                :highlightOnSelect="true"
                class="w-full md:w-14rem"
              >
              </Dropdown>
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
                  @click="editRequestedStock(slotProps.data)"
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
                    <span> {{ data.cl2desc }}</span>
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
                >
                  <template #body="{ data }">
                    <span>{{ data.remarks }} </span>
                  </template>
                </Column>
              </DataTable>
            </div>
          </template>
        </DataTable>

        <!-- @hide="clickOutsideDialog" -->
        <!-- create & edit dialog -->
        <Dialog
          v-model:visible="createRequestStocksDialog"
          :modal="true"
          class="p-fluid w-5"
          :closable="false"
          :closeOnEscape="false"
          @hide="whenDialogIsHidden"
        >
          <template #header>
            <div class="text-primary text-xl font-bold">REQUEST STOCK</div>
          </template>

          <!-- Warning while processing -->
          <div
            v-if="form.processing"
            class="mb-4"
          >
            <p class="font-bold text-red-600 text-lg border border-red-500 bg-red-100 p-3 rounded">
              ⚠️ Requesting stock is processing. Please don’t refresh or close this page.
            </p>
          </div>

          <div
            v-if="requestStockListDetails.length >= 10"
            class="mb-4"
          >
            <p class="font-bold text-warning text-lg border border-yellow-500 bg-yellow-100 p-3 rounded">
              ⚠️ Only 10 items are allowed per request. Please create a new request for additional items.
            </p>
          </div>

          <div
            v-if="noStockLevel == true"
            class="mb-4"
          >
            <p class="font-bold text-xl text-error">The stock level has not been set yet.</p>
          </div>
          <div class="field">
            <label>Available items</label>
            <Dropdown
              required="true"
              v-model="item"
              :options="itemsList"
              :virtualScrollerOptions="{ itemSize: 38 }"
              filter
              optionLabel="cl2desc"
              class="w-full mb-3"
            />
          </div>
          <div class="field">
            <label for="Item">Quantity</label>
            <InputText
              id="quantity"
              type="number"
              v-model="requested_qty"
              :class="{ 'p-invalid': requested_qty == '' || item == null }"
              @keydown="restrictNonNumericAndPeriod"
              @keyup.enter="fillRequestContainer"
            />
            <small
              class="text-error"
              v-if="itemNotSelected == true"
            >
              {{ itemNotSelectedMsg }}
            </small>
          </div>
          <div class="field mt-4">
            <label class="mr-2">Requested stock list</label>
            <i
              class="pi pi-shopping-cart text-blue-500"
              style="font-size: 1.5rem"
            />
            <DataTable
              v-model:filters="requestStockListDetailsFilter"
              :globalFilterFields="['cl2desc']"
              :value="requestStockListDetails"
              tableStyle="min-width: 50rem"
              class="p-datatable-sm w-full"
              paginator
              removableSort
              showGridlines
              :rows="5"
            >
              <template #header>
                <div class="flex justify-content-end">
                  <div class="p-inputgroup">
                    <span class="p-inputgroup-addon">
                      <i class="pi pi-search"></i>
                    </span>
                    <InputText
                      id="searchInput"
                      v-model="requestStockListDetailsFilter['global'].value"
                      placeholder="Search item"
                    />
                  </div>
                </div>
              </template>
              <Column
                field="cl2desc"
                header="PENDING ITEM"
                style="width: 70%"
                sortable
              ></Column>
              <Column
                field="requested_qty"
                header="PENDING QTY"
                style="width: 20%"
                sortable
              ></Column>
              <Column
                header=""
                style="width: 10%"
              >
                <template #body="slotProps">
                  <Button
                    icon="pi pi-times"
                    label="REMOVE"
                    severity="danger"
                    @click="removeFromRequestContainer(slotProps.data)"
                  />
                </template>
              </Column>
            </DataTable>
          </div>

          <template #footer>
            <Button
              :label="!form.processing ? 'CANCEL' : 'CANCEL'"
              icon="pi pi-times"
              :disabled="saving || form.processing"
              severity="danger"
              @click="cancel"
            />

            <Button
              v-if="isUpdate == true"
              :disabled="saving || form.processing || !requestStockListDetails || requestStockListDetails.length === 0"
              :label="!form.processing ? 'UPDATE' : 'UPDATE'"
              :icon="form.processing ? 'pi pi-spin pi-spinner' : 'pi pi-check'"
              severity="warning"
              type="submit"
              @click="submit"
            />

            <Button
              v-else
              :label="!form.processing ? 'REQUEST' : 'REQUEST'"
              :icon="form.processing ? 'pi pi-spin pi-spinner' : 'pi pi-check'"
              :disabled="saving || form.processing || !requestStockListDetails || requestStockListDetails.length === 0"
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
          :closable="false"
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
          :closable="false"
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
              <p><strong>Requested by:</strong></p>
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
              <!-- <span>Efleda Sarah V. Marders, RN, MAN</span> -->
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
              <!-- <span>CHIEF NURSE VII</span> -->
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
    // authWardcode: Object,
    items: Object,
    requestedStocks: Object,
    canTransact: Boolean,
    canAddExpiryDate: Boolean,
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
      requestStockId: null,
      isUpdate: false,
      createRequestStocksDialog: false,
      editStatusDialog: false,
      cancelItemDialog: false,
      search: '',
      status: null,
      saving: false,
      statusList: [
        { status: 'NO FILTER', code: '' },
        { status: 'PENDING', code: 'PENDING' },
        { status: 'ACKNOWLEDGED', code: 'ACKNOWLEDGED' },
        { status: 'FILLED', code: 'FILLED' },
        { status: 'RECEIVED', code: 'RECEIVED' },
      ],
      selectedItemsUomDesc: null,
      oldQuantity: 0,
      options: {},
      params: {},
      from: null,
      to: null,
      stockBalanceDeclared: false,
      noStockLevel: false,
      itemsList: [],
      requestStockList: [],
      // stock list details
      requestStockListDetailsFilter: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
      requestStockListDetails: [],
      fundSourceList: [],
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
      form: this.$inertia.form({
        request_stocks_id: null,
        location: null,
        requested_by: null,
        requestStockListDetails: [],
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

        // stock_no: null,
        // unit: null,
        // description: null,
        // req_qty: null,
        // stock_avail: null,
        // issue_qty: null,
        // remarks: null,

        items: [],
      }),
      formReorder: this.$inertia.form({
        wardcode: 'asdasd', // charge_slip_no
      }),
    };
  },
  // created will be initialize before mounted
  created() {
    this.totalRecords = this.requestedStocks.total;
    this.params.page = this.requestedStocks.current_page;
    this.rows = this.requestedStocks.per_page;
  },
  mounted() {
    this.authWardcode = this.$page.props.auth.user.location.location_name.wardcode;
    this.formReorder.wardcode = this.$page.props.auth.user.location.location_name.wardcode;

    // console.log('fs', this.$page.props.fundSource);

    window.Echo.channel('issued').listen('ItemIssued', (event) => {
      //   console.log('Location:', event.location); // Access the location data

      if (event.location == this.$page.props.auth.user.location.wardcode) {
        router.reload({
          onSuccess: () => {
            console.log('Data reloaded successfully');
            this.requestStockList = []; // Reset
            this.storeRequestedStocksInContainer();
          },
        });
      }
    });

    this.storeFundSourceInContainer();
    this.storeItemsInController();
    this.storeRequestedStocksInContainer();

    this.loading = false;
  },
  computed: {
    user() {
      return this.$page.props.auth.user;
    },
  },
  methods: {
    print(data) {
      if (data) {
        // Set up the print form details
        this.printForm.office = data.requested_at;
        this.printForm.ris_no = `RIS-${data.id}`;
        this.printForm.items = [];
        this.printForm.requested_by = data.requested_by;
        this.printForm.approved_by = data.approved_by;

        data.request_stocks_details.forEach((e) => {
          this.printForm.items.push({
            stock_no: e.issued_item.length === 0 ? '' : e.issued_item[0].id,
            description: e.cl2desc,
            req_qty: e.requested_qty,
            stock_avail: e.approved_qty != 0 ? 'y' : 'n',
            issue_qty: e.approved_qty,
            remarks: e.remarks,
          });
        });

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
    storeFundSourceInContainer() {
      this.$page.props.fundSource.forEach((e) => {
        this.fundSourceList.push({
          chrgcode: e.fsid,
          chrgdesc: e.fsName,
          bentypcod: null,
          chrgtable: null,
        });
      });
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
    // use storeRequestedStocksInContainer() function so that every time you make
    // server request such as POST, the data in the table
    // is updated
    storeRequestedStocksInContainer() {
      this.requestStockList = []; // reset

      //   console.log('before', this.requestedStocks.data);
      this.requestedStocks.data.forEach((e) => {
        this.requestStockList.push({
          id: e.id,
          status: e.status,
          requested_by: `${e.requested_by_firstname} ${e.requested_by_lastname}`,
          requested_at: e.requested_from,
          approved_by: e.approved_by_firstname ? `${e.approved_by_firstname} ${e.approved_by_lastname}` : null,
          created_at: e.created_at,
          request_stocks_details: e.request_stocks_details, // or fetch separately if needed
        });
      });
      //   console.log('after', this.requestStockList);
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

      this.$inertia.get('requeststocks', this.params, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: (visit) => {
          this.totalRecords = this.requestedStocks.total;
          this.requestStockList = [];
          this.expandedRow = [];
          this.storeRequestedStocksInContainer();
          this.loading = false;
          this.formUpdateStatus.reset();
        },
      });
    },
    openCreateRequestStocksDialog() {
      this.isUpdate = false;
      this.form.clearErrors();
      this.form.reset();
      this.requestStockId = null;
      this.createRequestStocksDialog = true;
    },
    // when dialog is hidden, do this function
    whenDialogIsHidden() {
      this.$emit(
        'hide',
        (this.noStockLevel = false),
        (this.requestStockId = null),
        (this.isUpdate = false),
        (this.requestStockListDetails = []),
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
    // fillRequestContainer() {
    //   // check if no selected item
    //   if (this.item == null || this.item == '') {
    //     this.itemNotSelected = true;
    //     this.itemNotSelectedMsg = 'Item not selected.';
    //   } else {
    //     // check if request qty is not provided
    //     if (this.requested_qty == 0 || this.requested_qty == null || this.requested_qty == '') {
    //       this.itemNotSelected = true;
    //       this.itemNotSelectedMsg = 'Please provide quantity.';
    //     } else {
    //       // check if item selected is already on the list
    //       if (this.requestStockListDetails.some((e) => e.cl2comb === this.item['cl2comb'])) {
    //         this.itemNotSelected = true;
    //         this.itemNotSelectedMsg = 'Item is already on the list.';
    //       } else {
    //         this.itemNotSelected = false;
    //         this.itemNotSelectedMsg = null;
    //         this.requestStockListDetails.push({
    //           cl2comb: this.item['cl2comb'],
    //           cl2desc: this.item['cl2desc'],
    //           requested_qty: this.requested_qty,
    //         });
    //       }
    //     }
    //   }
    // },
    fillRequestContainer() {
      if (this.requestStockListDetails.length >= 10) {
        this.itemNotSelected = true;
        this.itemNotSelectedMsg = 'Limit of 10 items reached. Please create a new request.';
        return;
      }

      if (this.item == null || this.item == '') {
        this.itemNotSelected = true;
        this.itemNotSelectedMsg = 'Item not selected.';
      } else if (this.requested_qty == 0 || this.requested_qty == null || this.requested_qty == '') {
        this.itemNotSelected = true;
        this.itemNotSelectedMsg = 'Please provide quantity.';
      } else if (this.requestStockListDetails.some((e) => e.cl2comb === this.item['cl2comb'])) {
        this.itemNotSelected = true;
        this.itemNotSelectedMsg = 'Item is already on the list.';
      } else {
        this.itemNotSelected = false;
        this.itemNotSelectedMsg = null;
        this.requestStockListDetails.push({
          cl2comb: this.item['cl2comb'],
          cl2desc: this.item['cl2desc'],
          requested_qty: this.requested_qty,
        });
      }
    },

    removeFromRequestContainer(item) {
      this.requestStockListDetails.splice(
        this.requestStockListDetails.findIndex((e) => e.cl2comb === item.cl2comb),
        1
      );
    },
    editRequestedStock(item) {
      this.form.request_stocks_id = item.id;

      this.isUpdate = true;
      this.createRequestStocksDialog = true;
      this.requestStockId = item.id;

      item.request_stocks_details.forEach((e) => {
        this.requestStockListDetails.push({
          request_stocks_details_id: e.id,
          cl2comb: e.cl2comb,
          cl2desc: e.cl2desc,
          requested_qty: e.requested_qty,
          remarks: e.remarks,
        });
      });
    },
    editStatus(item) {
      this.editStatusDialog = true;
      this.formUpdateStatus.request_stock_id = item.id;
      this.formUpdateStatus.status = 'RECEIVED';
    },
    updateStatus() {
      this.formUpdateStatus.put(route('requeststocks.updatedeliverystatus', this.formUpdateStatus), {
        preserveScroll: true,
        onSuccess: () => {
          this.requestStockId = null;
          this.editStatusDialog = false;
          this.cancel();
          this.updateData();
          this.updatedStatusMsg();
          this.loading = false;
        },
      });
    },
    submit() {
      if (this.saving || this.form.processing) {
        return false;
      }

      this.form.location = this.authWardcode;
      this.form.requested_by = this.user.userDetail.employeeid;
      this.form.requestStockListDetails = this.requestStockListDetails;

      this.saving = true;

      const options = {
        preserveScroll: true,
        onSuccess: (page) => {
          this.requestStockId = null;
          this.createRequestStocksDialog = false;
          this.cancel();

          // Update local data with fresh props from the redirect
          this.totalRecords = page.props.requestedStocks.total;
          this.params.page = page.props.requestedStocks.current_page;
          this.rows = page.props.requestedStocks.per_page;
          this.requestStockList = [];
          this.expandedRow = [];
          this.storeRequestedStocksInContainer();

          this.isUpdate ? this.updatedMsg() : this.createdMsg();
          this.loading = false;
        },
        onFinish: () => {
          this.saving = false;
        },
      };

      if (this.isUpdate) {
        this.form.put(route('requeststocks.update', this.requestStockId), options);
      } else {
        this.form.post(route('requeststocks.store'), options);
      }
    },
    reorder() {
      this.requestStockListDetails = [];
      axios
        .get('requeststocks/view-reorder')
        .then((response) => {
          //   console.log(response.data);
          this.reorders = response.data; // Store it in your component
          if (response.data == [] || response.data == null) {
            this.noStockLevel = true;
          }

          this.reorders.forEach((e) => {
            this.requestStockListDetails.push({
              cl2comb: e.cl2comb,
              cl2desc: e.cl2desc,
              requested_qty: e.reorder_quantity,
            });
          });
        })
        .then(() => {
          this.createRequestStocksDialog = true;
        })
        .catch((error) => {
          console.error('Fetch error:', error);
        });
    },
    confirmCancelItem(item) {
      this.requestStockId = item.id;
      this.cancelItemDialog = true;
    },
    cancelItem() {
      this.form.delete(route('requeststocks.destroy', this.requestStockId), {
        preserveScroll: true,
        onSuccess: (page) => {
          this.loading = false;
          this.cancelItemDialog = false;
          this.requestStockId = null;
          this.form.clearErrors();
          this.form.reset();

          // Update local data with fresh props from the redirect
          this.totalRecords = page.props.requestedStocks.total;
          this.params.page = page.props.requestedStocks.current_page;
          this.rows = page.props.requestedStocks.per_page;
          this.requestStockList = [];
          this.expandedRow = [];
          this.storeRequestedStocksInContainer();

          this.cancelledMsg();
        },
      });
    },
    cancel() {
      this.requestStockId = null;
      this.isUpdate = false;
      this.createRequestStocksDialog = false;
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
    updatedStockMsg() {
      this.$toast.add({ severity: 'warn', summary: 'Success', detail: 'Stock updated', life: 3000 });
    },
    // end ward stocks logs
  },
  watch: {
    search: function (val, oldVal) {
      this.params.search = val;
      this.updateData();
    },
    status: function (val, oldVal) {
      this.params.status = val;
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
