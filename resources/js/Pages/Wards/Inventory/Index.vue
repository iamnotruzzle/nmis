<template>
  <app-layout>
    <Head title="NMIS - Stocks" />

    <div>
      <!-- <div
        class="card"
        style="border-top-left-radius: 0; border-top-right-radius: 0"
      >
        <Toast />
      </div> -->

      <div class="card">
        <Toast />
        <!-- current ward stocks -->
        <div class="flex align-items-center mb-4">
          <span class="text-xl text-900 font-bold text-primary">CURRENT STOCKS</span>
          <Button
            label="ADJUSTMENT HISTORY"
            icon="pi pi-calendar"
            severity="info"
            @click="openWardStockHistory"
            class="ml-2"
          />
        </div>

        <DataTable
          class="p-datatable-sm"
          dataKey="id"
          v-model:filters="currentWardStocksFilter"
          :value="currentWardStocksList"
          paginator
          :rows="20"
          :rowsPerPageOptions="[20, 30, 50]"
          removableSort
          sortField="expiration_date"
          :sortOrder="1"
          showGridlines
          :loading="isCurrentWardStocksLoading"
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
                <!-- :disabled="canTransact == false" -->
                <Button
                  v-if="['CATHL', 'CENDU', 'HEMO', 'BUCAS', '3MHLK'].includes(authWardcode)"
                  label="EXISTING STOCK"
                  icon="pi pi-plus"
                  iconPos="right"
                  severity="info"
                  @click="openExistingDialog"
                />
                <div class="mr-2"></div>
                <Button
                  v-if="['CATHL', 'CENDU', 'HEMO'].includes(authWardcode)"
                  label="DELIVERY"
                  icon="pi pi-plus"
                  iconPos="right"
                  severity="info"
                  @click="openDeliveryDialog"
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
                <Button
                  label="SUPPLEMENTAL"
                  icon="pi pi-plus"
                  iconPos="right"
                  severity="contrast"
                  @click="openSupplementalDialog"
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
                v-if="data.from == 'DELIVERY'"
                value="DELIVERY"
                severity="primary"
              />
              <Tag
                v-if="data.from == 'WARD'"
                value="WARD"
                severity="secondary"
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
                v-if="data.from == 'SUPPLEMENTAL'"
                value="SUPPLEMENTAL"
                severity="contrast"
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
          <!-- <Column
            field="unit"
            header="UNIT"
            style="text-align: right; width: 5%"
            :pt="{ headerContent: 'justify-content-end' }"
            sortable
          >
          </Column> -->
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
                <!-- :disabled="canTransact == false" -->
                <Button
                  v-if="slotProps.data.from == 'CSR'"
                  label="RETURN"
                  severity="primary"
                  @click="returnToCsr(slotProps.data)"
                />
                <!-- :disabled="canTransact == false" -->
                <Button
                  v-if="slotProps.data.from == 'CONSIGNMENT'"
                  label="UPDATE"
                  severity="info"
                  @click="openUpdateConsignment(slotProps.data)"
                />
                <Button
                  v-if="slotProps.data.from == 'SUPPLEMENTAL'"
                  label="UPDATE"
                  severity="info"
                  @click="openUpdateSupplemental(slotProps.data)"
                />
                <Button
                  v-if="slotProps.data.from == 'DELIVERY'"
                  label="UPDATE"
                  severity="info"
                  @click="openUpdateDelivery(slotProps.data)"
                />
                <Button
                  v-if="slotProps.data.from == 'EXISTING_STOCKS'"
                  label="UPDATE"
                  severity="info"
                  @click="openUpdateStock(slotProps.data)"
                />
                <Button
                  label="ADJUST"
                  severity="warning"
                  class="ml-2"
                  @click="openStockAdjustment(slotProps.data)"
                />
              </div>
            </template>
          </Column>
        </DataTable>
        <!-- test -->
      </div>

      <!-- Delivery -->
      <Dialog
        v-model:visible="deliveryDialog"
        :modal="true"
        :closeOnEscape="false"
        :closable="false"
        class="p-fluid w-4"
        @hide="whenDialogIsHidden"
      >
        <template #header>
          <div class="text-blue-500 text-xl font-bold">DELIVERY</div>
        </template>
        <div class="bg-orange-700 text-white p-4 rounded font-semibold my-2 text-3xl">
          Please double-check the expiration date. It cannot be updated once saved.
        </div>
        <div
          v-if="isUpdateDelivery != true"
          class="field"
        >
          <label for="fundSource">Fund source</label>
          <Dropdown
            id="fundSource"
            required="true"
            v-model="formDelivery.fund_source"
            :options="fundSourceList"
            filter
            showClear
            dataKey="chrgcode"
            optionLabel="chrgdesc"
            optionValue="chrgcode"
            class="w-full"
            :class="{ 'p-invalid': formDelivery.fund_source == '' }"
          />
          <small
            class="text-error"
            v-if="formDelivery.errors.fund_source"
          >
            {{ formDelivery.errors.fund_source }}
          </small>
        </div>
        <div class="field">
          <label>Items</label>
          <Dropdown
            required="true"
            v-model="formDelivery.cl2comb"
            :options="itemsList"
            :virtualScrollerOptions="{ itemSize: 60 }"
            filter
            dataKey="cl2comb"
            optionValue="cl2comb"
            optionLabel="cl2desc"
            class="w-full custom-dropdown-height"
            :disabled="isUpdateDelivery == true"
          >
            <template #option="{ option }">
              <div class="flex flex-column">
                <span
                  class="font-semibold text-base"
                  style="white-space: normal; word-wrap: break-word"
                >
                  {{ option.cl2desc }}
                </span>
              </div>
            </template>

            <template #value="{ value, placeholder }">
              <span
                v-if="value"
                style="white-space: normal; word-wrap: break-word"
              >
                {{ itemsList.find((item) => item.cl2comb === value)?.cl2desc || value }}
              </span>
              <span
                v-else
                class="text-gray-400"
              >
                {{ placeholder }}
              </span>
            </template>
          </Dropdown>
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
            v-model.trim="formDelivery.quantity"
            required="true"
            autofocus
            :class="{ 'p-invalid': formDelivery.quantity == '' || formDelivery.quantity == null }"
            onkeypress="return event.charCode >= 48 && event.charCode <= 57"
            inputId="integeronly"
          />
          <small
            class="text-error"
            v-if="formDelivery.errors.quantity"
          >
            {{ formDelivery.errors.quantity }}
          </small>
        </div>
        <div
          v-if="isUpdateDelivery != true"
          class="field"
        >
          <label>Price per unit</label>
          <InputNumber
            id="price_per_unit"
            inputId="minmaxfraction"
            :minFractionDigits="2"
            :maxFractionDigits="5"
            v-model.trim="formDelivery.price_per_unit"
            required="true"
            :class="{ 'p-invalid': formDelivery.price_per_unit == '' || formDelivery.price_per_unit == null }"
          />
          <small
            class="text-error"
            v-if="formDelivery.errors.price_per_unit"
          >
            {{ formDelivery.errors.price_per_unit }}
          </small>
        </div>
        <div
          v-if="isUpdateDelivery != true"
          class="field"
        >
          <label for="delivered_date">Delivered date</label>
          <Calendar
            v-model="formDelivery.delivered_date"
            dateFormat="mm-dd-yy"
            showIcon
            showButtonBar
            :manualInput="false"
            :hideOnDateTimeSelect="true"
          />
          <small
            class="text-error"
            v-if="formDelivery.errors.delivered_date"
          >
            {{ formDelivery.errors.delivered_date }}
          </small>
        </div>
        <div class="field flex flex-column">
          <div>
            <label>Expiration date</label>
          </div>
          <input
            type="date"
            v-model="formDelivery.expiration_date"
            class="text-4xl"
            style="border-radius: 10px; padding: 5px"
          />
        </div>

        <template #footer>
          <Button
            :label="!formDelivery.processing ? 'CANCEL' : 'CANCEL'"
            icon="pi pi-times"
            :disabled="formDelivery.processing"
            severity="danger"
            @click="cancel"
          />

          <Button
            v-if="isUpdateDelivery == false"
            :disabled="
              formDelivery.processing ||
              formDelivery.fund_source == null ||
              formDelivery.cl2comb == null ||
              formDelivery.quantity == null ||
              formDelivery.quantity <= 0 ||
              formDelivery.price_per_unit == null ||
              formDelivery.price_per_unit <= 0 ||
              formDelivery.delivered_date == null
            "
            :label="!formDelivery.processing ? 'SAVE' : 'SAVE'"
            :icon="formDelivery.processing ? 'pi pi-spin pi-spinner' : 'pi pi-check'"
            severity="warning"
            type="submit"
            @click="openConfirmDeliveryDialog"
          />

          <Button
            v-else
            :label="!formDelivery.processing ? 'SAVE' : 'SAVE'"
            icon="pi pi-check"
            severity="warning"
            type="submit"
            :disabled="formDelivery.processing || formDelivery.quantity == null"
            @click="submitDelivery"
          />
        </template>
      </Dialog>

      <!-- confirm delivery -->
      <Dialog
        v-model:visible="confirmDeliveryDialog"
        :modal="true"
        :closeOnEscape="false"
        :closable="false"
        class="p-fluid w-4"
        persist
      >
        <template #header>
          <div class="text-error uppercase text-xl font-bold">Are you sure you have entered the correct data?</div>
        </template>

        <div class="text-xl text-justify">
          <p>
            Please double-check the data you have entered, especially the fund source and the item details, as these
            cannot be reversed.
          </p>

          <p>
            Imagine saving an item with its price, only to realize later that it was incorrect after someone has already
            charged it. Since this data cannot be undone, any mistakes will directly impact the patient’s statement of
            account and charge logs.
          </p>

          <p>
            If you are unsure, <b>do not proceed</b>. Every time an item is added, your user account will be recorded in
            the logs.
          </p>
        </div>

        <template #footer>
          <Button
            :label="!formDelivery.processing ? 'CANCEL' : 'CANCEL'"
            icon="pi pi-times"
            :disabled="formDelivery.processing"
            severity="danger"
            @click="cancel"
          />

          <Button
            :disabled="formDelivery.processing || countdown > 0"
            :icon="formDelivery.processing ? 'pi pi-spin pi-spinner' : 'pi pi-check'"
            type="submit"
            @click="submitDelivery"
          >
            {{ countdown > 0 ? `YES, I'M SURE (${countdown})` : "YES, I'M SURE" }}
          </Button>
        </template>
      </Dialog>

      <!-- Consignment -->
      <Dialog
        v-model:visible="consignmentDialog"
        :modal="true"
        :closeOnEscape="false"
        :closable="false"
        class="p-fluid w-4"
        @hide="whenDialogIsHidden"
      >
        <template #header>
          <div class="text-orange-500 text-xl font-bold">CONSIGNMENT</div>
        </template>
        <div
          v-if="canAddExpiryDate"
          class="bg-orange-700 text-white p-4 rounded font-semibold my-2 text-3xl"
        >
          Please double-check the expiration date. It cannot be updated once saved.
        </div>
        <div
          v-if="isUpdateConsignment != true"
          class="field"
        >
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
            :virtualScrollerOptions="{ itemSize: 60 }"
            filter
            dataKey="cl2comb"
            optionValue="cl2comb"
            optionLabel="cl2desc"
            class="w-full custom-dropdown-height"
            :disabled="isUpdateConsignment == true"
          >
            <template #option="{ option }">
              <div class="flex flex-column">
                <span
                  class="font-semibold text-base"
                  style="white-space: normal; word-wrap: break-word"
                >
                  {{ option.cl2desc }}
                </span>
              </div>
            </template>

            <template #value="{ value, placeholder }">
              <span
                v-if="value"
                style="white-space: normal; word-wrap: break-word"
              >
                {{ itemsList.find((item) => item.cl2comb === value)?.cl2desc || value }}
              </span>
              <span
                v-else
                class="text-gray-400"
              >
                {{ placeholder }}
              </span>
            </template>
          </Dropdown>
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
        <div
          v-if="isUpdateConsignment != true"
          class="field"
        >
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
        <div
          v-if="isUpdateConsignment != true"
          class="field"
        >
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
        <div
          v-if="canAddExpiryDate"
          class="field flex flex-column"
        >
          <div>
            <label>Expiration date</label>
            <span class="ml-2 text-error">* MAX BY DEFAULT.</span>
          </div>
          <input
            type="date"
            v-model="formConsignment.expiration_date"
            class="text-4xl"
            style="border-radius: 10px; padding: 5px"
          />
        </div>

        <template #footer>
          <Button
            :label="!formConsignment.processing ? 'CANCEL' : 'CANCEL'"
            icon="pi pi-times"
            :disabled="formConsignment.processing"
            severity="danger"
            @click="cancel"
          />

          <Button
            v-if="isUpdateConsignment == false"
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
            :label="!formConsignment.processing ? 'SAVE' : 'SAVE'"
            :icon="formConsignment.processing ? 'pi pi-spin pi-spinner' : 'pi pi-check'"
            severity="warning"
            type="submit"
            @click="openConfirmConsignmentDialog"
          />

          <Button
            v-else
            :label="!formConsignment.processing ? 'SAVE' : 'SAVE'"
            icon="pi pi-check"
            severity="warning"
            type="submit"
            :disabled="formConsignment.processing || formConsignment.quantity == null"
            @click="submitConsignment"
          />
        </template>
      </Dialog>

      <!-- confirm consignment -->
      <Dialog
        v-model:visible="confirmConsignmentDialog"
        :modal="true"
        :closeOnEscape="false"
        :closable="false"
        class="p-fluid w-4"
        persist
      >
        <template #header>
          <div class="text-error uppercase text-xl font-bold">Are you sure you have entered the correct data?</div>
        </template>

        <div class="text-xl text-justify">
          <p>
            Please double-check the data you have entered, especially the fund source and the item details, as these
            cannot be reversed.
          </p>

          <p>
            Imagine saving an item with its price, only to realize later that it was incorrect after someone has already
            charged it. Since this data cannot be undone, any mistakes will directly impact the patient’s statement of
            account and charge logs.
          </p>

          <p>
            If you are unsure, <b>do not proceed</b>. Every time an item is added, your user account will be recorded in
            the logs.
          </p>
        </div>

        <template #footer>
          <Button
            :label="!formConsignment.processing ? 'CANCEL' : 'CANCEL'"
            icon="pi pi-times"
            :disabled="formConsignment.processing"
            severity="danger"
            @click="cancel"
          />

          <Button
            :disabled="formConsignment.processing || countdown > 0"
            :icon="formConsignment.processing ? 'pi pi-spin pi-spinner' : 'pi pi-check'"
            type="submit"
            @click="submitConsignment"
          >
            {{ countdown > 0 ? `YES, I'M SURE (${countdown})` : "YES, I'M SURE" }}
          </Button>
        </template>
      </Dialog>

      <!-- supplemental -->
      <Dialog
        v-model:visible="supplementalDialog"
        :modal="true"
        :closeOnEscape="false"
        :closable="false"
        class="p-fluid w-4"
        @hide="whenDialogIsHidden"
      >
        <template #header>
          <div class="text-whitetext-xl font-bold">SUPPLEMENTAL</div>
        </template>
        <div
          v-if="canAddExpiryDate"
          class="bg-orange-700 text-white p-4 rounded font-semibold my-2 text-3xl"
        >
          Please double-check the expiration date. It cannot be updated once saved.
        </div>
        <div
          v-if="isUpdateSupplemental != true"
          class="field"
        >
          <label for="fundSource">Fund source</label>
          <Dropdown
            id="fundSource"
            required="true"
            v-model="formSupplemental.fund_source"
            :options="fundSourceList"
            filter
            showClear
            dataKey="chrgcode"
            optionLabel="chrgdesc"
            optionValue="chrgcode"
            class="w-full"
            :class="{ 'p-invalid': formSupplemental.fund_source == '' }"
          />
          <small
            class="text-error"
            v-if="formSupplemental.errors.fund_source"
          >
            {{ formSupplemental.errors.fund_source }}
          </small>
        </div>
        <div class="field">
          <label>Items</label>
          <Dropdown
            required="true"
            v-model="formSupplemental.cl2comb"
            :options="itemsList"
            :virtualScrollerOptions="{ itemSize: 60 }"
            filter
            dataKey="cl2comb"
            optionValue="cl2comb"
            optionLabel="cl2desc"
            class="w-full custom-dropdown-height"
            :disabled="isUpdateSupplemental == true"
          >
            <template #option="{ option }">
              <div class="flex flex-column">
                <span
                  class="font-semibold text-base"
                  style="white-space: normal; word-wrap: break-word"
                >
                  {{ option.cl2desc }}
                </span>
              </div>
            </template>

            <template #value="{ value, placeholder }">
              <span
                v-if="value"
                style="white-space: normal; word-wrap: break-word"
              >
                {{ itemsList.find((item) => item.cl2comb === value)?.cl2desc || value }}
              </span>
              <span
                v-else
                class="text-gray-400"
              >
                {{ placeholder }}
              </span>
            </template>
          </Dropdown>
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
            v-model.trim="formSupplemental.quantity"
            required="true"
            autofocus
            :class="{ 'p-invalid': formSupplemental.quantity == '' || formSupplemental.quantity == null }"
            onkeypress="return event.charCode >= 48 && event.charCode <= 57"
            inputId="integeronly"
          />
          <small
            class="text-error"
            v-if="formSupplemental.errors.quantity"
          >
            {{ formSupplemental.errors.quantity }}
          </small>
        </div>
        <div
          v-if="isUpdateSupplemental != true"
          class="field"
        >
          <label>Price per unit</label>
          <InputNumber
            id="price_per_unit"
            inputId="minmaxfraction"
            :minFractionDigits="2"
            :maxFractionDigits="5"
            v-model.trim="formSupplemental.price_per_unit"
            required="true"
            :class="{ 'p-invalid': formSupplemental.price_per_unit == '' || formSupplemental.price_per_unit == null }"
          />
          <small
            class="text-error"
            v-if="formSupplemental.errors.price_per_unit"
          >
            {{ formSupplemental.errors.price_per_unit }}
          </small>
        </div>
        <div
          v-if="isUpdateSupplemental != true"
          class="field"
        >
          <label for="delivered_date">Delivered date</label>
          <Calendar
            v-model="formSupplemental.delivered_date"
            dateFormat="mm-dd-yy"
            showIcon
            showButtonBar
            :manualInput="false"
            :hideOnDateTimeSelect="true"
          />
          <small
            class="text-error"
            v-if="formSupplemental.errors.delivered_date"
          >
            {{ formSupplemental.errors.delivered_date }}
          </small>
        </div>
        <div
          v-if="canAddExpiryDate"
          class="field flex flex-column"
        >
          <div>
            <label>Expiration date</label>
            <span class="ml-2 text-error">* MAX BY DEFAULT.</span>
          </div>
          <input
            type="date"
            v-model="formSupplemental.expiration_date"
            class="text-4xl"
            style="border-radius: 10px; padding: 5px"
          />
        </div>

        <template #footer>
          <Button
            :label="!formSupplemental.processing ? 'CANCEL' : 'CANCEL'"
            icon="pi pi-times"
            :disabled="formSupplemental.processing"
            severity="danger"
            @click="cancel"
          />

          <Button
            v-if="isUpdateSupplemental == false"
            :disabled="
              formSupplemental.processing ||
              formSupplemental.fund_source == null ||
              formSupplemental.cl2comb == null ||
              formSupplemental.quantity == null ||
              formSupplemental.quantity <= 0 ||
              formSupplemental.price_per_unit == null ||
              formSupplemental.price_per_unit <= 0 ||
              formSupplemental.delivered_date == null
            "
            :label="!formSupplemental.processing ? 'SAVE' : 'SAVE'"
            :icon="formSupplemental.processing ? 'pi pi-spin pi-spinner' : 'pi pi-check'"
            severity="warning"
            type="submit"
            @click="openConfirmSupplementalDialog"
          />

          <Button
            v-else
            :label="!formSupplemental.processing ? 'SAVE' : 'SAVE'"
            icon="pi pi-check"
            severity="warning"
            type="submit"
            :disabled="formSupplemental.processing || formSupplemental.quantity == null"
            @click="submitSupplemental"
          />
        </template>
      </Dialog>

      <!-- confirm supplemental -->
      <Dialog
        v-model:visible="confirmSupplementalDialog"
        :modal="true"
        :closeOnEscape="false"
        :closable="false"
        class="p-fluid w-4"
        persist
      >
        <template #header>
          <div class="text-error uppercase text-xl font-bold">Are you sure you have entered the correct data?</div>
        </template>

        <div class="text-xl text-justify">
          <p>
            Please double-check the data you have entered, especially the fund source and the item details, as these
            cannot be reversed.
          </p>

          <p>
            Imagine saving an item with its price, only to realize later that it was incorrect after someone has already
            charged it. Since this data cannot be undone, any mistakes will directly impact the patient’s statement of
            account and charge logs.
          </p>

          <p>
            If you are unsure, <b>do not proceed</b>. Every time an item is added, your user account will be recorded in
            the logs.
          </p>
        </div>

        <template #footer>
          <Button
            :label="!formSupplemental.processing ? 'CANCEL' : 'CANCEL'"
            icon="pi pi-times"
            :disabled="formSupplemental.processing"
            severity="danger"
            @click="cancel"
          />

          <Button
            :disabled="formSupplemental.processing || countdown > 0"
            :icon="formSupplemental.processing ? 'pi pi-spin pi-spinner' : 'pi pi-check'"
            type="submit"
            @click="submitSupplemental"
          >
            {{ countdown > 0 ? `YES, I'M SURE (${countdown})` : "YES, I'M SURE" }}
          </Button>
        </template>
      </Dialog>

      <!-- Existing -->
      <Dialog
        v-model:visible="existingDialog"
        :modal="true"
        :closable="false"
        class="p-fluid w-4"
        @hide="whenDialogIsHidden"
      >
        <template #header>
          <div class="text-blue-500 text-xl font-bold">EXISTING STOCK</div>
        </template>
        <div
          v-if="canAddExpiryDate"
          class="bg-orange-700 text-white p-4 rounded font-semibold my-2 text-3xl"
        >
          Please double-check the expiration date. It cannot be updated once saved.
        </div>
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
            :loading="isItemsLoading"
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
        <div class="field flex flex-column">
          <div>
            <label>Expiration date</label>
            <span class="ml-2 text-error">* MAX BY DEFAULT.</span>
          </div>
          <input
            type="date"
            v-model="formExisting.expiration_date"
            class="text-4xl"
            style="border-radius: 10px; padding: 5px"
          />
        </div>

        <template #footer>
          <Button
            :label="!formExisting.processing ? 'CANCEL' : 'CANCEL'"
            icon="pi pi-times"
            :disabled="formExisting.processing || formExisting.cl2comb == null || formExisting.quantity == null"
            severity="danger"
            @click="cancel"
          />

          <Button
            v-if="isUpdateExisting == false"
            :disabled="formExisting.processing || formExisting.cl2comb == null || formExisting.quantity == null"
            :label="!formExisting.processing ? 'SAVE' : 'SAVE'"
            :icon="formExisting.processing ? 'pi pi-spin pi-spinner' : 'pi pi-check'"
            severity="info"
            type="submit"
            @click="submitExisting"
          />

          <Button
            v-else
            :disabled="formExisting.processing || formExisting.cl2comb == null || formExisting.quantity == null"
            :label="!formExisting.processing ? 'UPDATE' : 'UPDATE'"
            :icon="formExisting.processing ? 'pi pi-spin pi-spinner' : 'pi pi-check'"
            severity="info"
            type="submit"
            @click="submitExisting"
          />
        </template>
      </Dialog>

      <!-- return to csr -->
      <Dialog
        v-model:visible="returnToCsrDialog"
        :modal="true"
        :closable="false"
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
            :label="!formReturnToCsr.processing ? 'CANCEL' : 'CANCEL'"
            icon="pi pi-times"
            :disabled="formReturnToCsr.processing"
            severity="danger"
            @click="cancel"
          />

          <Button
            :disabled="
              formReturnToCsr.processing ||
              formReturnToCsr.quantity == null ||
              formReturnToCsr.remarks == '' ||
              formReturnToCsr.remarks == null
            "
            :label="!formReturnToCsr.processing ? 'RETURN' : 'RETURN'"
            :icon="formReturnToCsr.processing ? 'pi pi-spin pi-spinner' : 'pi pi-check'"
            type="submit"
            @click="submitReturnToCsr"
          />
        </template>
      </Dialog>

      <!-- Stock Adjustment Dialog -->
      <Dialog
        v-model:visible="stockAdjustmentDialog"
        :modal="true"
        :closeOnEscape="false"
        :closable="false"
        class="p-fluid w-4"
        @hide="whenDialogIsHidden"
      >
        <template #header>
          <div class="text-orange-500 text-xl font-bold">STOCK USAGE ADJUSTMENT</div>
        </template>

        <div class="bg-red-600 text-white p-4 rounded font-semibold my-2 text-lg">
          ⚠️ WARNING: Stock usage adjustments cannot be undone. Please review the quantity used thoroughly before
          proceeding.
        </div>

        <div class="field">
          <label for="adjustItem">Item</label>
          <TextArea
            id="adjustItem"
            v-model.trim="formStockAdjustment.item_description"
            readonly
            rows="5"
            class="text-blue-600 font-bold text-xl"
          />
        </div>

        <div class="field">
          <label for="currentQty">Current Quantity</label>
          <InputText
            id="currentQty"
            v-model.trim="formStockAdjustment.current_quantity"
            readonly
            class="text-green-500 font-bold text-xl"
          />
        </div>

        <div class="field">
          <label for="quantityUsed">Quantity Used <span class="text-red-500">*</span></label>
          <InputText
            id="quantityUsed"
            v-model.trim="formStockAdjustment.quantity"
            required
            autofocus
            :class="{ 'p-invalid': formStockAdjustment.quantity == '' || formStockAdjustment.quantity == null }"
            onkeypress="return event.charCode >= 48 && event.charCode <= 57"
          />
          <small class="text-gray-500">Enter the quantity that was used/consumed</small>
          <small
            class="text-red-500 block"
            v-if="formStockAdjustment.errors.quantity"
          >
            {{ formStockAdjustment.errors.quantity }}
          </small>
        </div>

        <div
          class="field"
          v-if="formStockAdjustment.quantity"
        >
          <label for="resultingQty">Resulting Quantity</label>
          <InputText
            id="resultingQty"
            :value="calculateResultingQuantity"
            readonly
            class="bg-yellow-600 bg-opacity-40 font-bold text-xl"
            :class="{ 'text-red-600': calculateResultingQuantity < 0 }"
          />
          <small class="text-gray-500">This will be the remaining stock after usage</small>
          <small
            class="text-red-500 block"
            v-if="calculateResultingQuantity < 0"
          >
            Warning: This would result in negative stock!
          </small>
        </div>

        <div class="field">
          <label for="adjustRemarks">Reason for Usage <span class="text-red-500">*</span></label>
          <TextArea
            id="adjustRemarks"
            v-model.trim="formStockAdjustment.remarks"
            rows="4"
            required
            placeholder="Please provide a detailed reason for this stock usage."
            :class="{ 'p-invalid': formStockAdjustment.remarks == '' || formStockAdjustment.remarks == null }"
          />
          <small class="text-gray-500">Explain why this quantity was used/consumed</small>
          <small
            class="text-red-500 block"
            v-if="formStockAdjustment.errors.remarks"
          >
            {{ formStockAdjustment.errors.remarks }}
          </small>
        </div>

        <template #footer>
          <Button
            label="CANCEL"
            icon="pi pi-times"
            :disabled="formStockAdjustment.processing"
            severity="secondary"
            @click="cancel"
          />

          <Button
            :disabled="
              formStockAdjustment.processing ||
              formStockAdjustment.quantity == null ||
              formStockAdjustment.quantity == '' ||
              formStockAdjustment.remarks == null ||
              formStockAdjustment.remarks.trim() == '' ||
              calculateResultingQuantity < 0
            "
            label="PROCEED TO REVIEW"
            icon="pi pi-arrow-right"
            severity="warning"
            @click="openConfirmStockAdjustment"
          />
        </template>
      </Dialog>

      <!-- Confirmation Dialog -->
      <Dialog
        v-model:visible="confirmStockAdjustmentDialog"
        :modal="true"
        :closeOnEscape="false"
        :closable="false"
        class="p-fluid w-5"
        persist
      >
        <template #header>
          <div class="text-red-500 uppercase text-xl font-bold">⚠️ FINAL CONFIRMATION - STOCK USAGE</div>
        </template>

        <div class="text-lg">
          <div class="bg-yellow-100 border-l-4 border-yellow-500 p-4 mb-4">
            <div class="flex">
              <div class="flex-shrink-0">
                <i class="pi pi-exclamation-triangle text-yellow-500 text-2xl"></i>
              </div>
              <div class="ml-3">
                <p class="text-lg text-yellow-700 font-bold">This action cannot be reversed!</p>
                <p class="text-base text-yellow-600">
                  Once confirmed, the used quantity will be permanently deducted from stock.
                </p>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 gap-4 mb-4">
            <div class="p-4 border rounded">
              <h4 class="font-semibold mb-2">Usage Details:</h4>
              <p class="mb-2">{{ formStockAdjustment.item_description }}</p>

              <div class="flex justify-between items-center mb-2">
                <span class="font-medium mr-2">Current Stock:</span>
                <span class="text-lg font-bold text-blue-600">{{ formStockAdjustment.current_quantity }}</span>
              </div>

              <div class="flex justify-between items-center mb-2">
                <span class="font-medium mr-2">Quantity Used:</span>
                <span class="text-lg font-bold text-error">-{{ formStockAdjustment.quantity }}</span>
              </div>

              <div class="flex justify-between items-center mb-2 border-t pt-2">
                <span class="font-medium mr-2">Remaining Stock:</span>
                <span
                  class="text-lg font-bold"
                  :class="calculateResultingQuantity >= 0 ? 'text-green-600' : 'text-error'"
                >
                  {{ calculateResultingQuantity }}
                </span>
              </div>

              <div class="mt-4">
                <span class="font-medium">Reason for Usage:</span>
                <p class="italic mt-1">{{ formStockAdjustment.remarks }}</p>
              </div>
            </div>
          </div>

          <div class="text-sm space-y-2">
            <p>
              • Your user account ({{ $page.props.auth.user.userDetail.employeeid }}) will be recorded in the usage logs
            </p>
            <p>• This usage will be tagged as "{{ formStockAdjustment.tag }}"</p>
            <p>• The used quantity will be deducted from the current stock</p>
            <p>• If you are unsure about any details, please <strong>CANCEL</strong> and verify the information</p>
          </div>
        </div>

        <template #footer>
          <Button
            label="CANCEL"
            icon="pi pi-times"
            :disabled="formStockAdjustment.processing"
            severity="secondary"
            @click="cancel"
          />

          <Button
            :disabled="formStockAdjustment.processing || adjustmentCountdown > 0"
            :icon="formStockAdjustment.processing ? 'pi pi-spin pi-spinner' : 'pi pi-check'"
            severity="danger"
            @click="submitStockAdjustment"
          >
            {{
              adjustmentCountdown > 0 ? `CONFIRM ADJUSTMENT (${adjustmentCountdown})` : 'YES, PROCEED WITH ADJUSTMENT'
            }}
          </Button>
        </template>
      </Dialog>

      <!-- Stock History Dialog for Ward -->
      <Dialog
        v-model:visible="stockHistoryDialog"
        :modal="true"
        :closable="true"
        class="p-fluid w-11 md:w-10 lg:w-8"
        @hide="whenDialogIsHidden"
      >
        <template #header>
          <div class="text-primary text-xl font-bold">WARD STOCK ADJUSTMENT HISTORY</div>
        </template>

        <div v-if="stockHistoryData">
          <!-- Summary Section - Dark theme compatible -->
          <div class="surface-100 border-l-4 border-primary p-4 mb-4 border-round">
            <h3 class="text-color font-semibold text-lg mb-2">{{ authWardcode }} - Ward Summary</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
              <div class="text-center">
                <div class="text-2xl font-bold text-green-400">{{ stockHistoryData.total_current_items }}</div>
                <div class="text-sm text-color-secondary">Current Items</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-blue-400">{{ stockHistoryData.total_current_quantity }}</div>
                <div class="text-sm text-color-secondary">Total Stock Qty</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-red-400">{{ stockHistoryData.total_adjusted }}</div>
                <div class="text-sm text-color-secondary">Total Adjusted</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-purple-400">{{ stockHistoryData.total_adjustment_records }}</div>
                <div class="text-sm text-color-secondary">Total Records</div>
              </div>
            </div>
          </div>

          <!-- Filter Section - Dark theme compatible -->
          <div class="surface-50 p-4 mb-4 border-round">
            <h4 class="text-color font-semibold mb-3">Filter by Adjustment Date</h4>
            <div class="flex flex-wrap gap-4 align-items-end">
              <div
                class="flex-1 min-w-0"
                style="min-width: 200px"
              >
                <label
                  for="dateFrom"
                  class="text-color block mb-2 text-sm"
                  >From Date</label
                >
                <Calendar
                  id="dateFrom"
                  v-model="stockHistoryFilters.dateFrom"
                  dateFormat="mm/dd/yy"
                  showIcon
                  showButtonBar
                  class="w-full"
                />
              </div>
              <div
                class="flex-1 min-w-0"
                style="min-width: 200px"
              >
                <label
                  for="dateTo"
                  class="text-color block mb-2 text-sm"
                  >To Date</label
                >
                <Calendar
                  id="dateTo"
                  v-model="stockHistoryFilters.dateTo"
                  dateFormat="mm/dd/yy"
                  showIcon
                  showButtonBar
                  class="w-full"
                />
              </div>
              <div class="flex gap-3 align-items-end">
                <Button
                  label="Apply"
                  icon="pi pi-filter"
                  @click="applyStockHistoryFilter"
                  size="small"
                  class="px-3 py-2"
                  style="height: 2.5rem; min-width: 80px"
                />
                <Button
                  label="Clear"
                  icon="pi pi-times"
                  severity="secondary"
                  @click="clearStockHistoryFilter"
                  size="small"
                  class="px-3 py-2"
                  style="height: 2.5rem; min-width: 80px"
                />
              </div>
            </div>
          </div>

          <!-- Adjustments Table -->
          <div class="mb-4">
            <DataTable
              :value="stockHistoryData.adjustments"
              class="p-datatable-sm"
              :loading="isStockHistoryLoading"
              paginator
              :rows="15"
              showGridlines
              emptyMessage="No adjustments found"
              sortField="date"
              :sortOrder="-1"
            >
              <Column
                field="date"
                header="Date & Time"
                sortable
                class="w-2"
              ></Column>
              <Column
                field="item_description"
                header="Item"
                sortable
                class="w-3"
              >
                <template #body="{ data }">
                  <span class="text-sm text-color">{{ data.item_description }}</span>
                </template>
              </Column>
              <Column
                field="quantity_used"
                header="Used"
                sortable
                class="w-1 text-center"
              >
                <template #body="{ data }">
                  <span class="text-red-400 font-semibold">-{{ data.quantity_used }}</span>
                </template>
              </Column>
              <Column
                field="previous_quantity"
                header="Previous"
                sortable
                class="w-1 text-center"
              >
                <template #body="{ data }">
                  <span class="text-color-secondary">{{ data.previous_quantity }}</span>
                </template>
              </Column>
              <Column
                field="new_quantity"
                header="New"
                sortable
                class="w-1 text-center"
              >
                <template #body="{ data }">
                  <span class="text-blue-400 font-semibold">{{ data.new_quantity }}</span>
                </template>
              </Column>
              <Column
                field="tag"
                header="Tag"
                sortable
                class="w-2"
              >
                <template #body="{ data }">
                  <Tag
                    :value="data.tag"
                    severity="info"
                  />
                </template>
              </Column>
              <Column
                field="remarks"
                header="Remarks"
                class="w-2"
              >
                <template #body="{ data }">
                  <span class="text-sm text-color">{{ data.remarks || '-' }}</span>
                </template>
              </Column>
              <Column
                field="employee_id"
                header="By"
                sortable
                class="w-1 text-center"
              >
                <template #body="{ data }">
                  <span class="text-xs text-color-secondary">{{ data.employee_id }}</span>
                </template>
              </Column>
            </DataTable>
          </div>
        </div>

        <div
          v-else-if="isStockHistoryLoading"
          class="text-center p-4"
        >
          <i class="pi pi-spinner pi-spin text-2xl text-color"></i>
          <p class="mt-2 text-color">Loading ward stock history...</p>
        </div>

        <template #footer>
          <Button
            label="Print"
            icon="pi pi-print"
            severity="info"
            @click="printStockHistory"
            :disabled="!stockHistoryData || stockHistoryData.adjustments.length === 0"
          />
          <Button
            label="Close"
            icon="pi pi-times"
            severity="secondary"
            @click="stockHistoryDialog = false"
          />
        </template>
      </Dialog>
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
    canTransact: Boolean,
    canAddExpiryDate: Boolean,
  },
  data() {
    return {
      CACHE_CONFIG: {
        ITEMS: {
          key: 'WardsInv_itemsCache',
          timestamp: 'WardsInv_itemsCacheTimestamp',
        },
        CURRENT_WARD_STOCKS: {
          key: 'WardsInv_currentWardStocksCache',
          timestamp: 'WardsInv_currentWardStocksCacheTimestamp',
        },
      },
      CACHE_DURATION_MS: 1000 * 60 * 5, // 5 minutes
      // loading states
      isItemsLoading: false,
      isCurrentWardStocksLoading: false,

      // Stock adjustment dialog states
      stockAdjustmentDialog: false,
      confirmStockAdjustmentDialog: false,
      adjustmentCountdown: 0,
      adjustmentTimer: null,

      authWardcode: '',
      expandedRow: [],
      // paginator
      loading: false,
      totalRecords: null,
      rows: null,
      // end paginator,
      countdown: 0,
      isUpdate: false,
      //   medicalGasesDialog: false,
      consignmentDialog: false,
      deliveryDialog: false,
      confirmConsignmentDialog: false,
      confirmDeliveryDialog: false,
      confirmSupplementalDialog: false,
      isUpdateExisting: false,
      isUpdateDelivery: false,
      isUpdateSupplemental: false,
      existingDialog: false,
      supplementalDialog: false,
      returnToCsrDialog: false,
      editAverageOfStocksDialog: false,
      editStatusDialog: false,
      cancelItemDialog: false,
      search: '',
      selectedItemsUomDesc: null,

      stockHistoryDialog: false,
      stockHistoryData: null,
      isStockHistoryLoading: false,
      stockHistoryFilters: {
        dateFrom: null,
        dateTo: null,
      },

      options: {},
      params: {},
      from: null,
      to: null,
      stockBalanceDeclared: false,
      itemsList: [],
      currentWardStocksList: [],
      currentWardStocksFilter: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
      fundSourceList: [],
      item: null,
      cl2desc: null,
      approved_qty: null,
      itemNotSelected: false,
      itemNotSelectedMsg: null,
      // end stock list details
      filters: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
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
        expiration_date: '9999-12-30',
      }),
      formDelivery: this.$inertia.form({
        id: null,
        authLocation: null,
        fund_source: null,
        cl2comb: null,
        uomcode: null,
        quantity: null,
        price_per_unit: null,
        delivered_date: null,
        expiration_date: null,
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
        expiration_date: '9999-12-30',
      }),
      formSupplemental: this.$inertia.form({
        id: null,
        authLocation: null,
        fund_source: null,
        cl2comb: null,
        uomcode: null,
        quantity: null,
        price_per_unit: null,
        delivered_date: null,
        expiration_date: '9999-12-30',
      }),
      formReturnToCsr: this.$inertia.form({
        ward_stock_id: null,
        item: null,
        quantity: null,
        expiration_date: null,
        remarks: null,
      }),
      // Stock adjustment form
      formStockAdjustment: this.$inertia.form({
        id: null,
        cl2comb: null,
        item_description: null,
        current_quantity: null,
        quantity: null,
        remarks: null,
        tag: 'Infection Control', // default tag
        employeeid: null,
        wardcode: null,
      }),
      previousQty: 0,
      targetItemDesc: null,
    };
  },
  beforeUnmount() {
    this.clearAdjustmentTimer();
  },
  mounted() {
    this.authWardcode = this.$page.props.auth.user.location.location_name.wardcode;

    this.storeFundSourceInContainer();

    this.fetchItems();
    this.fetchCurrentWardStocks();
  },
  computed: {
    user() {
      return this.$page.props.auth.user;
    },
    calculateResultingQuantity() {
      const current = parseInt(this.formStockAdjustment.current_quantity) || 0;
      const used = parseInt(this.formStockAdjustment.quantity) || 0;
      return current - used;
    },
  },
  methods: {
    openWardStockHistory() {
      this.stockHistoryDialog = true;
      this.stockHistoryData = null;
      this.stockHistoryFilters.dateFrom = null;
      this.stockHistoryFilters.dateTo = null;

      this.fetchWardStockHistory();
    },

    async fetchWardStockHistory(dateFrom = null, dateTo = null) {
      this.isStockHistoryLoading = true;

      try {
        const params = {
          wardcode: this.authWardcode,
        };

        if (dateFrom) {
          params.date_from = this.formatDateForApi(dateFrom);
          console.log('Sending date_from:', params.date_from); // Debug log
        }
        if (dateTo) {
          params.date_to = this.formatDateForApi(dateTo);
          console.log('Sending date_to:', params.date_to); // Debug log
        }

        console.log('Fetching with params:', params); // Debug log

        const response = await axios.get('wardstockadjustment/ward-history', { params });
        this.stockHistoryData = response.data;

        console.log('Received data:', response.data); // Debug log
      } catch (error) {
        console.error('Failed to fetch ward stock history:', error);
        this.$toast.add({
          severity: 'error',
          summary: 'Error',
          detail: 'Failed to fetch ward stock history',
          life: 3000,
        });
      } finally {
        this.isStockHistoryLoading = false;
      }
    },

    async applyStockHistoryFilter() {
      console.log('Applying filter with dates:', {
        from: this.stockHistoryFilters.dateFrom,
        to: this.stockHistoryFilters.dateTo,
      }); // Debug log

      await this.fetchWardStockHistory(this.stockHistoryFilters.dateFrom, this.stockHistoryFilters.dateTo);
    },

    clearStockHistoryFilter() {
      this.stockHistoryFilters.dateFrom = null;
      this.stockHistoryFilters.dateTo = null;
      this.fetchWardStockHistory();
    },

    printStockHistory() {
      if (!this.stockHistoryData || this.stockHistoryData.adjustments.length === 0) {
        return;
      }

      this.$nextTick(() => {
        // Create a hidden iframe for printing (like your reference)
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
        iframeDoc.write(this.generatePrintHTML());
        iframeDoc.close();

        // Trigger the print dialog
        iframe.contentWindow.focus();
        iframe.contentWindow.print();

        // Remove the iframe after a delay to ensure proper cleanup
        setTimeout(() => {
          document.body.removeChild(iframe);
        }, 100);
      });
    },

    generatePrintHTML() {
      const data = this.stockHistoryData;
      const filters = this.stockHistoryFilters;
      const user = this.$page.props.auth.user;

      const tableRows = data.adjustments
        .map(
          (adjustment) => `
        <tr>
            <td>${adjustment.date}</td>
            <td>${adjustment.item_description}</td>
            <td style="font-weight: bold;">-${adjustment.quantity_used}</td>
            <td>${adjustment.previous_quantity}</td>
            <td style="font-weight: bold;">${adjustment.new_quantity}</td>
            <td>${adjustment.tag}</td>
            <td>${adjustment.remarks || '-'}</td>
            <td>${adjustment.employee_id}</td>
        </tr>
        `
        )
        .join('');

      return `
        <html>
        <head>
        <title>Ward Stock Adjustment History</title>
        <style>
            body { font-family: Arial, sans-serif; }
            table {
            width: 100%;
            border-collapse: collapse;
            font-family: Arial, sans-serif;
            }
            table, th, td {
            border: 1px solid black;
            }
            td, th {
            padding: 8px;
            text-align: center;
            font-size: 0.75rem;
            }
            p { margin: 0; padding: 0; }
        </style>
        </head>
        <body style="background-color: white; color: black;">

        <!-- Header -->
        <div>
            <h3 style="font-weight: bold; text-align: center;">WARD STOCK ADJUSTMENT HISTORY</h3>
            <h4 style="margin-top: 1rem;">MARIANO MARCOS MEMORIAL HOSPITAL AND MEDICAL CENTER</h4>
        </div>

        <!-- Ward Information Table -->
        <table>
            <tbody>
            <tr>
                <td colspan="4"><strong>Ward Code: ${data.ward_code}</strong></td>
                <td colspan="4"><strong>Date Printed: ${new Date().toLocaleDateString()}</strong></td>
            </tr>
            <tr>
                <td colspan="2"><strong>Current Items: ${data.total_current_items}</strong></td>
                <td colspan="2"><strong>Total Stock Qty: ${data.total_current_quantity}</strong></td>
                <td colspan="2"><strong>Total Adjusted: ${data.total_adjusted}</strong></td>
                <td colspan="2"><strong>Total Records: ${data.total_adjustment_records}</strong></td>
            </tr>
            ${
              filters.dateFrom || filters.dateTo
                ? `
            <tr>
                <td colspan="8">
                Filter Period: <strong><u>${
                  filters.dateFrom ? new Date(filters.dateFrom).toLocaleDateString() : 'All'
                } to ${filters.dateTo ? new Date(filters.dateTo).toLocaleDateString() : 'All'}</u></strong>
                </td>
            </tr>
            `
                : ''
            }
            </tbody>
        </table>

        <!-- Main Adjustments Table -->
        <table>
            <thead>
            <tr>
                <th>DATE & TIME</th>
                <th>ITEM</th>
                <th>USED</th>
                <th>PREVIOUS</th>
                <th>NEW</th>
                <th>TAG</th>
                <th>REMARKS</th>
                <th>BY</th>
            </tr>
            </thead>
            <tbody>
            ${tableRows}
            </tbody>
        </table>

        <!-- Footer Table -->
        <table>
            <tr>
            <td><strong>Generated by:</strong></td>
            <td colspan="3" style="padding: 20px 0;"><strong>${user.userDetail.employeeid}</strong></td>
            <td><strong>Date:</strong></td>
            <td colspan="3" style="padding: 20px 0;">${new Date().toLocaleDateString()}</td>
            </tr>
        </table>

        </body>
        </html>
    `;
    },

    formatDateForApi(date) {
      if (!date) return null;

      // Handle different date input types
      let dateObj;

      if (typeof date === 'string') {
        if (date.match(/^\d{4}-\d{2}-\d{2}$/)) {
          return date; // Already in correct format
        }
        dateObj = new Date(date);
      } else if (date instanceof Date) {
        dateObj = date;
      } else if (moment.isMoment(date)) {
        dateObj = date.toDate();
      } else {
        console.warn('Unknown date format:', date);
        return null;
      }

      // Ensure we have a valid date
      if (isNaN(dateObj.getTime())) {
        console.error('Invalid date:', date);
        return null;
      }

      // Format as YYYY-MM-DD (local date, not UTC)
      const year = dateObj.getFullYear();
      const month = String(dateObj.getMonth() + 1).padStart(2, '0');
      const day = String(dateObj.getDate()).padStart(2, '0');

      const formatted = `${year}-${month}-${day}`;
      console.log('Formatted date:', date, '->', formatted); // Debug log

      return formatted;
    },

    formatCurrency(value) {
      return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
      }).format(value || 0);
    },

    getCachedData(cacheType) {
      const config = this.CACHE_CONFIG[cacheType];
      const cached = localStorage.getItem(config.key);
      const timestamp = localStorage.getItem(config.timestamp);

      if (!cached || !timestamp) return null;

      const age = Date.now() - parseInt(timestamp);
      if (age > this.CACHE_DURATION_MS) {
        console.log(`⚠️ Cache expired for ${cacheType}`);
        this.clearCacheData(cacheType);
        return null;
      }

      return JSON.parse(cached);
    },
    setCachedData(cacheType, data) {
      const config = this.CACHE_CONFIG[cacheType];
      localStorage.setItem(config.key, JSON.stringify(data));
      localStorage.setItem(config.timestamp, Date.now().toString());
    },
    clearCacheData(cacheType) {
      const config = this.CACHE_CONFIG[cacheType];
      localStorage.removeItem(config.key);
      localStorage.removeItem(config.timestamp);
    },
    clearAllCaches() {
      Object.keys(this.CACHE_CONFIG).forEach((cacheType) => {
        this.clearCacheData(cacheType);
      });
    },
    async fetchItems(forceRefresh = false) {
      this.isItemsLoading = true;
      this.error = null;

      const cached = this.getCachedData('ITEMS');

      if (cached && !forceRefresh) {
        // console.log('🟢 Using cached ward stocks from localStorage');
        this.itemsList = cached;
        this.isItemsLoading = false;
        return;
      }

      try {
        const response = await axios.get('wardinv/getItems');

        response.data.forEach((e) => {
          this.itemsList.push({
            cl2comb: e.cl2comb,
            cl2desc: e.cl2desc,
            uomcode: e.uomcode,
            uomdesc: e.uomdesc,
          });
        });

        this.setCachedData('ITEMS', this.itemsList);
        // console.log('🔵 Fetched fresh ward stocks and cached to localStorage');
      } catch (err) {
        this.error = err.response?.data ?? err.message;
        console.error('❌ Failed to fetch items:', this.error);
      } finally {
        this.isItemsLoading = false;
      }
    },
    async fetchCurrentWardStocks(forceRefresh = false) {
      this.isCurrentWardStocksLoading = true;
      this.error = null;

      const cached = this.getCachedData('CURRENT_WARD_STOCKS');

      if (cached && !forceRefresh) {
        // console.log('🟢 Using cached ward stocks from localStorage');
        this.currentWardStocksList = cached;
        this.isCurrentWardStocksLoading = false;
        return;
      }

      try {
        const response = await axios.get('wardinv/getCurrentWardStocks');
        this.currentWardStocksList = [];
        moment.suppressDeprecationWarnings = true;

        response.data.forEach((e) => {
          let expiration_date = moment.tz(e.expiration_date, 'Asia/Manila').format('MM/DD/YYYY');

          this.currentWardStocksList.push({
            from: e.from,
            ward_stock_id: e.id,
            cl2comb: e.cl2comb,
            item: e.cl2desc,
            //   unit: e == null ? null : e.uomdesc,
            quantity: e.quantity,
            average: e.average,
            is_consumable: e.is_consumable == null ? null : e.is_consumable,
            expiration_date: expiration_date.toString(),
          });
        });

        this.setCachedData('CURRENT_WARD_STOCKS', this.currentWardStocksList);
        // console.log('🔵 Fetched fresh ward stocks and cached to localStorage');
      } catch (err) {
        this.error = err.response?.data ?? err.message;
        console.error('❌ Failed to fetch current ward stocks:', this.error);
      } finally {
        this.isCurrentWardStocksLoading = false;
      }
    },
    async invalidateAndRefreshCurrentWardStocks() {
      this.clearCacheData('CURRENT_WARD_STOCKS');
      await this.fetchCurrentWardStocks(true);
    },
    // Method to refresh specific data after POST operations
    async refreshDataAfterPost() {
      console.log('🔄 Refreshing current ward stocks after POST');

      // Clear localStorage cache for the three specific datasets
      this.clearCacheData('CURRENT_WARD_STOCKS');
      //   this.clearCacheData('TRANSFERRED_STOCKS');

      // Fetch fresh data and cache in localStorage
      await Promise.all([this.fetchCurrentWardStocks(true)]);
    },

    openUpdateStock(data) {
      console.log(data);
      if (data.from == 'EXISTING_STOCKS') {
        this.formExisting.id = data.ward_stock_id;
        this.formExisting.cl2comb = data.cl2comb;
        this.formExisting.quantity = data.quantity;
        this.formExisting.expiration_date = data.expiration_date;

        this.isUpdateExisting = true;
        this.existingDialog = true;
      }
    },
    openUpdateConsignment(data) {
      if (data.from == 'CONSIGNMENT') {
        this.formConsignment.id = data.ward_stock_id;
        this.formConsignment.cl2comb = data.cl2comb;
        this.formConsignment.quantity = data.quantity;
        this.formConsignment.expiration_date = data.expiration_date;

        this.isUpdateConsignment = true;
        this.consignmentDialog = true;
      }
    },
    openUpdateDelivery(data) {
      if (data.from == 'DELIVERY') {
        this.formDelivery.id = data.ward_stock_id;
        this.formDelivery.cl2comb = data.cl2comb;
        this.formDelivery.quantity = data.quantity;
        this.formDelivery.expiration_date = data.expiration_date;

        this.isUpdateDelivery = true;
        this.deliveryDialog = true;
      }
    },
    openUpdateSupplemental(data) {
      if (data.from == 'SUPPLEMENTAL') {
        this.formSupplemental.id = data.ward_stock_id;
        this.formSupplemental.cl2comb = data.cl2comb;
        this.formSupplemental.quantity = data.quantity;
        this.formSupplemental.expiration_date = data.expiration_date;

        this.isUpdateSupplemental = true;
        this.supplementalDialog = true;
      }
    },
    openConfirmConsignmentDialog() {
      this.confirmConsignmentDialog = true;
    },
    openConfirmDeliveryDialog() {
      this.confirmDeliveryDialog = true;
    },
    openConfirmSupplementalDialog() {
      this.confirmSupplementalDialog = true;
    },
    openStockAdjustment(data) {
      this.formStockAdjustment.clearErrors();
      this.formStockAdjustment.reset();

      // Set form data
      this.formStockAdjustment.id = data.ward_stock_id;
      this.formStockAdjustment.cl2comb = data.cl2comb;
      this.formStockAdjustment.item_description = data.item;
      this.formStockAdjustment.current_quantity = data.quantity;
      this.formStockAdjustment.quantity = null; // User enters quantity used
      this.formStockAdjustment.wardcode = this.authWardcode; // User location wardcode
      this.formStockAdjustment.employeeid = this.$page.props.auth.user.userDetail.employeeid;

      this.stockAdjustmentDialog = true;
    },
    openConfirmStockAdjustment() {
      if (this.validateStockAdjustment()) {
        this.confirmStockAdjustmentDialog = true;
      }
    },
    validateStockAdjustment() {
      let isValid = true;

      // Clear previous errors
      this.formStockAdjustment.clearErrors();

      // Validate quantity used
      const quantityUsed = parseInt(this.formStockAdjustment.quantity) || 0;
      const currentQty = parseInt(this.formStockAdjustment.current_quantity) || 0;

      if (!this.formStockAdjustment.quantity || quantityUsed <= 0) {
        this.formStockAdjustment.setError('quantity', 'Quantity used must be greater than 0');
        isValid = false;
      }

      if (quantityUsed > currentQty) {
        this.formStockAdjustment.setError('quantity', 'Quantity used cannot exceed current stock');
        isValid = false;
      }

      // Validate remarks
      if (!this.formStockAdjustment.remarks || this.formStockAdjustment.remarks.trim() === '') {
        this.formStockAdjustment.setError('remarks', 'Reason for usage is required');
        isValid = false;
      }

      return isValid;
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
    clearAdjustmentTimer() {
      if (this.adjustmentTimer) {
        clearInterval(this.adjustmentTimer);
        this.adjustmentTimer = null;
      }
      this.adjustmentCountdown = 0;
    },
    updateData() {
      this.$inertia.get('wardinv', this.params, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: (visit) => {
          this.cancel();
          this.storeItemsInController();
          this.storeCurrentWardStocksInContainer();
        },
      });
    },
    openConsignmentDialog() {
      this.formConsignment.clearErrors();
      this.formConsignment.reset();
      this.consignmentDialog = true;
    },
    openDeliveryDialog() {
      this.formDelivery.clearErrors();
      this.formDelivery.reset();
      this.deliveryDialog = true;
    },
    openExistingDialog() {
      this.formExisting.clearErrors();
      this.formExisting.reset();
      this.existingDialog = true;
    },
    openSupplementalDialog() {
      this.formSupplemental.clearErrors();
      this.formSupplemental.reset();
      this.supplementalDialog = true;
    },
    // when dialog is hidden, do this function
    whenDialogIsHidden() {
      this.$emit(
        'hide',
        (this.isUpdate = false),
        (this.stockHistoryDialog = false),
        (this.stockHistoryData = null),
        (this.isUpdateExisting = false),
        (this.isUpdateSupplemental = false),
        (this.isUpdateConsignment = false),
        (this.confirmConsignmentDialog = false),
        (this.isUpdateDelivery = false),
        (this.confirmDeliveryDialog = false),
        (this.confirmSupplementalDialog = false),
        (this.item = null),
        (this.cl2desc = null),
        (this.approved_qty = null),
        (this.itemNotSelected = null),
        (this.itemNotSelectedMsg = null),
        (this.targetItemDesc = null),
        (this.selectedItemsUomDesc = ''),
        (this.oldQuantity = 0),
        (this.stockAdjustmentDialog = false),
        (this.confirmStockAdjustmentDialog = false),
        this.clearAdjustmentTimer(),
        this.formStockAdjustment.clearErrors(),
        this.formStockAdjustment.reset(),
        this.formMedicalGases.clearErrors(),
        this.formMedicalGases.reset(),
        this.formConsignment.reset(),
        this.formConsignment.clearErrors(),
        this.formDelivery.reset(),
        this.formDelivery.clearErrors(),
        this.formReturnToCsr.clearErrors(),
        this.formReturnToCsr.reset(),
        this.formExisting.clearErrors(),
        this.formExisting.reset(),
        this.formSupplemental.clearErrors(),
        this.formSupplemental.reset()
      );
    },
    submitExisting() {
      if (this.formExisting.processing || this.formExisting.cl2comb == null || this.formExisting.quantity == null) {
        return false;
      }

      this.formExisting.authLocation = this.authWardcode;
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
            onSuccess: (e) => {
              //   console.log(this.$page.props);
              if (e.props.flash.noItemPrice == 0) {
                // this.cancel();
                this.noItemPriceMsg();
              } else {
                this.formExisting.reset();
                this.cancel();
                this.createdMsg();

                // Refresh only the data that changes after POST
                this.refreshDataAfterPost();
              }
            },
          });
        } else {
          this.formExisting.put(route('existingstock.update', this.formExisting.id), {
            preserveScroll: true,
            onSuccess: () => {
              this.formExisting.reset();
              this.cancel();
              this.updateExistingMessage();

              // Refresh only the data that changes after POST
              this.refreshDataAfterPost();
            },
          });
        }
      }
    },
    submitConsignment() {
      this.formConsignment.authLocation = this.authWardcode;
      if (this.isUpdateConsignment != true) {
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
          this.formConsignment.post(route('consignment.store'), {
            preserveScroll: true,
            onSuccess: () => {
              this.formConsignment.reset();
              this.cancel();
              this.createdMsg();

              // Refresh only the data that changes after POST
              this.refreshDataAfterPost();
            },
          });
        }
      } else {
        if (this.formConsignment.processing || this.formConsignment.quantity == null) {
          return false;
        }

        this.formConsignment.put(route('consignment.update', this.formConsignment.id), {
          preserveScroll: true,
          onSuccess: () => {
            +this.formConsignment.reset();
            this.cancel();
            this.updateConsignmentMessage();

            // Refresh only the data that changes after POST
            this.refreshDataAfterPost();
          },
        });
      }
    },
    submitDelivery() {
      this.formDelivery.authLocation = this.authWardcode;
      if (this.isUpdateDelivery != true) {
        if (
          this.formDelivery.processing ||
          this.formDelivery.fund_source == null ||
          this.formDelivery.cl2comb == null ||
          this.formDelivery.quantity == null ||
          this.formDelivery.price_per_unit == null ||
          this.formDelivery.price_per_unit <= 0 ||
          this.formDelivery.delivered_date == null
        ) {
          return false;
        }

        if (
          this.formDelivery.fund_source != null ||
          this.formDelivery.fund_source != '' ||
          this.formDelivery.cl2comb != null ||
          this.formDelivery.cl2comb != '' ||
          this.formDelivery.quantity != null ||
          this.formDelivery.quantity != '' ||
          this.formDelivery.quantity != 0 ||
          this.formDelivery.price_per_unit != '' ||
          this.formDelivery.price_per_unit != 0 ||
          this.formDelivery.delivered_date != null ||
          this.formDelivery.delivered_date != ''
        ) {
          this.formDelivery.post(route('delivery.store'), {
            preserveScroll: true,
            onSuccess: () => {
              this.formDelivery.reset();
              this.cancel();
              this.createdMsg();

              // Refresh only the data that changes after POST
              this.refreshDataAfterPost();
            },
          });
        }
      } else {
        if (this.formDelivery.processing || this.formDelivery.quantity == null) {
          return false;
        }

        this.formDelivery.put(route('delivery.update', this.formDelivery.id), {
          preserveScroll: true,
          onSuccess: () => {
            +this.formDelivery.reset();
            this.cancel();
            this.updateDeliveryMessage();

            // Refresh only the data that changes after POST
            this.refreshDataAfterPost();
          },
        });
      }
    },
    submitSupplemental() {
      this.formSupplemental.authLocation = this.authWardcode;
      if (this.isUpdateSupplemental != true) {
        if (
          this.formSupplemental.processing ||
          this.formSupplemental.fund_source == null ||
          this.formSupplemental.cl2comb == null ||
          this.formSupplemental.quantity == null ||
          this.formSupplemental.price_per_unit == null ||
          this.formSupplemental.price_per_unit <= 0 ||
          this.formSupplemental.delivered_date == null
        ) {
          return false;
        }

        if (
          this.formSupplemental.fund_source != null ||
          this.formSupplemental.fund_source != '' ||
          this.formSupplemental.cl2comb != null ||
          this.formSupplemental.cl2comb != '' ||
          this.formSupplemental.quantity != null ||
          this.formSupplemental.quantity != '' ||
          this.formSupplemental.quantity != 0 ||
          this.formSupplemental.price_per_unit != '' ||
          this.formSupplemental.price_per_unit != 0 ||
          this.formSupplemental.delivered_date != null ||
          this.formSupplemental.delivered_date != ''
        ) {
          this.formSupplemental.post(route('supplemental.store'), {
            preserveScroll: true,
            onSuccess: () => {
              this.formSupplemental.reset();
              this.cancel();
              this.createdMsg();

              // Refresh only the data that changes after POST
              this.refreshDataAfterPost();
            },
          });
        }
      } else {
        if (this.formSupplemental.processing || this.formSupplemental.quantity == null) {
          return false;
        }

        this.formSupplemental.put(route('supplemental.update', this.formSupplemental.id), {
          preserveScroll: true,
          onSuccess: () => {
            +this.formSupplemental.reset();
            this.cancel();
            this.updateSupplementalMessage();

            // Refresh only the data that changes after POST
            this.refreshDataAfterPost();
          },
        });
      }
    },
    submitStockAdjustment() {
      if (this.formStockAdjustment.processing) {
        return false;
      }

      if (!this.validateStockAdjustment()) {
        this.confirmStockAdjustmentDialog = false;
        return false;
      }

      this.formStockAdjustment.post(route('wardstockadjustment.store'), {
        preserveScroll: true,
        onSuccess: () => {
          this.formStockAdjustment.reset();
          this.cancel();
          this.stockAdjustmentSuccessMsg();

          // Refresh the current ward stocks data
          this.refreshDataAfterPost();
        },
        onError: (errors) => {
          console.error('Stock adjustment error:', errors);
          this.stockAdjustmentErrorMsg();
          this.confirmStockAdjustmentDialog = false;
        },
      });
    },
    cancel() {
      this.confirmConsignmentDialog = false;
      this.confirmDeliveryDialog = false;
      this.confirmSupplementalDialog = false;
      this.isUpdate = false;
      this.isUpdateExisting = false;
      this.isUpdateConsignment = false;
      this.isUpdateDelivery = false;
      this.isUpdateSupplemental = false;
      this.returnToCsrDialog = false;
      this.editAverageOfStocksDialog = false;
      this.consignmentDialog = false;
      this.deliveryDialog = false;
      this.supplementalDialog = false;
      this.existingDialog = false;
      this.editStatusDialog = false;
      this.targetItemDesc = null;
      this.oldQuantity = 0;
      this.selectedItemsUomDesc = '';
      this.stockAdjustmentDialog = false;
      this.confirmStockAdjustmentDialog = false;
      this.clearAdjustmentTimer();
      this.formStockAdjustment.reset();
      this.formStockAdjustment.clearErrors();
      this.formMedicalGases.reset();
      this.formMedicalGases.clearErrors();
      this.formConsignment.reset();
      this.formConsignment.clearErrors();
      this.formDelivery.reset();
      this.formDelivery.clearErrors();
      this.formSupplemental.reset();
      this.formSupplemental.clearErrors();
      this.formExisting.reset();
      this.formExisting.clearErrors();
      this.formReturnToCsr.reset();
      this.formReturnToCsr.clearErrors();
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
    createdMsg() {
      this.$toast.add({ severity: 'success', summary: 'Success', detail: 'Stock created', life: 5000 });
    },
    updateExistingMessage() {
      this.$toast.add({ severity: 'warn', summary: 'Success', detail: 'Stock updated', life: 5000 });
    },
    updateConsignmentMessage() {
      this.$toast.add({ severity: 'warn', summary: 'Success', detail: 'Stock updated', life: 5000 });
    },
    updateDeliveryMessage() {
      this.$toast.add({ severity: 'warn', summary: 'Success', detail: 'Stock updated', life: 5000 });
    },
    updateSupplementalMessage() {
      this.$toast.add({ severity: 'warn', summary: 'Success', detail: 'Stock updated', life: 5000 });
    },
    stockAdjustmentSuccessMsg() {
      this.$toast.add({
        severity: 'success',
        summary: 'Stock Usage Recorded',
        detail: 'Stock usage has been successfully recorded and deducted from inventory',
        life: 5000,
      });
    },
    stockAdjustmentErrorMsg() {
      this.$toast.add({
        severity: 'error',
        summary: 'Usage Recording Failed',
        detail: 'Failed to record stock usage. Please try again.',
        life: 5000,
      });
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
    },
    submitReturnToCsr() {
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
        onSuccess: () => {
          this.returnToCsrDialog = false;
          this.cancel();
          this.updatedStockMsg();

          // Refresh only the data that changes after POST
          this.refreshDataAfterPost();
        },
      });
    },
    updatedStockMsg() {
      this.$toast.add({ severity: 'warn', summary: 'Success', detail: 'Stock updated', life: 3000 });
    },
    // end ward stocks logs
  },
  watch: {
    confirmStockAdjustmentDialog(newVal) {
      if (newVal) {
        this.adjustmentCountdown = 5; // 5 second countdown
        this.adjustmentTimer = setInterval(() => {
          if (this.adjustmentCountdown > 0) {
            this.adjustmentCountdown--;
          } else {
            this.clearAdjustmentTimer();
          }
        }, 1000);
      } else {
        this.clearAdjustmentTimer();
      }
    },
    confirmConsignmentDialog(newVal) {
      if (newVal) {
        this.countdown = 5; // Reset countdown when dialog is opened
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
    confirmDeliveryDialog(newVal) {
      if (newVal) {
        this.countdown = 1; // Reset countdown when dialog is opened
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
    confirmSupplementalDialog(newVal) {
      if (newVal) {
        this.countdown = 5; // Reset countdown when dialog is opened
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
    // 'formMedicalGases.cl2comb': function (val) {
    //   this.selectedItemsUomDesc = null;

    //   this.medicalGasList.forEach((e) => {
    //     if (e.cl2comb == val) {
    //       if (e.uomdesc != null || e.uomdesc == '') {
    //         // console.log('if');
    //         this.selectedItemsUomDesc = e.uomdesc;
    //         this.formMedicalGases.uomcode = e.uomcode;
    //       } else {
    //         this.selectedItemsUomDesc = null;
    //       }
    //     }
    //   });
    // },
    'formConsignment.cl2comb': function (val) {
      this.selectedItemsUomDesc = null;

      this.itemsList.forEach((e) => {
        if (e.cl2comb == val) {
          if (e.uomdesc != null || e.uomdesc == '') {
            this.selectedItemsUomDesc = e.uomdesc;
            this.formConsignment.uomcode = e.uomcode;
          } else {
            this.selectedItemsUomDesc = null;
          }
        }
      });
    },
    'formDelivery.cl2comb': function (val) {
      this.selectedItemsUomDesc = null;

      this.itemsList.forEach((e) => {
        if (e.cl2comb == val) {
          if (e.uomdesc != null || e.uomdesc == '') {
            this.selectedItemsUomDesc = e.uomdesc;
            this.formDelivery.uomcode = e.uomcode;
          } else {
            this.selectedItemsUomDesc = null;
          }
        }
      });
    },
    'formSupplemental.cl2comb': function (val) {
      this.selectedItemsUomDesc = null;

      this.itemsList.forEach((e) => {
        if (e.cl2comb == val) {
          if (e.uomdesc != null || e.uomdesc == '') {
            this.selectedItemsUomDesc = e.uomdesc;
            this.formSupplemental.uomcode = e.uomcode;
          } else {
            this.selectedItemsUomDesc = null;
          }
        }
      });
    },
    'formExisting.cl2comb': function (val) {
      this.selectedItemsUomDesc = null;
      //   console.log(val);

      this.itemsList.forEach((e) => {
        if (e.cl2comb == val) {
          if (e.uomdesc != null || e.uomdesc == '') {
            this.selectedItemsUomDesc = e.uomdesc;
            this.formExisting.uomcode = e.uomcode;
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
@media print {
  #stock-history-print {
    display: block !important;
  }

  body * {
    visibility: hidden;
  }

  #stock-history-print,
  #stock-history-print * {
    visibility: visible;
  }

  #stock-history-print {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
  }
}

.custom-dropdown-height :deep(.p-dropdown-label) {
  min-height: 2.5rem !important;
  display: flex !important;
  align-items: center !important;
  white-space: normal !important;
  word-wrap: break-word !important;
}

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
