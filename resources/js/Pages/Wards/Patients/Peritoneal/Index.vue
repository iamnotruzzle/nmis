<template>
  <app-layout>
    <Head title="NMIS - Patients" />

    <div class="w-full flex align-items-center justify-content-center">
      <Toast />

      <div class="card w-7">
        <DataTable
          :value="encounterList"
          removableSort
        >
          <template #header>
            <h1 class="font-bold text-primary">SEARCH PATIENT</h1>
            <h3 class="text-yellow-500">(EITHER SEARCH HOSP. # ONLY OR FIRST AND LAST NAME)</h3>

            <div class="w-auto flex justify-content-between">
              <div class="p-inputgroup w-auto">
                <span class="p-inputgroup-addon">
                  <i class="pi pi-search"></i>
                </span>
                <InputText
                  id="searchInput"
                  v-model="hpercode"
                  size="large"
                  placeholder="HOSP. #"
                />
              </div>

              <div class="mx-1"></div>

              <div class="p-inputgroup w-auto">
                <span class="p-inputgroup-addon">
                  <i class="pi pi-search"></i>
                </span>
                <InputText
                  id="searchInput"
                  v-model="patfirst"
                  size="large"
                  placeholder="First name"
                />
              </div>

              <div class="mx-1"></div>

              <div class="p-inputgroup w-auto">
                <span class="p-inputgroup-addon">
                  <i class="pi pi-search"></i>
                </span>
                <InputText
                  id="searchInput"
                  v-model="patlast"
                  size="large"
                  placeholder="Last name"
                />
              </div>

              <div class="mx-1"></div>

              <Button
                class="w-auto"
                icon="pi pi-search"
                label="SEARCH"
                severity="info"
                @click="searchPatient(hpercode, patfirst, patlast)"
              />
            </div>

            <div class="border-1 my-4"></div>

            <h4 v-if="patient != null">HOSP. #: {{ patient.hpercode }}</h4>
            <h4 v-if="patient != null">PATIENT: {{ patient.patient }}</h4>
          </template>
          <Column
            field="toecode"
            header="TYPE OF ENCOUNTER"
            sortable
          >
            <template #body="{ data }">
              <!-- <span v-if="data.toecode == 'ADM'">ADMITTING</span>
              <span v-else-if="data.toecode == 'OPD'">OUT-PATIENT</span>
              <span v-else>EMERGENCY ROOM</span> -->

              <Tag
                v-if="data.toecode == 'ADM'"
                severity="success"
                value="ADMITTING"
              />
              <Tag
                v-else-if="data.toecode == 'OPD'"
                severity="warning"
                value="OUT-PATIENT"
              />
              <Tag
                v-else
                severity="danger"
                value="EMERGENCY ROOM"
              />
            </template>
          </Column>
          <Column
            field="encdate"
            header="DATE"
            sortable
          >
            <template #body="{ data }">
              {{ tzone(data.encdate) }}
            </template>
          </Column>
          <Column
            header="ACTION"
            style="width: 5%"
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
import RadioButton from 'primevue/radiobutton';
import Card from 'primevue/card';
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
    Card,
  },
  props: {
    encounters: Array,
  },
  data() {
    return {
      search: '',
      options: {},
      params: {},
      encounterList: [],
      search: '',
      patientHospitalNumber: '',
      patient: '',
      hpercode: '',
      patfirst: '',
      patlast: '',
      form: this.$inertia.form({}),
    };
  },
  // created will be initialize before mounted
  created() {},
  mounted() {
    // console.log(this.encounters);
    this.storeEncounterList();
  },
  methods: {
    tzone(date) {
      return moment.tz(date, 'Asia/Manila').format('LLL');
    },
    storeEncounterList() {
      if (this.hpercode) {
        this.encounters.forEach((e) => {
          console.log(e);
          this.encounterList.push({
            enccode: e.enccode,
            toecode: e.toecode,
            hpercode: e.hpercode,
            encdate: e.encdate,
            tscode: e.tscode,
            patient: e.patlast + ',' + ' ' + e.patfirst + ' ' + (e.patsuffix == null ? '' : e.patsuffix),
          });
        });

        this.patient = this.encounterList[0];
      } else if (this.patfirst && this.patlast) {
        this.encounters.forEach((e) => {
          console.log(e);
          this.encounterList.push({
            enccode: e.enccode,
            toecode: e.toecode,
            hpercode: e.hpercode,
            encdate: e.encdate,
            tscode: e.tscode,
            patient: e.patlast + ',' + ' ' + e.patfirst + ' ' + (e.patsuffix == null ? '' : e.patsuffix),
          });
        });

        this.patient = this.encounterList[0];
      } else {
        this.encounterList = [];
      }
    },
    updateData() {
      this.patientsList = [];
      this.loading = true;

      this.$inertia.get('wardspatients', this.params, {
        preserveState: true,
        preserveScroll: true,
        onFinish: (visit) => {
          this.encounterList = [];
          this.storeEncounterList();
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
    searchPatient(hpercode, firstname, lastname) {
      //   console.log(firstname);
      this.params.hpercode = hpercode;
      this.params.patfirst = firstname;
      this.params.patlast = lastname;

      if (hpercode != null && hpercode != '') {
        this.updateData();
      } else {
        if (firstname != null && firstname != '' && lastname != null && lastname != '') {
          this.updateData();
        } else {
          this.searchError();
        }
      }
    },
    searchError() {
      this.$toast.add({
        severity: 'warn',
        summary: '',
        detail: 'Please fill in both the first and last name fields.',
        life: 3000,
      });
    },
  },
  watch: {
    // search: function (val, oldVal) {
    //   this.params.search = val;
    //   this.updateData();
    // },
  },
};
</script>
