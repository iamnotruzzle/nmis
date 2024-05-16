<template>
  <app-layout>
    <Head title="NMIS - Stocks" />

    <div>
      <!-- <div class="">
        <Link
          href="requeststocks"
          class="button-link-current bar"
        >
          OTHER ITEMS
        </Link>

        <Link
          href="requesttankstocks"
          class="button-link"
        >
          TANKS
        </Link>
      </div>

      <div style="background-color: #818cf8; width: 100%; height: 4px"></div> -->

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
                  label="Request stocks"
                  icon="pi pi-plus"
                  iconPos="right"
                  @click="openCreateRequestStocksDialog"
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
            style="width: 10%"
          >
            <template #body="{ data }">
              <div class="flex justify-content-start align-content-center">
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
                <div>
                  <i
                    v-if="data.status == 'FILLED'"
                    class="pi pi-check ml-3"
                    style="color: skyblue"
                    @click="editStatus(data)"
                  ></i>
                </div>
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
                <img
                  v-if="data.requested_by_image != null"
                  :src="`storage/${data.requested_by_image}`"
                  class="w-3rem h-3rem rounded-card"
                />
                <img
                  v-else
                  src="images/no_profile.png"
                  class="w-3rem h-3rem rounded-card"
                />

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
                <img
                  v-if="data.approved_by_image != null"
                  :src="`storage/${data.approved_by_image}`"
                  class="w-3rem h-3rem rounded-card"
                />

                <img
                  v-if="data.approved_by != null && data.approved_by_image == null"
                  src="images/no_profile.png"
                  class="w-3rem h-3rem rounded-card"
                />

                <span class="font-semibold text-xl pl-3">
                  {{ data.approved_by }}
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
                  v-if="slotProps.data.status == 'PENDING'"
                  name="pr-pencil"
                  class="text-yellow-500 text-xl"
                  @click="editRequestedStock(slotProps.data)"
                ></v-icon>

                <!-- <Button
              v-if="slotProps.data.status == 'PENDING'"
              icon="fc fc-cancel"
              rounded
              text
              severity="danger"
              @click="confirmCancelItem(slotProps.data)"
            /> -->
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
            <div class="p-3">
              <h5 class="text-green-500">LIST</h5>
              <DataTable
                paginator
                removableSort
                showGridlines
                :rows="7"
                :value="slotProps.data.request_stocks_details"
              >
                <Column
                  field="item"
                  header="ITEM"
                  style="width: 60%"
                >
                  <template #body="{ data }">
                    {{ data.item_details.cl2desc }}
                  </template>
                </Column>
                <Column
                  field="requested_qty"
                  header="PENDING QTY"
                  style="width: 10%"
                ></Column>
                <Column
                  field="approved_qty"
                  header="APPROVED QTY"
                  style="width: 30%"
                ></Column>
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
          @hide="whenDialogIsHidden"
        >
          <template #header>
            <div class="text-primary text-xl font-bold">REQUEST STOCK</div>
          </template>
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
              v-model.trim="requested_qty"
              required="true"
              autofocus
              :class="{ 'p-invalid': requested_qty == '' || item == null }"
              @keyup.enter="fillRequestContainer"
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
                    rounded
                    text
                    severity="danger"
                    @click="removeFromRequestContainer(slotProps.data)"
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
              :disabled="form.processing || requestStockListDetails == '' || requestStockListDetails == null"
              @click="submit"
            />
            <Button
              v-else
              label="Save"
              icon="pi pi-check"
              text
              type="submit"
              :disabled="form.processing || requestStockListDetails == '' || requestStockListDetails == null"
              @click="submit"
            />
          </template>
        </Dialog>

        <!-- edit status confirmation dialog -->
        <Dialog
          v-model:visible="editStatusDialog"
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
            <span v-if="form">
              Are you sure you want to <b>update</b> the status of this requested stocks to <b>RECEIVED</b>?
            </span>
          </div>
          <template #footer>
            <Button
              label="No"
              icon="pi pi-times"
              class="p-button-text"
              @click="editStatusDialog = false"
            />
            <Button
              label="Yes"
              icon="pi pi-check"
              severity="danger"
              text
              :disabled="formUpdateStatus.processing"
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
              label="No"
              icon="pi pi-times"
              class="p-button-text"
              @click="cancelItemDialog = false"
            />
            <Button
              label="Yes"
              icon="pi pi-check"
              severity="danger"
              text
              :disabled="form.processing"
              @click="cancelItem"
            />
          </template>
        </Dialog>

        <!-- Consignment -->
        <Dialog
          v-model:visible="consignmentDialog"
          :modal="true"
          class="p-fluid w-4"
          @hide="whenDialogIsHidden"
        >
          <template #header>
            <div class="text-primary text-xl font-bold">CONSIGNMENT</div>
          </template>
          <div class="field">
            <label for="fundSource">Fund source</label>
            <Dropdown
              id="fundSource"
              required="true"
              v-model="formConsignment.fund_source"
              :options="fundSourceList"
              filter
              showClear
              dataKey="chrgcode"
              optionLabel="chrgdesc"
              optionValue="chrgcode"
              class="w-full"
              :class="{ 'p-invalid': formConsignment.fund_source == '' }"
            />
            <small
              class="text-error"
              v-if="formConsignment.errors.fund_source"
            >
              {{ formConsignment.errors.fund_source }}
            </small>
          </div>
          <div class="field">
            <label>Item</label>
            <Dropdown
              required="true"
              v-model="formConsignment.cl2comb"
              :options="itemsList"
              :virtualScrollerOptions="{ itemSize: 38 }"
              filter
              optionValue="cl2comb"
              optionLabel="cl2desc"
              class="w-full mb-3"
            />
            <small
              class="text-error"
              v-if="formConsignment.errors.cl2comb"
            >
              {{ formConsignment.errors.cl2comb }}
            </small>
          </div>
          <div class="field">
            <label for="unit">Unit</label>
            <InputText
              id="unit"
              v-model.trim="selectedItemsUomDesc"
              readonly
            />
          </div>
          <div class="field">
            <label>Quantity</label>
            <InputText
              id="quantity"
              v-model.trim="formConsignment.quantity"
              required="true"
              autofocus
              :class="{ 'p-invalid': formConsignment.quantity == '' || formConsignment.quantity == null }"
              inputId="integeronly"
            />
            <small
              class="text-error"
              v-if="formConsignment.errors.quantity"
            >
              {{ formConsignment.errors.quantity }}
            </small>
          </div>
          <div class="field">
            <label for="manufactured_date">Manufactured date</label>
            <Calendar
              v-model="formConsignment.manufactured_date"
              dateFormat="mm-dd-yy"
              showIcon
              showButtonBar
              :manualInput="false"
              :hideOnDateTimeSelect="true"
            />
          </div>
          <div class="field">
            <label for="delivered_date">Delivered date</label>
            <Calendar
              v-model="formConsignment.delivered_date"
              dateFormat="mm-dd-yy"
              showIcon
              showButtonBar
              :manualInput="false"
              :hideOnDateTimeSelect="true"
            />
          </div>
          <div class="field">
            <label for="expiration_date">Expiration date</label>
            <Calendar
              v-model="formConsignment.expiration_date"
              dateFormat="mm-dd-yy"
              showIcon
              showButtonBar
              :manualInput="false"
              :hideOnDateTimeSelect="true"
            />
            <small
              class="text-error"
              v-if="formConsignment.errors.expiration_date"
            >
              {{ formConsignment.errors.expiration_date }}
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
              label="Save"
              icon="pi pi-check"
              text
              type="submit"
              :disabled="
                formConsignment.processing ||
                formConsignment.fund_source == null ||
                formConsignment.cl2comb == null ||
                formConsignment.quantity == null ||
                formConsignment.expiration_date == null
              "
              @click="submitConsignment"
            />
          </template>
        </Dialog>

        <!-- Convert item -->
        <Dialog
          v-model:visible="convertItemDialog"
          header="CONVERT ITEM"
          :modal="true"
          @hide="whenDialogIsHidden"
          :style="{ width: '50rem' }"
        >
          <p
            class="text-error text-xl font-semibold my-1"
            v-if="stockBalanceDeclared != false"
          >
            <!-- {{ $page.props.errors['requestStockListDetails.0.cl2comb'].toUpperCase() }} -->
            {{ $page.props.errors.cl2comb }}
          </p>
          <div class="form-container">
            <!-- Left Side: From Items -->
            <div class="form-side border-1 p-3">
              <h2>FROM</h2>
              <div class="p-field flex flex-column">
                <label for="targetItem">ITEM</label>
                <InputText
                  id="targetItem"
                  v-model.trim="targetItemDesc"
                  readonly
                />
              </div>

              <div class="p-field flex flex-column">
                <label for="qty_to_convert">QUANTITY TO CONVERT</label>
                <InputText
                  id="qty_to_convert"
                  v-model.trim="formConvertItem.qty_to_convert"
                  required="true"
                  autofocus
                  :class="{
                    'p-invalid':
                      formConvertItem.qty_to_convert == '' ||
                      formConvertItem.qty_to_convert == null ||
                      formConvertItem.qty_to_convert > oldQuantity,
                  }"
                  inputId="integeronly"
                />
                <span
                  v-if="formConvertItem.qty_to_convert > oldQuantity"
                  class="text-error"
                >
                  Current stock quantity is no enough.
                </span>
              </div>
              <!-- Add more fields as needed -->
            </div>

            <div class="mx-2">
              <v-icon
                name="co-arrow-thick-right"
                scale="2"
              ></v-icon>
            </div>

            <!-- Right Side: To Items -->
            <div class="form-side border-1 p-3">
              <h2>TO</h2>
              <div class="p-field flex flex-column">
                <label for="toItem">ITEM</label>
                <Dropdown
                  id="toItem"
                  required="true"
                  v-model="formConvertItem.to"
                  :options="itemsList"
                  :virtualScrollerOptions="{ itemSize: 38 }"
                  filter
                  optionValue="cl2comb"
                  optionLabel="cl2desc"
                />
              </div>

              <div class="p-field flex flex-column">
                <label for="qty_after">EQUIVALENT QUANTITY</label>
                <InputNumber
                  id="qty_after"
                  v-model.trim="formConvertItem.equivalent_quantity"
                  required="true"
                  autofocus
                  :class="{
                    'p-invalid':
                      formConvertItem.equivalent_quantity == '' || formConvertItem.equivalent_quantity == null,
                  }"
                  inputId="minmaxfraction"
                  :maxFractionDigits="2"
                />
              </div>
            </div>
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
              text
              type="submit"
              :disabled="
                formConvertItem.processing ||
                formConvertItem.to == null ||
                formConvertItem.qty_to_convert == null ||
                formConvertItem.equivalent_quantity == null ||
                formConvertItem.qty_to_convert > oldQuantity
              "
              @click="submitConvertItem"
            >
              <template #default="">
                <v-icon name="si-convertio"></v-icon>
                <label class="ml-2">Convert</label>
              </template>
            </Button>
          </template>
        </Dialog>

        <!-- update ward stock dialog -->
        <Dialog
          v-model:visible="editWardStocksDialog"
          header="Update stock"
          :modal="true"
          class="p-fluid w-4"
          @hide="whenDialogIsHidden"
          dismissableMask
        >
          <div class="field">
            <label for="item">Item</label>
            <InputText
              id="item"
              v-model.trim="formWardStocks.item"
              readonly
              class="w-full"
            />
          </div>
          <div class="field">
            <label for="quantity">Deduct from Stock</label>
            <InputText
              id="quantity"
              v-model.trim="formWardStocks.quantity"
              autofocus
              class="w-full"
            />
            <small
              class="text-error"
              v-if="Number(formWardStocks.current_quantity) < Number(formWardStocks.quantity)"
            >
              Input must be less than the current stock quantity.
            </small>
          </div>
          <div class="field">
            <label for="expiration_date">Expiration date</label>
            <InputText
              id="expiration_date"
              v-model.trim="formWardStocks.expiration_date"
              readonly
              class="w-full"
            />
          </div>
          <div class="field">
            <label for="remarks">Remarks <span class="text-error">(Required)</span></label>
            <TextArea
              v-model.trim="formWardStocks.remarks"
              rows="5"
              autofocus
              :class="{ 'p-invalid': formWardStocks.remarks == '' }"
            />
            <small
              class="text-error"
              v-if="formWardStocks.errors.remarks"
            >
              {{ formWardStocks.errors.remarks }}
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
              label="Update"
              icon="pi pi-check"
              severity="warning"
              text
              type="submit"
              :disabled="
                formWardStocks.processing ||
                formWardStocks.quantity == null ||
                formWardStocks.quantity == '' ||
                formWardStocks.quantity == 0 ||
                Number(formWardStocks.current_quantity) < Number(formWardStocks.quantity)
              "
              @click="submitEditWardStocks"
            />
          </template>
        </Dialog>
      </div>

      <div class="card">
        <!-- current ward stocks -->
        <span class="text-xl text-900 font-bold text-primary">CURRENT STOCKS</span>

        <DataTable
          class="p-datatable-sm"
          dataKey="id"
          v-model:filters="currentWardStocksFilter"
          :value="currentWardStocksList"
          paginator
          :rows="10"
          :rowsPerPageOptions="[10, 30, 50]"
          removableSort
          sortField="expiration_date"
          :sortOrder="1"
          showGridlines
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
                      v-model="currentWardStocksFilter['global'].value"
                      placeholder="Search item"
                    />
                  </div>
                </div>
                <Button
                  label="Consignment"
                  icon="pi pi-plus"
                  iconPos="right"
                  @click="openConsignmentDialog"
                />
              </div>
            </div>
          </template>
          <template #empty> No item found. </template>
          <template #loading> Loading item data. Please wait. </template>
          <Column
            field="from"
            header="FROM"
            style="width: 20%"
            sortable
          >
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
            field="unit"
            header="UNIT"
            style="width: 10%"
            sortable
          >
          </Column>
          <Column
            field="quantity"
            header="QUANTITY"
            style="width: 5%"
            sortable
          >
            <template #body="{ data }">
              {{ data.quantity }}
            </template>
          </Column>
          <Column
            field="expiration_date"
            header="EXPIRATION DATE"
            style="width: 15%"
            sortable
          >
            <template #body="{ data }">
              <div class="flex flex-column">
                <div>
                  {{ tzone(data.expiration_date) }}
                </div>

                <div class="mays-2">
                  <span
                    :class="
                      checkIfAboutToExpire(data.expiration_date) != 'Item has expired.'
                        ? 'text-lg text-green-500'
                        : 'text-lg text-error'
                    "
                  >
                    {{ checkIfAboutToExpire(data.expiration_date) }}
                  </span>
                </div>
              </div>
            </template>
          </Column>
          <Column
            header="ACTION"
            style="width: 10%"
          >
            <template #body="slotProps">
              <div class="flex justify-content-center">
                <Button
                  rounded
                  text
                  severity="warning"
                  @click="editWardStocks(slotProps.data)"
                >
                  <template #default="">
                    <v-icon
                      name="pr-pencil"
                      class="text-yellow-500"
                    ></v-icon>
                  </template>
                </Button>
                <Button
                  rounded
                  text
                  @click="openConvertDialog(slotProps.data)"
                >
                  <template #default="">
                    <v-icon
                      name="si-convertio"
                      class="text-blue-500"
                    ></v-icon>
                  </template>
                </Button>
              </div>
            </template>
          </Column>
        </DataTable>
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
import NProgress from 'nprogress';
import Echo from 'laravel-echo';
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
    TextArea,
    Link,
    InputNumber,
  },
  props: {
    authWardcode: Object,
    items: Object,
    requestedStocks: Object,
    currentWardStocks: Object,
    currentWardStocks2: Object,
    // typeOfCharge: Object,
    fundSource: Object,
  },
  data() {
    return {
      expandedRow: [],
      // paginator
      loading: false,
      totalRecords: null,
      rows: null,
      // end paginator
      requestStockId: null,
      isUpdate: false,
      createRequestStocksDialog: false,
      convertItemDialog: false,
      consignmentDialog: false,
      editWardStocksDialog: false,
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
      itemsList: [],
      requestStockList: [],
      currentWardStocksList: [],
      // stock list details
      requestStockListDetailsFilter: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
      currentWardStocksFilter: {
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
      formConsignment: this.$inertia.form({
        authLocation: null,
        fund_source: null,
        cl2comb: null,
        uomcode: null,
        quantity: null,
        manufactured_date: null,
        delivered_date: null,
        expiration_date: null,
      }),
      formWardStocks: this.$inertia.form({
        ward_stock_id: null,
        item: null,
        current_quantity: null,
        quantity: null,
        expiration_date: null,
        remarks: null,
      }),
      targetItemDesc: null,
      formConvertItem: this.$inertia.form({
        cl2comb: null,
        ward_stock_id: null,
        to: null,
        qty_to_convert: null,
        equivalent_quantity: null,
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
    window.Echo.channel('issued').listen('ItemIssued', (args) => {
      if (args.message[0] == this.$page.props.authWardcode.wardcode) {
        router.reload({
          onSuccess: (e) => {
            this.requestStockList = [];
            this.storeRequestedStocksInContainer();
            this.createRequestStocksDialog = false;
          },
        });
      }
    });

    this.storeFundSourceInContainer();
    this.storeItemsInController();
    this.storeRequestedStocksInContainer();
    this.storeCurrentWardStocksInContainer();

    this.loading = false;
  },
  computed: {
    user() {
      return this.$page.props.auth.user;
    },
  },
  methods: {
    storeFundSourceInContainer() {
      //   this.typeOfCharge.forEach((e) => {
      //     this.fundSourceList.push({
      //       chrgcode: e.chrgcode,
      //       chrgdesc: e.chrgdesc,
      //       bentypcod: e.bentypcod,
      //       chrgtable: e.chrgtable,
      //     });
      //   });

      this.fundSource.forEach((e) => {
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

      this.requestedStocks.data.forEach((e) => {
        this.requestStockList.push({
          id: e.id,
          status: e.status,
          requested_by: e.requested_by_details.firstname + ' ' + e.requested_by_details.lastname,
          requested_by_image: e.requested_by_details.user_account.image,
          approved_by:
            e.approved_by_details != null
              ? e.approved_by_details.firstname + ' ' + e.approved_by_details.lastname
              : null,
          approved_by_image: e.approved_by_details != null ? e.approved_by_details.user_account.image : null,
          created_at: e.created_at,
          request_stocks_details: e.request_stocks_details,
        });
      });
    },
    // store current stocks
    storeCurrentWardStocksInContainer() {
      this.currentWardStocksList = []; // reset

      moment.suppressDeprecationWarnings = true;

      this.currentWardStocks.forEach((e) => {
        let expiration_date = moment.tz(e.expiration_date, 'Asia/Manila').format('MM/DD/YYYY');

        this.currentWardStocksList.push({
          from: e.from,
          ward_stock_id: e.id,
          cl2comb: e.item_details.cl2comb,
          item: e.item_details.cl2desc,
          unit: e.unit_of_measurement == null ? null : e.unit_of_measurement.uomdesc,
          quantity: e.quantity,
          expiration_date: expiration_date.toString(),
        });
      });

      this.currentWardStocks2.forEach((e) => {
        let expiration_date = moment.tz(e.expiration_date, 'Asia/Manila').format('MM/DD/YYYY');

        this.currentWardStocksList.push({
          from: e.from,
          ward_stock_id: e.id,
          cl2comb: e.item_details.cl2comb,
          item: e.item_details.cl2desc,
          unit: e.unit_of_measurement == null ? null : e.unit_of_measurement.uomdesc,
          quantity: e.quantity,
          expiration_date: expiration_date.toString(),
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

      //   console.log(current_date.format('MM-DD-YY') == exp_date.format('MM-DD-YY'));

      //    exp_date.format('MM-DD-YY') < current_date.format('MM-DD-YY')
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
        onFinish: (visit) => {
          this.totalRecords = this.requestedStocks.total;
          this.requestStockList = [];
          this.currentWardStocksList = [];
          this.expandedRow = [];
          this.storeRequestedStocksInContainer();
          this.storeCurrentWardStocksInContainer();
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
    openConsignmentDialog() {
      this.formConsignment.clearErrors();
      this.formConsignment.reset();
      this.consignmentDialog = true;
    },
    openConvertDialog(item) {
      //   this.formConsignment.clearErrors();
      //   this.formConsignment.reset();
      //   this.consignmentDialog = true;

      //   console.log('item', item);

      this.convertItemDialog = true;
      this.formConvertItem.cl2comb = item.cl2comb;
      this.formConvertItem.ward_stock_id = item.ward_stock_id;
      this.oldQuantity = item.quantity;
      this.targetItemDesc = item.item;
    },
    // when dialog is hidden, do this function
    whenDialogIsHidden() {
      this.$emit(
        'hide',
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
        (this.oldQuantity = 0),
        this.form.clearErrors(),
        this.form.reset(),
        this.formWardStocks.clearErrors(),
        this.formWardStocks.reset(),
        this.formConvertItem.clearErrors(),
        this.formConvertItem.reset(),
        this.formUpdateStatus.reset()
      );
    },
    fillRequestContainer() {
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
          if (this.requestStockListDetails.some((e) => e.cl2comb === this.item['cl2comb'])) {
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
        }
      }
      //   console.log(this.requestStockListDetails);
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
          cl2desc: e.item_details.cl2desc,
          requested_qty: e.requested_qty,
        });
      });
    },
    editStatus(item) {
      //   console.log(item);
      this.editStatusDialog = true;
      this.formUpdateStatus.request_stock_id = item.id;
      this.formUpdateStatus.status = 'RECEIVED';
    },
    updateStatus() {
      //   console.log(item);
      //   this.formUpdateStatus.status = item;

      this.formUpdateStatus.put(route('requeststocks.updatedeliverystatus', this.formUpdateStatus), {
        preserveScroll: true,
        onSuccess: () => {
          this.requestStockId = null;
          this.editStatusDialog = false;
          this.cancel();
          this.updateData();
          this.updatedStatusMsg();
        },
      });
    },
    submit() {
      if (this.form.processing) {
        return false;
      }

      // setup location, requested by and requestStockListDetails before submitting
      this.form.location = this.authWardcode.wardcode;
      this.form.requested_by = this.user.userDetail.employeeid;
      this.form.requestStockListDetails = this.requestStockListDetails;

      if (this.isUpdate) {
        this.form.put(route('requeststocks.update', this.requestStockId), {
          preserveScroll: true,
          onSuccess: () => {
            this.requestStockId = null;
            this.createRequestStocksDialog = false;
            this.cancel();
            this.updateData();
            this.updatedMsg();
          },
        });
      } else {
        this.form.post(route('requeststocks.store'), {
          preserveScroll: true,
          onSuccess: () => {
            this.requestStockId = null;
            this.createRequestStocksDialog = false;
            this.cancel();
            this.updateData();
            this.createdMsg();
          },
        });
      }
    },
    submitConsignment() {
      if (this.formConsignment.processing) {
        return false;
      }

      this.formConsignment.authLocation = this.$page.props.authWardcode.wardcode;
      if (
        this.formConsignment.fund_source != null ||
        this.formConsignment.fund_source != '' ||
        this.formConsignment.cl2comb != null ||
        this.formConsignment.cl2comb != '' ||
        this.formConsignment.quantity != null ||
        this.formConsignment.quantity != '' ||
        this.formConsignment.quantity != 0 ||
        this.formConsignment.expiration_date != null ||
        this.formConsignment.expiration_date != ''
      ) {
        this.formConsignment.post(route('consignment.store'), {
          preserveScroll: true,
          onSuccess: () => {
            this.formConsignment.reset();
            this.cancel();
            this.updateData();
            this.createdMsg();
          },
        });
      }
    },
    submitConvertItem() {
      this.formConvertItem.post(route('convertitem.store'), {
        preserveScroll: true,
        onSuccess: () => {
          this.formConvertItem.reset();
          this.cancel();
          this.updateData();
          this.convertedMsg();
        },
        onError: (errors) => {
          this.stockBalanceDeclared = true;
        },
        onFinish: (visit) => {
          if (this.stockBalanceDeclared != true) {
            this.convertItemDialog = false;
            this.cancel();
            this.updateData();
            //   this.updatedMsg();
          }
        },
      });
    },
    confirmCancelItem(item) {
      //   console.log(item);
      this.requestStockId = item.id;
      this.cancelItemDialog = true;
    },
    cancelItem() {
      this.form.delete(route('requeststocks.destroy', this.requestStockId), {
        preserveScroll: true,
        onSuccess: () => {
          this.requestStockList = [];
          this.cancelItemDialog = false;
          this.requestStockId = null;
          this.form.clearErrors();
          this.form.reset();
          this.updateData();
          this.cancelledMsg();
        },
      });
    },
    cancel() {
      this.requestStockId = null;
      this.isUpdate = false;
      this.createRequestStocksDialog = false;
      this.editWardStocksDialog = false;
      this.consignmentDialog = false;
      this.targetItemDesc = null;
      this.convertItemDialog = false;
      this.oldQuantity = 0;
      this.form.reset();
      this.form.clearErrors();
      this.formWardStocks.reset();
      this.formWardStocks.clearErrors();
      this.formConvertItem.reset();
      this.formConvertItem.clearErrors();
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
    // ward stocks logs
    editWardStocks(data) {
      //   console.log(data);
      this.editWardStocksDialog = true;

      this.formWardStocks.ward_stock_id = data.ward_stock_id;
      this.formWardStocks.item = data.item;
      this.formWardStocks.current_quantity = data.quantity;
      this.formWardStocks.quantity = data.quantity;
      this.formWardStocks.expiration_date = data.expiration_date;
    },
    submitEditWardStocks() {
      this.formWardStocks.post(route('wardsstockslogs.store'), {
        preserveScroll: true,
        onSuccess: () => {
          this.editWardStocksDialog = false;
          this.cancel();
          this.updateData();
          this.updatedStockMsg();
        },
      });
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
    'formConsignment.cl2comb': function (val) {
      this.selectedItemsUomDesc = null;
      console.log(this.itemsList);

      this.itemsList.forEach((e) => {
        if (e.cl2comb == val) {
          if (e.uomdesc != null) {
            // console.log(e.uomdesc);
            this.selectedItemsUomDesc = e.uomdesc;
            this.formConsignment.uomcode = e.uomcode;
          } else {
            this.selectedItemsUomDesc = null;
          }
        }
      });
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
