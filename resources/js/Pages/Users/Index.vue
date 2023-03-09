<template>
  <app-layout>
    <div class="card">
      <!-- v-model:filters="filters" -->
      <DataTable
        class="p-datatable-sm"
        v-model:filters="filters"
        :value="usersList"
        paginator
        :rows="20"
        :rowsPerPageOptions="[5, 10, 20, 50]"
        sortField="name"
        :sortOrder="1"
        removableSort
        dataKey="id"
        filterDisplay="row"
        :loading="loading"
        :globalFilterFields="['name', 'email']"
      >
        <template #header>
          <div class="flex justify-content-end">
            <span class="p-input-icon-left">
              <i class="pi pi-search" />
              <InputText
                v-model="filters['global'].value"
                placeholder="Keyword Search"
              />
            </span>
          </div>
        </template>
        <template #empty> No user found. </template>
        <template #loading> Loading user data. Please wait. </template>
        <Column
          field="name"
          header="Name"
          sortable
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.name }}
          </template>
          <template #filter="{ filterModel, filterCallback }">
            <InputText
              v-model="filterModel.value"
              type="text"
              @input="filterCallback()"
              class="p-column-filter"
              placeholder="Search by name"
            />
          </template>
        </Column>
        <Column
          field="email"
          header="Email"
          sortable
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.email }}
          </template>
          <template #filter="{ filterModel, filterCallback }">
            <InputText
              v-model="filterModel.value"
              type="text"
              @input="filterCallback()"
              class="p-column-filter"
              placeholder="Search by email"
            />
          </template>
        </Column>
        <!-- TODO fixed the correct date that shows in the table column and database -->
        <Column
          field="created_at"
          header="Created at"
          sortable
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.created_at }}
          </template>
          <template #filter="{ filterModel, filterCallback }">
            <InputText
              v-model="filterModel.value"
              type="text"
              @input="filterCallback()"
              class="p-column-filter"
              placeholder="Search by created at"
            />
          </template>
        </Column>
      </DataTable>
    </div>
  </app-layout>
</template>

<script>
import { FilterMatchMode } from 'primevue/api';
// import { CustomerService } from '@/service/CustomerService';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputText from 'primevue/inputtext';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';

export default {
  components: {
    AppLayout,
    InputText,
    Column,
    DataTable,
  },
  props: {
    users: Object,
  },
  data() {
    return {
      search: '',
      options: {},
      params: {},
      usersList: null,
      filters: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
        name: { value: null, matchMode: FilterMatchMode.CONTAINS },
        email: { value: null, matchMode: FilterMatchMode.CONTAINS },
        created_at: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },

      loading: true,
    };
  },
  mounted() {
    // this.users.getUsersMedium().then((data) => {
    //   this.usersList = this.getUsers(data);
    //   this.loading = false;
    // });
    // console.log(this.users.data);
    this.usersList = this.users.data;
    this.loading = false;
  },
  methods: {
    updateData() {
      this.$inertia.get('users', this.params, {
        preserveState: true,
        preserveScroll: true,
      });
    },
  },
  watch: {
    options: function (val) {
      this.params.page = val.page;
      this.params.page_size = val.itemsPerPage;
      this.updateData();
    },
    'filters.global': function (val, oldVal) {
      console.log(val);
      this.params.search = val;
      this.params.page = 1;
      this.updateData();
    },
  },
};
</script>
