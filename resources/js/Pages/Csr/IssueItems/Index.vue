<template>
  <app-layout>
    <Head title="NMIS - RIS" />

    <div>
      <div class="card">
        <Toast />

        <DataTable
          class="p-datatable-sm"
          v-model:expandedRows="expandedRow"
          v-model:filters="filters"
          :value="requestStockList"
          selectionMode="single"
          lazy
          paginator
          removableSort
          :rows="rows"
          ref="dt"
          :totalRecords="totalRecords"
          @page="onPage($event)"
          dataKey="id"
          filterDisplay="row"
          :loading="loading"
        >
          <template #header>
            <div class="flex flex-wrap align-items-center justify-content-between gap-2">
              <span class="text-xl text-900 font-bold text-primary">REQUESTS</span>
              <div class="flex">
                <div class="mr-2">
                  <div class="p-inputgroup">
                    <span class="p-inputgroup-addon">
                      <i class="pi pi-search"></i>
                    </span>
                    <InputText
                      id="searchInput"
                      v-model="search"
                      placeholder="Search requested at"
                    />
                  </div>
                </div>
                <Button
                  label="MEDICAL GASES"
                  icon="pi pi-plus"
                  iconPos="right"
                  @click="openMedicalGasesDialog"
                />
              </div>
            </div>
          </template>
          <Column expander />
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
            filterField="status"
            :showFilterMenu="false"
            style="width: 10%"
          >
            <template #body="{ data }">
              <div class="text-center">
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
              </div>
            </template>
            <template #filter="{}">
              <Dropdown
                v-model="selectedStatus"
                :options="status"
                optionLabel="name"
                optionValue="code"
                placeholder="NO FILTER"
                class="w-full"
              />
            </template>
          </Column>
          <Column
            field="requested_by"
            header="PENDING BY"
            style="width: 20%"
          >
            <template #body="{ data }">
              <div class="flex flex-row align-items-center">
                <span class="font-semibold text-xl pl-3">
                  {{ data.requested_by }}
                </span>
              </div>
            </template>
          </Column>
          <Column
            field="approved_by"
            header="APPROVED BY"
            style="width: 20%"
          >
            <template #body="{ data }">
              <div class="flex flex-row align-items-center">
                <span class="font-semibold text-xl pl-3">
                  {{ data.approved_by }}
                </span>
              </div>
            </template>
          </Column>
          <Column
            field="requested_at"
            header="REQUESTED AT"
            style="width: 20%"
          >
            <template #body="{ data }">
              {{ data.requested_at }}
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
            style="width: 10%"
          >
            <template #body="slotProps">
              <div class="text-center">
                <Button
                  v-if="slotProps.data.status == 'PENDING'"
                  icon="pi pi-check"
                  class="mr-1"
                  rounded
                  text
                  severity="success"
                  @click="editStatus(slotProps.data)"
                />
                <Button
                  v-if="slotProps.data.status == 'ACKNOWLEDGED'"
                  icon="pi pi-plus"
                  class="mr-1"
                  rounded
                  text
                  severity="primary"
                  @click="openCreateRequestStocksDialog(slotProps.data)"
                />
                <Button
                  v-if="slotProps.data.status == 'FILLED'"
                  icon="pi pi-pencil"
                  class="mr-1"
                  rounded
                  text
                  severity="warning"
                  @click="editRequestedStock(slotProps.data)"
                />
              </div>
            </template>
          </Column>
          <template #expansion="slotProps">
            <div class="p-3">
              <DataTable
                paginator
                removableSort
                :rows="7"
                :value="slotProps.data.request_stocks_details"
                showGridlines
              >
                <template #header>
                  <div class="flex flex-wrap align-items-center justify-content-between gap-2">
                    <span class="text-primary">PENDING ITEMS</span>
                    <div class="flex flex-row align-items-center">
                      <!-- <a
                        v-if="slotProps.data.status != 'PENDING'"
                        :href="`issueitems/issued?from=${params.from}&to=${params.to}
                      &id=${(params.id = slotProps.data.id)}`"
                        target="_blank"
                        class="mr-3"
                      >
                        <i
                          class="pi pi-download"
                          :style="{ color: 'green', 'font-size': '1.2rem' }"
                        ></i>
                      </a> -->
                      <Button
                        v-if="slotProps.data.status != 'PENDING'"
                        icon="pi pi-book"
                        severity="success"
                        text
                        rounded
                        aria-label="export"
                        @click="viewIssuedItem(slotProps.data)"
                        size="large"
                      />
                    </div>
                  </div>
                </template>
                <Column
                  header="ITEM"
                  style="width: 50%"
                >
                  <template #body="{ data }"> {{ data.item_details.cl2desc }} </template>
                </Column>
                <Column
                  field="requested_qty"
                  header="PENDING QTY"
                  style="text-align: right; width: 20%"
                >
                </Column>
                <Column
                  field="approved_qty"
                  header="APPROVED QTY"
                  style="text-align: right; width: 20%"
                ></Column>
                <Column
                  field="remarks"
                  header="REMARKS"
                  style="width: 10%"
                ></Column>
              </DataTable>
            </div>
          </template>
        </DataTable>
      </div>

      <div class="card">
        <DataTable
          class="p-datatable-sm w-full"
          v-model:filters="medicalGasFilter"
          :value="wardsMedicalGasStockList"
          paginator
          :rows="20"
          dataKey="stock_id"
          sortField="quantity"
          :sortOrder="1"
          removableSort
          :globalFilterFields="['wardname', 'cl2desc']"
          showGridlines
        >
          <template #header>
            <div class="flex flex-wrap align-items-center justify-content-between gap-2">
              <span class="text-xl text-900 font-bold text-primary">DISTRIBUTED MEDICAL GAS</span>
              <div class="flex">
                <div class="mr-2">
                  <div class="p-inputgroup">
                    <span class="p-inputgroup-addon">
                      <i class="pi pi-search"></i>
                    </span>
                    <InputText
                      id="searchInput"
                      v-model="medicalGasFilter['global'].value"
                      placeholder="Search"
                    />
                  </div>
                </div>
              </div>
            </div>
          </template>
          <template #empty> No data found. </template>
          <template #loading> Loading data. Please wait. </template>
          <Column
            field="stock_id"
            header="STOCK ID"
          >
          </Column>
          <Column
            field="wardname"
            header="WARD"
            sortable
          >
          </Column>
          <Column
            field="cl2desc"
            header="GAS"
            sortable
          >
          </Column>
          <Column
            field="quantity"
            header="QUANTITY"
            sortable
            style="width: 5%; text-align: right"
            :pt="{ headerContent: 'justify-content-end' }"
          >
            <template #body="{ data }">
              <p class="text-right">
                {{ data.quantity }}
              </p>
            </template>
          </Column>
          <Column header="ACTION">
            <template #body="slotProps">
              <div class="text-center">
                <Button
                  icon="pi pi-pencil"
                  rounded
                  text
                  severity="warning"
                  @click="editMedicalGas(slotProps.data)"
                />
              </div>
            </template>
          </Column>
        </DataTable>
      </div>

      <!-- @hide="clickOutsideDialog" -->
      <!-- create & edit dialog -->
      <Dialog
        v-model:visible="createRequestStocksDialog"
        :modal="true"
        class="p-fluid"
        @hide="whenDialogIsHidden"
      >
        <template #header>
          <div class="text-primary text-xl font-bold">REQUESTED STOCKS</div>
        </template>
        <div class="field">
          <DataTable
            v-model:filters="requestStockListDetailsFilter"
            :globalFilterFields="['cl2desc']"
            :value="requestStockListDetails"
            class="p-datatable-sm"
            paginator
            showGridlines
            removableSort
            :rows="7"
          >
            <template #header>
              <div>
                <div class="mr-2">
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
              </div>
              <!-- <p
                  class="text-error text-xl font-semibold my-1"
                  v-if="stockBalanceDeclared != false"
                >
                  {{ $page.props.errors['requestStockListDetails.0.cl2comb'].toUpperCase() }}
                </p> -->
            </template>
            <Column
              field="cl2desc"
              header="PENDING ITEM"
              style="width: 50%"
              sortable
            ></Column>
            <Column
              field="requested_qty"
              header="PENDING QTY"
              style="text-align: right; width: 10%"
            ></Column>
            <Column
              v-if="isUpdate"
              field="stock_qty"
              header="Current stock including approved qty"
              style="text-align: right; width: 10%"
            >
              <template #body="{ data }">
                {{ data.stock_w_approved }}
              </template>
            </Column>
            <Column
              v-else
              field="stock_qty"
              header="TOTAL STOCK"
              style="text-align: right; width: 10%"
            ></Column>
            <Column
              field="approved_qty"
              header="APPROVED QTY"
              style="text-align: right; width: 10%"
            >
              <template #body="slotProps">
                <InputText
                  id="quantity"
                  v-model="slotProps.data.approved_qty"
                  required="true"
                  autofocus
                  @keyup.enter="submit"
                  type="number"
                  onkeypress="return event.charCode >= 48 && event.charCode <= 57"
                  inputId="integeronly"
                />
              </template>
            </Column>
            <Column
              field="remarks"
              header="REMARKS (OPTIONAL)"
              style="width: 20%"
            >
              <template #body="slotProps">
                <Textarea
                  v-model.trim="slotProps.data.remarks"
                  rows="2"
                  cols="30"
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

      <!-- view issued items -->
      <Dialog
        v-model:visible="issuedItemsDialog"
        :modal="true"
        class="p-fluid w-5"
        @hide="whenDialogIsHidden"
      >
        <template #header>
          <div class="text-primary text-xl font-bold">ISSUED ITEMS</div>
        </template>
        <div class="field">
          <DataTable
            v-model:filters="issuedItemsFilter"
            :globalFilterFields="['cl2desc']"
            :value="issuedItemList"
            class="p-datatable-sm w-full"
            paginator
            showGridlines
            removableSort
            :rows="7"
          >
            <template #header>
              <div>
                <div class="p-inputgroup">
                  <span class="p-inputgroup-addon">
                    <i class="pi pi-search"></i>
                  </span>
                  <InputText
                    id="searchInput"
                    v-model="issuedItemsFilter['global'].value"
                    placeholder="Search issued item"
                  />
                </div>
              </div>
            </template>
            <Column
              field="ris_no"
              header="RIS NO."
              sortable
              style="width: 10%"
            ></Column>
            <Column
              field="cl2desc"
              header="ITEM"
              sortable
              style="width: 60%"
            ></Column>
            <Column
              field="quantity"
              header="QTY"
              sortable
              style="width: 10%"
            ></Column>
            <Column
              field="expiration_date"
              header="EXP. DATE"
              sortable
              style="width: 20%"
            >
              <template #body="{ data }">
                {{ tzone(data.expiration_date) }}
              </template>
            </Column>
          </DataTable>
        </div>
      </Dialog>

      <!-- edit status -->
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
            Are you sure you want to <b>update</b> the status of this requested stocks to <b>ACKNOWLEDGED</b>?
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

      <!-- MedicalGases -->
      <Dialog
        v-model:visible="medicalGasDialog"
        :modal="true"
        class="p-fluid w-4"
        @hide="whenDialogIsHidden"
      >
        <template #header>
          <div class="text-primary text-xl font-bold">MEDICAL GASES</div>
        </template>
        <div class="field">
          <label
            for="location"
            class="block text-900 text-xl font-medium mb-1"
          >
            Location
          </label>
          <Dropdown
            v-model="formMedicalGas.wardcode"
            :options="wardList"
            :virtualScrollerOptions="{ itemSize: 38 }"
            optionLabel="wardname"
            optionValue="wardcode"
            filter
            class="w-full"
          >
          </Dropdown>
        </div>
        <div class="field">
          <label for="fundSource">Fund source</label>
          <Dropdown
            id="fundSource"
            required="true"
            v-model="formMedicalGas.fund_source"
            :options="fundSourceList"
            filter
            showClear
            dataKey="chrgcode"
            optionLabel="chrgdesc"
            optionValue="chrgcode"
            class="w-full"
            :class="{ 'p-invalid': formMedicalGas.fund_source == '' }"
          />
          <small
            class="text-error"
            v-if="formMedicalGas.errors.fund_source"
          >
            {{ formMedicalGas.errors.fund_source }}
          </small>
        </div>
        <div class="field">
          <label>Item</label>
          <Dropdown
            required="true"
            v-model="formMedicalGas.cl2comb"
            :options="medicalGasList"
            :virtualScrollerOptions="{ itemSize: 38 }"
            filter
            optionValue="cl2comb"
            optionLabel="cl2desc"
            class="w-full mb-3"
          />
          <small
            class="text-error"
            v-if="formMedicalGas.errors.cl2comb"
          >
            {{ formMedicalGas.errors.cl2comb }}
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
            v-model.trim="formMedicalGas.quantity"
            required="true"
            autofocus
            :class="{ 'p-invalid': formMedicalGas.quantity == '' || formMedicalGas.quantity == null }"
            onkeypress="return event.charCode >= 48 && event.charCode <= 57"
            inputId="integeronly"
          />
          <small
            class="text-error"
            v-if="formMedicalGas.errors.quantity"
          >
            {{ formMedicalGas.errors.quantity }}
          </small>
        </div>
        <div class="field">
          <label>Pounds per tank</label>
          <InputText
            id="average"
            v-model.trim="formMedicalGas.average"
            required="true"
            autofocus
            :class="{ 'p-invalid': formMedicalGas.average == '' || formMedicalGas.average == null }"
            onkeypress="return event.charCode >= 48 && event.charCode <= 57"
            inputId="integeronly"
          />
          <small
            class="text-error"
            v-if="formMedicalGas.errors.average"
          >
            {{ formMedicalGas.errors.average }}
          </small>
        </div>
        <div class="field">
          <label>Total pounds <span class="text-error">(Quantity * pounds per tank)</span></label>
          <InputText
            v-model="totalUsage"
            readonly
          />
        </div>
        <div class="field">
          <label for="delivered_date">Delivered date</label>
          <Calendar
            v-model="formMedicalGas.delivered_date"
            dateFormat="mm-dd-yy"
            showIcon
            showButtonBar
            :manualInput="false"
            :hideOnDateTimeSelect="true"
          />
          <small
            class="text-error"
            v-if="formMedicalGas.errors.delivered_date"
          >
            {{ formMedicalGas.errors.delivered_date }}
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
              formMedicalGas.processing ||
              formMedicalGas.fund_source == null ||
              formMedicalGas.cl2comb == null ||
              formMedicalGas.quantity == null ||
              formMedicalGas.quantity <= 0 ||
              formMedicalGas.average == null ||
              formMedicalGas.average <= 0 ||
              formMedicalGas.delivered_date == null
            "
            @click="submitMedicalGases"
          />
        </template>
      </Dialog>

      <!-- MedicalGases -->
      <Dialog
        v-model:visible="updateMedicalGasDialog"
        :modal="true"
        class="p-fluid w-4"
        @hide="whenDialogIsHidden"
      >
        <template #header>
          <div class="text-primary text-xl font-bold">
            Update medical gases of <span class="text-yellow-500">{{ formUpdateMedicalGas.wardname }}</span>
          </div>
        </template>
        <div class="field">
          <label for="item">Item</label>
          <InputText
            id="item"
            v-model.trim="formUpdateMedicalGas.cl2desc"
            readonly
          />
        </div>
        <div class="field">
          <label>Quantity to remove (TANK)</label>
          <InputText
            id="quantity"
            v-model.trim="formUpdateMedicalGas.quantity"
            required="true"
            autofocus
            :class="{
              'p-invalid':
                formUpdateMedicalGas.quantity == '' ||
                formUpdateMedicalGas.quantity == null ||
                formUpdateMedicalGas.quantity <= 0 ||
                Number(formUpdateMedicalGas.quantity) > Number(formUpdateMedicalGas.prev_quantity),
            }"
            onkeypress="return event.charCode >= 48 && event.charCode <= 57"
            inputId="integeronly"
          />
          <small
            class="text-error"
            v-if="formUpdateMedicalGas.errors.quantity"
          >
            {{ formUpdateMedicalGas.errors.quantity }}
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
              formUpdateMedicalGas.processing ||
              formUpdateMedicalGas.quantity == null ||
              formUpdateMedicalGas.quantity <= 0 ||
              Number(formUpdateMedicalGas.quantity) > Number(formUpdateMedicalGas.prev_quantity)
            "
            @click="updateMedicalGas"
          />
        </template>
      </Dialog>

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
import Textarea from 'primevue/textarea';
import Tag from 'primevue/tag';
import moment from 'moment';
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
    Textarea,
    Link,
    InputNumber,
  },
  props: {
    authWardcode: Object,
    items: Array,
    requestedStocks: Object,
    medicalGas: Object,
    wardsMedicalGasStock: Object,
  },
  data() {
    return {
      //   stockBalanceDeclared: false,
      //   expandedRow: null,
      expandedRow: [],
      // paginator
      loading: false,
      totalRecords: null,
      rows: null,
      // end paginator
      requestStockId: null,
      isUpdate: false,
      medicalGasDialog: false,
      createRequestStocksDialog: false,
      issuedItemsDialog: false,
      editStatusDialog: false,
      selectedItemsUomDesc: null,
      updateMedicalGasDialog: false,
      search: '',
      options: {},
      params: {},
      from: null,
      to: null,
      itemsList: [],
      requestStockList: [],
      issuedItemList: [],
      medicalGasList: [],
      fundSourceList: [],
      wardsMedicalGasStockList: [],
      wardList: [],
      // stock list details
      medicalGasFilter: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
        wardname: { value: null, matchMode: FilterMatchMode.CONTAINS },
        cl2desc: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
      requestStockListDetailsFilter: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
      issuedItemsFilter: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
      disabled: false,
      app_qty_checker: null,
      requestStockListDetails: [],
      item: null,
      cl2desc: null,
      requested_qty: null,
      approved_qty: null,
      remarks: null,
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
        approved_by: null,
        remarks: null,
        requestStockListDetails: [],
      }),
      formMedicalGas: this.$inertia.form({
        wardcode: null,
        fund_source: null,
        cl2comb: null,
        uomcode: null,
        quantity: null,
        average: null,
        delivered_date: null,
      }),
      formUpdateMedicalGas: this.$inertia.form({
        stock_id: null,
        cl2desc: null,
        quantity: null,
        prev_quantity: null,
        wardname: null,
      }),
      formUpdateStatus: this.$inertia.form({
        request_stock_id: null,
      }),
      selectedStatus: null,
      status: [
        { name: 'NO FILTER', code: '' },
        { name: 'PENDING', code: 'PENDING' },
        { name: 'ACKNOWLEDGED', code: 'ACKNOWLEDGED' },
        { name: 'FILLED', code: 'FILLED' },
        { name: 'RECEIVED', code: 'RECEIVED' },
        { name: 'CANCELLED', code: 'CANCELLED' },
      ],
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
    // store ward list
    this.$page.props.locations.forEach((e) => {
      if (e.wardcode != 'CSR' && e.wardcode != 'ADMIN') {
        this.wardList.push({
          wardcode: e.wardcode,
          wardname: e.wardname,
        });
      }
    });

    window.Echo.channel('request').listen('RequestStock', (args) => {
      router.reload({
        onSuccess: (e) => {
          console.log(e);
          this.requestStockList = [];
          this.storeRequestedStocksInContainer();
        },
      });
    });

    this.storeItemsInController();
    this.storeRequestedStocksInContainer();
    this.storeFundSourceInContainer();
    this.storeWardsMedicalGasStock();

    this.loading = false;
  },
  computed: {
    user() {
      return this.$page.props.auth.user;
    },
    totalUsage() {
      return Number(this.formMedicalGas.quantity) * Number(this.formMedicalGas.average);
    },
  },
  methods: {
    print(data) {
      console.log(data);
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
    tzone(date) {
      if (date == null || date == '') {
        return null;
      } else {
        return moment.tz(date, 'Asia/Manila').format('L');
      }
    },
    openMedicalGasesDialog() {
      this.formMedicalGas.reset();
      this.medicalGasDialog = true;
    },
    storeItemsInController() {
      this.items.forEach((e) => {
        this.itemsList.push({
          cl2comb: e.cl2comb,
          cl2desc: e.cl2desc,
        });
      });

      this.medicalGas.forEach((e) => {
        this.medicalGasList.push({
          cl2comb: e.cl2comb,
          cl2desc: e.cl2desc,
          uomcode: e.uomcode,
          uomdesc: e.uomdesc,
        });
      });
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
    storeWardsMedicalGasStock() {
      this.wardsMedicalGasStock.forEach((e) => {
        this.wardsMedicalGasStockList.push({
          stock_id: e.stock_id,
          wardname: e.wardname,
          cl2comb: e.cl2comb,
          cl2desc: e.cl2desc,
          quantity: e.quantity,
          average: e.average,
          total_usage: e.total_usage,
        });
      });
    },
    // use storeRequestedStocksInContainer() function so that every time you make
    // server request such as POST, the data in the table
    // is updated
    storeRequestedStocksInContainer() {
      //   console.log(this.requestedStocks.data);
      this.requestedStocks.data.forEach((e) => {
        this.requestStockList.push({
          id: e.id,
          status: e.status,
          requested_by: e.requested_by_details.firstname + ' ' + e.requested_by_details.lastname,
          approved_by:
            e.approved_by_details != null
              ? e.approved_by_details.firstname + ' ' + e.approved_by_details.lastname
              : null,
          remarks: e.remarks,
          requested_at: e.requested_at_details.wardname,
          created_at: e.created_at,
          request_stocks_details: e.request_stocks_details,
        });
      });
      //   console.log(this.requestStockList);
    },
    viewIssuedItem(data) {
      //   console.log('data', data.id);
      data.request_stocks_details.forEach((item) => {
        // console.log('item', item);

        item.issued_item.forEach((e) => {
          //   console.log(e);
          if (data.id == e.request_stocks_id) {
            this.issuedItemList.push({
              ris_no: e.ris_no,
              cl2desc: e.item_details.cl2desc,
              quantity: e.quantity,
              expiration_date: e.expiration_date,
            });
          }
        });
      });

      //   console.log(this.issuedItemList);
      this.issuedItemsDialog = true;
    },
    // getSeverity(status) {
    //   switch (status) {
    //     case 'PENDING':
    //       return 'primary';

    //     case 'ACKNOWLEDGED':
    //       return 'yellow';

    //     case 'FILLED':
    //       return 'info';

    //     case 'RECEIVED':
    //       return 'success';

    //     default:
    //       return null;
    //   }
    // },
    onPage(event) {
      this.params.page = event.page + 1;
      this.updateData();
    },
    updateData() {
      this.loading = true;

      this.$inertia.get('issueitems', this.params, {
        preserveState: true,
        preserveScroll: true,
        onFinish: (visit) => {
          this.totalRecords = this.requestedStocks.total;
          this.requestStockList = [];
          this.expandedRow = [];
          this.wardsMedicalGasStockList = [];
          this.medicalGasList = [];
          this.storeRequestedStocksInContainer();
          this.storeWardsMedicalGasStock();
          this.storeItemsInController();
          this.loading = false;
        },
      });
    },
    openCreateRequestStocksDialog(item) {
      //   console.log(item.request_stocks_details);
      this.form.clearErrors();
      this.form.reset();
      this.form.request_stocks_id = item.id;

      this.isUpdate = false;
      this.createRequestStocksDialog = true;
      this.requestStockId = item.id;

      item.request_stocks_details.forEach((e) => {
        // console.log(e);
        this.requestStockListDetails.push({
          request_stocks_details_id: e.id,
          cl2comb: e.cl2comb,
          cl2desc: e.item_details.cl2desc,
          requested_qty: e.requested_qty,
          approved_qty: e.approved_qty,
          remarks: e.remarks,
          stock_qty: e.converted_item.reduce((accumulator, object) => {
            return Number(accumulator) + Number(object.quantity_after);
          }, 0),
        });
      });
    },
    // when dialog is hidden, do this function
    whenDialogIsHidden() {
      this.$emit(
        'hide',
        // (this.stockBalanceDeclared = false),
        (this.requestStockId = null),
        (this.isUpdate = false),
        (this.requestStockListDetails = []),
        (this.item = null),
        (this.cl2desc = null),
        (this.requested_qty = null),
        (this.approved_qty = null),
        (this.remarks = null),
        (this.itemNotSelected = null),
        (this.itemNotSelectedMsg = null),
        (this.issuedItemList = []),
        (this.issuedItemsDialog = false),
        (this.medicalGasDialog = false),
        (this.updateMedicalGasDialog = false),
        (this.selectedItemsUomDesc = null),
        this.form.clearErrors(),
        this.form.reset()
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
          approved_qty: e.approved_qty,
          staticApproved_qty: e.approved_qty,
          stock_w_approved: Number(
            Number(e.approved_qty) +
              e.converted_item.reduce((accumulator, object) => {
                return Number(accumulator) + Number(object.quantity_after);
              }, 0)
          ),
          stock_qty: e.converted_item.reduce((accumulator, object) => {
            return Number(accumulator) + Number(object.quantity_after);
          }, 0),
          remarks: e.remarks,
        });
      });
      //   console.log(this.requestStockListDetails);
    },
    submit() {
      if (this.form.processing) {
        return false;
      }

      // setup approved by and requestStockListDetails before submitting
      this.form.approved_by = this.user.userDetail.employeeid;
      this.form.requestStockListDetails = this.requestStockListDetails;

      // prevents submitting the form using enter key when this.disabled is true or the
      // approved qty <= total stock
      if (this.disabled != true) {
        if (this.isUpdate) {
          //   console.log(this.requestStockListDetails);

          this.requestStockListDetails.forEach((e) => {
            e.approved_qty = e.approved_qty == '' || e.approved_qty == null ? 0 : e.approved_qty;
            e.remarks = e.remarks == '' || e.remarks == null ? null : e.remarks;
          });

          let isQtyEnough = this.requestStockListDetails.every(function (e) {
            return Number(e.approved_qty) <= Number(e.stock_w_approved);
          });

          if (isQtyEnough) {
            this.form.put(route('issueitems.update', this.requestStockId), {
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
            this.qtyIsNotEnough();
          }
        } else {
          this.requestStockListDetails.forEach((e) => {
            e.approved_qty = e.approved_qty == '' || e.approved_qty == null ? 0 : e.approved_qty;
            e.remarks = e.remarks == '' || e.remarks == null ? null : e.remarks;
          });

          let isQtyEnough = this.requestStockListDetails.every(function (e) {
            return Number(e.approved_qty) <= Number(e.stock_qty);
          });

          if (isQtyEnough) {
            this.form.post(route('issueitems.store'), {
              preserveScroll: true,
              onSuccess: () => {
                this.requestStockId = null;
                this.createRequestStocksDialog = false;
                this.cancel();
                this.updateData();
                this.createdMsg();
              },
            });
          } else {
            this.qtyIsNotEnough();
          }
        }
      }
    },
    submitMedicalGases() {
      if (
        this.formMedicalGas.processing ||
        this.formMedicalGas.wardcode == null ||
        this.formMedicalGas.fund_source == null ||
        this.formMedicalGas.cl2comb == null ||
        this.formMedicalGas.quantity == null ||
        this.formMedicalGas.average == null ||
        this.formMedicalGas.delivered_date == null
      ) {
        return false;
      }

      if (
        this.formMedicalGas.wardcode != null ||
        this.formMedicalGas.wardcode != '' ||
        this.formMedicalGas.fund_source != null ||
        this.formMedicalGas.fund_source != '' ||
        this.formMedicalGas.cl2comb != null ||
        this.formMedicalGas.cl2comb != '' ||
        this.formMedicalGas.quantity != null ||
        this.formMedicalGas.quantity != '' ||
        this.formMedicalGas.quantity != 0 ||
        this.formMedicalGas.average != '' ||
        this.formMedicalGas.average != 0 ||
        this.formMedicalGas.delivered_date != null ||
        this.formMedicalGas.delivered_date != ''
      ) {
        // console.log('success');
        this.formMedicalGas.post(route('medicalgastoward.store'), {
          preserveScroll: true,
          onFinish: () => {
            this.formMedicalGas.reset();
            this.cancel();
            this.updateData();
            this.createdMsg();
            this.loading = false;
          },
        });
      }
    },
    updateMedicalGas() {
      //   console.log(item);
      //   this.formUpdateStatus.status = item;

      if (
        this.formUpdateMedicalGas.processing ||
        this.formUpdateMedicalGas.quantity == '' ||
        this.formUpdateMedicalGas.quantity == null ||
        this.formUpdateMedicalGas.quantity <= 0 ||
        Number(this.formUpdateMedicalGas.quantity) > Number(this.formUpdateMedicalGas.prev_quantity)
      ) {
        return false;
      }

      this.formUpdateMedicalGas.put(route('medicalgastoward.update', this.formUpdateMedicalGas.stock_id), {
        preserveScroll: true,
        onSuccess: () => {
          this.updateMedicalGasDialog = false;
          this.cancel();
          this.updateData();
        },
      });
    },
    editMedicalGas(data) {
      //   console.log(data);

      this.updateMedicalGasDialog = true;

      this.formUpdateMedicalGas.stock_id = data.stock_id;
      this.formUpdateMedicalGas.cl2desc = data.cl2desc;
      this.formUpdateMedicalGas.quantity = Number(data.quantity);
      this.formUpdateMedicalGas.prev_quantity = Number(data.quantity);
      this.formUpdateMedicalGas.wardname = data.wardname;
    },
    cancel() {
      //   this.stockBalanceDeclared = false;
      this.requestStockId = null;
      this.isUpdate = false;
      this.createRequestStocksDialog = false;
      this.medicalGasDialog = false;
      this.selectedItemsUomDesc = null;
      this.updateMedicalGasDialog = false;
      this.medicalGasList = [];
      this.form.reset();
      this.form.clearErrors();
      this.formMedicalGas.reset();
      this.formMedicalGas.clearErrors();
      this.formUpdateMedicalGas.reset();
      this.formUpdateMedicalGas.clearErrors();
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

      this.formUpdateStatus.put(route('issueitems.acknowledgedrequest', this.formUpdateStatus), {
        preserveScroll: true,
        onSuccess: () => {
          this.requestStockId = null;
          this.editStatusDialog = false;
          this.cancel();
          this.updateData();
        },
      });
    },
    createdMsg() {
      this.$toast.add({ severity: 'success', summary: 'Success', detail: 'Issued item.', life: 3000 });
    },
    updatedMsg() {
      this.$toast.add({ severity: 'warn', summary: 'Success', detail: 'Issued item updated.', life: 3000 });
    },
    qtyIsNotEnough() {
      this.$toast.add({ severity: 'error', summary: 'Failed', detail: 'Stock quantity is not enough.', life: 3000 });
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
  },
  watch: {
    'formMedicalGas.cl2comb': function (val) {
      this.selectedItemsUomDesc = null;
      //   console.log(val);

      this.medicalGasList.forEach((e) => {
        // console.log(e);
        if (e.cl2comb == val) {
          if (e.uomdesc != null || e.uomdesc == '') {
            // console.log('if');
            this.selectedItemsUomDesc = e.uomdesc;
            this.formMedicalGas.uomcode = e.uomcode;
          } else {
            this.selectedItemsUomDesc = null;
          }
        }
      });
    },
    formUpdateMedicalGas: function (val) {
      console.log(val);
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
    selectedStatus: function (val) {
      //   console.log(val['code']);
      this.params.status = this.selectedStatus;

      this.updateData();
    },
  },
};
</script>

<style>
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

.rounded-card {
  border-radius: 50%;
  /* min-height: 100px;
  min-width: 100px; */
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
  color: #fff !important;
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
  color: #818cf8 !important;
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
  color: #fff !important;
}
</style>
