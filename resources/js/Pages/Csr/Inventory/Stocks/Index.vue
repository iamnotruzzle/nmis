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
        sortField="expiration_date"
        :sortOrder="1"
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
              <Button
                label="Add deliveries"
                icon="pi pi-plus"
                iconPos="right"
                @click="openCreateItemDialog"
              />
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
          field="brand_name"
          header="BRAND"
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
          field="mark_up"
          header="MARK UP"
          style="width: 5%"
        >
        </Column>
        <Column
          field="selling_price"
          header="SELLING PRICE"
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
            <div class="flex flex-row m-0 p-0">
              <Button
                icon="pi pi-pencil"
                class="mr-1"
                rounded
                text
                severity="warning"
                @click="editItem(slotProps.data)"
              />

              <Button
                icon="pi pi-trash"
                rounded
                text
                severity="danger"
                @click="confirmDeleteItem(slotProps.data)"
              />
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

      <!-- create delivery details dialog -->
      <Dialog
        v-model:visible="createStockDialog"
        :modal="true"
        class="p-fluid w-11 overflow-hidden"
        :style="{ height: '90%' }"
        @hide="clickOutsideDialog"
        :draggable="false"
      >
        <template #header>
          <div class="text-primary text-xl font-bold">DELIVERIES</div>
          <!-- <div class="field">
            <label>RIS no.</label>
            <InputText
              v-model.trim="form.ris_no"
              autofocus
              @keyup.enter="fillDeliveriesContainer"
            />
          </div> -->
        </template>

        <div class="flex flex-row justify-content-between overflow-hidden">
          <!-- form -->
          <div class="w-3">
            <div class="field">
              <label>RIS no.</label>
              <InputText
                v-model.trim="form.searchRis"
                autofocus
                @keyup.enter="fillDeliveriesContainer"
                :readonly="disableSearchRisInput == true"
              />
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
                dataKey="suppcode"
                optionLabel="suppname"
                class="w-full"
              />
            </div>
            <div class="field">
              <div class="flex align-content-center">
                <label>Fund source</label>
                <span class="ml-2 text-error">*</span>
              </div>
              <InputText
                v-model="formAdditional.fsName"
                readonly
              />
            </div>
            <div class="field">
              <div class="flex align-content-center">
                <label>Item</label>
                <span class="ml-2 text-error">*</span>
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
            <div class="field">
              <div class="flex align-content-center">
                <label>Brand</label>
                <span class="ml-2 text-error">*</span>
              </div>
              <Dropdown
                required="true"
                v-model="formAdditional.brand"
                :options="brandDropDownList"
                :virtualScrollerOptions="{ itemSize: 38 }"
                filter
                showClear
                dataKey="id"
                optionLabel="name"
                class="w-full mb-3"
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
                :minDate="minimumDate"
                :hideOnDateTimeSelect="true"
              />
            </div>
            <div class="field">
              <div class="flex align-content-center">
                <label>Quantity</label>
              </div>
              <InputNumber
                required="true"
                v-model.trim="formAdditional.quantity"
                autofocus
                inputId="integeronly"
                readonly
              />
            </div>
            <div class="field flex justify-content-between">
              <div>
                <div class="flex align-content-center">
                  <label>Acquisition price</label>
                  <span class="ml-2 text-error">*</span>
                </div>
                <InputNumber
                  required="true"
                  v-model.trim="formAdditional.acquisitionPrice"
                  autofocus
                  :maxFractionDigits="2"
                  readonly
                />
              </div>

              <div class="mx-2"></div>

              <div>
                <div class="flex align-content-center">
                  <label>Markup price (%)</label>
                  <span class="ml-2 text-error">*</span>
                </div>
                <InputText
                  required="true"
                  type="number"
                  v-model.trim="formAdditional.markupPercentage"
                  autofocus
                  @keydown="restrictNonNumeric"
                />
              </div>
            </div>
            <div class="field">
              <div class="flex align-content-center">
                <label>Selling price</label>
              </div>
              <InputNumber
                required="true"
                v-model="roundedSellingPrice"
                readonly
              />
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
              scrollHeight="580px"
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
                field="brandName"
                header="BRAND"
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
                field="markupPercentage"
                header="MARK UP PERCENTAGE"
              >
              </Column>
              <Column
                field="sellingPrice"
                header="SELLING PRICE"
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
            :disabled="form.processing || deliveryDetails.length == 0"
            @click="submit"
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
                v-model="form.suppcode"
                :options="suppliersList"
                :virtualScrollerOptions="{ itemSize: 38 }"
                filter
                dataKey="suppcode"
                optionLabel="suppname"
                optionValue="suppcode"
                class="w-full"
                :class="{ 'p-invalid': form.suppcode == '' }"
              />
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
                <label>Brand</label>
                <span class="ml-2 text-error">*</span>
              </div>
              <Dropdown
                required="true"
                v-model="form.brand"
                :options="brandDropDownList"
                :virtualScrollerOptions="{ itemSize: 38 }"
                filter
                showClear
                dataKey="id"
                optionLabel="name"
                optionValue="id"
                class="w-full mb-3"
                :class="{ 'p-invalid': form.brand == '' }"
              />
            </div>
            <div class="field">
              <div class="flex align-content-center">
                <label>Quantity</label>
              </div>
              <InputNumber
                id="quantity"
                v-model.trim="form.quantity"
                readonly
                inputId="integeronly"
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
                :minDate="minimumDate"
                :hideOnDateTimeSelect="true"
              />
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
            <div class="field">
              <div class="flex align-content-center">
                <label>Mark up (%)</label>
              </div>
              <InputText
                v-model="form.mark_up"
                readonly
              />
            </div>
            <div class="field">
              <div class="flex align-content-center">
                <label>Selling price</label>
              </div>
              <InputText
                v-model="form.selling_price"
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

      <!-- Delete confirmation dialog -->
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
          <span v-if="form"
            >Are you sure you want to delete <b>{{ form.cl2desc }}</b> with RIS number <b>{{ form.ris_no }}</b> ?</span
          >
        </div>

        <div class="field mt-5 flex flex-column">
          <label for="remarks">REMARKS <span class="text-error">*</span></label>
          <Textarea
            v-model.trim="form.remarks"
            rows="5"
            autofocus
          />
          <small
            class="text-error"
            v-if="form.errors.remarks"
          >
            {{ form.errors.remarks }}
          </small>
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
            :disabled="form.remarks == '' || form.remarks == null || form.processing"
            @click="deleteItem"
          />
        </template>
      </Dialog>

      <!-- create & edit brand dialog -->
      <Dialog
        v-model:visible="createBrandDialog"
        :style="{ width: '450px' }"
        :modal="true"
        class="p-fluid"
        @hide="clickOutsideDialog"
        dismissableMask
      >
        <template #header>
          <div class="text-primary text-xl font-bold">STOCK DETAIL</div>
        </template>
        <div class="field">
          <label for="brand_name">Brand name</label>
          <InputText
            id="brand_name"
            v-model.trim="formBrand.name"
            required="true"
            autofocus
            :class="{ 'p-invalid': formBrand.name == '' }"
          />
          <small
            class="text-error"
            v-if="formBrand.errors.name"
          >
            {{ formBrand.errors.name }}
          </small>
        </div>

        <div class="field">
          <label for="status">Status</label>
          <Dropdown
            v-model="formBrand.status"
            :options="brandStatus"
            optionLabel="name"
            optionValue="value"
            class="w-full md:w-14rem"
          />
          <small
            class="text-error"
            v-if="formBrand.errors.status"
          >
            {{ formBrand.errors.status }}
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
            v-if="isUpdateBrand == true"
            label="Update"
            icon="pi pi-check"
            severity="warning"
            text
            type="submit"
            :disabled="formBrand.processing"
            @click="submitBrand"
          />
          <Button
            v-else
            label="Save"
            icon="pi pi-check"
            text
            type="submit"
            :disabled="formBrand.processing"
            @click="submitBrand"
          />
        </template>
      </Dialog>
      <!-- end brand -->
    </div>

    <div class="card">
      <div class="grid">
        <!-- brand -->
        <DataTable
          class="p-datatable-sm sm:col-12 md:col-6"
          dataKey="id"
          v-model:filters="brandFilters"
          :value="brandsList"
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
              <span class="text-xl text-900 font-bold text-primary">BRANDS</span>

              <div class="flex">
                <div class="mr-2">
                  <div class="p-inputgroup">
                    <span class="p-inputgroup-addon">
                      <i class="pi pi-search"></i>
                    </span>
                    <InputText
                      v-model="brandFilters['global'].value"
                      placeholder="Search brand"
                    />
                  </div>
                </div>
                <Button
                  label="Add brand"
                  icon="pi pi-plus"
                  iconPos="right"
                  @click="openCreateBrandDialog"
                />
              </div>
            </div>
          </template>
          <template #empty> No brand found. </template>
          <template #loading> Loading brand data. Please wait. </template>
          <Column
            field="name"
            header="NAME"
            sortable
            style="width: 70%"
          >
            <template #body="{ data }">
              {{ data.name }}
            </template>
          </Column>
          <Column
            field="status"
            header="STATUS"
            sortable
            style="width: 15%"
          >
            <template #body="{ data }">
              <div class="flex justify-content-center">
                <Tag
                  v-if="data.status == 'A'"
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
          </Column>
          <Column
            header="ACTION"
            style="width: 15%"
          >
            <template #body="slotProps">
              <Button
                v-if="slotProps.data.name != 'NO BRAND'"
                icon="pi pi-pencil"
                class="mr-1"
                rounded
                text
                severity="warning"
                @click="editBrand(slotProps.data)"
              />

              <!-- <Button
              icon="pi pi-trash"
              rounded
              text
              severity="danger"
              @click="confirmDeleteItem(slotProps.data)"
            /> -->
            </template>
          </Column>
        </DataTable>

        <!-- total stocks -->
        <DataTable
          class="p-datatable-sm sm:col-12 md:col-6"
          dataKey="id"
          v-model:filters="totalStocksFilters"
          :value="totalStocksList"
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
              <span class="text-xl text-900 font-bold text-primary">TOTAL STOCKS</span>

              <div class="flex">
                <div class="mr-2">
                  <div class="p-inputgroup">
                    <span class="p-inputgroup-addon">
                      <i class="pi pi-search"></i>
                    </span>
                    <InputText
                      v-model="totalStocksFilters['global'].value"
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
import InputNumber from 'primevue/inputnumber';
import Tag from 'primevue/tag';
import Textarea from 'primevue/textarea';
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
  },
  props: {
    items: Object,
    stocks: Object,
    brands: Object,
    totalStocks: Object,
    typeOfCharge: Object,
    fundSource: Object,
    suppliers: Object,
  },
  data() {
    return {
      minimumDate: null,
      stockId: null,
      isUpdate: false,
      isUpdateBrand: false,
      createStockDialog: false,
      updateStockDialog: false,
      createBrandDialog: false,
      deleteStockDialog: false,
      deleteBrandDialog: false,
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
      // -----------------
      disableSearchRisInput: false,
      itemNotSelected: false,
      item: null,
      itemsList: [],
      supplier: null,
      brand: null,
      selectedFundSource: null,
      selectedItemsUomCode: null,
      selectedItemsUomDesc: null,
      quantity: null,
      deliveryDetails: [],
      manufactured_date: null,
      delivered_date: null,
      expiration_date: null,
      acquisitionPrice: 0,
      markupPercentage: 0,
      localSellingPrice: 0,
      //   sellingPrice: null,
      deliveryDetailsFilter: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
      // ------------------
      // data when clicking row to populate form
      formAdditional: this.$inertia.form({
        ris_no: null,
        supplier: null,
        supplierName: null,
        brand: null,
        brandName: null,
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
        markupPercentage: 0,
        calculatedSellingPrice: null,
        quantity: null,
        deliveryDetails: null,
      }),
      // end data when clicking row to populate form
      brandsList: [],
      brandDropDownList: [],
      stocksList: [],
      totalStocksList: [],
      suppliersList: [],
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
      brandFilters: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
      totalStocksFilters: {
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

        ris_no: null,
        suppcode: null,
        fund_source: null,
        chrgdesc: null,
        cl2comb: null,
        cl2desc: null,
        uomcode: null,
        uomdesc: null,
        brand: null,
        cl2desc: null,
        quantity: null,
        acquisition_price: null,
        mark_up: null,
        selling_price: null,
        manufactured_date: null,
        delivered_date: null,
        expiration_date: null,
        remarks: null,
        delivery_list: [],
      }),
      formBrand: this.$inertia.form({
        id: null,
        name: null,
        status: null,
      }),
      brandStatus: [
        {
          name: 'ACTIVE',
          value: 'A',
        },
        {
          name: 'INACTIVE',
          value: 'I',
        },
      ],
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
    sellingPrice() {
      return this.formAdditional.acquisitionPrice * (1 + this.formAdditional.markupPercentage / 100);
    },
    roundedSellingPrice() {
      let sellingPrice = this.sellingPrice;

      // Round to the nearest 0.25, 0.50, or 0.75
      if (sellingPrice % 1 < 0.25) {
        this.formAdditional.calculatedSellingPrice = Math.floor(sellingPrice) + 0.25;
        return Math.floor(sellingPrice) + 0.25;
      } else if (sellingPrice % 1 >= 0.25 && sellingPrice % 1 < 0.5) {
        this.formAdditional.calculatedSellingPrice = Math.floor(sellingPrice) + 0.5;
        return Math.floor(sellingPrice) + 0.5;
      } else if (sellingPrice % 1 >= 0.5 && sellingPrice % 1 < 0.75) {
        this.formAdditional.calculatedSellingPrice = Math.floor(sellingPrice) + 0.75;
        return Math.floor(sellingPrice) + 0.75;
      } else {
        this.formAdditional.calculatedSellingPrice = Math.ceil(sellingPrice);
        return Math.ceil(sellingPrice);
      }
    },
  },
  mounted() {
    this.setMinimumDate();
    this.storeFundSourceInContainer();
    this.storeItemsInContainer();
    this.storeStocksInContainer();
    this.storeBrandsInContainer();
    this.storeActiveBrandsInContainer();
    this.storeTotalStocksInContainer();
    this.storeSuppliersInContainer();

    // Add event listener to the document
    document.addEventListener('keydown', this.handleKeyPress);
  },
  beforeUnmount() {
    // Remove event listener before component is destroyed
    document.removeEventListener('keydown', this.handleKeyPress);
  },
  methods: {
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
    updateLocalSellingPrice() {
      this.localSellingPrice = this.roundedSellingPrice;
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
          suppcode: e.suppcode,
          suppname: e.suppname,
          chrgcode: e.codeFromHCharge === null ? e.codeFromFundSource : e.codeFromHCharge,
          chrgdesc: e.codeFromHCharge === null ? e.descFromFundSource : e.descFromHCharge,
          cl2comb: e.cl2comb,
          cl2desc: e.cl2desc,
          uomcode: e.uomcode == null ? null : e.uomcode,
          uomdesc: e.uomcode == null ? null : e.uomdesc,
          brand_id: e.brand_id,
          brand_name: e.brand_name,
          quantity: Number(e.quantity),
          acquisition_price: e.acquisition_price,
          mark_up: e.mark_up,
          selling_price: e.selling_price,
          normal_stock: e.normal_stock == null ? 'N/A' : Number(e.normal_stock),
          alert_stock: e.alert_stock == null ? 'N/A' : Number(e.alert_stock),
          critical_stock: e.critical_stock == null ? 'N/A' : Number(e.critical_stock),
          stock_lvl: stock_lvl,
          manufactured_date: e.manufactured_date === null ? '' : e.manufactured_date,
          delivered_date: e.delivered_date === null ? '' : e.delivered_date,
          expiration_date: expirationDate, // Assign expirationDate to expiration_date field
        });
      });
    },
    storeSuppliersInContainer() {
      this.suppliers.forEach((e) => {
        this.suppliersList.push({
          suppcode: e.suppcode,
          suppname: e.suppname,
        });
      });
    },
    storeFundSourceInContainer() {
      this.typeOfCharge.forEach((e) => {
        this.fundSourceList.push({
          chrgcode: e.chrgcode,
          chrgdesc: e.chrgdesc,
          bentypcod: e.bentypcod,
          chrgtable: e.chrgtable,
        });
      });

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
      this.totalStocks.forEach((e) => {
        this.totalStocksList.push({
          cl2comb: e.cl2comb,
          cl2desc: e.item_detail.cl2desc.trim(),
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
          this.brandsList = [];
          this.totalStocksList = [];
          this.brandDropDownList = [];
          this.storeStocksInContainer();
          this.storeBrandsInContainer();
          this.storeActiveBrandsInContainer();
          this.storeTotalStocksInContainer();
        },
      });
    },
    openCreateItemDialog() {
      this.isUpdate = false;
      this.form.clearErrors();
      this.form.reset();
      this.stockId = null;
      this.createStockDialog = true;
    },
    // emit close dialog
    clickOutsideDialog() {
      this.$emit(
        'hide',
        (this.disableSearchRisInput = false),
        (this.isUpdate = false),
        (this.isUpdateBrand = false),
        (this.item = null),
        (this.brand = null),
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
        this.formBrand.clearErrors(),
        this.formBrand.reset()
      );
    },
    editItem(item) {
      console.log(item);
      this.isUpdate = true;
      this.updateStockDialog = true;
      this.stockId = item.id;
      this.form.ris_no = item.ris_no;
      this.form.suppcode = item.suppcode;
      this.form.fund_source = item.chrgcode;
      this.form.chrgdesc = item.chrgdesc;
      this.form.cl2comb = item.cl2comb;
      this.form.uomcode = item.uomcode;
      this.form.cl2desc = item.cl2desc;
      this.form.uomdesc = item.uomdesc;
      this.form.brand = Number(item.brand_id);
      this.form.quantity = item.quantity;
      this.form.acquisition_price = item.acquisition_price;
      this.form.mark_up = item.mark_up;
      this.form.selling_price = item.selling_price;
      this.form.manufactured_date = item.manufactured_date;
      this.form.delivered_date = item.delivered_date;
      this.form.expiration_date = item.expiration_date;
    },
    async fillDeliveriesContainer() {
      try {
        const response = await axios.post('csrstocks', this.form);
        console.log(response.data); // Log the response data if needed

        let sanitizedData = response.data;

        sanitizedData.forEach((e) => {
          // conditions make it so that items are not entered twice
          if (sanitizedData.length != this.deliveryDetails.length) {
            this.deliveryDetails.push({
              risid: e.risid,
              cl2comb: e.cl2comb,
              cl2desc: e.cl2desc,
              brand: null,
              brandName: null,
              supplier: null,
              supplierName: null,
              fsid: e.fundSourceId,
              fundSourceName: e.fundSourceName,
              uomcode: e.uomcode,
              uomdesc: e.uomdesc,
              unitprice: e.unitprice,
              markupPercentage: null,
              sellingPrice: null,
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
    },
    onRowClick(e) {
      //   console.log(e.data);
      this.formAdditional.ris_no = e.data.risid;
      this.formAdditional.suppcode = e.data.supplierId;
      this.formAdditional.suppname = e.data.supplierName;
      this.formAdditional.fsId = e.data.fsid;
      this.formAdditional.fsName = e.data.fundSourceName;
      this.formAdditional.cl2comb = e.data.cl2comb;
      this.formAdditional.cl2desc = e.data.cl2desc;
      this.formAdditional.uomcode = e.data.uomcode;
      this.formAdditional.uomdesc = e.data.uomdesc;
      this.formAdditional.brandId = null;
      this.formAdditional.quantity = Number(e.data.releaseqty);
      this.formAdditional.manufactured_date = e.data.manufactured_date;
      this.formAdditional.delivered_date = e.data.delivered_date;
      this.formAdditional.expiration_date = e.data.expiration_date;
      this.formAdditional.acquisitionPrice = Number(e.data.unitprice);
    },
    updateNewDetailsToDeliveryDets() {
      this.deliveryDetails.forEach((e) => {
        if (
          this.formAdditional.ris_no == e.risid &&
          this.formAdditional.cl2comb == e.cl2comb &&
          this.formAdditional.quantity == e.releaseqty
        ) {
          // only update property if none on the properties is null
          if (
            e.supplier == null ||
            e.brand == null ||
            e.markupPercentage == null ||
            e.sellingPrice == null ||
            e.expiration_date == null ||
            e.markupPercentage != this.formAdditional.markupPercentage
          ) {
            e.supplier = this.formAdditional.supplier;
            e.supplierName = this.formAdditional.supplier == null ? null : this.formAdditional.supplier.suppname;
            e.brand = this.formAdditional.brand;
            e.brandName = this.formAdditional.brand == null ? null : this.formAdditional.brand.name;
            e.markupPercentage = this.formAdditional.markupPercentage;
            e.sellingPrice = this.formAdditional.calculatedSellingPrice;
            e.manufactured_date = this.formAdditional.manufactured_date;
            e.delivered_date = this.formAdditional.delivered_date;
            e.expiration_date = this.formAdditional.expiration_date;
            e.sellingPrice = this.formAdditional.calculatedSellingPrice;
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
      if (this.form.processing) {
        return false;
      }

      this.formAdditional.deliveryDetails = this.deliveryDetails;

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
        this.formAdditional.post(route('csrstocks.store'), {
          preserveScroll: true,
          onSuccess: () => {
            this.stockId = null;
            this.createStockDialog = false;
            this.cancel();
            this.updateData();
            this.createdMsg();
          },
        });
      }
    },
    confirmDeleteItem(item) {
      this.stockId = item.id;
      this.form.ris_no = item.ris_no;
      this.form.cl2desc = item.cl2desc;
      this.deleteStockDialog = true;
    },
    deleteItem() {
      this.form.delete(route('csrstocks.destroy', this.stockId), {
        preserveScroll: true,
        onSuccess: () => {
          this.stocksList = [];
          this.deleteStockDialog = false;
          this.stockId = null;
          this.form.clearErrors();
          this.form.reset();
          this.updateData();
          this.deletedMsg();
          this.storeStocksInContainer();
        },
      });
    },
    cancel() {
      this.stockId = null;
      this.isUpdate = false;
      this.isUpdateBrand = false;
      this.createStockDialog = false;
      this.createBrandDialog = false;
      this.disableSearchRisInput = false;
      this.form.reset();
      this.form.clearErrors();
      this.formAdditional.reset();
      this.formAdditional.clearErrors();
      this.formBrand.reset();
      this.formBrand.clearErrors();
      this.stocksList = [];
      this.storeStocksInContainer();
    },
    createdMsg() {
      this.$toast.add({ severity: 'success', summary: 'Success', detail: 'Delivery created', life: 3000 });
    },
    updatedMsg() {
      this.$toast.add({ severity: 'warn', summary: 'Success', detail: 'Delivery updated', life: 3000 });
    },
    updateRisNo() {
      this.$toast.add({ severity: 'warn', summary: 'Success', detail: 'RIS NO. updated', life: 3000 });
    },
    deletedMsg() {
      this.$toast.add({ severity: 'error', summary: 'Success', detail: 'Delivery deleted', life: 3000 });
    },
    // brand
    storeBrandsInContainer() {
      this.brands.forEach((e) => {
        this.brandsList.push({
          id: e.id,
          name: e.name,
          status: e.status,
        });
      });
    },
    // filtered list for dropdown, only show brand that is active
    storeActiveBrandsInContainer() {
      this.brandDropDownList = this.brands.filter((item) => item.status === 'A');
    },
    openCreateBrandDialog() {
      this.isUpdateBrand = false;
      this.formBrand.clearErrors();
      this.formBrand.reset();
      this.createBrandDialog = true;
    },
    editBrand(brand) {
      this.isUpdateBrand = true;
      this.createBrandDialog = true;
      this.formBrand.id = brand.id;
      this.formBrand.name = brand.name;
      this.formBrand.status = brand.status;
    },
    submitBrand() {
      if (this.formBrand.processing) {
        return false;
      }

      if (this.isUpdateBrand) {
        this.formBrand.put(route('brands.update', this.formBrand.id), {
          preserveScroll: true,
          onSuccess: () => {
            this.createBrandDialog = false;
            this.cancel();
            this.updateData();
            this.updatedBrandMessage();
          },
        });
      } else {
        this.formBrand.post(route('brands.store'), {
          preserveScroll: true,
          onSuccess: () => {
            this.createBrandDialog = false;
            this.cancel();
            this.updateData();
            this.createdBrandMessage();
          },
        });
      }
    },
    createdBrandMessage() {
      this.$toast.add({ severity: 'success', summary: 'Success', detail: 'Brand created', life: 3000 });
    },
    updatedBrandMessage() {
      this.$toast.add({ severity: 'warn', summary: 'Success', detail: 'Brand updated', life: 3000 });
    },
    deleteBrandMessage() {
      this.$toast.add({ severity: 'error', summary: 'Success', detail: 'Brand deleted', life: 3000 });
    },
    // end brand
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

.p-datatable .p-datatable-tbody > tr:hover {
  cursor: pointer;
}
</style>
