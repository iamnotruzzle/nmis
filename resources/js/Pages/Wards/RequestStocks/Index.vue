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
                  label="Receive"
                  class="ml-2"
                  icon="pi pi-check"
                  iconPos="right"
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
                  v-if="slotProps.data.status == 'ACKNOWLEDGED'"
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
          v-model:visible="createRequestStocksDialog"
          :modal="true"
          class="p-fluid w-5"
          :closeOnEscape="false"
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
              label="Request"
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
              label="No"
              icon="pi pi-times"
              class="p-button-text"
              @click="editStatusDialog = false"
            />
            <Button
              label="Yes"
              icon="pi pi-check"
              severity="success"
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

        <!-- MedicalGases -->
        <Dialog
          v-model:visible="medicalGasesDialog"
          :modal="true"
          class="p-fluid w-4"
          @hide="whenDialogIsHidden"
        >
          <template #header>
            <div class="text-primary text-xl font-bold">MEDICAL GASES</div>
          </template>
          <div class="field">
            <label for="fundSource">Fund source</label>
            <Dropdown
              id="fundSource"
              required="true"
              v-model="formMedicalGases.fund_source"
              :options="fundSourceList"
              filter
              showClear
              dataKey="chrgcode"
              optionLabel="chrgdesc"
              optionValue="chrgcode"
              class="w-full"
              :class="{ 'p-invalid': formMedicalGases.fund_source == '' }"
            />
            <small
              class="text-error"
              v-if="formMedicalGases.errors.fund_source"
            >
              {{ formMedicalGases.errors.fund_source }}
            </small>
          </div>
          <div class="field">
            <label>Item</label>
            <Dropdown
              required="true"
              v-model="formMedicalGases.cl2comb"
              :options="medicalGasList"
              :virtualScrollerOptions="{ itemSize: 38 }"
              filter
              optionValue="cl2comb"
              optionLabel="cl2desc"
              class="w-full mb-3"
            />
            <small
              class="text-error"
              v-if="formMedicalGases.errors.cl2comb"
            >
              {{ formMedicalGases.errors.cl2comb }}
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
              v-model.trim="formMedicalGases.quantity"
              required="true"
              autofocus
              :class="{ 'p-invalid': formMedicalGases.quantity == '' || formMedicalGases.quantity == null }"
              onkeypress="return event.charCode >= 48 && event.charCode <= 57"
              inputId="integeronly"
            />
            <small
              class="text-error"
              v-if="formMedicalGases.errors.quantity"
            >
              {{ formMedicalGases.errors.quantity }}
            </small>
          </div>
          <div class="field">
            <label
              >Usage
              <b class="text-error"
                >NOTE: If 1 tank equals 1800 lbs, declare the usage as 1800 lbs regardless of the number of tanks
                received, as long as each tank is 1800 lbs.</b
              ></label
            >
            <InputText
              id="average"
              v-model.trim="formMedicalGases.average"
              required="true"
              autofocus
              :class="{ 'p-invalid': formMedicalGases.average == '' || formMedicalGases.average == null }"
              onkeypress="return event.charCode >= 48 && event.charCode <= 57"
              inputId="integeronly"
            />
            <small
              class="text-error"
              v-if="formMedicalGases.errors.average"
            >
              {{ formMedicalGases.errors.average }}
            </small>
          </div>
          <div class="field">
            <label for="delivered_date">Delivered date</label>
            <Calendar
              v-model="formMedicalGases.delivered_date"
              dateFormat="mm-dd-yy"
              showIcon
              showButtonBar
              :manualInput="false"
              :hideOnDateTimeSelect="true"
            />
            <small
              class="text-error"
              v-if="formMedicalGases.errors.delivered_date"
            >
              {{ formMedicalGases.errors.delivered_date }}
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
                formMedicalGases.processing ||
                formMedicalGases.fund_source == null ||
                formMedicalGases.cl2comb == null ||
                formMedicalGases.quantity == null ||
                formMedicalGases.quantity <= 0 ||
                formMedicalGases.average == null ||
                formMedicalGases.average <= 0 ||
                formMedicalGases.delivered_date == null
              "
              @click="submitMedicalGases"
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
            <div class="text-orange-500 text-xl font-bold">CONSIGNMENT</div>
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
            <label>Items</label>
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
              onkeypress="return event.charCode >= 48 && event.charCode <= 57"
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
            <label>Price per unit</label>
            <InputNumber
              id="price_per_unit"
              inputId="minmaxfraction"
              :minFractionDigits="2"
              :maxFractionDigits="5"
              v-model.trim="formConsignment.price_per_unit"
              required="true"
              :class="{ 'p-invalid': formConsignment.price_per_unit == '' || formConsignment.price_per_unit == null }"
            />
            <small
              class="text-error"
              v-if="formConsignment.errors.price_per_unit"
            >
              {{ formConsignment.errors.price_per_unit }}
            </small>
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
            <small
              class="text-error"
              v-if="formConsignment.errors.delivered_date"
            >
              {{ formConsignment.errors.delivered_date }}
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
              severity="warning"
              text
              type="submit"
              :disabled="
                formConsignment.processing ||
                formConsignment.fund_source == null ||
                formConsignment.cl2comb == null ||
                formConsignment.quantity == null ||
                formConsignment.quantity <= 0 ||
                formConsignment.price_per_unit == null ||
                formConsignment.price_per_unit <= 0 ||
                formConsignment.delivered_date == null
              "
              @click="submitConsignment"
            />
          </template>
        </Dialog>

        <!-- Existing -->
        <Dialog
          v-model:visible="existingDialog"
          :modal="true"
          class="p-fluid w-4"
          @hide="whenDialogIsHidden"
        >
          <template #header>
            <div class="text-orange-500 text-xl font-bold">EXISTING STOCK</div>
          </template>
          <div class="field">
            <label>Items</label>
            <Dropdown
              required="true"
              v-model="formExisting.cl2comb"
              :options="itemsList"
              :virtualScrollerOptions="{ itemSize: 38 }"
              filter
              optionValue="cl2comb"
              optionLabel="cl2desc"
              class="w-full mb-3"
              :disabled="isUpdateExisting == true"
            />
          </div>
          <div class="field">
            <label for="unit">Unit</label>
            <InputText
              id="unit"
              v-model.trim="selectedItemsUomDesc"
              readonly
              :disabled="isUpdateExisting == true"
            />
          </div>
          <div class="field">
            <label>Quantity</label>
            <InputText
              id="quantity"
              v-model.trim="formExisting.quantity"
              required="true"
              autofocus
              :class="{ 'p-invalid': formExisting.quantity == '' || formExisting.quantity == null }"
              onkeypress="return event.charCode >= 48 && event.charCode <= 57"
              inputId="integeronly"
            />
            <small
              class="text-error"
              v-if="formExisting.errors.quantity"
            >
              {{ formExisting.errors.quantity }}
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
              v-if="isUpdateExisting == false"
              label="Save"
              icon="pi pi-check"
              severity="warning"
              text
              type="submit"
              :disabled="formExisting.processing || formExisting.cl2comb == null || formExisting.quantity == null"
              @click="submitExisting"
            />
            <Button
              v-else
              label="Update"
              icon="pi pi-check"
              severity="warning"
              text
              type="submit"
              :disabled="formExisting.processing || formExisting.cl2comb == null || formExisting.quantity == null"
              @click="submitExisting"
            />
          </template>
        </Dialog>

        <!-- update stocks -->
        <Dialog
          v-model:visible="returnToCsrDialog"
          :modal="true"
          class="p-fluid w-3"
          @hide="whenDialogIsHidden"
        >
          <template #header>
            <div class="text-primary text-xl font-bold">RETURN STOCK TO CSR</div>
          </template>
          <div class="field flex flex-column">
            <label for="unit">Item</label>
            <TextArea
              v-model.trim="formReturnToCsr.item"
              readonly
              rows="3"
            />
          </div>
          <div class="field flex flex-column">
            <label>Quantity</label>
            <InputText
              id="quantity"
              v-model.trim="formReturnToCsr.quantity"
              required="true"
              autofocus
              class="my-0"
              :class="{
                'p-invalid':
                  formReturnToCsr.processing ||
                  formReturnToCsr.quantity == '' ||
                  formReturnToCsr.quantity == null ||
                  Number(formReturnToCsr.quantity) <= 0 ||
                  Number(formReturnToCsr.quantity) > Number(previousQty),
              }"
              @keydown="restrictNonNumericAndPeriod"
              inputId="integeronly"
            />
            <small
              class="text-error"
              v-if="formReturnToCsr.errors.quantity"
            >
              {{ formReturnToCsr.errors.quantity }}
            </small>
          </div>
          <div class="field flex flex-column">
            <label for="remarks">Remarks <span class="text-error">(Required)</span></label>
            <TextArea
              v-model.trim="formReturnToCsr.remarks"
              rows="10"
              autofocus
              :class="{ 'p-invalid': formReturnToCsr.remarks == '' }"
            />
            <small
              class="text-error"
              v-if="formReturnToCsr.errors.remarks"
            >
              {{ formReturnToCsr.errors.remarks }}
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
                formReturnToCsr.processing ||
                formReturnToCsr.quantity == null ||
                formReturnToCsr.remarks == '' ||
                formReturnToCsr.remarks == null
              "
              @click="submitReturnToCsr"
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
                  label="EXISTING STOCK"
                  icon="pi pi-plus"
                  iconPos="right"
                  severity="info"
                  @click="openExistingDialog"
                />
                <div class="mr-2"></div>
                <Button
                  label="CONSIGNMENT"
                  icon="pi pi-plus"
                  iconPos="right"
                  severity="warning"
                  @click="openConsignmentDialog"
                />
                <div class="mr-2"></div>
                <!-- <Button
                  label="MEDICAL GASES"
                  icon="pi pi-plus"
                  iconPos="right"
                  @click="openMedicalGasesDialog"
                /> -->
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
            <template #body="{ data }">
              <!-- <span> {{ data.from }}</span> -->

              <Tag
                v-if="data.from == 'CSR'"
                value="CSR"
                severity="primary"
              />
              <Tag
                v-if="data.from == 'MEDICAL GASES'"
                value="MEDICAL GASES"
                severity="success"
              />
              <Tag
                v-if="data.from == 'CONSIGNMENT'"
                value="CONSIGNMENT"
                severity="warning"
              />
              <Tag
                v-if="data.from == 'EXISTING_STOCKS'"
                value="EXISTING STOCK"
                severity="info"
              />
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
            field="unit"
            header="UNIT"
            style="text-align: right; width: 5%"
            :pt="{ headerContent: 'justify-content-end' }"
            sortable
          >
          </Column>
          <Column
            field="quantity"
            header="QUANTITY"
            style="text-align: right; width: 5%"
            sortable
          >
            <template #body="{ data }">
              {{ data.quantity }}
            </template>
          </Column>
          <Column
            field="average"
            header="AVERAGE"
            style="text-align: right; width: 5%"
            sortable
          >
            <template #body="{ data }">
              <p v-if="data.is_consumable == 'y'">
                <span class="test-success"> {{ data.average }}/unit</span>
              </p>
            </template>
          </Column>
          <Column
            field="expiration_date"
            header="EXPIRATION DATE"
            style="text-align: right; width: 15%"
            :pt="{ headerContent: 'justify-content-end' }"
            sortable
          >
            <template #body="{ data }">
              <div
                v-if="data.is_consumable != 'y'"
                class="flex flex-column"
              >
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
            :pt="{ headerContent: 'justify-content-center' }"
          >
            <template #body="slotProps">
              <div class="flex justify-content-center">
                <Button
                  v-if="slotProps.data.from == 'CSR'"
                  label="RETURN"
                  severity="primary"
                  @click="returnToCsr(slotProps.data)"
                />
                <Button
                  v-if="slotProps.data.from == 'EXISTING_STOCKS'"
                  label="UPDATE"
                  severity="info"
                  @click="openUpdateStock(slotProps.data)"
                />
              </div>
            </template>
          </Column>
        </DataTable>
        <!-- test -->
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
          <div style="display: flex; width: 100%; font-size: 1.25rem">
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
              <!-- uncomment -->
              <!-- <td style="border: 1px solid black; text-align: center">
                <p><strong>Unit</strong></p>
              </td> -->
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
              <!-- uncomment -->
              <!-- <td style="border: 1px solid black; text-align: center">
                <p>Pcs</p>
              </td> -->
              <td style="border: 1px solid black; text-align: center">
                <p>{{ item.description }}</p>
              </td>
              <td style="border: 1px solid black; text-align: center">
                <p>{{ item.req_qty }}</p>
              </td>
              <td style="border: 1px solid black; text-align: center">
                <!-- <v-icon
                  v-if="item.stock_avail == 'y'"
                  name="bi-check2"
                ></v-icon> -->
              </td>
              <td style="border: 1px solid black; text-align: center">
                <!-- <v-icon
                  v-if="item.stock_avail == 'n'"
                  name="bi-check2"
                ></v-icon> -->
              </td>
              <td style="border: 1px solid black; text-align: center">
                <p>{{ item.issue_qty }}</p>
              </td>
              <td style="border: 1px solid black">
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
    medicalGas: Object,
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
      medicalGasesDialog: false,
      consignmentDialog: false,
      isUpdateExisting: false,
      isUpdateConsignment: false,
      existingDialog: false,
      returnToCsrDialog: false,
      editAverageOfStocksDialog: false,
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
      medicalGasList: [],
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
      formMedicalGases: this.$inertia.form({
        authLocation: null,
        fund_source: null,
        cl2comb: null,
        uomcode: null,
        quantity: null,
        average: null,
        delivered_date: null,
      }),
      formConsignment: this.$inertia.form({
        id: null,
        authLocation: null,
        fund_source: null,
        cl2comb: null,
        uomcode: null,
        quantity: null,
        price_per_unit: null,
        delivered_date: null,
      }),
      formExisting: this.$inertia.form({
        id: null,
        authLocation: null,
        fund_source: null,
        cl2comb: null,
        uomcode: null,
        quantity: null,
        prev_quantity: null,
        delivered_date: null,
      }),
      formReturnToCsr: this.$inertia.form({
        ward_stock_id: null,
        item: null,
        quantity: null,
        expiration_date: null,
        remarks: null,
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
    };
  },
  // created will be initialize before mounted
  created() {
    this.totalRecords = this.requestedStocks.total;
    this.params.page = this.requestedStocks.current_page;
    this.rows = this.requestedStocks.per_page;
  },
  mounted() {
    // console.log(this.requestedStocks);
    // window.Echo.channel('issued').listen('ItemIssued', (args) => {
    //   if (args.message[0] == this.$page.props.authWardcode.wardcode) {
    //     router.reload({
    //       onFinish: (e) => {
    //         this.requestStockList = [];
    //         this.storeRequestedStocksInContainer();
    //         this.createRequestStocksDialog = false;
    //         this.loading = false;
    //       },
    //     });
    //   }
    // });

    this.storeFundSourceInContainer();
    this.storeItemsInController();
    this.storeRequestedStocksInContainer();
    this.storeCurrentWardStocksInContainer();

    this.loading = false;

    // console.log('currentWardStocks', this.currentWardStocks);
    // console.log('currentWardStocks2', this.currentWardStocks2);
  },
  computed: {
    user() {
      return this.$page.props.auth.user;
    },
  },
  methods: {
    print(data) {
      if (data) {
        console.log(data);
        // Set up the print form details
        this.printForm.office = data.requested_at;
        this.printForm.ris_no = `RIS-${data.id}`;
        this.printForm.items = [];
        this.printForm.requested_by = data.requested_by;
        this.printForm.approved_by = data.approved_by;

        data.request_stocks_details.forEach((e) => {
          //   console.log(e);
          this.printForm.items.push({
            stock_no: e.issued_item.length === 0 ? '' : e.issued_item[0].id,
            description: e.item_details.cl2desc,
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
    openUpdateStock(data) {
      console.log(data);

      if (data.from == 'EXISTING_STOCKS') {
        this.formExisting.id = data.ward_stock_id;
        this.formExisting.cl2comb = data.cl2comb;
        this.formExisting.quantity = data.quantity;

        this.isUpdateExisting = true;
        this.existingDialog = true;
      } else {
        // data.from == "CONSIGNMENT"
        // this.formConsignment.id = data.ward_stock_id;
        // this.formConsignment.fund_source = data.fund_source;
        // this.formConsignment.cl2comb = data.cl2comb;
        // this.formConsignment.quantity = data.quantity;
        // this.formConsignment.price_per_unit = data.price_per_unit;
        // this.formConsignment.delivered_date = data.delivered_date;
        // this.isUpdateConsignment = true;
        // this.consignmentDialog = true;
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

      this.medicalGasList = []; // reset
      this.medicalGas.forEach((e) => {
        this.medicalGasList.push({
          cl2comb: e.cl2comb,
          cl2desc: e.cl2desc,
          uomcode: e.uomcode,
          uomdesc: e.uomdesc,
        });
      });

      //   console.log(this.itemsList);
    },
    // use storeRequestedStocksInContainer() function so that every time you make
    // server request such as POST, the data in the table
    // is updated
    storeRequestedStocksInContainer() {
      this.requestStockList = []; // reset

      this.requestedStocks.data.forEach((e) => {
        // console.log(e);
        this.requestStockList.push({
          id: e.id,
          status: e.status,
          requested_by: e.requested_by_details.firstname + ' ' + e.requested_by_details.lastname,
          //   requested_by_image: e.requested_by_details.user_account.image,
          requested_at: e.requested_at_details.wardname,
          approved_by:
            e.approved_by_details != null
              ? e.approved_by_details.firstname + ' ' + e.approved_by_details.lastname
              : null,
          //   approved_by_image: e.approved_by_details != null ? e.approved_by_details.user_account.image : null,
          //   approved_by_image: null,
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
          average: e.average,
          is_consumable: e.is_consumable == null ? null : e.is_consumable,
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
          average: e.average,
          is_consumable: e.is_consumable == null ? null : e.is_consumable,
          expiration_date: expiration_date.toString(),
        });
      });

      //   console.log(this.currentWardStocksList);
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
    openMedicalGasesDialog() {
      this.formMedicalGases.clearErrors();
      this.formMedicalGases.reset();
      this.medicalGasesDialog = true;
    },
    openConsignmentDialog() {
      this.formMedicalGases.clearErrors();
      this.formMedicalGases.reset();
      this.consignmentDialog = true;
    },
    openExistingDialog() {
      this.formMedicalGases.clearErrors();
      this.formMedicalGases.reset();
      this.existingDialog = true;
    },
    // when dialog is hidden, do this function
    whenDialogIsHidden() {
      this.$emit(
        'hide',
        (this.requestStockId = null),
        (this.isUpdate = false),
        (this.isUpdateExisting = false),
        (this.isUpdateConsignment = false),
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
        this.formMedicalGases.clearErrors(),
        this.formMedicalGases.reset(),
        this.formConsignment.clearErrors(),
        this.formConsignment.reset(),
        this.formReturnToCsr.clearErrors(),
        this.formReturnToCsr.reset(),
        this.formUpdateStatus.reset(),
        this.formExisting.clearErrors(),
        this.formExisting.reset()
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
        onFinish: () => {
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
          onFinish: () => {
            this.requestStockId = null;
            this.createRequestStocksDialog = false;
            this.cancel();
            this.updateData();
            this.updatedMsg();
            this.loading = false;
          },
        });
      } else {
        this.form.post(route('requeststocks.store'), {
          preserveScroll: true,
          onFinish: () => {
            this.requestStockId = null;
            this.createRequestStocksDialog = false;
            this.cancel();
            this.updateData();
            this.createdMsg();
            this.loading = false;
          },
        });
      }
    },
    submitMedicalGases() {
      if (
        this.formMedicalGases.processing ||
        this.formMedicalGases.fund_source == null ||
        this.formMedicalGases.cl2comb == null ||
        this.formMedicalGases.quantity == null ||
        this.formMedicalGases.average == null ||
        this.formMedicalGases.delivered_date == null
      ) {
        return false;
      }

      this.formMedicalGases.authLocation = this.$page.props.authWardcode.wardcode;
      if (
        this.formMedicalGases.fund_source != null ||
        this.formMedicalGases.fund_source != '' ||
        this.formMedicalGases.cl2comb != null ||
        this.formMedicalGases.cl2comb != '' ||
        this.formMedicalGases.quantity != null ||
        this.formMedicalGases.quantity != '' ||
        this.formMedicalGases.quantity != 0 ||
        this.formMedicalGases.average != '' ||
        this.formMedicalGases.average != 0 ||
        this.formMedicalGases.delivered_date != null ||
        this.formMedicalGases.delivered_date != ''
      ) {
        // console.log('success');
        this.formMedicalGases.post(route('medicalGases.store'), {
          preserveScroll: true,
          onFinish: () => {
            this.formMedicalGases.reset();
            this.cancel();
            this.updateData();
            this.createdMsg();
            this.loading = false;
          },
        });
      }
    },
    submitConsignment() {
      if (
        this.formConsignment.processing ||
        this.formConsignment.fund_source == null ||
        this.formConsignment.cl2comb == null ||
        this.formConsignment.quantity == null ||
        this.formConsignment.price_per_unit == null ||
        this.formConsignment.price_per_unit <= 0 ||
        this.formConsignment.delivered_date == null
      ) {
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
        this.formConsignment.price_per_unit != '' ||
        this.formConsignment.price_per_unit != 0 ||
        this.formConsignment.delivered_date != null ||
        this.formConsignment.delivered_date != ''
      ) {
        // console.log('success');
        this.formConsignment.post(route('consignment.store'), {
          preserveScroll: true,
          onFinish: () => {
            this.formConsignment.reset();
            this.cancel();
            this.updateData();
            this.createdMsg();
            this.loading = false;
          },
        });
      }
    },
    submitExisting() {
      if (this.formExisting.processing || this.formExisting.cl2comb == null || this.formExisting.quantity == null) {
        return false;
      }

      this.formExisting.authLocation = this.$page.props.authWardcode.wardcode;
      if (
        this.formExisting.cl2comb != null ||
        this.formExisting.cl2comb != '' ||
        this.formExisting.quantity != null ||
        this.formExisting.quantity != ''
      ) {
        // check if in update mode
        if (this.isUpdateExisting == false) {
          this.formExisting.post(route('existingstock.store'), {
            preserveScroll: true,
            onFinish: (e) => {
              //   console.log('$page', this.$page.props.flash.noItemPrice);

              if (this.$page.props.flash.noItemPrice != null) {
                // this.cancel();
                this.noItemPriceMsg();
              } else {
                this.formExisting.reset();
                this.cancel();
                this.updateData();
                this.createdMsg();
                this.loading = false;
              }
            },
          });
        } else {
          this.formExisting.put(route('existingstock.update', this.formExisting.id), {
            preserveScroll: true,
            onSuccess: () => {
              this.cancel();
              this.updateData();
              this.updateExistingMessage();
            },
          });
        }
      }
    },
    confirmCancelItem(item) {
      //   console.log(item);
      this.requestStockId = item.id;
      this.cancelItemDialog = true;
    },
    cancelItem() {
      this.form.delete(route('requeststocks.destroy', this.requestStockId), {
        preserveScroll: true,
        onFinish: () => {
          this.loading = false;
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
      this.isUpdateExisting = false;
      this.isUpdateConsignment = false;
      this.createRequestStocksDialog = false;
      this.returnToCsrDialog = false;
      this.editAverageOfStocksDialog = false;
      this.medicalGasesDialog = false;
      this.consignmentDialog = false;
      this.existingDialog = false;
      this.editStatusDialog = false;
      this.targetItemDesc = null;
      this.oldQuantity = 0;
      this.selectedItemsUomDesc = '';
      this.form.reset();
      this.form.clearErrors();
      this.formMedicalGases.reset();
      this.formMedicalGases.clearErrors();
      this.formConsignment.reset();
      this.formConsignment.clearErrors();
      this.formExisting.reset();
      this.formExisting.clearErrors();
      this.formReturnToCsr.reset();
      this.formReturnToCsr.clearErrors();
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
    noItemPriceMsg() {
      this.$toast.add({
        severity: 'error',
        summary: 'Failed',
        detail:
          'Items do not have a price assigned yet, which is why they are not being added to your existing stock. Please contact CSR for further assistance.',
        life: 10000,
      });
    },
    updateExistingMessage() {
      this.$toast.add({ severity: 'warn', summary: 'Success', detail: 'Stock updated', life: 3000 });
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
    returnToCsr(data) {
      this.returnToCsrDialog = true;
      this.previousQty = data.quantity;

      this.formReturnToCsr.ward_stock_id = data.ward_stock_id;
      this.formReturnToCsr.item = data.item;
      this.formReturnToCsr.quantity = data.quantity;

      //   console.log(this.formReturnToCsr);
    },
    submitReturnToCsr() {
      //   console.log(this.previousQty);
      if (
        this.formReturnToCsr.processing ||
        this.formReturnToCsr.quantity == null ||
        Number(this.formReturnToCsr.quantity) <= 0 ||
        this.formReturnToCsr.remarks == '' ||
        Number(this.formReturnToCsr.quantity) > Number(this.previousQty) ||
        this.formReturnToCsr.remarks == null
      ) {
        return false;
      }

      this.formReturnToCsr.post(route('wardsstockslogs.store'), {
        preserveScroll: true,
        onFinish: () => {
          this.returnToCsrDialog = false;
          this.cancel();
          this.updateData();
          this.updatedStockMsg();
          this.loading = false;
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
    'formMedicalGases.cl2comb': function (val) {
      this.selectedItemsUomDesc = null;
      //   console.log(val);

      this.medicalGasList.forEach((e) => {
        if (e.cl2comb == val) {
          if (e.uomdesc != null || e.uomdesc == '') {
            // console.log('if');
            this.selectedItemsUomDesc = e.uomdesc;
            this.formMedicalGases.uomcode = e.uomcode;
          } else {
            this.selectedItemsUomDesc = null;
          }
        }
      });
    },
    'formConsignment.cl2comb': function (val) {
      this.selectedItemsUomDesc = null;
      //   console.log(val);

      this.itemsList.forEach((e) => {
        if (e.cl2comb == val) {
          if (e.uomdesc != null || e.uomdesc == '') {
            // console.log(e.uomdesc);
            this.selectedItemsUomDesc = e.uomdesc;
            this.formConsignment.uomcode = e.uomcode;
          } else {
            this.selectedItemsUomDesc = null;
          }
        }
      });

      //   console.log(this.selectedItemsUomDesc);
    },
    'formExisting.cl2comb': function (val) {
      this.selectedItemsUomDesc = null;
      //   console.log(val);

      this.itemsList.forEach((e) => {
        if (e.cl2comb == val) {
          if (e.uomdesc != null || e.uomdesc == '') {
            // console.log(e.uomdesc);
            this.selectedItemsUomDesc = e.uomdesc;
            this.formExisting.uomcode = e.uomcode;
          } else {
            this.selectedItemsUomDesc = null;
          }
        }
      });

      //   console.log(this.selectedItemsUomDesc);
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
