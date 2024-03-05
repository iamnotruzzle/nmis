<template>
  <app-layout>
    <Head title="NMIS - Patients" />

    <div class="card">
      <!-- v-model:filters="filters" -->
      <DataTable
        class="p-datatable-sm"
        v-model:filters="filters"
        :value="patientsList"
        paginator
        :rows="10"
        :rowsPerPageOptions="[10, 20, 50]"
        showGridlines
      >
        <template #header>
          <div class="flex flex-wrap align-items-center justify-content-between gap-2">
            <span class="text-xl text-900 font-bold text-primary">NO. OF PATIENTS: {{ patientsList.length }}</span>

            <div class="mr-2">
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
        </Column>
        <Column
          field="patient"
          header="PATIENT"
          sortable
          style="width: 20%"
        >
        </Column>
        <Column
          field="bill_stat"
          header="STATUS"
          style="width: 10%"
        >
          <template #body="{ data }">
            <div class="flex justify-content-center">
              <Tag
                v-if="data.bill_stat == '02'"
                value="BILLED"
                severity="info"
                class="px-2"
              />
              <Tag
                v-if="data.bill_stat == '03'"
                value="MAY GO HOME"
                severity="success"
                class="px-2"
              />
            </div>
          </template>
        </Column>
        <Column
          field="admission_date"
          header="ADMISSION DATE"
          sortable
          style="width: 10%"
        >
          <template #body="{ data }">
            {{ tzone(data.admission_date) }}
          </template>
        </Column>
        <Column
          field="kg"
          header="WEIGHT"
          style="width: 5%"
        >
        </Column>
        <Column
          field="cm"
          header="HEIGHT"
          style="width: 5%"
        >
        </Column>
        <Column
          field="bmi"
          header="BMI"
          style="width: 5%"
        >
        </Column>
        <Column
          field="room_bed"
          header="ROOM | BED"
          style="width: 10%"
        >
        </Column>
        <Column
          field="physician"
          header="PHYSICIAN"
          sortable
          style="width: 20%"
        >
        </Column>

        <Column
          header="ACTION"
          style="width: 10%"
        >
          <template #body="{ data }">
            <Button
              icon="pi pi-money-bill"
              class="mr-1"
              rounded
              text
              severity="info"
              @click="goToPatientCharge(data.enccode)"
            />
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
    console.log('mounted', this.patients);
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
      if (e.physician_licnof != null) {
        return e.physician_licnof;
      }
      if (e.physician_licno2 != null) {
        return e.physician_licno2;
      }
      if (e.physician_licno3 != null) {
        return e.physician_licno3;
      }
      if (e.physician_licno4 != null) {
        return e.physician_licno4;
      }
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
          bill_stat: null,
          //   los: ,
          admission_date: e.admdate,
          kg: e.kg,
          cm: e.cm,
          bmi: this.calculateBmi(e),
          room_bed: e.rmname + ' - ' + e.bdname,
          //   physician: this.setPhysician(null, null),
          physician: this.setPhysician(e),
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
    goToPatientCharge(e) {
      this.params.enccode = e;
      this.$inertia.get('patientcharge', this.params, {
        preserveState: true,
        preserveScroll: true,
      });
    },
  },
  watch: {},
};
</script>
