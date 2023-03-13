<template>
  <app-layout>
    <div class="card">
      <!--
            data table sort order
            asc = 1
            desc =-1
        -->
      <DataTable
        class="p-datatable-sm"
        v-model:filters="filters"
        :value="usersList"
        paginator
        :rows="20"
        :rowsPerPageOptions="[5, 10, 20, 50]"
        sortField="lastName"
        :sortOrder="1"
        removableSort
        dataKey="id"
        filterDisplay="row"
        :loading="loading"
      >
        <template #header>
          <div class="flex flex-wrap align-items-center justify-content-between gap-2">
            <span class="text-xl text-900 font-bold">Users</span>
            <div>
              <span class="p-input-icon-left mr-2">
                <i class="pi pi-search" />
                <InputText
                  v-model="search"
                  placeholder="Keyword Search"
                />
              </span>
              <Button
                label="Add user"
                icon="pi pi-user-plus"
                iconPos="right"
                @click="openCreateItemDialog"
              />
            </div>
          </div>
        </template>
        <template #empty> No user found. </template>
        <template #loading> Loading user data. Please wait. </template>
        <Column
          field="lastName"
          header="Last name"
          sortable
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.lastName }}
          </template>
          <template #filter="{ filterModel, filterCallback }">
            <InputText
              v-model="filterModel.value"
              type="text"
              @input="filterCallback()"
              class="p-column-filter"
              placeholder="Search by last name"
            />
          </template>
        </Column>
        <Column
          field="firstName"
          header="First name"
          sortable
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.firstName }}
          </template>
          <template #filter="{ filterModel, filterCallback }">
            <InputText
              v-model="filterModel.value"
              type="text"
              @input="filterCallback()"
              class="p-column-filter"
              placeholder="Search by first name"
            />
          </template>
        </Column>
        <Column
          field="middleName"
          header="Middle name"
          sortable
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.middleName }}
          </template>
          <template #filter="{ filterModel, filterCallback }">
            <InputText
              v-model="filterModel.value"
              type="text"
              @input="filterCallback()"
              class="p-column-filter"
              placeholder="Search by middle name"
            />
          </template>
        </Column>
        <Column
          field="suffix"
          header="Suffix"
          sortable
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            <span v-if="data.suffix == 'na'"></span>
            <span v-else>{{ data.suffix }}</span>
          </template>
          <template #filter="{ filterModel, filterCallback }">
            <InputText
              v-model="filterModel.value"
              type="text"
              @input="filterCallback()"
              class="p-column-filter"
              placeholder="Search by suffix"
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
              @click="editItem(slotProps.data)"
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
        v-model:visible="createItemDialog"
        :style="{ width: '450px' }"
        header="User Detail"
        :modal="true"
        class="p-fluid"
        dismissableMask="true"
      >
        <div class="field">
          <label for="firstName">First name</label>
          <InputText
            id="firstName"
            v-model.trim="form.firstName"
            required="true"
            autofocus
            :class="{ 'p-invalid': form.firstName == '' }"
          />
          <small
            class="text-error"
            v-if="form.firstName == ''"
          >
            First name is required.
          </small>
        </div>
        <div class="field">
          <label for="middleName">Middle name</label>
          <InputText
            id="middleName"
            v-model.trim="form.middleName"
            required="true"
            autofocus
            :class="{ 'p-invalid': form.middleName == '' }"
          />
          <small
            class="text-error"
            v-if="form.middleName == ''"
          >
            Middle name is required.
          </small>
        </div>
        <div class="field">
          <label for="lastName">Last name</label>
          <InputText
            id="lastName"
            v-model.trim="form.lastName"
            required="true"
            autofocus
            :class="{ 'p-invalid': form.lastName == '' }"
          />
          <small
            class="text-error"
            v-if="form.lastName == ''"
          >
            Last name is required.
          </small>
        </div>
        <div class="field">
          <label for="suffix">Suffix</label>
          <InputText
            id="suffix"
            v-model.trim="form.suffix"
            required="true"
            autofocus
            :class="{ 'p-invalid': form.suffix == '' }"
          />
          <small
            class="text-error"
            v-if="form.suffix == ''"
          >
            Suffix name is required.
          </small>
        </div>
        <div class="field">
          <label for="description">Email</label>
          <InputText
            type="email"
            id="email"
            v-model.trim="form.email"
            required="true"
            autofocus
            :class="{ 'p-invalid': form.email == '' }"
          />
          <small
            class="text-error"
            v-if="form.email == ''"
          >
            Email is required.
          </small>
        </div>
        <div class="field">
          <label for="username">Username</label>
          <InputText
            id="username"
            v-model.trim="form.username"
            required="true"
            autofocus
            :class="{ 'p-invalid': form.username == '' }"
          />
          <small
            class="text-error"
            v-if="form.username == ''"
          >
            Username is required.
          </small>
        </div>
        <div class="field">
          <label for="password">Password</label>
          <Password
            id="password"
            type="password"
            toggleMask
            v-model.trim="form.password"
            required="true"
            autofocus
            :class="{ 'p-invalid': form.password == '' }"
          />
          <small
            class="text-error"
            v-if="form.password == ''"
          >
            Password is required.
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
            @click="submit"
          />
          <Button
            v-else
            label="Save"
            icon="pi pi-check"
            text
            @click="submit"
          />
        </template>
      </Dialog>

      <!-- Delete confirmation dialog -->
      <Dialog
        v-model:visible="deleteItemDialog"
        :style="{ width: '450px' }"
        header="Confirm"
        :modal="true"
        dismissableMask="true"
      >
        <div class="flex align-items-center justify-content-center">
          <i
            class="pi pi-exclamation-triangle mr-3"
            style="font-size: 2rem"
          />
          <span v-if="form"
            >Are you sure you want to delete
            <b>{{ form.firstName }} {{ form.middleName }} {{ form.lastName }} </b> ?</span
          >
        </div>
        <template #footer>
          <Button
            label="No"
            icon="pi pi-times"
            class="p-button-text"
            @click="deleteItemDialog = false"
          />
          <Button
            label="Yes"
            icon="pi pi-check"
            severity="danger"
            text
            @click="deleteItem"
          />
        </template>
      </Dialog>
    </div>
  </app-layout>
</template>

<script>
import { FilterMatchMode } from 'primevue/api';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';

export default {
  components: {
    AppLayout,
    InputText,
    Column,
    Password,
    DataTable,
    Button,
    Dialog,
  },
  props: {
    users: Object,
  },
  data() {
    return {
      itemId: null,
      isUpdate: false,
      createItemDialog: false,
      deleteItemDialog: false,
      search: '',
      options: {},
      params: {},
      totalRecords: 0,
      usersList: null,
      filters: {
        // global: { value: null, matchMode: FilterMatchMode.CONTAINS },
        firstName: { value: null, matchMode: FilterMatchMode.CONTAINS },
        middleName: { value: null, matchMode: FilterMatchMode.CONTAINS },
        lastName: { value: null, matchMode: FilterMatchMode.CONTAINS },
        suffix: { value: null, matchMode: FilterMatchMode.CONTAINS },
        username: { value: null, matchMode: FilterMatchMode.CONTAINS },
        email: { value: null, matchMode: FilterMatchMode.CONTAINS },
        created_at: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
      loading: true,
      form: this.$inertia.form({
        firstName: null,
        middleName: null,
        lastName: null,
        suffix: null,
        username: null,
        email: null,
        password: null,
      }),
    };
  },
  mounted() {
    this.usersList = this.users.data;
    this.loading = false;
  },
  methods: {
    updateData() {
      this.$inertia.get('users', this.params, {
        preserveState: true,
        preserveScroll: true,
        onFinish: (visit) => {
          this.usersList = this.users.data;
        },
      });
    },
    openCreateItemDialog() {
      this.form.reset();
      this.itemId = null;
      this.createItemDialog = true;
    },
    editItem(item) {
      this.isUpdate = true;
      this.createItemDialog = true;
      this.itemId = item.id;
      this.form.firstName = item.firstName;
      this.form.middleName = item.middleName;
      this.form.lastName = item.lastName;
      this.form.suffix = item.suffix;
      this.form.username = item.username;
      this.form.email = item.email;
      this.form.password = item.password;
    },
    submit() {
      if (this.isUpdate) {
        this.form.put(route('users.update', this.itemId), {
          preserveScroll: true,
          onSuccess: () => {
            this.itemId = null;
            this.createItemDialog = false;
            this.cancel();
            // this.createdMsg();
          },
        });
      } else {
        this.form.post(route('users.store'), {
          preserveScroll: true,
          onSuccess: () => {
            this.itemId = null;
            this.createItemDialog = false;
            this.cancel();
            // this.createdMsg();
          },
        });
      }
    },
    confirmDeleteItem(item) {
      this.itemId = item.id;
      this.form.firstName = item.firstName;
      this.form.middleName = item.middleName;
      this.form.lastName = item.lastName;
      this.form.suffix = item.suffix;
      this.form.username = item.username;
      this.form.email = item.email;
      this.form.password = item.password;
      this.deleteItemDialog = true;
    },
    deleteItem() {
      this.form.delete(route('users.destroy', this.itemId), {
        preserveScroll: true,
        onSuccess: () => {
          this.deleteItemDialog = false;
          this.itemId = null;
          //   this.deletedMsg();
        },
      });
    },
    cancel() {
      this.itemId = null;
      this.isUpdate = false;
      this.createItemDialog = false;
      this.form.reset();
      this.form.clearErrors();
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
