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
        <span class="text-xl text-900 font-bold text-primary">CURRENT STOCKS</span>

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
                <!-- :disabled="canTransact == false" -->
                <Button
                  v-if="!['CATHL', 'CENDU', 'HEMO'].includes(authWardcode)"
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
                  v-if="slotProps.data.from == 'EXISTING_STOCKS'"
                  label="UPDATE"
                  severity="info"
                  @click="openUpdateStock(slotProps.data)"
                />
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
        class="p-fluid w-4"
        @hide="whenDialogIsHidden"
      >
        <template #header>
          <div class="text-blue-500 text-xl font-bold">DELIVERY</div>
        </template>
        <div
          v-if="canAddExpiryDate"
          class="bg-orange-700 text-white p-4 rounded font-semibold my-2 text-3xl"
        >
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
            :virtualScrollerOptions="{ itemSize: 38 }"
            filter
            optionValue="cl2comb"
            optionLabel="cl2desc"
            class="w-full mb-3"
            :disabled="isUpdateDelivery == true"
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
            :virtualScrollerOptions="{ itemSize: 38 }"
            filter
            optionValue="cl2comb"
            optionLabel="cl2desc"
            class="w-full mb-3"
            :disabled="isUpdateConsignment == true"
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
            :virtualScrollerOptions="{ itemSize: 38 }"
            filter
            optionValue="cl2comb"
            optionLabel="cl2desc"
            class="w-full mb-3"
            :disabled="isUpdateSupplemental == true"
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
    items: Object,
    currentWardStocks: Object,
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
      oldQuantity: 0,
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
        expiration_date: null,
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
        expiration_date: null,
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
    };
  },
  mounted() {
    this.authWardcode = this.$page.props.auth.user.location.location_name.wardcode;

    this.storeFundSourceInContainer();
    this.storeItemsInController();
    this.storeCurrentWardStocksInContainer();

    this.loading = false;
  },
  computed: {
    user() {
      return this.$page.props.auth.user;
    },
  },
  methods: {
    openUpdateStock(data) {
      if (data.from == 'EXISTING_STOCKS') {
        this.formExisting.id = data.ward_stock_id;
        this.formExisting.cl2comb = data.cl2comb;
        this.formExisting.quantity = data.quantity;

        this.isUpdateExisting = true;
        this.existingDialog = true;
      }
    },
    openUpdateConsignment(data) {
      if (data.from == 'CONSIGNMENT') {
        this.formConsignment.id = data.ward_stock_id;
        this.formConsignment.cl2comb = data.cl2comb;
        this.formConsignment.quantity = data.quantity;

        this.isUpdateConsignment = true;
        this.consignmentDialog = true;
      }
    },
    openUpdateDelivery(data) {
      if (data.from == 'DELIVERY') {
        this.formDelivery.id = data.ward_stock_id;
        this.formDelivery.cl2comb = data.cl2comb;
        this.formDelivery.quantity = data.quantity;

        this.isUpdateDelivery = true;
        this.DeliveryDialog = true;
      }
    },
    openUpdateSupplemental(data) {
      if (data.from == 'SUPPLEMENTAL') {
        this.formSupplemental.id = data.ward_stock_id;
        this.formSupplemental.cl2comb = data.cl2comb;
        this.formSupplemental.quantity = data.quantity;

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

      //   this.medicalGasList = []; // reset
      //   this.medicalGas.forEach((e) => {
      //     this.medicalGasList.push({
      //       cl2comb: e.cl2comb,
      //       cl2desc: e.cl2desc,
      //       uomcode: e.uomcode,
      //       uomdesc: e.uomdesc,
      //     });
      //   });
    },
    // store current stocks
    storeCurrentWardStocksInContainer() {
      //   console.log(this.currentWardStocks);
      this.currentWardStocksList = []; // reset

      moment.suppressDeprecationWarnings = true;

      this.currentWardStocks.forEach((e) => {
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

      //   this.currentWardStocks2.forEach((e) => {
      //     let expiration_date = moment.tz(e.expiration_date, 'Asia/Manila').format('MM/DD/YYYY');

      //     this.currentWardStocksList.push({
      //       from: e.from,
      //       ward_stock_id: e.id,
      //       cl2comb: e.item_details.cl2comb,
      //       item: e.item_details.cl2desc,
      //       unit: e == null ? null : e.uomdesc,
      //       quantity: e.quantity,
      //       average: e.average,
      //       is_consumable: e.is_consumable == null ? null : e.is_consumable,
      //       expiration_date: expiration_date.toString(),
      //     });
      //   });

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
              this.formExisting.reset();
              this.cancel();
              this.updateData();
              this.updateExistingMessage();
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
              this.updateData();
              this.createdMsg();
              this.loading = false;
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
            this.updateData();
            this.updateConsignmentMessage();
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
              this.updateData();
              this.createdMsg();
              this.loading = false;
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
            this.updateData();
            this.updateDeliveryMessage();
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
              this.updateData();
              this.createdMsg();
              this.loading = false;
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
            this.updateData();
            this.updateSupplementalMessage();
          },
        });
      }
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
