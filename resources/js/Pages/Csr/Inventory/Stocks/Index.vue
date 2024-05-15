<template>
  <app-layout>
    <Head title="NMIS - Deliveries" />

    <div class="card">
      <Toast />

      <!-- DELIVERIES -->
      <DataTable
        class="p-datatable-sm"
        v-model:filters="filters"
        :value="filteredData"
        paginator
        :rows="10"
        :rowsPerPageOptions="[10, 20, 30, 40]"
        filterDisplay="row"
        removableSort
        :globalFilterFields="['ris_no', 'cl2desc', 'suppname', 'chrgdesc', 'stock_lvl']"
        showGridlines
        selectionMode="single"
        sortMode="single"
        rowGroupMode="subheader"
        groupRowsBy="ris_no"
      >
        <template #header>
          <div class="flex flex-wrap align-items-center justify-content-between gap-2">
            <span class="text-xl text-900 font-bold text-primary mr-2">DELIVERIES</span>

            <div class="flex">
              <div class="mr-2">
                <div class="p-inputgroup">
                  <span class="p-inputgroup-addon">
                    <i class="pi pi-search"></i>
                  </span>
                  <InputText
                    id="searchInput"
                    v-model="filters['global'].value"
                    placeholder="Search item"
                  />
                </div>
              </div>
              <div>
                <Button
                  label="Import delivery"
                  icon="pi pi-angle-up"
                  iconPos="right"
                  @click="openImportDeliveryDialog"
                />
                <Button
                  class="ml-2"
                  label="Add delivery"
                  severity="success"
                  icon="pi pi-plus"
                  iconPos="right"
                  @click="openAddDeliveryDialog"
                />
              </div>
            </div>
          </div>
        </template>
        <template #empty> No stock found. </template>
        <template #loading> Loading stock data. Please wait. </template>
        <Column
          field="ris_no"
          header="RIS NO."
          style="width: 5%"
        >
        </Column>
        <Column
          field="suppname"
          header="SUPPLIER"
          sortable
          style="width: 10%"
        >
        </Column>
        <Column
          field="chrgdesc"
          header="FUND SOURCE"
          sortable
          style="width: 10%"
        >
        </Column>
        <Column
          field="cl2desc"
          header="ITEM"
          sortable
          style="width: 15%"
        >
        </Column>
        <Column
          field="uomdesc"
          header="UNIT"
          style="width: 5%"
        >
        </Column>
        <Column
          field="quantity"
          header="QTY"
          sortable
          style="width: 5%"
        >
          <template #body="{ data }">
            <div
              v-if="data.normal_stock == null && data.alert_stock == null && data.critical_stock == null"
              class="text-center"
            >
              <span>
                {{ data.quantity }}
              </span>
            </div>
            <div
              v-else
              class="text-center"
            >
              <span
                v-if="data.quantity >= data.normal_stock || data.quantity > data.alert_stock"
                class="text-green-500 font-bold"
              >
                {{ data.quantity }}
              </span>
              <span
                v-else-if="data.quantity <= data.alert_stock && data.quantity > data.critical_stock"
                class="text-yellow-500 font-bold"
              >
                {{ data.quantity }}
              </span>
              <span
                v-else-if="data.quantity <= data.critical_stock && data.quantity != 0"
                style="color: #c02422; font-weight: bold"
              >
                {{ data.quantity }}
              </span>
              <span
                v-else
                class="text-white font-bold"
                >{{ data.quantity }}</span
              >
            </div>
          </template>
        </Column>
        <Column
          field="acquisition_price"
          header="ACQ. PRICE"
          style="width: 5%"
        >
        </Column>
        <Column
          field="stock_lvl"
          header="STOCK LVL."
          :showFilterMenu="false"
          style="width: 5%"
        >
          <template #body="slotProps">
            <div
              v-if="slotProps.data.stock_lvl == 'N/A'"
              class="flex justify-content-center"
            >
              <Tag
                value="N/A"
                severity="contrast"
              />
            </div>
            <div
              v-else
              class="flex justify-content-center"
            >
              <Tag
                v-if="slotProps.data.stock_lvl == 'NORMAL'"
                value="NORMAL"
                severity="success"
              />
              <Tag
                v-else-if="slotProps.data.stock_lvl == 'ALERT'"
                value="ALERT"
                severity="warning"
              />
              <Tag
                v-else-if="slotProps.data.stock_lvl == 'CRITICAL'"
                value="CRITICAL"
                severity="danger"
              />
              <Tag
                v-else
                value="OUTOFSTOCK"
                severity="info"
              />
            </div>
          </template>
          <template #filter="{ filterModel, filterCallback }">
            <Dropdown
              v-model="filterModel.value"
              :options="stockLvlFilter"
              @change="filterCallback()"
              optionLabel="name"
              optionValue="code"
              placeholder="NO FILTER"
              class="w-full"
            />
          </template>
        </Column>
        <Column
          field="manufactured_date"
          header="MFD. DATE"
          style="width: 10%"
          sortable
          :showFilterMenu="false"
        >
          <template #body="{ data }">
            {{ tzone(data.manufactured_date) }}
          </template>
        </Column>
        <Column
          field="delivered_date"
          header="DD. DATE"
          style="width: 10%"
          sortable
          :showFilterMenu="false"
        >
          <template #body="{ data }">
            {{ tzone(data.delivered_date) }}
          </template>
        </Column>
        <Column
          field="expiration_date"
          header="EXP. DATE"
          style="width: 10%"
          sortable
          :showFilterMenu="false"
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
          <template #filter="slotProps">
            <Calendar
              v-model="filters['expiration'].from"
              placeholder="FROM"
              showIcon
              showButtonBar
              :manualInput="false"
              :hideOnDateTimeSelect="true"
            />
            <div class="mt-2"></div>
            <Calendar
              v-model="filters['expiration'].to"
              placeholder="TO"
              showIcon
              showButtonBar
              :manualInput="false"
              :hideOnDateTimeSelect="true"
            />
          </template>
        </Column>
        <Column
          header="ACTION"
          style="width: 5%"
        >
          <template #body="slotProps">
            <div class="flex flex-row justify-content-between align-content-around">
              <Button
                v-if="slotProps.data.converted == 'n'"
                v-tooltip.top="'Update'"
                icon="pi pi-pencil"
                class="mr-2"
                rounded
                severity="warning"
                @click="editItem(slotProps.data)"
              />
              <Button
                v-if="slotProps.data.converted == 'n'"
                v-tooltip.top="'Convert'"
                class=""
                rounded
                severity="success"
                @click="convertItem(slotProps.data)"
              >
                <template #icon>
                  <v-icon name="bi-arrow-left-right"></v-icon>
                </template>
              </Button>
            </div>
          </template>
        </Column>

        <template #groupheader="slotProps">
          <div class="bg-primary-reverse py-2 flex align-items-center">
            <div>
              <span class="mr-2">RIS No.: </span>
              <span>{{ slotProps.data.ris_no }}</span>
            </div>
          </div>
        </template>
      </DataTable>

      <!-- import delivery dialog -->
      <Dialog
        v-model:visible="importDeliveryDialog"
        :modal="true"
        class="p-fluid w-11 overflow-hidden"
        :style="{ height: '90%' }"
        @hide="clickOutsideDialog"
        :draggable="false"
      >
        <template #header>
          <div class="text-primary text-xl font-bold">IMPORT DELIVERY</div>
        </template>

        <div class="flex flex-row justify-content-between overflow-hidden">
          <!-- form -->
          <div class="w-3">
            <div class="field">
              <div class="flex justify-content-between align-items-center">
                <label>RIS no.</label>
                <Button
                  icon="pi pi-refresh"
                  severity="success"
                  text
                  rounded
                  aria-label="Cancel"
                  class="p-0 m-0"
                  @click="resetDeliveryDialog"
                />
              </div>
              <InputText
                v-model.trim="form.searchRis"
                autofocus
                @keyup.enter="fillDeliveriesContainer"
                :readonly="disableSearchRisInput == true"
              />
              <small
                v-if="deliveryExist == true"
                class="text-error text-lg font-semibold"
              >
                Delivery already exist
              </small>
            </div>
            <div class="field">
              <div class="flex align-content-center">
                <label>Supplier</label>
                <span class="ml-2 text-error">*</span>
              </div>
              <Dropdown
                required="true"
                v-model="formAdditional.supplier"
                :options="suppliersList"
                :virtualScrollerOptions="{ itemSize: 38 }"
                filter
                dataKey="supplierID"
                optionLabel="suppname"
                class="w-full"
              />
            </div>
            <div class="field">
              <div class="flex align-content-center">
                <label>Fund source</label>
              </div>
              <InputText
                v-model="formAdditional.fsName"
                readonly
              />
            </div>
            <div class="field">
              <div class="flex align-content-center">
                <label>Item</label>
              </div>
              <InputText
                v-model="formAdditional.cl2desc"
                readonly
              />
            </div>
            <div class="field">
              <div class="">
                <label>Unit</label>
              </div>
              <InputText
                id="unit"
                v-model.trim="formAdditional.uomdesc"
                readonly
              />
            </div>
            <div class="field flex flex-row justify-space-between">
              <div>
                <label for="manufactured_date">Manufactured date</label>
                <Calendar
                  v-model="formAdditional.manufactured_date"
                  dateFormat="mm-dd-yy"
                  showIcon
                  showButtonBar
                  :manualInput="false"
                  :hideOnDateTimeSelect="true"
                />
              </div>

              <div class="mx-2"></div>

              <div>
                <label for="delivered_date">Delivered date</label>
                <Calendar
                  v-model="formAdditional.delivered_date"
                  dateFormat="mm-dd-yy"
                  showIcon
                  showButtonBar
                  :manualInput="false"
                  :hideOnDateTimeSelect="true"
                />
              </div>
            </div>
            <div class="field">
              <div class="flex align-content-center">
                <label>Expiration date</label>
                <span class="ml-2 text-error">*</span>
              </div>
              <Calendar
                required="true"
                v-model="formAdditional.expiration_date"
                dateFormat="mm-dd-yy"
                showIcon
                showButtonBar
                :manualInput="false"
                :hideOnDateTimeSelect="true"
              />
            </div>
            <div class="field">
              <div class="flex align-content-center">
                <label>Quantity</label>
              </div>
              <InputText
                required="true"
                v-model.trim="formAdditional.quantity"
                autofocus
                inputId="integeronly"
                readonly
              />
            </div>
            <div class="field">
              <div>
                <div class="flex align-content-center">
                  <label>Acquisition price</label>
                </div>
                <InputText
                  class="w-full"
                  v-model.trim="formAdditional.acquisitionPrice"
                  autofocus
                  :maxFractionDigits="2"
                  readonly
                />
              </div>
            </div>
          </div>
          <div class="border-1 mx-4"></div>
          <!-- delivery list -->
          <div class="w-9">
            <DataTable
              class="p-datatable-sm"
              v-model:filters="deliveryDetailsFilter"
              :value="deliveryDetails"
              paginator
              :rows="30"
              dataKey="id"
              removableSort
              showGridlines
              @row-click="onRowClick"
              scrollable
              scrollHeight="680px"
            >
              <template #header>
                <div class="flex flex-wrap align-items-center justify-content-between gap-2">
                  <span class="text-xl text-900 font-bold text-primary mr-2">LIST</span>

                  <div class="flex">
                    <div class="mr-2">
                      <div class="p-inputgroup">
                        <span class="p-inputgroup-addon">
                          <i class="pi pi-search"></i>
                        </span>
                        <InputText
                          id="searchInput"
                          v-model="deliveryDetailsFilter['global'].value"
                          placeholder="Search item"
                        />
                      </div>
                    </div>
                  </div>
                </div>
              </template>
              <template #empty> No stock found. </template>
              <template #loading> Loading stock data. Please wait. </template>
              <Column
                field="cl2desc"
                header="ITEM"
              >
              </Column>
              <Column
                field="uomdesc"
                header="UNIT"
              >
              </Column>
              <Column
                field="supplierName"
                header="SUPPLIER"
              >
              </Column>
              <Column
                field="fundSourceName"
                header="FUND SOURCE"
              >
              </Column>
              <Column
                field="unitprice"
                header="ACQUISITION PRICE"
              >
              </Column>
              <Column
                field="releaseqty"
                header="QTY"
              >
              </Column>
              <Column
                field="manufactured_date"
                header="MFD. DATE"
              >
                <template #body="{ data }">
                  {{ tzone(data.manufactured_date) }}
                </template>
              </Column>
              <Column
                field="delivered_date"
                header="DELIVERED DATE"
              >
                <template #body="{ data }">
                  {{ tzone(data.delivered_date) }}
                </template>
              </Column>
              <Column
                field="expiration_date"
                header="EXPIRATION DATE"
              >
                <template #body="{ data }">
                  {{ tzone(data.expiration_date) }}
                </template>
              </Column>
            </DataTable>
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
            label="Save"
            icon="pi pi-check"
            text
            type="submit"
            :disabled="formAdditional.processing || deliveryDetails.length == 0"
            @click="submit"
          />
        </template>
      </Dialog>

      <!-- add deliveries manually -->
      <Dialog
        v-model:visible="addDeliveryDialog"
        :modal="true"
        class="p-fluid overflow-hidden"
        :style="{ width: '550px' }"
        @hide="clickOutsideDialog"
        :draggable="false"
      >
        <template #header>
          <div class="text-primary text-xl font-bold">ADD DELIVERY</div>
        </template>

        <div class="field">
          <div class="flex align-items-center">
            <label class="mr-2 font-semibold text-xl"> GENERATE RIS NO.: </label>

            <input
              v-model="formAddDelivery.generateRisNo"
              type="checkbox"
              class="text-primary temp-ris-no"
              :style="{ width: '25px', height: '25px', 'font-size': '20px' }"
              cursor-pointer
            />
          </div>
        </div>
        <div class="field">
          <div class="flex align-content-center">
            <label>RIS no.</label>
            <span class="ml-2 text-error">*</span>
          </div>
          <InputText
            v-model.trim="formAddDelivery.ris_no"
            :readonly="generatedRisNo"
            autofocus
          />
        </div>
        <div class="field">
          <div class="flex align-content-center">
            <label>Supplier</label>
            <span class="ml-2 text-error">*</span>
          </div>
          <Dropdown
            required="true"
            v-model="formAddDelivery.supplier"
            :options="suppliersList"
            :virtualScrollerOptions="{ itemSize: 38 }"
            filter
            dataKey="supplierID"
            optionValue="supplierID"
            optionLabel="suppname"
            class="w-full"
          />
        </div>
        <div class="field">
          <div class="flex align-content-center">
            <label>Fund source</label>
            <span class="ml-2 text-error">*</span>
          </div>
          <Dropdown
            required="true"
            v-model="formAddDelivery.fund_source"
            :options="fundSourceList"
            :virtualScrollerOptions="{ itemSize: 38 }"
            filter
            dataKey="chrgcode"
            optionValue="chrgcode"
            optionLabel="chrgdesc"
            class="w-full"
          />
        </div>
        <div class="field">
          <div class="flex align-content-center">
            <label>Item</label>
            <span class="ml-2 text-error">*</span>
          </div>
          <Dropdown
            required="true"
            v-model="formAddDelivery.cl2comb"
            :options="itemsList"
            :virtualScrollerOptions="{ itemSize: 38 }"
            filter
            dataKey="cl2comb"
            optionValue="cl2comb"
            optionLabel="cl2desc"
            class="w-full"
          />
        </div>
        <div class="field flex flex-row justify-content-between">
          <div>
            <label for="manufactured_date">Manufactured date</label>
            <Calendar
              v-model="formAddDelivery.manufactured_date"
              dateFormat="mm-dd-yy"
              showIcon
              showButtonBar
              :manualInput="false"
              :hideOnDateTimeSelect="true"
            />
          </div>

          <div>
            <label for="delivered_date">Delivered date</label>
            <Calendar
              v-model="formAddDelivery.delivered_date"
              dateFormat="mm-dd-yy"
              showIcon
              showButtonBar
              :manualInput="false"
              :hideOnDateTimeSelect="true"
            />
          </div>
        </div>
        <div class="field">
          <div class="flex align-content-center">
            <label>Expiration date</label>
            <span class="ml-2 text-error">*</span>
          </div>
          <Calendar
            required="true"
            v-model="formAddDelivery.expiration_date"
            dateFormat="mm-dd-yy"
            showIcon
            showButtonBar
            :manualInput="false"
            :hideOnDateTimeSelect="true"
          />
        </div>
        <div class="field">
          <div class="flex align-content-center">
            <label>Quantity</label>
            <span class="ml-2 text-error">*</span>
          </div>
          <InputText
            required="true"
            v-model.trim="formAddDelivery.quantity"
            inputId="integeronly"
          />
        </div>
        <div class="field flex justify-content-between">
          <div>
            <div class="flex align-content-center">
              <label>Acquisition price</label>
              <span class="ml-2 text-error">*</span>
            </div>
            <InputText
              required="true"
              type="number"
              v-model.trim="formAddDelivery.acquisitionPrice"
              @keydown="restrictNonNumeric"
            />
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
            label="Save"
            icon="pi pi-check"
            text
            type="submit"
            :disabled="
              formAddDelivery.processing ||
              formAddDelivery.ris_no == null ||
              formAddDelivery.supplier == null ||
              formAddDelivery.fund_source == null ||
              formAddDelivery.cl2comb == null ||
              formAddDelivery.expiration_date == null ||
              formAddDelivery.quantity == null ||
              formAddDelivery.acquisitionPrice == null
            "
            @click="submitAddDelivery"
          />
        </template>
      </Dialog>

      <!-- update delivery details dialog -->
      <Dialog
        v-model:visible="updateStockDialog"
        :style="{ width: '650px' }"
        :modal="true"
        class="p-fluid"
        @hide="clickOutsideDialog"
      >
        <template #header>
          <div class="text-primary text-xl font-bold">DELIVERY DETAILS</div>
        </template>

        <div class="flex flex-row w-full">
          <!-- left side form -->
          <div class="mr-2">
            <div class="field">
              <div class="flex align-content-center">
                <label>RIS No.</label>
              </div>
              <InputText
                id="ris_no"
                v-model.trim="form.ris_no"
                readonly
              />
            </div>
            <div
              class="field"
              style="width: 300px"
            >
              <div class="flex align-content-center">
                <label>Supplier</label>
                <span class="ml-2 text-error">*</span>
              </div>
              <Dropdown
                required="true"
                v-model="form.supplierID"
                :options="suppliersList"
                :virtualScrollerOptions="{ itemSize: 38 }"
                filter
                dataKey="supplierID"
                optionLabel="suppname"
                optionValue="supplierID"
                class="w-full"
                :class="{ 'p-invalid': form.supplierID == '' }"
              />
              <small
                class="text-error"
                v-if="form.errors.supplierID"
              >
                Supplier is required.
              </small>
            </div>
            <div class="field">
              <div class="flex align-content-center">
                <label>Fund source</label>
              </div>
              <InputText
                v-model="form.chrgdesc"
                readonly
              />
            </div>
            <div class="field">
              <div class="flex align-content-center">
                <label>Item</label>
              </div>
              <InputText
                v-model="form.cl2desc"
                readonly
              />
            </div>
            <div class="field">
              <div class="flex align-content-center">
                <label>Unit</label>
              </div>
              <InputText
                id="unit"
                v-model.trim="form.uomdesc"
                readonly
              />
            </div>
            <div class="field">
              <div class="flex align-content-center">
                <label>Quantity</label>
              </div>
              <InputText
                id="quantity"
                type="number"
                v-model.trim="form.quantity"
                @keydown="restrictNonNumericAndPeriod"
              />
            </div>
            <div class="field">
              <div class="flex align-content-center">
                <label>Manufactured date</label>
              </div>
              <Calendar
                v-model="form.manufactured_date"
                dateFormat="mm-dd-yy"
                showIcon
                showButtonBar
                :manualInput="false"
                :hideOnDateTimeSelect="true"
              />
            </div>
            <div class="field">
              <div class="flex align-content-center">
                <label>Delivered date</label>
              </div>
              <Calendar
                v-model="form.delivered_date"
                dateFormat="mm-dd-yy"
                showIcon
                showButtonBar
                :manualInput="false"
                :hideOnDateTimeSelect="true"
              />
            </div>
            <div class="field">
              <div class="flex align-content-center">
                <label>Expiration date</label>
                <span class="ml-2 text-error">*</span>
              </div>
              <Calendar
                v-model="form.expiration_date"
                dateFormat="mm-dd-yy"
                showIcon
                showButtonBar
                :manualInput="false"
                :hideOnDateTimeSelect="true"
              />
              <small
                class="text-error"
                v-if="form.errors.expiration_date"
              >
                Expiration date is required.
              </small>
            </div>
          </div>

          <div class="w-full">
            <div class="field">
              <div class="flex align-content-center">
                <label>Acquisition price</label>
              </div>
              <InputText
                v-model="form.acquisition_price"
                readonly
              />
            </div>
            <div>
              <div class="flex align-content-center">
                <label>Remarks</label>
                <span class="ml-2 text-error">*</span>
              </div>
              <Textarea
                v-model.trim="form.remarks"
                rows="10"
                class="w-full"
              />
              <small
                class="text-error"
                v-if="form.errors.remarks"
              >
                {{ form.errors.remarks }}
              </small>
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
            label="Update"
            icon="pi pi-check"
            severity="warning"
            text
            type="submit"
            :disabled="form.processing || form.remarks == '' || form.remarks == null"
            @click="submit"
          />
        </template>
      </Dialog>

      <!-- convert dialog -->
      <Dialog
        v-model:visible="convertDialog"
        :style="{ width: '650px' }"
        :modal="true"
        class="p-fluid"
        @hide="clickOutsideDialog"
        dismissableMask
      >
        <template #header>
          <div class="text-primary text-xl font-bold">CONVERT</div>
        </template>
        <div class="field">
          <div class="flex align-content-center">
            <label>Item</label>
          </div>
          <InputText
            v-model.trim="formConvert.cl2desc_before"
            readonly
          />
        </div>
        <div class="field">
          <div class="flex align-content-center">
            <label>Convert to <label class="text-yellow-500">(optional)</label></label>
          </div>
          <Dropdown
            v-model.trim="formConvert.cl2comb_after"
            required="true"
            :options="convertedItemsList"
            :virtualScrollerOptions="{ itemSize: 48 }"
            filter
            optionLabel="cl2desc"
            optionValue="cl2comb"
            class="w-full mb-3"
            :class="{ 'p-invalid': formConvert.cl1comb == '' }"
          />
        </div>
        <div class="field">
          <div class="flex align-content-center">
            <label>Quantity</label>
          </div>
          <InputText
            id="quantity"
            type="number"
            v-model="formConvert.quantity_after"
            @keydown="restrictNonNumericAndPeriod"
            autofocus
            @keyup.enter="submitConvert"
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
            :disabled="formConvert.processing || formConvert.quantity_after == '' || formConvert.quantity_after == null"
            @click="submitConvert"
          />
        </template>
      </Dialog>
    </div>

    <div class="card">
      <TabView>
        <TabPanel header="TOTAL DELIVERIES">
          <!-- total stocks -->
          <DataTable
            class="p-datatable-sm"
            dataKey="id"
            v-model:filters="totalDeliveriesFilters"
            :value="totalDeliveriesList"
            paginator
            :rows="10"
            :rowsPerPageOptions="[10, 20, 30]"
            removableSort
            sortField="name"
            :sortOrder="1"
            showGridlines
          >
            <template #header>
              <div class="flex flex-wrap align-items-center justify-content-between gap-2">
                <span class="text-xl text-900 font-bold text-primary">TOTAL DELIVERIES</span>

                <div class="flex">
                  <div class="mr-2">
                    <div class="p-inputgroup">
                      <span class="p-inputgroup-addon">
                        <i class="pi pi-search"></i>
                      </span>
                      <InputText
                        v-model="totalDeliveriesFilters['global'].value"
                        placeholder="Search item"
                      />
                    </div>
                  </div>
                </div>
              </div>
            </template>
            <template #empty> No item found. </template>
            <template #loading> Loading item data. Please wait. </template>
            <Column
              field="cl2desc"
              header="ITEM"
              sortable
              style="width: 90%"
            >
              <template #body="{ data }">
                {{ data.cl2desc }}
              </template>
            </Column>
            <Column
              field="total_quantity"
              header="QTY"
              sortable
              style="width: 10%"
            >
              <template #body="{ data }">
                {{ data.total_quantity }}
              </template>
            </Column>
          </DataTable>
        </TabPanel>
        <TabPanel>
          <template #header>
            <div class="flex align-items-center gap-2">
              <span class="font-bold white-space-nowrap text-green-500">TOTAL CONVERTED ITEMS</span>
            </div>
          </template>
          <!-- total total converted items  -->
          <DataTable
            class="p-datatable-sm"
            dataKey="id"
            v-model:filters="totalConvertedItemsFilters"
            :value="totalConvertedItemsList"
            paginator
            :rows="10"
            :rowsPerPageOptions="[10, 20, 30]"
            removableSort
            sortField="name"
            :sortOrder="1"
            showGridlines
          >
            <template #header>
              <div class="flex flex-wrap align-items-center justify-content-between gap-2">
                <span class="text-xl font-bold text-green-500">TOTAL CONVERTED ITEMS</span>

                <div class="flex">
                  <div class="mr-2">
                    <div class="p-inputgroup">
                      <span class="p-inputgroup-addon">
                        <i class="pi pi-search"></i>
                      </span>
                      <InputText
                        v-model="totalConvertedItemsFilters['global'].value"
                        placeholder="Search item"
                      />
                    </div>
                  </div>
                </div>
              </div>
            </template>
            <template #empty> No item found. </template>
            <template #loading> Loading item data. Please wait. </template>
            <Column
              field="ris_no"
              header="RIS NO."
            >
            </Column>
            <Column
              field="fsName"
              header="FUND SOURCE"
              sortable
            >
            </Column>
            <Column
              field="cl2desc"
              header="ITEM"
              sortable
            >
              <!-- <template #body="{ data }">
              {{ data.cl2desc }}
            </template> -->
            </Column>
            <Column
              field="quantity_after"
              header="QTY"
              sortable
            >
            </Column>
            <Column
              field="expiration_date"
              header="EXP. DATE"
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
              field="converted_by"
              header="CONV. BY"
              sortable
            >
            </Column>
            <Column
              header="ACTION"
              style="width: 5%"
            >
              <template #body="slotProps">
                <div class="flex flex-row justify-content-center align-content-around">
                  <Button
                    v-tooltip.top="'Update'"
                    icon="pi pi-pencil"
                    class="mr-2"
                    rounded
                    severity="warning"
                    @click="editConvertedItem(slotProps.data)"
                  />
                  <!-- <Button
                    v-tooltip.top="'Convert'"
                    class=""
                    rounded
                    severity="success"
                    @click="convertItem(slotProps.data)"
                  >
                    <template #icon>
                      <v-icon name="bi-arrow-left-right"></v-icon>
                    </template>
                  </Button> -->
                </div>
              </template>
            </Column>
          </DataTable>
        </TabPanel>
      </TabView>
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
import Tag from 'primevue/tag';
import Textarea from 'primevue/textarea';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import Checkbox from 'primevue/checkbox';
import axios from 'axios';

import moment, { now } from 'moment';

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
    InputNumber,
    TabView,
    TabPanel,
    Checkbox,
  },
  props: {
    items: Object,
    stocks: Object,
    totalDeliveries: Object,
    typeOfCharge: Object,
    fundSource: Object,
    suppliers: Object,
    convertedItems: Object,
    totalConvertedItems: Object,
  },
  data() {
    return {
      minimumDate: null,
      stockId: null,
      isUpdate: false,
      importDeliveryDialog: false,
      addDeliveryDialog: false,
      updateStockDialog: false,
      deliveryExist: false,
      convertDialog: false,
      params: {},
      search: '',
      // manufactured date
      from_md: null,
      to_md: null,
      // delivered date
      from_dd: null,
      to_dd: null,
      // expiration date
      from_ed: null,
      to_ed: null,
      fundSourceList: [],
      totalConvertedItemsList: [],
      // -----------------
      disableSearchRisInput: false,
      itemNotSelected: false,
      item: null,
      itemsList: [],
      supplier: null,
      selectedFundSource: null,
      selectedItemsUomCode: null,
      selectedItemsUomDesc: null,
      quantity: null,
      deliveryDetails: [],
      manufactured_date: null,
      delivered_date: null,
      expiration_date: null,
      acquisitionPrice: 0,
      editConvertedItemIsUpdate: false,
      generatedRisNo: false,
      deliveryDetailsFilter: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
      // ------------------
      // data when clicking row to populate form
      formAdditional: this.$inertia.form({
        ris_no: null,
        supplier: null,
        supplierName: null,
        fsId: null,
        fsName: null,
        cl2comb: null,
        cl2desc: null,
        uomcode: null,
        uomdesc: null,
        manufactured_date: null,
        delivered_date: null,
        expiration_date: null,
        acquisitionPrice: null,
        quantity: null,
        deliveryDetails: null,
      }),
      // end data when clicking row to populate form
      stocksList: [],
      totalDeliveriesList: [],
      suppliersList: [],
      convertedItemsList: [],
      filters: {
        global: {
          value: null,
          matchMode: FilterMatchMode.CONTAINS,
        },
        ris_no: { value: null, matchMode: FilterMatchMode.CONTAINS },
        cl2desc: { value: null, matchMode: FilterMatchMode.CONTAINS },
        suppname: { value: null, matchMode: FilterMatchMode.CONTAINS },
        chrgdesc: { value: null, matchMode: FilterMatchMode.CONTAINS },
        stock_lvl: { value: null, matchMode: FilterMatchMode.EQUALS },
        expiration: { from: null, to: null },
      },
      totalDeliveriesFilters: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
      totalConvertedItemsFilters: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
      cl1stats: [
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
        newRisNo: null,

        searchRis: null,

        stock_id: null,
        ris_no: null,
        supplierID: null,
        fund_source: null,
        chrgdesc: null,
        cl2comb: null,
        cl2desc: null,
        uomcode: null,
        uomdesc: null,
        quantity: null,
        acquisition_price: null,
        manufactured_date: null,
        delivered_date: null,
        expiration_date: null,
        remarks: null,
        delivery_list: [],
      }),
      formAddDelivery: this.$inertia.form({
        generateRisNo: false,
        ris_no: null,
        supplier: null,
        fund_source: null,
        cl2comb: null,
        manufactured_date: null,
        delivered_date: null,
        expiration_date: null,
        quantity: null,
        acquisitionPrice: null,
      }),
      formConvert: this.$inertia.form({
        id: null,
        csr_stock_id: null,
        ris_no: null,
        chrgcode: null,
        cl2comb_before: null,
        cl2desc_before: null,
        quantity_before: null,
        cl2comb_after: null,
        quantity_after: null,
        supplierID: null,
        manufactured_date: null,
        delivered_date: null,
        expiration_date: null,
      }),
      stockLvlFilter: [
        {
          name: 'NORMAL',
          code: 'NORMAL',
        },
        {
          name: 'ALERT',
          code: 'ALERT',
        },
        {
          name: 'CRITICAL',
          code: 'CRITICAL',
        },
        {
          name: 'OUTOFSTOCK',
          code: 'OUTOFSTOCK',
        },
        {
          name: 'N/A',
          code: 'N/A',
        },
      ],
    };
  },
  computed: {
    filteredData() {
      let filtered = this.stocksList;

      // Apply global filter
      if (this.filters['global'] && this.filters['global'].value) {
        const filterText = this.filters['global'].value.toLowerCase();
        filtered = filtered.filter(
          (item) =>
            item.ris_no.toLowerCase().includes(filterText) ||
            item.cl2desc.toLowerCase().includes(filterText) ||
            item.suppname.toLowerCase().includes(filterText) ||
            item.chrgdesc.toLowerCase().includes(filterText) ||
            item.stock_lvl.toLowerCase().includes(filterText)
        );
      }

      // Apply expiration date range filter
      if (this.filters['expiration']) {
        const from = this.filters['expiration'].from;
        const to = this.filters['expiration'].to;

        if (from && to) {
          filtered = filtered.filter((item) => {
            return item.expiration_date >= from && item.expiration_date <= to;
          });
        }
      }

      return filtered;
    },
  },
  mounted() {
    // console.log(this.convertedItems);
    this.setMinimumDate();
    this.storeFundSourceInContainer();
    this.storeItemsInContainer();
    this.storeStocksInContainer();
    this.storeTotalStocksInContainer();
    this.storeSuppliersInContainer();
    // this.storeConvertedItemsInContainer();
    this.storeTotalConvertedItemsInContainer();
    // this.generateTempRisNo();

    // Add event listener to the document
    document.addEventListener('keydown', this.handleKeyPress);
  },
  beforeUnmount() {
    // Remove event listener before component is destroyed
    document.removeEventListener('keydown', this.handleKeyPress);
  },
  methods: {
    generateTempRisNo() {
      // Get the current year
      const currentYear = new Date().getFullYear();

      // Extract the last two digits of the year (YY format)
      const yearDigits = String(currentYear).slice(-2);

      // Generate random numbers for the XXX part (3 digits)
      const randomPart1 = Math.floor(Math.random() * 1000)
        .toString()
        .padStart(3, '0');

      // Generate random numbers for the XXXXX part (5 digits)
      const randomPart2 = Math.floor(Math.random() * 100000)
        .toString()
        .padStart(5, '0');

      // Create the final formatted string
      const randomNumber = `${yearDigits}-${randomPart1}-${randomPart2}T`;

      this.formAddDelivery.ris_no = randomNumber;
      //   console.log(randomNumber);
    },
    resetDeliveryDialog() {
      this.disableSearchRisInput = false;
      this.isUpdate = false;
      this.item = null;
      this.supplier = null;
      this.selectedFundSource = null;
      this.selectedItemsUomCode = null;
      this.selectedItemsUomDesc = null;
      this.quantity = null;
      this.deliveryDetails = [];
      this.manufactured_date = null;
      this.delivered_date = null;
      this.expiration_date = null;
      this.form.clearErrors();
      this.form.reset();
      this.formAdditional.clearErrors();
      this.formAdditional.reset();
    },
    handleKeyPress(event) {
      // Check if Ctrl key is pressed and key is 'd'
      if (event.ctrlKey && event.key === 'd') {
        // Prevent the default action of the browser
        event.preventDefault();
        // Execute the function
        this.updateNewDetailsToDeliveryDets();
      }
    },
    // this a method to make sure that non-numeric character is remove
    // because the component using this is InputText and type number the letter 'e' can be type
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
    },
    setMinimumDate() {
      this.minimumDate = new Date();
      let returnVal = 0;
      let dateToday = new Date();
      let getDate = dateToday.getDate();
      let getHour = dateToday.getHours();

      if (getHour >= 12 && getDate == 1) {
        this.minimumDate.setDate(dateToday.getDate() + 14);
      } else if (getHour >= 12 && getDate == 15) {
        this.minimumDate.setMonth(dateToday.getMonth() + 1, 1);
      } else if (getHour < 12 && getDate == 13) {
        this.minimumDate.setMonth(dateToday.getMonth() + 1, 1);
      } else {
      }
    },
    // use storeStocksInContainer() function so that every time you make
    // server request such as POST, the data in the table
    // is updated
    storeStocksInContainer() {
      let stock_lvl = null;
      this.stocks.forEach((e) => {
        if (e.normal_stock == null) {
          stock_lvl = 'N/A';
        } else if (Number(e.quantity) >= Number(e.normal_stock)) {
          stock_lvl = 'NORMAL';
        } else if (
          Number(e.alert_stock) >= Number(e.quantity) &&
          Number(e.quantity) <= Number(e.normal_stock) &&
          Number(e.quantity) > Number(e.critical_stock)
        ) {
          stock_lvl = 'ALERT';
        } else if (Number(e.critical_stock) >= Number(e.quantity) && Number(e.quantity) > 0) {
          stock_lvl = 'CRITICAL';
        } else if (Number(e.quantity) == 0) {
          stock_lvl = 'OUTOFSTOCK';
        }

        const expirationDate = e.expiration_date === null ? null : new Date(e.expiration_date); // Convert expiration_date to Date object
        this.stocksList.push({
          id: e.id,
          ris_no: e.ris_no,
          supplierID: e.supplierID,
          suppname: e.suppname,
          chrgcode: e.codeFromHCharge === null ? e.codeFromFundSource : e.codeFromHCharge,
          chrgdesc: e.codeFromHCharge === null ? e.descFromFundSource : e.descFromHCharge,
          cl2comb: e.cl2comb,
          cl2desc: e.cl2desc,
          uomcode: e.uomcode == null ? null : e.uomcode,
          uomdesc: e.uomcode == null ? null : e.uomdesc,
          quantity: Number(e.quantity),
          acquisition_price: e.acquisition_price,
          normal_stock: e.normal_stock == null ? 'N/A' : Number(e.normal_stock),
          alert_stock: e.alert_stock == null ? 'N/A' : Number(e.alert_stock),
          critical_stock: e.critical_stock == null ? 'N/A' : Number(e.critical_stock),
          stock_lvl: stock_lvl,
          manufactured_date: e.manufactured_date === null ? '' : e.manufactured_date,
          delivered_date: e.delivered_date === null ? '' : e.delivered_date,
          expiration_date: expirationDate, // Assign expirationDate to expiration_date field
          converted: e.converted,
        });
      });
    },
    storeSuppliersInContainer() {
      this.suppliers.forEach((e) => {
        this.suppliersList.push({
          supplierID: e.supplierID,
          suppname: e.suppname,
        });
      });
    },
    storeTotalConvertedItemsInContainer() {
      this.totalConvertedItems.forEach((e) => {
        this.totalConvertedItemsList.push({
          id: e.id,
          ris_no: e.ris_no,
          fsName: e.fsName,
          cl2comb_after: e.cl2comb_after,
          cl2desc: e.cl2desc,
          quantity_after: e.quantity_after,
          expiration_date: e.expiration_date,
          converted_by: e.firstname.trim() + ' ' + e.lastname.trim(),
        });
      });
    },
    // storeConvertedItemsInContainer() {
    //   this.convertedItems.forEach((e) => {
    //     this.convertedItemsList.push({
    //       cl1comb: e.cl1comb,
    //       cl2comb: e.cl2comb,
    //       cl2desc: e.cl2desc,
    //       uomcode: e.uomcode,
    //     });
    //   });
    // },
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
    storeItemsInContainer() {
      this.items.forEach((e) => {
        this.itemsList.push({
          cl2comb: e.cl2comb,
          cl2desc: e.cl2desc,
          uomcode: e.uomcode == null ? null : e.uomcode,
          uomdesc: e.uomdesc == null ? null : e.uomdesc,
        });
      });
    },
    storeTotalStocksInContainer() {
      this.totalDeliveries.forEach((e) => {
        this.totalDeliveriesList.push({
          cl2comb: e.cl2comb,
          cl2desc: e.cl2desc.trim(),
          total_quantity: e.total_quantity,
        });
      });
    },
    updateData() {
      this.$inertia.get('csrstocks', this.params, {
        preserveState: true,
        preserveScroll: true,
        onFinish: (visit) => {
          this.stocksList = [];
          this.totalDeliveriesList = [];
          this.fundSourceList = [];
          this.itemsList = [];
          this.suppliersList = [];
          this.convertedItemsList = [];
          this.totalConvertedItemsList = [];
          this.storeFundSourceInContainer();
          this.storeItemsInContainer();
          this.storeStocksInContainer();
          this.storeTotalStocksInContainer();
          this.storeSuppliersInContainer();
          //   this.storeConvertedItemsInContainer();
          this.storeTotalConvertedItemsInContainer();
        },
      });
    },
    openImportDeliveryDialog() {
      this.isUpdate = false;
      this.form.clearErrors();
      this.form.reset();
      this.stockId = null;
      this.importDeliveryDialog = true;
    },
    openAddDeliveryDialog() {
      this.formAddDelivery.clearErrors();
      this.formAddDelivery.reset();
      this.addDeliveryDialog = true;
    },
    // emit close dialog
    clickOutsideDialog() {
      this.$emit(
        'hide',
        (this.disableSearchRisInput = false),
        (this.isUpdate = false),
        (this.item = null),
        (this.supplier = null),
        (this.selectedFundSource = null),
        (this.selectedItemsUomCode = null),
        (this.selectedItemsUomDesc = null),
        (this.quantity = null),
        (this.deliveryDetails = []),
        (this.manufactured_date = null),
        (this.delivered_date = null),
        (this.expiration_date = null),
        this.form.clearErrors(),
        this.form.reset(),
        this.formAdditional.clearErrors(),
        this.formAdditional.reset(),
        this.formConvert.reset(),
        this.formConvert.clearErrors(),
        this.formAddDelivery.clearErrors(),
        this.formAddDelivery.reset()
      );
    },
    editConvertedItem(item) {
      console.log(item);

      let itemCl2comb = this.extractCl2comb(item.cl2comb_after);
      this.updateConvertedIemListBasedOnCl1comb(itemCl2comb);

      this.editConvertedItemIsUpdate = true;
      this.convertDialog = true;

      this.formConvert.id = item.id;

      this.formConvert.cl2comb_before = item.cl2comb_after;
      this.formConvert.cl2desc_before = item.cl2desc;
      this.formConvert.quantity_after = item.quantity_after;
    },
    editItem(item) {
      //   console.log(item);
      this.isUpdate = true;
      this.updateStockDialog = true;
      this.stockId = item.id;
      this.form.ris_no = item.ris_no;
      this.form.supplierID = item.supplierID;
      this.form.fund_source = item.chrgcode;
      this.form.chrgdesc = item.chrgdesc;
      this.form.cl2comb = item.cl2comb;
      this.form.uomcode = item.uomcode;
      this.form.cl2desc = item.cl2desc;
      this.form.uomdesc = item.uomdesc;
      this.form.quantity = item.quantity;
      this.form.acquisition_price = item.acquisition_price;
      this.form.manufactured_date = item.manufactured_date;
      this.form.delivered_date = item.delivered_date;
      this.form.expiration_date = item.expiration_date;
    },
    async fillDeliveriesContainer() {
      this.deliveryExist = this.stocksList.some((item) => item.ris_no === this.form.searchRis);

      if (this.deliveryExist != true && this.form.searchRis != null && this.form.searchRis != '') {
        try {
          const response = await axios.post('csrstocks', this.form);
          //   console.log(response.data); // Log the response data if needed

          let sanitizedData = response.data;

          sanitizedData.forEach((e) => {
            // conditions make it so that items are not entered twice
            if (sanitizedData.length != this.deliveryDetails.length) {
              this.deliveryDetails.push({
                risno: e.risno,
                cl2comb: e.cl2comb,
                cl2desc: e.cl2desc,
                supplier: null,
                supplierName: null,
                fsid: e.fundSourceId,
                fundSourceName: e.fundSourceName,
                uomcode: e.uomcode,
                uomdesc: e.uomdesc,
                unitprice: e.unitprice,
                releaseqty: Number(e.releaseqty),
                manufactured_date: null,
                delivered_date: null,
                expiration_date: null,
              });
            }
          });

          this.disableSearchRisInput = true;
        } catch (error) {
          console.error('Something went wrong:', error);
          // Handle error
        }
      }
    },
    extractCl2comb(str) {
      //   const firstDashIndex = str.indexOf('-');
      //   const secondDashIndex = str.indexOf('-', firstDashIndex + 1);
      //   return str.substring(0, secondDashIndex + 1);

      // Find the index of the first occurrence of '-'
      const firstDashIndex = str.indexOf('-');

      // Find the index of the second occurrence of '-' starting from the position after the first occurrence
      const secondDashIndex = str.indexOf('-', firstDashIndex + 1);

      // Extract the substring from the start of the string until the position of the second '-'
      return str.substring(0, secondDashIndex);
    },
    updateConvertedIemListBasedOnCl1comb(itemCl2comb) {
      // reset list
      this.convertedItemsList = [];

      // Modify the forEach loop to apply the extraction and push items based on the extracted result
      this.convertedItems.forEach((e) => {
        // Conditionally push the item into the new list based on the extracted result
        if (itemCl2comb === e.cl1comb) {
          // Adjust the condition as needed
          this.convertedItemsList.push({
            cl1comb: e.cl1comb,
            cl2comb: e.cl2comb,
            cl2desc: e.cl2desc,
            uomcode: e.uomcode,
          });
        }
      });
    },
    convertItem(item) {
      //   console.log(item);

      let itemCl2comb = this.extractCl2comb(item.cl2comb);
      this.updateConvertedIemListBasedOnCl1comb(itemCl2comb);

      this.convertDialog = true;
      this.formConvert.cl2comb_before = item.cl2comb;
      this.formConvert.cl2desc_before = item.cl2desc;
      this.formConvert.quantity_before = item.quantity;

      this.formConvert.csr_stock_id = item.id;
      this.formConvert.ris_no = item.ris_no;
      this.formConvert.chrgcode = item.chrgcode;
      this.formConvert.supplierID = item.supplierID;
      this.formConvert.manufactured_date = item.manufactured_date;
      this.formConvert.delivered_date = item.delivered_date;
      this.formConvert.expiration_date = item.expiration_date;
    },
    submitConvert() {
      if (
        this.formConvert.processing ||
        this.formConvert.quantity_after == '' ||
        this.formConvert.quantity_after == null
      ) {
        return false;
      }

      if (this.editConvertedItemIsUpdate) {
        this.formConvert.put(route('csrconvertdelivery.update', this.formConvert.id), {
          preserveScroll: true,
          onSuccess: () => {
            //   console.log('DONE');
            this.convertDialog = false;
            this.cancel();
            this.updateData();
            this.itemConverted();
          },
          onError: (error) => {
            console.log(error);
          },
        });
      } else {
        this.formConvert.post(route('csrconvertdelivery.store'), {
          preserveScroll: true,
          onSuccess: () => {
            //   console.log('DONE');
            this.convertDialog = false;
            this.cancel();
            this.updateData();
            this.itemConverted();
          },
          onError: (error) => {
            console.log(error);
          },
        });
      }
    },
    onRowClick(e) {
      //   console.log(e.data);
      this.formAdditional.ris_no = e.data.risno;
      this.formAdditional.supplierID = e.data.supplierId;
      this.formAdditional.suppname = e.data.supplierName;
      this.formAdditional.fsId = e.data.fsid;
      this.formAdditional.fsName = e.data.fundSourceName;
      this.formAdditional.cl2comb = e.data.cl2comb;
      this.formAdditional.cl2desc = e.data.cl2desc;
      this.formAdditional.uomcode = e.data.uomcode;
      this.formAdditional.uomdesc = e.data.uomdesc;
      this.formAdditional.quantity = Number(e.data.releaseqty);
      this.formAdditional.manufactured_date = e.data.manufactured_date;
      this.formAdditional.delivered_date = e.data.delivered_date;
      this.formAdditional.expiration_date = e.data.expiration_date;
      this.formAdditional.acquisitionPrice = Number(e.data.unitprice);
    },
    updateNewDetailsToDeliveryDets() {
      this.deliveryDetails.forEach((e) => {
        if (
          this.formAdditional.ris_no == e.risno &&
          this.formAdditional.cl2comb == e.cl2comb &&
          this.formAdditional.quantity == e.releaseqty
        ) {
          // only update property if none on the properties is null
          if (e.supplier == null || e.expiration_date == null) {
            e.supplier = this.formAdditional.supplier;
            e.supplierName = this.formAdditional.supplier == null ? null : this.formAdditional.supplier.suppname;
            e.manufactured_date = this.formAdditional.manufactured_date;
            e.delivered_date = this.formAdditional.delivered_date;
            e.expiration_date = this.formAdditional.expiration_date;
            e.releaseqty = this.formAdditional.quantity;
          }
        }
      });
    },
    removeFromRequestContainer(item) {
      this.deliveryDetails.splice(
        this.deliveryDetails.findIndex((e) => e.cl2comb === item.cl2comb),
        1
      );
    },
    submit() {
      if (this.form.processing && this.formAdditional.processing) {
        return false;
      }

      this.formAdditional.deliveryDetails = this.deliveryDetails;

      const isEmpty = this.deliveryDetails.some((item) => {
        return !item.supplier || !item.expiration_date;
      });

      this.form.stock_id = this.stockId;

      if (this.isUpdate) {
        this.form.put(route('csrstocks.update', this.stockId), {
          preserveScroll: true,
          onSuccess: () => {
            this.stockId = null;
            this.updateStockDialog = false;
            this.cancel();
            this.updateData();
            this.updatedMsg();
          },
        });
      } else {
        if (isEmpty != true) {
          this.formAdditional.post(route('csrstocks.store'), {
            preserveScroll: true,
            onSuccess: () => {
              this.stockId = null;
              this.importDeliveryDialog = false;
              this.cancel();
              this.updateData();
              this.createdMsg();
            },
          });
        }
      }
    },
    submitAddDelivery() {
      if (
        this.formAddDelivery.processing ||
        this.formAddDelivery.ris_no == null ||
        this.formAddDelivery.supplier == null ||
        this.formAddDelivery.fund_source == null ||
        this.formAddDelivery.cl2comb == null ||
        this.formAddDelivery.expiration_date == null ||
        this.formAddDelivery.quantity == null ||
        this.formAddDelivery.acquisitionPrice == null
      ) {
        return false;
      }

      this.formAddDelivery.post(route('csrmanualadd.store'), {
        preserveScroll: true,
        onSuccess: () => {
          this.addDeliveryDialog = false;
          this.cancel();
          this.updateData();
          this.createdMsg();
        },
      });
    },
    cancel() {
      this.convertDialog = false;
      this.stockId = null;
      this.isUpdate = false;
      this.importDeliveryDialog = false;
      this.addDeliveryDialog = false;
      this.disableSearchRisInput = false;
      this.updateStockDialog = false;
      this.form.reset();
      this.form.clearErrors();
      this.formAdditional.reset();
      this.formAdditional.clearErrors();
      this.formConvert.reset();
      this.formConvert.clearErrors();
      this.formAddDelivery.reset();
      this.formAddDelivery.clearErrors();
      this.stocksList = [];
      this.storeStocksInContainer();
    },
    createdMsg() {
      this.$toast.add({ severity: 'success', summary: 'Success', detail: 'Delivery created', life: 3000 });
    },
    itemConverted() {
      this.$toast.add({ severity: 'success', summary: 'Success', detail: 'Item converted', life: 3000 });
    },
    updatedMsg() {
      this.$toast.add({ severity: 'warn', summary: 'Success', detail: 'Delivery updated', life: 3000 });
    },
    updateRisNo() {
      this.$toast.add({ severity: 'warn', summary: 'Success', detail: 'RIS NO. updated', life: 3000 });
    },
  },
  watch: {
    item: function (val) {
      this.selectedItemsUomDesc = null;

      if (val != null) {
        this.selectedItemsUomDesc = val.uomdesc;
        this.unit = val.uomcode;
      } else {
        this.selectedItemsUomDesc = null;
      }
    },
    'formAddDelivery.generateRisNo': function (val) {
      //   console.log(val);

      if (val == true) {
        this.generateTempRisNo();
        this.generatedRisNo = true;
      } else {
        this.formAddDelivery.ris_no = null;
        this.generatedRisNo = false;
      }
    },
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

.p-datatable .p-datatable-tbody > tr:hover {
  cursor: pointer;
}

.temp-ris-no {
  cursor: pointer;
}
</style>
