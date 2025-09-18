<template>
  <app-layout>
    <Head title="NMIS - Deliveries" />

    <div class="card">
      <Toast />

      <!-- DELIVERIES -->
      <!-- :value="filteredData" -->
      <DataTable
        class="p-datatable-sm"
        v-model:filters="filters"
        :value="stocksList"
        :globalFilterFields="['ris_no', 'cl2desc', 'suppname', 'chrgdesc', 'expiration']"
        lazy
        paginator
        :rows="rows"
        removableSort
        showGridlines
        sortMode="single"
        rowGroupMode="subheader"
        groupRowsBy="ris_no"
        :totalRecords="totalRecords"
        @page="onPage($event)"
        :loading="loading"
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
                    v-model="search"
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
          style="width: 10%"
        >
        </Column>
        <Column
          field="chrgdesc"
          header="FUND SOURCE"
          style="width: 10%"
        >
        </Column>
        <Column
          field="cl2desc"
          header="ITEM"
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
          style="width: 5%"
        >
        </Column>
        <Column
          field="acquisition_price"
          header="ACQ. PRICE"
          style="width: 5%"
        >
        </Column>
        <Column
          field="delivered_date"
          header="DD. DATE"
          style="width: 10%"
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
            <!-- <Calendar
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
            /> -->
          </template>
        </Column>
        <Column
          field="created_at"
          header="CREATED_AT"
          style="width: 10%"
          :showFilterMenu="false"
        >
          <template #body="{ data }">
            <div class="flex flex-column">
              <div>
                {{ tzoneWithTime(data.created_at) }}
              </div>
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
      <!-- class="p-fluid w-11 overflow-hidden" -->
      <Dialog
        v-model:visible="importDeliveryDialog"
        :modal="true"
        class="p-fluid w-11 overflow-hidden"
        :style="{ height: '95%' }"
        @hide="clickOutsideDialog"
        :draggable="false"
        :closeOnEscape="false"
      >
        <template #header>
          <!-- ("CTRL + D " to update an item in the list) -->
          <div class="text-xl font-bold">
            <span class="text-primary">IMPORT DELIVERY </span>
            <span>("CTRL + D" to update an item in the list)</span>
          </div>
        </template>

        <div class="flex flex-row justify-content-between overflow-hidden">
          <!-- form -->
          <div class="w-4">
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
                <!-- <span class="ml-2 text-error">*</span> -->
              </div>
              <Dropdown
                v-model="formImport.supplier"
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
                v-model="formImport.fsName"
                readonly
              />
            </div>
            <div class="field">
              <div class="flex align-content-center">
                <label>Item</label>
              </div>
              <Textarea
                v-model="formImport.cl2desc"
                readonly
                rows="5"
                class="w-full"
              />
            </div>
            <div class="field">
              <div class="">
                <label>Unit</label>
              </div>
              <InputText
                id="unit"
                v-model.trim="formImport.uomdesc"
                readonly
              />
            </div>
            <div class="field">
              <!-- <div>
                <label for="manufactured_date">Manufactured date</label>
                <Calendar
                  v-model="formImport.manufactured_date"
                  dateFormat="mm-dd-yy"
                  showIcon
                  showButtonBar
                  :manualInput="false"
                  :hideOnDateTimeSelect="true"
                />
              </div>

              <div class="mx-2"></div> -->

              <div>
                <div class="flex align-content-center">
                  <label>Delivered date</label>
                  <span class="ml-2 text-error">*</span>
                </div>
                <Calendar
                  v-model="formImport.delivered_date"
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
                v-model="formImport.expiration_date"
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
                v-model.trim="formImport.quantity"
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
                <InputNumber
                  class="w-full"
                  v-model.trim="formImport.acquisitionPrice"
                  autofocus
                  :maxFractionDigits="2"
                  readonly
                />
              </div>
            </div>
            <div class="field flex flex-row">
              <div
                :style="{ width: '100%' }"
                class="mt-auto"
              >
                <div class="flex align-content-center">
                  <label>Convert to</label>
                </div>
                <Dropdown
                  required="true"
                  v-model="formImport.cl2comb_after"
                  :options="convertedItemList"
                  :virtualScrollerOptions="{ itemSize: 38 }"
                  filter
                  showClear
                  dataKey="cl2comb"
                  optionValue="cl2comb"
                  optionLabel="cl2desc"
                  class="w-full"
                />
              </div>
              <div :style="{ width: '35%' }">
                <div class="flex align-content-center">
                  <label
                    >Convert quantity
                    <span class="text-error">(Total converted quantity of delivered item)</span></label
                  >
                </div>
                <InputText
                  id="quantity"
                  type="number"
                  v-model="formImport.quantity_after"
                  @keydown="restrictNonNumericAndPeriod"
                  autofocus
                  class="w-full"
                />
              </div>
            </div>
            <div class="field">
              <div>
                <div class="flex align-content-center">
                  <label class="text-green-500">Hospital price</label>
                </div>
                <InputNumber
                  class="w-full"
                  v-model.trim="formImport.hospital_price"
                  inputId="minmaxfraction"
                  :minFractionDigits="2"
                  :maxFractionDigits="5"
                  :disabled="true"
                />
              </div>
            </div>
            <div class="field">
              <div>
                <div class="flex align-content-center">
                  <label class="text-blue-500">Price per unit </label>
                </div>
                <InputNumber
                  class="w-full"
                  v-model.trim="formImport.price_per_unit"
                  inputId="minmaxfraction"
                  :minFractionDigits="2"
                  :maxFractionDigits="5"
                  :disabled="true"
                />
              </div>
            </div>
          </div>

          <div class="border-1 mx-3"></div>
          <!-- delivery list -->
          <div class="w-8">
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
                field="hospital_price"
                header="Hospital price"
              >
                <template #body="{ data }">
                  <span class="text-green-500">{{ data.hospital_price }}</span>
                </template>
              </Column>
              <Column
                field="releaseqty"
                header="QTY"
              >
              </Column>
              <Column
                field="cl2comb_after"
                header="CONVERTED TO"
              >
                <template #body="{ data }">
                  {{ findItem(data.cl2comb_after) }}
                </template>
              </Column>
              <Column
                field="quantity_after"
                header="Converted qty"
              >
              </Column>
              <Column
                field="price_per_unit"
                header="Price per unit"
              >
                <template #body="{ data }">
                  <span class="text-blue-500"> {{ data.price_per_unit }}</span>
                </template>
              </Column>
              <!-- <Column
                field="manufactured_date"
                header="MFD. DATE"
              >
                <template #body="{ data }">
                  {{ tzone(data.manufactured_date) }}
                </template>
              </Column> -->
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
            :disabled="formImport.processing || deliveryDetails.length == 0"
            @click="submit"
          />
        </template>
      </Dialog>

      <!-- add deliveries manually -->
      <Dialog
        v-model:visible="addDeliveryDialog"
        :modal="true"
        class="p-fluid overflow-hidden"
        :style="{ width: '1050px' }"
        :draggable="true"
        :closeOnEscape="false"
      >
        <template #header>
          <div class="text-primary text-xl font-bold">ADD DELIVERY</div>
        </template>

        <div class="field w-6">
          <div class="flex justify-content-between align-content-center">
            <div>
              <label class="text-4xl">RIS NUMBER</label>
              <span class="ml-2 text-error text-4xl">*</span>
            </div>
            <div class="mb-2 flex">
              <Button
                label="CURR."
                size="sm"
                severity="warning"
                class="text-center"
                @click="currentRisNo"
              />
              <Button
                label="NEW"
                size="sm"
                severity="info"
                class="ml-2"
                @click="incrementRisNo"
              />
            </div>
          </div>
          <InputText
            v-model.trim="formAddDelivery.ris_no"
            class="text-4xl py-4 text-yellow-500 font-bold w-full"
          />
        </div>
        <div class="field">
          <div class="flex align-content-center">
            <label>Supplier</label>
          </div>
          <Dropdown
            v-model="formAddDelivery.supplier"
            :options="suppliersList"
            :virtualScrollerOptions="{ itemSize: 38 }"
            filter
            dataKey="supplierID"
            optionValue="supplierID"
            optionLabel="suppname"
            class="w-full"
            autofocus
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

        <div>
          <div class="flex">
            <label>Delivered date</label>
            <span class="ml-2 text-error">*</span>
          </div>
          <div class="flex flex-row">
            <!-- <Calendar
              v-model="formAddDelivery.delivered_date"
              dateFormat="mm-dd-yy"
              showIcon
              showButtonBar
              :manualInput="false"
              :hideOnDateTimeSelect="true"
            /> -->
            <input
              type="date"
              v-model="formAddDelivery.delivered_date"
              class="text-4xl"
              style="border-radius: 10px; padding: 5px"
            />
          </div>
        </div>
        <div class="my-3">
          <div class="flex">
            <label>Expiration date</label>
            <span class="ml-2 text-error">* MAX BY DEFAULT.</span>
          </div>
          <div class="flex flex-row">
            <!-- <Calendar
              required="true"
              v-model="formAddDelivery.expiration_date"
              dateFormat="mm-dd-yy"
              showIcon
              showButtonBar
              :manualInput="false"
              :hideOnDateTimeSelect="true"
            /> -->
            <input
              type="date"
              v-model="formAddDelivery.expiration_date"
              class="text-4xl"
              style="border-radius: 10px; padding: 5px"
            />
          </div>
        </div>

        <div class="field w-6">
          <div class="flex align-content-center">
            <label>Quantity</label>
            <span class="ml-2 text-error">*</span>
          </div>
          <InputText
            required="true"
            v-model.trim="formAddDelivery.quantity"
            inputId="integeronly"
            @keydown="restrictNonNumericAndPeriod"
          />
        </div>
        <div class="field w-6">
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

        <div class="mr-2 mt-auto">
          <div class="flex align-content-center">
            <label>Convert to</label>
          </div>
          <Dropdown
            required="true"
            v-model="formAddDelivery.cl2comb_after"
            :options="convertedItemList"
            :virtualScrollerOptions="{ itemSize: 38 }"
            filter
            showClear
            dataKey="cl2comb"
            optionValue="cl2comb"
            optionLabel="cl2desc"
            class="w-full"
          />
        </div>
        <div>
          <div class="flex align-content-center">
            <label>Convert quantity <span class="text-error">(Total converted quantity of delivered item)</span></label>
          </div>
          <InputText
            id="quantity"
            type="number"
            v-model="formAddDelivery.quantity_after"
            @keydown="restrictNonNumericAndPeriod"
            autofocus
            class="w-full"
          />
        </div>

        <div class="field mt-4">
          <div>
            <div class="flex align-content-center">
              <label class="text-green-500"
                >Hospital price <span class="text-error">((Quantity * Acquisition price) / 0.7)</span></label
              >
            </div>
            <InputNumber
              class="w-full"
              v-model="formAddDelivery.hospital_price"
              :disabled="true"
            />
          </div>
        </div>
        <div class="field">
          <div>
            <div class="flex align-content-center">
              <label class="text-blue-500"
                >Price per unit <span class="text-error">(Hospital price / Converted qty)</span></label
              >
            </div>
            <InputNumber
              class="w-full"
              v-model="formAddDelivery.price_per_unit"
              :disabled="true"
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
            :disabled="
              formAddDelivery.processing ||
              formAddDelivery.ris_no == null ||
              formAddDelivery.fund_source == null ||
              formAddDelivery.cl2comb == null ||
              formAddDelivery.delivered_date == null ||
              formAddDelivery.expiration_date == null ||
              formAddDelivery.quantity == null ||
              formAddDelivery.quantity == 0 ||
              formAddDelivery.acquisitionPrice == null ||
              formAddDelivery.acquisitionPrice == '' ||
              (!isDonationItem && formAddDelivery.acquisitionPrice == 0)
            "
            @click="openSummaryAddDeliveryDialog"
          />
          <!-- <Button
            label="Save"
            icon="pi pi-check"
            text
            type="submit"
            :disabled="
              formAddDelivery.processing ||
              formAddDelivery.ris_no == null ||
              formAddDelivery.fund_source == null ||
              formAddDelivery.cl2comb == null ||
              formAddDelivery.delivered_date == null ||
              formAddDelivery.expiration_date == null ||
              formAddDelivery.quantity == null ||
              formAddDelivery.quantity == 0 ||
              formAddDelivery.acquisitionPrice == null ||
              formAddDelivery.acquisitionPrice == 0
            "
            @click="submitAddDelivery"
          /> -->
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
                <!-- <span class="ml-2 text-error">*</span> -->
              </div>
              <Dropdown
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
            <!-- <div class="field">
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
            </div> -->
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
        :style="{ width: '550px' }"
        :modal="true"
        class="p-fluid"
        @hide="clickOutsideDialog"
      >
        <template #header>
          <div class="text-primary text-xl font-bold">CONVERT ITEM</div>
        </template>
        <div class="field">
          <div class="flex align-content-center">
            <label>RIS no.</label>
            <span class="ml-2 text-error">*</span>
          </div>
          <InputText
            v-model.trim="formConvertItem.ris_no"
            readonly
          />
        </div>
        <div class="field">
          <div class="flex align-content-center">
            <label>Supplier</label>
            <!-- <span class="ml-2 text-error">*</span> -->
          </div>
          <Dropdown
            v-model="formConvertItem.supplierID"
            :options="suppliersList"
            :virtualScrollerOptions="{ itemSize: 38 }"
            filter
            dataKey="supplierID"
            optionValue="supplierID"
            optionLabel="suppname"
            class="w-full"
            disabled
          />
        </div>
        <div class="field">
          <div class="flex align-content-center">
            <label>Fund source</label>
            <span class="ml-2 text-error">*</span>
          </div>
          <Dropdown
            required="true"
            v-model="formConvertItem.chrgcode"
            :options="fundSourceList"
            :virtualScrollerOptions="{ itemSize: 38 }"
            filter
            dataKey="chrgcode"
            optionValue="chrgcode"
            optionLabel="chrgdesc"
            class="w-full"
            disabled
          />
        </div>
        <div class="field">
          <div class="flex align-content-center">
            <label>Item</label>
            <span class="ml-2 text-error">*</span>
          </div>
          <Dropdown
            required="true"
            v-model="formConvertItem.cl2comb_before"
            :options="itemsList"
            :virtualScrollerOptions="{ itemSize: 38 }"
            filter
            dataKey="cl2comb"
            optionValue="cl2comb"
            optionLabel="cl2desc"
            class="w-full"
            disabled
          />
        </div>
        <div class="field flex flex-row justify-content-between">
          <div>
            <div class="flex align-content-center">
              <label>Delivered date</label>
              <span class="ml-2 text-error">*</span>
            </div>
            <Calendar
              v-model="formConvertItem.delivered_date"
              dateFormat="mm-dd-yy"
              showIcon
              showButtonBar
              :manualInput="false"
              :hideOnDateTimeSelect="true"
              disabled
            />
          </div>
          <div>
            <div class="flex">
              <label>Expiration date</label>
              <span class="ml-2 text-error">*</span>
            </div>
            <div class="flex flex-row">
              <Calendar
                required="true"
                v-model="formConvertItem.expiration_date"
                dateFormat="mm-dd-yy"
                showIcon
                showButtonBar
                :manualInput="false"
                :hideOnDateTimeSelect="true"
                disabled
              />
            </div>
            <!-- <ToggleButton
              v-model="maxDate"
              onLabel="Fixed date"
              offLabel="Custom date"
              onIcon="pi pi-lock"
              offIcon="pi pi-lock-open"
            /> -->
          </div>
        </div>

        <div class="field w-6">
          <div class="flex align-content-center">
            <label>Quantity</label>
            <span class="ml-2 text-error">*</span>
          </div>
          <InputText
            required="true"
            v-model.trim="formConvertItem.quantity_before"
            inputId="integeronly"
            @keydown="restrictNonNumericAndPeriod"
            readonly
          />
        </div>
        <div class="field w-6">
          <div>
            <div class="flex align-content-center">
              <label>Acquisition price</label>
              <span class="ml-2 text-error">*</span>
            </div>
            <InputText
              required="true"
              type="number"
              v-model.trim="formConvertItem.acquisition_price"
              @keydown="restrictNonNumeric"
              readonly
            />
          </div>
        </div>
        <div class="field flex flex-row">
          <div
            :style="{ width: '65%' }"
            class="mr-2 mt-auto"
          >
            <div class="flex align-content-center">
              <label>Convert to</label>
            </div>
            <Dropdown
              required="true"
              v-model="formConvertItem.cl2comb_after"
              :options="convertedItemList"
              :virtualScrollerOptions="{ itemSize: 38 }"
              filter
              showClear
              dataKey="cl2comb"
              optionValue="cl2comb"
              optionLabel="cl2desc"
              class="w-full"
            />
          </div>
          <div :style="{ width: '35%' }">
            <div class="flex align-content-center">
              <label
                >Convert quantity <span class="text-error">(Total converted quantity of delivered item)</span></label
              >
            </div>
            <InputText
              id="quantity"
              type="number"
              v-model="formConvertItem.quantity_after"
              @keydown="restrictNonNumericAndPeriod"
              autofocus
              class="w-full"
            />
          </div>
        </div>

        <div class="field">
          <div>
            <div class="flex align-content-center">
              <label class="text-green-500"
                >Hospital price <span class="text-error">((Quantity * Acquisition price) / 0.7)</span></label
              >
            </div>
            <InputNumber
              class="w-full"
              v-model="formConvertItem.hospital_price"
              :disabled="true"
            />
          </div>
        </div>
        <div class="field">
          <div>
            <div class="flex align-content-center">
              <label class="text-blue-500"
                >Price per unit <span class="text-error">(Hospital price / Converted qty)</span></label
              >
            </div>
            <InputNumber
              class="w-full"
              v-model="formConvertItem.price_per_unit"
              :disabled="true"
            />
          </div>
        </div>

        <div>
          <div class="flex align-content-center">
            <label>Remarks</label>
            <span class="ml-2 text-error">*</span>
          </div>
          <Textarea
            v-model.trim="formConvertItem.remarks"
            rows="5"
            class="w-full"
          />
          <small
            class="text-error"
            v-if="formConvertItem.errors.remarks"
          >
            {{ formConvertItem.errors.remarks }}
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
            :disabled="
              formConvertItem.processing ||
              formConvertItem.cl2comb_after == '' ||
              formConvertItem.cl2comb_after == 0 ||
              formConvertItem.cl2comb_after == null ||
              formConvertItem.quantity_after == '' ||
              formConvertItem.quantity_after == 0 ||
              formConvertItem.quantity_after == null ||
              formConvertItem.remarks == '' ||
              formConvertItem.remarks == null
            "
            @click="openSummaryConvertDialog"
          />
        </template>
      </Dialog>

      <!-- edit convert dialog -->
      <Dialog
        v-model:visible="editConvertedItemDialog"
        :style="{ width: '550px' }"
        :modal="true"
        class="p-fluid"
        @hide="clickOutsideDialog"
      >
        <template #header>
          <div class="flex flex-column">
            <div class="text-primary text-xl font-bold">UPDATE QUANTITY</div>
            <div class="text-error text-xl font-bold mt-2">IMPORTANT!</div>
            <div class="text-error text-lg font-semibold">
              ONLY THE QUANTITY CAN BE MODIFIED. <span class="text-yellow-500">PRICE-RELATED</span> DATA REMAINS
              UNCHANGED.
            </div>
          </div>
        </template>
        <div class="field">
          <div class="flex align-content-center">
            <label>Quantity</label>
            <span class="ml-2 text-error">*</span>
          </div>
          <InputText
            required="true"
            v-model.trim="formConvertItem.quantity_after"
            inputId="integeronly"
            @keydown="restrictNonNumericAndPeriod"
            autofocus
          />
        </div>
        <!-- <div class="field">
          <div>
            <div class="flex align-content-center">
              <label class="text-green-500">Price per unit</label>
            </div>
            <InputNumber
              class="w-full"
              v-model.trim="formConvertItem.update_price_per_unit"
              inputId="minmaxfraction"
              :minFractionDigits="2"
              :maxFractionDigits="5"
            />
          </div>
        </div> -->
        <div class="field">
          <div class="flex align-content-center">
            <label>Remarks</label>
            <span class="ml-2 text-error">*</span>
          </div>
          <Textarea
            v-model.trim="formConvertItem.remarks"
            rows="10"
            class="w-full"
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
            :disabled="
              formConvertItem.processing ||
              formConvertItem.quantity_after == '' ||
              formConvertItem.quantity_after == null ||
              formConvertItem.remarks == '' ||
              formConvertItem.remarks == null
            "
            @click="submitUpdateConvertItem"
          />
        </template>
      </Dialog>

      <!-- Delete confirmation dialog -->
      <Dialog
        v-model:visible="deleteConvertedItemDialog"
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
          <span class="">
            Are you sure you want to delete <b>{{ formConvertItem.cl2desc_after }}? </b>
          </span>
        </div>
        <template #footer>
          <Button
            label="No"
            icon="pi pi-times"
            class="p-button-text"
            @click="deleteConvertedItemDialog = false"
          />
          <Button
            label="Yes"
            icon="pi pi-check"
            severity="danger"
            text
            :disabled="formConvertItem.processing"
            @click="deleteConvertItem"
          />
        </template>
      </Dialog>

      <!-- Open summary dialog add delivery -->
      <Dialog
        v-model:visible="summaryAddDeliveryDialog"
        :style="{ width: '500px' }"
        header="Confirm"
        :closeOnEscape="false"
      >
        <template #header>
          <div class="flex flex-column">
            <div class="text-primary text-xl font-bold">DELIVERY SUMMARY</div>
          </div>
        </template>

        <div class="p-4 bg-gray-50 rounded-md shadow-sm space-y-5 mt-3">
          <div class="text-base text-gray-700">
            <span class="font-bold text-2xl">CONVERTED ITEM:</span>
            <span class="text-green-700 font-semibold text-2xl ml-2">
              <span v-if="itemName == null">Item not converted yet.</span>
              <span v-else>{{ itemName }}</span>
            </span>
          </div>
          <div class="my-4"></div>
          <div class="text-base text-gray-700">
            <span class="font-bold text-2xl">PRICE PER UNIT:</span>
            <span class="text-green-700 font-semibold text-2xl ml-2">
              <span v-if="formAddDelivery.price_per_unit == Infinity">₱ 0</span>
              <span v-else>₱ {{ formAddDelivery.price_per_unit }}</span>
            </span>
          </div>
        </div>

        <template #footer>
          <Button
            label="Cancel"
            icon="pi pi-times"
            severity="danger"
            class="p-button-text"
            @click="summaryAddDeliveryDialog = false"
          />
          <Button
            :disabled="formAddDelivery.processing || countdown > 0"
            type="submit"
            severity="success"
            @click="submitAddDelivery"
          >
            <i
              :class="formAddDelivery.processing ? 'pi pi-spin pi-spinner' : 'mx-1 pi pi-check'"
              style="font-size: 1rem"
            ></i>
            {{ countdown > 0 ? `Save (${countdown})` : 'Save' }}
          </Button>
        </template>
      </Dialog>

      <!-- Open summary dialog convertItem -->
      <Dialog
        v-model:visible="summaryConvertDialog"
        :style="{ width: '500px' }"
        header="Confirm"
        :closeOnEscape="false"
      >
        <template #header>
          <div class="flex flex-column">
            <div class="text-primary text-xl font-bold">CONVERTED ITEM SUMMARY</div>
          </div>
        </template>

        <div class="p-4 bg-gray-50 rounded-md shadow-sm space-y-5 mt-3">
          <div class="text-base text-gray-700">
            <span class="font-bold text-2xl">CONVERTED ITEM:</span>
            <span class="text-green-700 font-semibold text-2xl ml-2">
              <span v-if="itemName == null">Item not converted yet.</span>
              <span v-else>{{ itemName }}</span>
            </span>
          </div>
          <div class="my-4"></div>
          <div class="text-base text-gray-700">
            <span class="font-bold text-2xl">PRICE PER UNIT:</span>
            <span class="text-green-700 font-semibold text-2xl ml-2">
              <span v-if="formConvertItem.price_per_unit == Infinity">₱ 0</span>
              <span v-else>₱ {{ formConvertItem.price_per_unit }}</span>
            </span>
          </div>
        </div>

        <template #footer>
          <Button
            label="Cancel"
            icon="pi pi-times"
            severity="danger"
            class="p-button-text"
            @click="summaryConvertDialog = false"
          />
          <Button
            :disabled="formConvertItem.processing || countdown > 0"
            type="submit"
            severity="success"
            @click="submitConvertItem"
          >
            <i
              :class="formConvertItem.processing ? 'pi pi-spin pi-spinner' : 'mx-1 pi pi-check'"
              style="font-size: 1rem"
            ></i>
            {{ countdown > 0 ? `Save (${countdown})` : 'Save' }}
          </Button>
        </template>
      </Dialog>

      <!-- DELETE STOCK -->
      <Dialog
        v-model:visible="deleteStockDialog"
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
          <span class="">
            Are you sure you want to delete <b class="text-yellow-500">{{ formDeleteStock.cl2desc }}? </b>
          </span>
        </div>
        <template #footer>
          <Button
            label="No"
            icon="pi pi-times"
            class="p-button-text"
            @click="deleteStockDialog = false"
          />
          <Button
            label="Yes"
            icon="pi pi-check"
            severity="danger"
            text
            :disabled="formDeleteStock.processing"
            @click="deleteStock"
          />
        </template>
      </Dialog>
    </div>

    <div class="card">
      <TabView>
        <TabPanel header="TOTAL DELIVERIES">
          <!-- total deliveries -->
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
              style="width: 70%"
            >
              <template #body="{ data }">
                {{ data.cl2desc }}
              </template>
            </Column>
            <Column
              field="quantity"
              header="QTY"
              sortable
              style="width: 10%"
            >
              <template #body="{ data }">
                {{ data.quantity }}
              </template>
            </Column>
            <Column
              header="EXP. DATE"
              sortable
              style="width: 10%"
            >
              <template #body="{ data }">
                <div>{{ tzone(data.expiration_date) }}</div>

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
              </template>
            </Column>
            <Column
              header="ACTION"
              style="width: 10%"
            >
              <template #body="slotProps">
                <div class="flex justify-content-between">
                  <!-- <Button
                    v-tooltip.top="'Modify'"
                    rounded
                    severity="warning"
                    @click="editDelivery(slotProps.data)"
                  >
                    <template #icon>
                      <i class="pi pi-pencil"></i>
                    </template>
                  </Button> -->

                  <Button
                    v-if="slotProps.data.converted == 'n'"
                    v-tooltip.top="'Convert'"
                    rounded
                    severity="info"
                    @click="convertItem(slotProps.data)"
                  >
                    <template #icon>
                      <v-icon name="bi-arrow-left-right"></v-icon>
                    </template>
                  </Button>

                  <Button
                    v-if="slotProps.data.converted == 'n'"
                    v-tooltip.top="'Delete'"
                    icon="pi pi-trash"
                    rounded
                    severity="danger"
                    @click="openDeleteStockDialog(slotProps.data)"
                  />
                </div>
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
              field="cl2desc_before"
              header="CONVERTED FROM"
              sortable
            >
            </Column>
            <Column
              field="cl2desc_after"
              header="CONVERTED TO"
              sortable
            >
            </Column>
            <Column
              field="quantity_after"
              header="CONVERTED QTY"
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

                  <Button
                    v-if="slotProps.data.total_issued_qty == 0"
                    v-tooltip.top="'Delete'"
                    icon="pi pi-trash"
                    rounded
                    severity="danger"
                    @click="openDeleteConvertedItemDialog(slotProps.data)"
                  />
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
import ToggleButton from 'primevue/togglebutton';
import axios from 'axios';

import moment, { now } from 'moment';

export default {
  components: {
    AppLayout,
    ToggleButton,
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
    suppliers: Array,
    totalDeliveries: Object,
    newRisNo: String,
    totalConvertedItems: Object,
  },
  data() {
    return {
      itemName: null,
      countdown: 0,
      maxDate: false,
      minimumDate: null,
      stockId: null,
      isUpdate: false,
      importDeliveryDialog: false,
      addDeliveryDialog: false,
      updateStockDialog: false,
      summaryAddDeliveryDialog: false,
      summaryConvertDialog: false,
      deliveryExist: false,
      editDeliveryQtyDialog: false,
      convertDialog: false,
      editConvertedItemDialog: false,
      deleteConvertedItemDialog: false,
      deleteStockDialog: false,
      params: {},
      // paginator
      loading: false,
      totalRecords: null,
      rows: null,
      // end paginator,
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
      //   manufactured_date: null,
      delivered_date: null,
      expiration_date: null,
      acquisitionPrice: 0,
      generatedRisNo: false,
      convertedItemList: [],
      deliveryDetailsFilter: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
      // ------------------
      stocksList: [],
      totalDeliveriesList: [],
      suppliersList: [],
      convertedItemsList: [],
      convertedItemSelection: [],
      //   filters: {
      //     global: {
      //       value: null,
      //       matchMode: FilterMatchMode.CONTAINS,
      //     },
      //     ris_no: { value: null, matchMode: FilterMatchMode.CONTAINS },
      //     cl2desc: { value: null, matchMode: FilterMatchMode.CONTAINS },
      //     suppname: { value: null, matchMode: FilterMatchMode.CONTAINS },
      //     chrgdesc: { value: null, matchMode: FilterMatchMode.CONTAINS },
      //     // stock_lvl: { value: null, matchMode: FilterMatchMode.EQUALS },
      //     expiration: { from: null, to: null },
      //   },
      filters: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
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
        // manufactured_date: null,
        delivered_date: null,
        expiration_date: null,
        remarks: null,
        delivery_list: [],
      }),
      // data when clicking row to populate form
      formImport: this.$inertia.form({
        ris_no: null,
        supplier: null,
        supplierName: null,
        fsId: null,
        fsName: null,
        cl2comb: null,
        cl2desc: null,
        uomcode: null,
        uomdesc: null,
        // manufactured_date: null,
        delivered_date: null,
        expiration_date: null,
        acquisitionPrice: null,
        quantity: null,
        deliveryDetails: null,
        cl2comb_after: null,
        quantity_after: null,
        hospital_price: null,
        price_per_unit: null,
      }),
      // end data when clicking row to populate form
      formDeleteStock: this.$inertia.form({
        stock_id: null,
        cl2desc: null,
      }),
      formAddDelivery: this.$inertia.form({
        generateRisNo: false,
        ris_no: null,
        supplier: null,
        fund_source: null,
        cl2comb: null,
        // manufactured_date: null,
        delivered_date: null,
        expiration_date: '9999-12-30',
        quantity: 0,
        acquisitionPrice: 0,
        cl2comb_after: 0,
        quantity_after: 0,
        hospital_price: 0,
        price_per_unit: 0,
      }),
      formConvertItem: this.$inertia.form({
        id: null,
        csr_stock_id: null,
        ris_no: null,
        supplierID: null,
        chrgcode: null,
        cl2comb_before: null,
        delivered_date: null,
        expiration_date: null,
        quantity_before: 0,
        acquisitionPrice: 0,
        cl2comb_after: 0,
        quantity_after: 0,
        hospital_price: 0,
        price_per_unit: 0,
        update_price_per_unit: 0,
        remarks: null,
      }),
      //   formEditConvertedItem: this.$inertia.form({
      //     csr_stock_id: null,
      //     ris_no: null,
      //     supplierID: null,
      //     chrgcode: null,
      //     cl2comb_before: null,
      //     delivered_date: null,
      //     expiration_date: null,
      //     quantity_before: null,
      //     acquisition_price: null,
      //     cl2comb_after: null,
      //     quantity_after: null,
      //     hospital_price: null,
      //     price_per_unit: null,
      //     remarks: null,
      //   }),
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
  // created will be initialize before mounted
  created() {
    this.totalRecords = this.stocks.total;
    this.params.page = this.stocks.current_page;
    this.rows = this.stocks.per_page;
  },
  mounted() {
    // console.log(this.convertedItems);
    this.setMinimumDate();
    this.storeFundSourceInContainer();
    this.storeItemsInContainer();
    this.storeStocksInContainer();
    this.storeTotalDeliveriesInContainer();
    this.storeSuppliersInContainer();
    this.storeTotalConvertedItemsInContainer();

    this.formAddDelivery.ris_no = this.newRisNo;
  },
  beforeUnmount() {
    // Remove event listener before component is destroyed
    // document.removeEventListener('keydown', this.handleKeyPress);
  },
  computed: {
    isDonationItem() {
      // Find the selected item from convertedItemList
      const selectedItem = this.convertedItemList.find((item) => item.cl2comb === this.formAddDelivery.cl2comb_after);

      // Check if the optionLabel (cl2desc) contains "(DONATION)"
      return selectedItem && selectedItem.cl2desc && selectedItem.cl2desc.includes('(DONATION)');
    },
  },
  methods: {
    recalculatePrices() {
      let acquisition_price = Number(this.formConvertItem.acquisition_price);
      let quantity_before = Number(this.formConvertItem.quantity_before);
      let quantity_after = Number(this.formConvertItem.quantity_after);

      // Avoid division by 0
      if (!quantity_before || !quantity_after) {
        this.formConvertItem.hospital_price = 0;
        this.formConvertItem.price_per_unit = 0;
        return;
      }

      let hospital_price = (acquisition_price * quantity_before) / 0.7;
      this.formConvertItem.hospital_price = Number(hospital_price.toFixed(2));

      let price_per_unit = this.formConvertItem.hospital_price / quantity_after;
      this.formConvertItem.price_per_unit = Number(price_per_unit.toFixed(2));
    },
    currentRisNo() {
      this.formAddDelivery.ris_no = this.newRisNo;
    },
    // Increment the RIS number manually
    incrementRisNo() {
      if (!this.formAddDelivery.ris_no) return;

      const parts = this.formAddDelivery.ris_no.split('-');
      if (parts.length !== 3) return;

      let lastNumber = parseInt(parts[2] || '0', 10);
      lastNumber++; // Increment by 1
      parts[2] = String(lastNumber).padStart(6, '0'); // Ensure 6-digit format

      this.formAddDelivery.ris_no = parts.join('-');
    },
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
      //   this.manufactured_date = null;
      this.delivered_date = null;
      this.expiration_date = null;
      this.form.clearErrors();
      this.form.reset();
      this.formImport.clearErrors();
      this.formImport.reset();
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
    tzoneWithTime(date) {
      if (date == null || date == '') {
        return null;
      } else {
        return moment.tz(date, 'Asia/Manila').format('L LT');
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
      this.stocks.data.forEach((e) => {
        const expirationDate = e.expiration_date === null ? null : new Date(e.expiration_date); // Convert expiration_date to Date object
        this.stocksList.push({
          id: e.id,
          ris_no: e.ris_no,
          supplierID: e.supplierID,
          suppname: e.suppname,
          chrgcode: e.codeFromFundSource,
          chrgdesc: e.descFromFundSource,
          cl2comb: e.cl2comb,
          cl2desc: e.cl2desc,
          uomcode: e.uomcode == null ? null : e.uomcode,
          uomdesc: e.uomcode == null ? null : e.uomdesc,
          quantity: Number(e.quantity),
          acquisition_price: e.acquisition_price,
          //   manufactured_date: e.manufactured_date === null ? '' : e.manufactured_date,
          delivered_date: e.delivered_date === null ? '' : e.delivered_date,
          expiration_date: expirationDate, // Assign expirationDate to expiration_date field
          converted: e.converted,
          created_at: e.created_at,
        });
      });
      //   console.log(this.stocks);
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
          fsid: e.fsid,
          fsName: e.fsName,
          cl2comb_before: e.cl2comb_before,
          cl2desc_before: e.cl2desc_before,
          cl2comb_after: e.cl2comb_after,
          cl2desc_after: e.cl2desc_after,
          quantity_after: e.quantity_after,
          total_issued_qty: e.total_issued_qty,
          acquisition_price: e.acquisition_price,
          price_per_unit: e.price_per_unit,
          //   manufactured_date: e.manufactured_date,
          delivered_date: e.delivered_date,
          expiration_date: e.expiration_date,
          converted_by: e.firstname.trim() + ' ' + e.lastname.trim(),
        });
      });
      //   console.log(this.totalConvertedItemsList);
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
    storeItemsInContainer() {
      this.items.forEach((e) => {
        // if (!e.cl2desc.includes('(pc)')) {
        if (e.uomcode == 'box') {
          this.itemsList.push({
            cl1comb: e.cl1comb,
            cl2comb: e.cl2comb,
            cl2desc: e.cl2desc,
            uomcode: e.uomcode == null ? null : e.uomcode,
            uomdesc: e.uomdesc == null ? null : e.uomdesc,
          });
        }
      });

      this.items.forEach((e) => {
        this.convertedItemSelection.push({
          cl1comb: e.cl1comb,
          cl2comb: e.cl2comb,
          cl2desc: e.cl2desc,
          uomcode: e.uomcode == null ? null : e.uomcode,
          uomdesc: e.uomdesc == null ? null : e.uomdesc,
        });
      });
    },
    findItem(e) {
      const item = this.itemsList.find((item) => item.cl2comb === e);
      //   return item.cl2desc;
      if (item != null) {
        return item.cl2desc;
      } else {
        return '';
      }
    },
    findSimilarIds(targetId, arr) {
      this.convertedItemList = [];
      // Extract the prefix from the target ID
      const prefix = targetId.split('-').slice(0, 2).join('-');

      // Filter the array to find matching IDs
      this.convertedItemList = arr.filter(
        (obj) => obj.cl1comb && obj.cl1comb.startsWith(prefix) && obj.uomcode !== 'box'
      );
    },
    storeTotalDeliveriesInContainer() {
      this.totalDeliveries.forEach((e) => {
        this.totalDeliveriesList.push({
          stock_id: e.stock_id,
          ris_no: e.ris_no,
          cl2comb: e.cl2comb,
          cl2desc: e.cl2desc.trim(),
          supplierID: e.supplierID,
          acquisition_price: e.acquisition_price,
          chrgcode: e.chrgcode,
          quantity: e.quantity,
          delivered_date: e.delivered_date,
          expiration_date: e.expiration_date,
          converted: e.converted,
        });
      });
    },
    onPage(event) {
      this.params.page = event.page + 1;
      this.updateData();
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
          this.convertedItemSelection = [];
          this.totalConvertedItemsList = [];
          this.storeFundSourceInContainer();
          this.storeItemsInContainer();
          this.storeStocksInContainer();
          this.storeTotalDeliveriesInContainer();
          this.storeSuppliersInContainer();
          this.storeTotalConvertedItemsInContainer();
        },
      });
    },
    openSummaryAddDeliveryDialog() {
      //   console.log(this.formAddDelivery.price_per_unit);
      //   console.log('summary item', this.formAddDelivery.cl2comb);
      this.convertedItemList.forEach((e) => {
        // console.log(e);
        if (this.formAddDelivery.cl2comb_after == e.cl2comb) {
          this.itemName = e.cl2desc;
        }
      });
      this.summaryAddDeliveryDialog = true;
    },
    openSummaryConvertDialog() {
      //   console.log(this.formConvertItem.price_per_unit);
      //   console.log('summary item', this.formConvertItem.cl2comb);
      this.convertedItemList.forEach((e) => {
        // console.log(e);
        if (this.formConvertItem.cl2comb_after == e.cl2comb) {
          this.itemName = e.cl2desc;
        }
      });
      this.summaryConvertDialog = true;
    },
    openImportDeliveryDialog() {
      this.isUpdate = false;
      this.form.clearErrors();
      this.form.reset();
      this.stockId = null;
      this.importDeliveryDialog = true;
    },
    openAddDeliveryDialog() {
      //   this.formAddDelivery.clearErrors();
      //   this.formAddDelivery.reset();
      //   this.formAddDelivery.expiration_date = new Date('12-30-9999');
      this.addDeliveryDialog = true;
    },
    // emit close dialog
    clickOutsideDialog() {
      this.$emit(
        'hide',
        (this.disableSearchRisInput = false),
        (this.isUpdate = false),
        (this.item = null),
        // (this.addDeliveryDialog = false),
        (this.editConvertedItemDialog = false),
        (this.supplier = null),
        (this.selectedFundSource = null),
        (this.selectedItemsUomCode = null),
        (this.selectedItemsUomDesc = null),
        (this.quantity = null),
        (this.deliveryDetails = []),
        // (this.manufactured_date = null),
        (this.delivered_date = null),
        (this.expiration_date = null),
        this.form.clearErrors(),
        this.form.reset(),
        this.formImport.clearErrors(),
        this.formImport.reset(),
        this.formConvertItem.reset(),
        this.formConvertItem.clearErrors()
        // this.formAddDelivery.clearErrors()
        // this.formAddDelivery.reset()
      );
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
      //   this.form.manufactured_date = item.manufactured_date;
      this.form.delivered_date = item.delivered_date;
      this.form.expiration_date = item.expiration_date;
    },
    async fillDeliveriesContainer() {
      this.deliveryExist = this.stocksList.some((item) => item.ris_no === this.form.searchRis);

      if (this.deliveryExist != true && this.form.searchRis != null && this.form.searchRis != '') {
        try {
          const response = await axios.post('csrstocks', this.form);
          // console.log(response.data); // Log the response data if needed

          let sanitizedData = response.data;
          //   console.log(response);

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
                // manufactured_date: null,
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

    convertItem(item) {
      const similarObjects = this.findSimilarIds(item.cl2comb, this.convertedItemSelection);

      this.formConvertItem.csr_stock_id = item.stock_id;
      this.formConvertItem.ris_no = item.ris_no;
      this.formConvertItem.supplierID = item.supplierID;
      this.formConvertItem.chrgcode = item.chrgcode;
      this.formConvertItem.cl2comb_before = item.cl2comb;
      this.formConvertItem.delivered_date = item.delivered_date;
      this.formConvertItem.expiration_date = item.expiration_date;
      this.formConvertItem.quantity_before = item.quantity;
      this.formConvertItem.acquisition_price = item.acquisition_price;

      this.convertDialog = true;
    },
    editDelivery(item) {
      //   console.log(item);
      const similarObjects = this.findSimilarIds(item.cl2comb, this.convertedItemSelection);

      this.formConvertItem.csr_stock_id = item.stock_id;
      this.formConvertItem.ris_no = item.ris_no;
      this.formConvertItem.supplierID = item.supplierID;
      this.formConvertItem.chrgcode = item.chrgcode;
      this.formConvertItem.cl2comb_before = item.cl2comb;
      this.formConvertItem.delivered_date = item.delivered_date;
      this.formConvertItem.expiration_date = item.expiration_date;
      this.formConvertItem.quantity_before = item.quantity;
      this.formConvertItem.acquisition_price = item.acquisition_price;

      this.convertDialog = true;
    },
    submitConvertItem() {
      if (
        this.formConvertItem.processing ||
        this.formConvertItem.cl2comb_after == '' ||
        this.formConvertItem.cl2comb_after == 0 ||
        this.formConvertItem.cl2comb_after == null ||
        this.formConvertItem.quantity_after == '' ||
        this.formConvertItem.quantity_after == 0 ||
        this.formConvertItem.quantity_after == null ||
        this.formConvertItem.remarks == '' ||
        this.formConvertItem.remarks == null
      ) {
        return false;
      }

      this.formConvertItem.post(route('csrconvertdelivery.store'), {
        preserveScroll: true,
        onSuccess: () => {
          this.convertDialog = false;
          this.cancel();
          this.updateData();
          this.createdMsg();
        },
      });
    },
    editConvertedItem(item) {
      //   console.log(item);
      this.formConvertItem.update_price_per_unit = item.price_per_unit;
      this.formConvertItem.id = item.id;
      this.formConvertItem.quantity_after = item.quantity_after;

      this.editConvertedItemDialog = true;
    },
    submitUpdateConvertItem() {
      this.formConvertItem.put(route('csrconvertdelivery.update', this.formConvertItem.id), {
        preserveScroll: true,
        onSuccess: () => {
          this.editConvertedItemDialog = true;
          this.cancel();
          this.updateData();
          this.updateQuantity();
        },
      });
    },
    openDeleteConvertedItemDialog(item) {
      this.formConvertItem.id = item.id;
      this.formConvertItem.ris_no = item.ris_no;
      this.formConvertItem.cl2desc_after = item.cl2desc_after;
      //   this.formConvertItem.total_issued_qty = item.total_issued_qty;
      this.deleteConvertedItemDialog = true;
    },
    deleteConvertItem() {
      if (this.formConvertItem.processing) {
        return false;
      }

      this.formConvertItem.delete(route('csrconvertdelivery.destroy', this.formConvertItem.id), {
        preserveScroll: true,
        onSuccess: () => {
          this.deleteConvertedItemDialog = false;
          this.cancel();
          this.updateData();
          this.deleteConvertedItemMsg();
        },
      });
    },
    openDeleteStockDialog(item) {
      this.formDeleteStock.stock_id = item.stock_id;
      this.formDeleteStock.cl2desc = item.cl2desc;
      this.deleteStockDialog = true;
    },
    deleteStock() {
      if (this.formDeleteStock.processing) {
        return false;
      }

      this.formDeleteStock.delete(route('csrstocks.destroy', this.formDeleteStock.stock_id), {
        preserveScroll: true,
        onSuccess: () => {
          this.deleteStockDialog = false;
          this.cancel();
          this.updateData();
          this.deleteStockMsg();
        },
      });
    },
    onRowClick(e) {
      //   console.log(e.data);
      this.formImport.ris_no = e.data.risno;
      this.formImport.supplier = e.data.supplier;
      this.formImport.suppname = e.data.supplierName;
      this.formImport.fsId = e.data.fsid;
      this.formImport.fsName = e.data.fundSourceName;
      this.formImport.cl2comb = e.data.cl2comb;
      this.formImport.cl2desc = e.data.cl2desc;
      this.formImport.uomcode = e.data.uomcode;
      this.formImport.uomdesc = e.data.uomdesc;
      this.formImport.quantity = Number(e.data.releaseqty);
      //   this.formImport.manufactured_date = e.data.manufactured_date;
      this.formImport.delivered_date = e.data.delivered_date;
      this.formImport.expiration_date = e.data.expiration_date;
      this.formImport.acquisitionPrice = Number(e.data.unitprice);
      this.formImport.cl2comb_after = e.data.cl2comb_after;
      this.formImport.quantity_after = e.data.quantity_after;

      //   this.storeConvertedItemsInContainer();

      // Find similar IDs in array2
      const similarObjects = this.findSimilarIds(e.data.cl2comb, this.convertedItemSelection);
      //   console.log(similarObjects);

      // Log or handle the similar objects as needed
      //   console.log('Similar objects:', similarObjects);
      //   this.convertedItemsList = similarObjects;
    },
    updateNewDetailsToDeliveryDets() {
      this.deliveryDetails.forEach((e) => {
        if (
          this.formImport.ris_no == e.risno &&
          this.formImport.cl2comb == e.cl2comb &&
          this.formImport.quantity == e.releaseqty
        ) {
          // only update property if none on the properties is null
          //   if (e.supplier == null || e.expiration_date == null) {
          e.supplier = this.formImport.supplier;
          e.supplierName = this.formImport.supplier == null ? null : this.formImport.supplier.suppname;
          //   e.manufactured_date = this.formImport.manufactured_date;
          e.delivered_date = this.formImport.delivered_date;
          e.expiration_date = this.formImport.expiration_date;
          e.releaseqty = this.formImport.quantity;
          e.cl2comb_after = this.formImport.cl2comb_after;
          e.quantity_after = this.formImport.quantity_after;
          e.hospital_price = this.formImport.hospital_price;
          e.price_per_unit = this.formImport.price_per_unit;
          //   }
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
      if (this.form.processing && this.formImport.processing) {
        return false;
      }

      this.formImport.deliveryDetails = this.deliveryDetails;

      const isEmpty = this.deliveryDetails.some((item) => {
        return !item.expiration_date || !item.delivered_date;
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
          this.formImport.post(route('csrstocks.store'), {
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
        this.formAddDelivery.fund_source == null ||
        this.formAddDelivery.cl2comb == null ||
        this.formAddDelivery.delivered_date == null ||
        this.formAddDelivery.expiration_date == null ||
        this.formAddDelivery.quantity == null ||
        this.formAddDelivery.quantity == 0 ||
        this.formAddDelivery.acquisitionPrice == null ||
        this.formAddDelivery.acquisitionPrice == '' ||
        (!this.isDonationItem && this.formAddDelivery.acquisitionPrice == 0)
      ) {
        return false;
      }

      this.formAddDelivery.post(route('csrmanualadd.store'), {
        preserveScroll: true,
        onSuccess: () => {
          this.addDeliveryDialog = true;
          //   this.cancel();

          this.formAddDelivery.cl2comb = null;
          this.formAddDelivery.quantity = 0;
          this.formAddDelivery.acquisitionPrice = 0;
          this.formAddDelivery.cl2comb_after = null;
          this.formAddDelivery.quantity_after = 0;
          this.formAddDelivery.hospital_price = 0;
          this.formAddDelivery.price_per_unit = 0;
          //   this.formAddDelivery.ris_no = this.newRisNo;

          this.stocksList = [];
          this.storeStocksInContainer();

          this.updateData();
          this.createdMsg();
          this.summaryAddDeliveryDialog = false;
        },
      });
    },
    cancel() {
      this.addDeliveryDialog = false;
      this.convertDialog = false;
      this.stockId = null;
      this.isUpdate = false;
      this.importDeliveryDialog = false;
      this.disableSearchRisInput = false;
      this.updateStockDialog = false;
      this.editConvertedItemDialog = false;
      this.summaryAddDeliveryDialog = false;
      this.summaryConvertDialog = false;
      this.form.reset();
      this.form.clearErrors();
      this.formImport.reset();
      this.formImport.clearErrors();
      this.formConvertItem.reset();
      this.formConvertItem.clearErrors();
      // formAddDelivery
      //   this.formAddDelivery.generateRisNo = false;
      //   this.formAddDelivery.fund_source = null;
      //   this.formAddDelivery.cl2comb = null;
      //   //   this.formAddDelivery.manufactured_date = null;
      //   this.formAddDelivery.delivered_date = null;
      //   this.formAddDelivery.expiration_date = null;
      //   this.formAddDelivery.quantity = 0;
      //   this.formAddDelivery.acquisitionPrice = 0;
      //   this.formAddDelivery.cl2comb_after = 0;
      //   this.formAddDelivery.quantity_after = 0;
      //   this.formAddDelivery.hospital_price = 0;
      //   this.formAddDelivery.price_per_unit = 0;
      //   this.formAddDelivery.clearErrors();

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
    updateQuantity() {
      this.$toast.add({ severity: 'warn', summary: 'Success', detail: 'Item quantity updated', life: 3000 });
    },
    deleteConvertedItemMsg() {
      this.$toast.add({ severity: 'error', summary: 'Success', detail: 'Converted item deleted', life: 3000 });
    },
    deleteStockMsg() {
      this.$toast.add({ severity: 'error', summary: 'Success', detail: 'Stock deleted', life: 3000 });
    },
  },
  watch: {
    search: function (val, oldVal) {
      this.params.search = val;
      this.updateData();
    },
    summaryAddDeliveryDialog(newVal) {
      if (newVal) {
        this.countdown = 4; // Reset countdown when dialog is opened
        this.timer = setInterval(() => {
          if (this.countdown > 0) {
            this.countdown--;
          } else {
            clearInterval(this.timer);
            this.timer = null;
          }
        }, 1000);
      } else {
        clearInterval(this.timer); // Stop countdown if dialog closes early
        this.timer = null;
      }
    },
    summaryConvertDialog(newVal) {
      if (newVal) {
        this.countdown = 4; // Reset countdown when dialog is opened
        this.timer = setInterval(() => {
          if (this.countdown > 0) {
            this.countdown--;
          } else {
            clearInterval(this.timer);
            this.timer = null;
          }
        }, 1000);
      } else {
        clearInterval(this.timer); // Stop countdown if dialog closes early
        this.timer = null;
      }
    },
    item: function (val) {
      this.selectedItemsUomDesc = null;

      if (val != null) {
        this.selectedItemsUomDesc = val.uomdesc;
        this.unit = val.uomcode;
      } else {
        this.selectedItemsUomDesc = null;
      }
    },
    convertedItemsList: function (val) {
      this.convertedItemsList = [];

      this.convertedItemsList = val;
    },
    'formAddDelivery.generateRisNo': function (val) {
      if (val == true) {
        this.generateTempRisNo();
        this.generatedRisNo = true;
      } else {
        this.formAddDelivery.ris_no = null;
        this.generatedRisNo = false;
      }
    },
    'formAddDelivery.cl2comb': function (cl2comb) {
      // Find similar IDs in array2
      if (cl2comb != null) {
        const similarObjects = this.findSimilarIds(cl2comb, this.convertedItemSelection);
      }
    },

    'formConvertItem.acquisition_price': 'recalculatePrices',
    'formConvertItem.quantity_before': 'recalculatePrices',
    'formConvertItem.quantity_after': 'recalculatePrices',
    // formConvertItem: {
    //   handler(e) {
    //     this.formConvertItem.hospital_price = 0;
    //     //    acquisition price
    //     let acquisition_price = Number(e.acquisition_price);
    //     let hospital_price = (acquisition_price * this.formConvertItem.quantity_before) / 0.7;
    //     this.formConvertItem.hospital_price = Number(hospital_price.toFixed(2));

    //     let price_per_unit = this.formConvertItem.hospital_price / this.formConvertItem.quantity_after;
    //     this.formConvertItem.price_per_unit = Number(price_per_unit).toFixed(2);
    //   },
    //   deep: true,
    // },

    formImport: {
      handler(e) {
        // console.log(this.formAddDelivery.price_per_unit);
        // // acquisition price
        let acquisitionPrice = Number(e.acquisitionPrice);
        let hospital_price = (acquisitionPrice * this.formImport.quantity) / 0.7;
        this.formImport.hospital_price = Number(hospital_price.toFixed(2));

        let price_per_unit = this.formImport.hospital_price / this.formImport.quantity_after;
        this.formImport.price_per_unit = Number(price_per_unit).toFixed(2);
      },
      deep: true,
    },
    formAddDelivery: {
      handler(e) {
        // console.log('formadddeliver', e);
        // // acquisition price
        let acquisitionPrice = Number(e.acquisitionPrice);
        let hospital_price = (acquisitionPrice * this.formAddDelivery.quantity) / 0.7;
        this.formAddDelivery.hospital_price = Number(hospital_price.toFixed(2));

        let price_per_unit = this.formAddDelivery.hospital_price / this.formAddDelivery.quantity_after;
        // isNaN(Number(this.formAddDelivery.price_per_unit)) || this.formAddDelivery.price_per_unit == 0
        //   ? '0.00'
        //   : Number(this.formAddDelivery.price_per_unit).toFixed(2);
        this.formAddDelivery.price_per_unit = Number(price_per_unit).toFixed(2);
      },
      deep: true,
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
