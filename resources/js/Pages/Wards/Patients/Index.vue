<template>
  <app-layout>
    <Head title="Template - Patients" />

    <div class="card">
      <Toast />

      <!-- v-model:filters="filters" -->
      <DataTable
        class="p-datatable-sm"
        v-model:filters="filters"
        :value="patientsList"
        selectionMode="single"
        lazy
        paginator
        :rows="rows"
        ref="dt"
        :totalRecords="totalRecords"
        @page="onPage($event)"
        dataKey="hpercode"
        filterDisplay="row"
        showGridlines
        :loading="loading"
      >
        <template #header>
          <div class="flex flex-wrap align-items-center justify-content-between gap-2">
            <span class="text-xl text-900 font-bold">Patients</span>
            <div>
              <span class="p-input-icon-left mr-2">
                <i class="pi pi-search" />
                <InputText
                  v-model="search"
                  placeholder="Search patient"
                />
              </span>
            </div>
          </div>
        </template>
        <template #empty> No patients found. </template>
        <template #loading> Loading patients data. Please wait. </template>
        <Column
          field="hpercode"
          header="HEALTH RECORD NUMBER"
          style="min-width: 12rem"
        >
        </Column>
        <Column
          field="lastName"
          header="LAST NAME"
          style="min-width: 12rem"
        >
        </Column>
        <Column
          field="firstName"
          header="FIRST NAME"
          style="min-width: 12rem"
        >
        </Column>
        <Column
          field="middleName"
          header="MIDDLE NAME"
          style="min-width: 12rem"
        >
        </Column>
        <Column
          field="suffix"
          header="SUFFIX"
          style="min-width: 12rem"
        >
        </Column>
        <Column
          field="admission_date"
          header="ADMISSION DATE"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ tzone(data.admission_date) }}
          </template>
        </Column>
        <Column
          field="kg"
          header="WEIGHT"
          style="min-width: 12rem"
        >
        </Column>
        <Column
          field="cm"
          header="HEIGHT"
          style="min-width: 12rem"
        >
        </Column>
        <Column
          field="bmi"
          header="BMI"
          style="min-width: 12rem"
        >
        </Column>
        <Column
          field="room_bed"
          header="ROOM | BED"
          style="min-width: 12rem"
        >
        </Column>

        <Column
          header="Action"
          style="min-width: 12rem"
        >
          <template #body="slotProps">
            <Button
              icon="pi pi-pencil"
              class="mr-1"
              rounded
              text
              severity="warning"
              @click="editCategory(slotProps.data)"
            />

            <Button
              icon="pi pi-trash"
              rounded
              text
              severity="danger"
              @click="confirmDeleteItem(slotProps.data)"
            />
          </template>
        </Column>
      </DataTable>

      <!-- create & edit dialog -->
      <Dialog
        v-model:visible="createPatientDialog"
        :style="{ width: '450px' }"
        header="Patient Detail"
        :modal="true"
        class="p-fluid"
        @hide="clickOutsideDialog"
        dismissableMask
      >
        <div class="field">
          <label for="cl1code">Cl1code</label>
          <InputText
            id="cl1code"
            v-model.trim="form.cl1code"
            required="true"
            autofocus
            :class="{ 'p-invalid': form.cl1code == '' }"
            @keyup.enter="submit"
          />
          <small
            class="text-error"
            v-if="form.errors.cl1code"
          >
            {{ form.errors.cl1code }}
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

      <!-- Delete confirmation dialog -->
      <Dialog
        v-model:visible="deletePatientDialog"
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
            >Are you sure you want to delete <b>{{ form.cl1desc }}</b> ?</span
          >
        </div>
        <template #footer>
          <Button
            label="No"
            icon="pi pi-times"
            class="p-button-text"
            @click="deletePatientDialog = false"
          />
          <Button
            label="Yes"
            icon="pi pi-check"
            severity="danger"
            text
            @click="deleteCategory"
          />
        </template>
      </Dialog>
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
import moment from 'moment';

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
  },
  props: {
    patients: Object,
  },
  data() {
    return {
      // paginator
      loading: false,
      totalRecords: null,
      rows: null,
      // end paginator
      cl1comb: null,
      isUpdate: false,
      createPatientDialog: false,
      deletePatientDialog: false,
      search: '',
      options: {},
      params: {},
      patientsList: [],
      filters: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
      form: this.$inertia.form({
        ptcode: null,
        cl1code: null,
        cl1desc: null,
        cl1stat: null,
        cl1upsw: null,
      }),
    };
  },
  // created will be initialize before mounted
  created() {
    this.totalRecords = this.patients.total;
    this.params.page = this.patients.current_page;
    this.rows = this.patients.per_page;
  },
  mounted() {
    console.log('mounted', this.patients);
    this.storePatientsInContainer();

    this.loading = false;
  },
  methods: {
    tzone(date) {
      return moment.tz(date, 'Asia/Manila').format('LL');
    },
    checkIfBmiExistForWeight(bmi) {
      if (bmi == null) {
        return null;
      } else {
        return bmi.vsweight == null ? null : bmi.vsweight;
      }
    },
    checkIfBmiExistForHeight(bmi) {
      if (bmi == null) {
        return null;
      } else {
        return bmi.vsheight == null ? null : bmi.vsheight;
      }
    },
    calculateBmi(weight, height) {
      const bmi = (weight / height / height) * 10000;
      if (bmi == Infinity || isNaN(bmi)) {
        return null;
      } else {
        return bmi.toFixed(2);
      }
    },
    // use storePatientsInContainer() function so that every time you make
    // server request such as POST, the data in the table
    // is updated
    storePatientsInContainer() {
      this.patients.data.forEach((e) => {
        this.patientsList.push({
          hpercode: e.hpercode,
          firstName: e.patient.patfirst,
          middleName: e.patient.patmiddle,
          lastName: e.patient.patlast,
          suffix: e.patient.patsuffix,
          //   los: ,
          admission_date: e.patient.admission_date.admdate,
          kg: this.checkIfBmiExistForWeight(e.patient.admission_date.bmi),
          cm: this.checkIfBmiExistForHeight(e.patient.admission_date.bmi),
          bmi: this.calculateBmi(e.patient.admission_date.bmi.vsweight, e.patient.admission_date.bmi.vsheight),
          room_bed: e.room.rmname + ' - ' + e.bed.bdname,
          //   physician:
          //     e.admission_date.physician3.user_detail.lastname +
          //     ', ' +
          //     e.admission_date.physician3.user_detail.firstname +
          //     ' ' +
          //     e.admission_date.physician3.user_detail.middlename,
        });
      });
    },
    onPage(event) {
      this.params.page = event.page + 1;
      this.updateData();
    },
    updateData() {
      this.patientsList = [];
      this.loading = true;

      this.$inertia.get('wardspatients', this.params, {
        preserveState: true,
        preserveScroll: true,
        onFinish: (visit) => {
          this.totalRecords = this.patients.total;
          this.patientsList = [];
          this.storePatientsInContainer();
          this.loading = false;
        },
      });
    },
    openCreateItemDialog() {
      this.isUpdate = false;
      this.form.clearErrors();
      this.form.reset();
      this.cl1comb = null;
      this.createPatientDialog = true;
    },
    // emit close dialog
    clickOutsideDialog() {
      this.$emit('hide', (this.cl1comb = null), (this.isUpdate = false), this.form.clearErrors(), this.form.reset());
    },
    editCategory(item) {
      this.isUpdate = true;
      this.createPatientDialog = true;
      this.cl1comb = item.cl1comb;
      this.form.ptcode = item.ptcode;
      this.form.cl1code = item.cl1code;
      this.form.cl1desc = item.cl1desc;
      this.form.cl1stat = item.cl1stat;
      this.form.cl1upsw = item.cl1upsw;
    },
    submit() {
      if (this.isUpdate) {
        this.form.put(route('wardspatients.update', this.cl1comb), {
          preserveScroll: true,
          onSuccess: () => {
            this.cl1comb = null;
            this.createPatientDialog = false;
            this.cancel();
            this.updateData();
            this.updatedMsg();
          },
        });
      } else {
        this.form.post(route('wardspatients.store'), {
          preserveScroll: true,
          onSuccess: () => {
            this.cl1comb = null;
            this.createPatientDialog = false;
            this.cancel();
            this.updateData();
            this.createdMsg();
          },
        });
      }
    },
    confirmDeleteItem(item) {
      this.cl1comb = item.cl1comb;
      this.form.cl1desc = item.cl1desc;
      this.deletePatientDialog = true;
    },
    deleteCategory() {
      this.form.delete(route('categories.destroy', this.cl1comb), {
        preserveScroll: true,
        onSuccess: () => {
          this.patientsList = [];
          this.deletePatientDialog = false;
          this.cl1comb = null;
          this.form.clearErrors();
          this.form.reset();
          this.updateData();
          this.deletedMsg();
          this.storePatientsInContainer();
        },
      });
    },
    cancel() {
      this.cl1comb = null;
      this.isUpdate = false;
      this.createPatientDialog = false;
      this.form.reset();
      this.form.clearErrors();
      this.patientsList = [];
      this.storePatientsInContainer();
    },
    createdMsg() {
      this.$toast.add({ severity: 'success', summary: 'Success', detail: 'Category created', life: 3000 });
    },
    updatedMsg() {
      this.$toast.add({ severity: 'warn', summary: 'Success', detail: 'Category updated', life: 3000 });
    },
    deletedMsg() {
      this.$toast.add({ severity: 'error', summary: 'Success', detail: 'Category deleted', life: 3000 });
    },
  },
  watch: {
    search: function (val, oldVal) {
      this.params.search = val;
      this.updateData();
    },
  },
};
</script>
