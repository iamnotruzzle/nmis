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
        :globalFilterFields="['hpercode', 'patient', 'status', 'room_bed', 'physician']"
        showGridlines
      >
        <template #header>
          <div class="flex flex-wrap align-items-center justify-content-end">
            <!-- <div>
              <v-icon
                name="ri-user-shared-fill"
                class="pi pi-send text-green-500 text-xl mr-2"
              ></v-icon>
              <span>:&nbsp;&nbsp;FOR DISCHARGE</span>
            </div> -->

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
          field="bill_stat"
          header="STATUS"
          style="width: 13%"
          :showFilterMenu="false"
          :filterMenuStyle="{ width: '14rem' }"
        >
          <template #body="{ data }">
            <div class="flex justify-content-center">
              <Tag
                v-if="data.bill_stat == '02'"
                value="MAY GO HOME"
                severity="success"
                class="px-2"
              />
              <Tag
                v-if="data.bill_stat == '03'"
                value="BILLED"
                severity="info"
                class="px-2"
              />
            </div>
          </template>
          <template #filter="{ filterModel, filterCallback }">
            <div class="mb-2 flex flex-row align-items-center">
              <RadioButton
                v-model="filterModel.value"
                @change="filterCallback()"
                name="MAY GO HOME"
                value="02"
                inputId="02"
              />
              <label
                for="02"
                class="ml-2 cursor-pointer"
              >
                <Tag
                  value="MAY GO HOME"
                  severity="success"
                  class="px-2"
                />
              </label>
            </div>
            <div class="flex flex-row align-items-center">
              <RadioButton
                v-model="filterModel.value"
                @change="filterCallback()"
                name="BILLED"
                value="03"
                inputId="03"
              />
              <label
                for="03"
                class="ml-2 cursor-pointer"
              >
                <Tag
                  value="BILLED"
                  severity="info"
                  class="px-2"
                />
              </label>
            </div>
          </template>
        </Column>
        <Column
          field="is_for_discharge"
          header="FOR DISCHARGE"
          style="width: 2%"
          :showFilterMenu="false"
          :filterMenuStyle="{ width: '14rem' }"
        >
          <template #body="{ data }">
            <div class="flex justify-content-center">
              <div v-if="data.is_for_discharge == true">
                <v-icon
                  name="ri-user-shared-fill"
                  class="pi pi-send text-green-500 text-xl"
                ></v-icon>
              </div>
            </div>
          </template>
          <template #filter="{ filterModel, filterCallback }">
            <div class="mb-2 flex flex-row align-items-center">
              <RadioButton
                v-model="filterModel.value"
                @change="filterCallback()"
                name="YES"
                value="true"
                inputId="true"
              />
              <label
                for="true"
                class="ml-2 cursor-pointer"
              >
                <Tag
                  value="YES"
                  severity="success"
                  class="px-2"
                />
              </label>
            </div>
            <div class="flex flex-row align-items-center">
              <RadioButton
                v-model="filterModel.value"
                @change="filterCallback()"
                name="NO"
                value="false"
                inputId="false"
              />
              <label
                for="false"
                class="ml-2 cursor-pointer"
              >
                <Tag
                  value="NO"
                  severity="info"
                  class="px-2"
                />
              </label>
            </div>
          </template>
        </Column>
        <Column
          field="admission_date"
          header="ADMISSION DATE"
          sortable
          style="width: 15%"
        >
          <template #body="{ data }">
            {{ tzone(data.admission_date) }}
          </template>
        </Column>
        <Column
          field="kg"
          header="WEIGHT (kg)"
          style="width: 3%"
        >
        </Column>
        <Column
          field="cm"
          header="HEIGHT (cm)"
          style="width: 3%"
        >
        </Column>
        <Column
          field="bmi"
          header="BMI"
          style="width: 3%"
        >
        </Column>
        <Column
          field="room_bed"
          header="ROOM | BED"
          sortable
          style="width: 13%"
        >
        </Column>
        <Column
          field="physician"
          header="PHYSICIAN"
          sortable
          style="width: 20%"
        >
          <template #body="{ data }">
            {{ data.physician }}
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
        is_for_discharge: { value: null, matchMode: FilterMatchMode.EQUALS },
        hpercode: { value: null, matchMode: FilterMatchMode.CONTAINS },
        patient: { value: null, matchMode: FilterMatchMode.CONTAINS },
        bill_stat: { value: null, matchMode: FilterMatchMode.EQUALS },
        room_bed: { value: null, matchMode: FilterMatchMode.CONTAINS },
        physician: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
    };
  },
  // created will be initialize before mounted
  created() {
    this.totalRecords = this.patients.total;
    this.params.page = this.patients.current_page;
    this.rows = this.patients.per_page;
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
    checkIfBmiExistForWeight(e) {
      if (e.bmi == null) {
        return null;
      } else {
        if (e.bmi.vsweight == null) {
          return null;
        } else {
          return e.bmi.vsweight == null ? null : e.bmi.vsweight;
        }
      }
    },
    checkIfBmiExistForHeight(e) {
      if (e.bmi == null) {
        return null;
      } else {
        if (e.bmi.vsheight == null) {
          return null;
        } else {
          return e.bmi.vsheight == null ? null : e.bmi.vsheight;
        }
      }
    },
    calculateBmi(e) {
      if (e.cm == null || e.kg == null) {
        return null;
      } else {
        return ((e.kg / e.cm / e.cm) * 10000).toFixed(2);
      }
    },
    setPhysician(e) {
      let assignedPhysician = [];

      if (e.physician_licnof != null) {
        assignedPhysician.push(e.physician_licnof);
      }
      if (e.physician_licno2 != null) {
        assignedPhysician.push(e.physician_licno2);
      }
      if (e.physician_licno3 != null) {
        assignedPhysician.push(e.physician_licno3);
      }
      if (e.physician_licno4 != null) {
        assignedPhysician.push(e.physician_licno4);
      }
      return assignedPhysician;
    },
    storePatientsInContainer() {
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
          //   los: ,
          admission_date: e.admdate,
          kg: e.kg,
          cm: e.cm,
          bmi: this.calculateBmi(e),
          room_bed: e.rmname + ' - ' + e.bdname,
          physician: e.firstname + ' ' + e.lastname,
          bill_stat: e.bill_stat,
          is_for_discharge: e.is_for_discharge == 'DISCH' ? true : false,
          tscode: e.tscode,
        });
      });
      //   console.log(this.patients);
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
    goToPatientCharge(e) {
      this.params.enccode = e.enccode;
      this.params.hpercode = e.hpercode;
      this.params.patient_name = e.patient;
      this.params.disch = e.is_for_discharge;
      this.params.patient = e.patient;
      this.params.room_bed = e.room_bed;
      this.params.tscode = e.tscode;
      //   console.log(e);
      // this.params.is_for_discharge =
      this.$inertia.get('patientcharge', this.params, {
        preserveState: true,
        preserveScroll: true,
      });
    },
  },
  watch: {},
};
</script>
