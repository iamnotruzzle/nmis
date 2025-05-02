<template>
  <app-layout>
    <Head title="NMIS - Patients" />

    <div class="card">
      <span class="text-xl text-900 font-bold text-primary">NO. OF PATIENTS: {{ patientsList.length }}</span>

      <DataTable
        class="p-datatable-sm"
        v-model:filters="filters"
        :value="patientsList"
        paginator
        :rows="20"
        :rowsPerPageOptions="[20, 30, 40]"
        dataKey="hpercode"
        filterDisplay="row"
        sortField="patient"
        :sortOrder="1"
        removableSort
        :globalFilterFields="['hpercode', 'patient', 'status', 'physician', 'tsdesc']"
        showGridlines
      >
        <template #header>
          <div class="flex flex-wrap align-items-center justify-content-end">
            <div>
              <div class="p-inputgroup">
                <span class="p-inputgroup-addon">
                  <i class="pi pi-search"></i>
                </span>
                <InputText
                  id="searchInput"
                  v-model="filters['global'].value"
                  placeholder="Search"
                />
              </div>
            </div>
          </div>
        </template>
        <template #empty> No patients found. </template>
        <template #loading> Loading patients data. Please wait. </template>
        <Column
          field="hpercode"
          header="HOSP. #"
          style="width: 5%"
        >
          <template #body="{ data }">
            <div class="flex flex-row align-items-center">
              <div class="">
                <span class="text-green-500">{{ data.hpercode }}</span>
              </div>
            </div>
          </template>
        </Column>
        <Column
          v-if="currWard == 'FAMED'"
          field="tsdesc"
          header="DEPARTMENT"
          style="width: 5%"
          sortable
        >
          <template #body="{ data }">
            <div class="flex flex-row align-items-center justify-content-center">
              <Tag
                v-if="data.tsdesc == 'Animal Bite'"
                value="ANIMAL BITE"
                severity="success"
              />
              <Tag
                v-else-if="data.tsdesc == 'Family Medicine'"
                value="FAMILY MEDICINE"
                severity="warning"
              />
              <Tag
                v-else-if="data.tsdesc == 'E-Konsulta'"
                value="E-KONSULTA"
                severity="info"
              />
              <Tag
                v-else
                value="EMS"
                severity="info"
              />
            </div>
          </template>
        </Column>
        <Column
          field="patient"
          header="PATIENT"
          sortable
          style="width: 20%"
          :showFilterMenu="false"
          :filterMenuStyle="{ width: '14rem' }"
        >
          <template #body="{ data }">
            <div class="flex flex-row align-items-center">
              <div class="">
                <span>{{ data.patient }}</span>
              </div>
            </div>
          </template>
        </Column>
        <Column
          header="ACTION"
          style="width: 3%"
        >
          <template #body="{ data }">
            <!-- {{ data }} -->
            <div class="flex justify-content-center">
              <Button
                class="m-1"
                icon="pi pi-money-bill"
                label="Bills"
                severity="success"
                @click="goToPatientCharge(data)"
              />
            </div>
          </template>
        </Column>
        <Column
          field="physician"
          header="PHYSICIAN"
          sortable
          style="width: 20%"
        >
          <template #body="{ data }">
            <span>{{ data.physician }}</span>
          </template>
        </Column>
      </DataTable>
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
import RadioButton from 'primevue/radiobutton';
import Tag from 'primevue/tag';
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
    Tag,
    RadioButton,
  },
  props: {
    patients: Object,
    currWard: String,
  },
  data() {
    return {
      // paginator
      loading: false,
      totalRecords: null,
      rows: null,
      options: {},
      params: {},
      patientsList: [],
      filters: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
        hpercode: { value: null, matchMode: FilterMatchMode.CONTAINS },
        patient: { value: null, matchMode: FilterMatchMode.CONTAINS },
        physician: { value: null, matchMode: FilterMatchMode.CONTAINS },
        tsdesc: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
    };
  },
  // created will be initialize before mounted
  created() {
    // this.totalRecords = this.patients.total;
    // this.params.page = this.patients.current_page;
    // this.rows = this.patients.per_page;
  },
  mounted() {
    // console.log('mounted', this.patients);
    this.storePatientsInContainer();

    this.loading = false;
  },
  methods: {
    tzone(date) {
      return moment.tz(date, 'Asia/Manila').format('LLL');
    },
    setPatient(patient) {
      const suffix = patient.patsuffix == null ? '' : patient.patsuffix;

      return patient.patlast + ', ' + patient.patfirst + ' ' + patient.patmiddle + suffix;
    },
    // use storePatientsInContainer() function so that every time you make
    // server request such as POST, the data in the table
    // is updated
    // storePatientsInContainer() {
    //   this.patients.data.forEach((e) => {
    //     this.patientsList.push({
    //       enccode: e.enccode,
    //       hpercode: e.hpercode,
    //       patient: this.setPatient(e.patient),
    //       bill_stat: e.patient.admission_date.patient_bill_stat.billstat,
    //       //   los: ,
    //       admission_date: e.patient.admission_date.admdate,
    //       kg: this.checkIfBmiExistForWeight(e.patient.admission_date),
    //       cm: this.checkIfBmiExistForHeight(e.patient.admission_date),
    //       bmi: this.calculateBmi(e.patient.admission_date),
    //       room_bed: e.room.rmname + ' - ' + e.bed.bdname,
    //       physician: this.setPhysician(
    //         e.patient.admission_date.physician,
    //         e.patient.admission_date.physician2,
    //         e.patient.admission_date.physician3,
    //         e.patient.admission_date.physician4
    //       ),
    //     });
    //   });
    // },
    storePatientsInContainer() {
      console.log(this.patients);
      this.patients.forEach((e) => {
        this.patientsList.push({
          enccode: e.enccode,
          hpercode: e.hpercode,
          patient:
            e.patlast +
            ',' +
            ' ' +
            e.patfirst +
            ' ' +
            (e.patmiddle == null ? '' : e.patmiddle) +
            ' ' +
            (e.patsuffix == null ? '' : e.patsuffix),
          physician: e.lastname + ',' + ' ' + e.firstname + ' ' + (e.empsuffix == null ? '' : e.empsuffix),
          tscode: e.tscode,
          tsdesc: e.tsdesc,
        });
      });
      //   console.log(this.patientsList);
    },
    onPage(event) {
      //   this.params.page = event.page + 1;
      this.updateData();
    },
    updateData() {
      this.patientsList = [];
      this.loading = true;

      this.$inertia.get('wardspatients', this.params, {
        preserveState: true,
        preserveScroll: true,
        onFinish: (visit) => {
          //   this.totalRecords = this.patients.total;
          this.patientsList = [];
          this.storePatientsInContainer();
          this.loading = false;
        },
      });
    },
    goToPatientCharge(e) {
      this.params.hpercode = e.hpercode;
      this.params.patient_name = e.patient;
      this.params.enccode = e.enccode;
      this.params.patient = e.patient;
      this.params.tscode = e.tscode;

      this.$inertia.get('patientcharge', this.params, {
        preserveState: true,
        preserveScroll: true,
      });
    },
  },
  watch: {},
};
</script>
