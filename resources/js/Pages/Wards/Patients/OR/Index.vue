<template>
  <app-layout>
    <Head title="NMIS - Patients" />

    <div class="w-full flex align-items-center justify-content-center">
      <div class="card w-8">
        <DataTable
          :value="encounterList"
          tableStyle="min-width: 50rem"
          removableSort
        >
          <template #header>
            <h3 class="text-xl text-900 font-bold text-primary">SEARCH PATIENT</h3>

            <div class="flex justify-content-end">
              <div class="p-inputgroup">
                <span class="p-inputgroup-addon">
                  <i class="pi pi-search"></i>
                </span>
                <InputText
                  id="searchInput"
                  v-model="search"
                  size="large"
                  placeholder="Hospital # (PRESS ENTER)"
                  @keydown.enter="searchPatient(search)"
                />
                <Button
                  class="m-1"
                  icon="pi pi-search"
                  label="SEARCH"
                  severity="info"
                  @click="searchPatient(search)"
                />
              </div>
            </div>

            <h1 v-if="patientName != null">PATIENT: {{ patientName.patient }}</h1>
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
      patientName: '',
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
      if (this.search != '') {
        this.encounters.forEach((e) => {
          this.encounterList.push({
            enccode: e.enccode,
            toecode: e.toecode,
            hpercode: e.hpercode,
            encdate: e.encdate,
            patient:
              e.patlast +
              ',' +
              ' ' +
              e.patfirst +
              ' ' +
              (e.patmiddle == null ? '' : e.patmiddle) +
              ' ' +
              (e.patsuffix == null ? '' : e.patsuffix),
          });
        });

        this.patientName = this.encounterList[0];
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
      this.params.enccode = e.enccode;
      this.params.patient = e.patient;
      this.$inertia.get('patientcharge', this.params, {
        preserveState: true,
        preserveScroll: true,
      });
    },
    searchPatient(e) {
      this.params.search = e;
      this.updateData();
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
