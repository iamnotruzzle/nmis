<template>
  <app-layout>
    <Head title="Template - Users" />

    <div class="card">
      <Toast />
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
        showGridlines
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
        <Column header="Avatar">
          <template #body="{ data }">
            <Avatar
              v-if="data.image != null"
              :image="`/storage/${data.image}`"
              shape="circle"
              size="large"
            />
            <Avatar
              v-else
              image="https://cdn.pixabay.com/photo/2015/10/05/22/37/blank-profile-picture-973460_960_720.png"
              shape="circle"
              size="large"
            />
          </template>
        </Column>
        <!-- <Column
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
        </Column> -->
        <!-- <Column
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
        </Column> -->
        <!-- <Column
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
        </Column> -->
        <!-- <Column
          field="suffix"
          header="Suffix"
          sortable
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.suffix }}
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
        </Column> -->
        <Column
          field="role"
          header="Role"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.roles[0].name }}
          </template>
          <!-- <template #filter="{ filterModel, filterCallback }">
            <InputText
              v-model="filterModel.value"
              type="text"
              @input="filterCallback()"
              class="p-column-filter"
              placeholder="Search by role"
            />
          </template> -->
        </Column>
        <!-- <Column
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
        </Column> -->
        <Column
          header="CREATED AT"
          filterField="created_at"
          style="min-width: 10rem"
          :showFilterMenu="false"
        >
          <template #body="{ data }">
            {{ tzone(data.created_at) }}
          </template>
          <template #filter="{}">
            <Calendar
              v-model="from"
              dateFormat="mm-dd-yy"
              placeholder="FROM"
              showIcon
              showButtonBar
              :hideOnDateTimeSelect="true"
            />
            <div class="mt-2"></div>
            <Calendar
              v-model="to"
              dateFormat="mm-dd-yy"
              placeholder="TO"
              showIcon
              showButtonBar
              :hideOnDateTimeSelect="true"
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
        @hide="clickOutsideDialog"
        dismissableMask
      >
        <!-- <div class="field">
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
            v-if="form.errors.firstName"
          >
            {{ form.errors.firstName }}
          </small>
        </div> -->
        <!-- <div class="field">
          <label for="middleName">Middle name</label>
          <InputText
            id="middleName"
            v-model.trim="form.middleName"
            autofocus
          />
          <small
            class="text-error"
            v-if="form.errors.middleName"
          >
            {{ form.errors.middleName }}
          </small>
        </div> -->
        <!-- <div class="field">
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
            v-if="form.errors.lastName"
          >
            {{ form.errors.lastName }}
          </small>
        </div> -->
        <!-- <div class="field">
          <label for="suffix">Suffix</label>
          <InputText
            id="suffix"
            v-model.trim="form.suffix"
            autofocus
          />
          <small
            class="text-error"
            v-if="form.errors.suffix"
          >
            {{ form.errors.suffix }}
          </small>
        </div> -->
        <!-- <div class="field">
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
            v-if="form.errors.email"
          >
            {{ form.errors.email }}
          </small>
        </div> -->
        <div class="field">
          <label for="role">Role</label>
          <!-- <InputText
            id="role"
            v-model.trim="form.employeeid"
            required="true"
            autofocus
            :class="{ 'p-invalid': form.employeeid == '' }"
          /> -->
          <Dropdown
            v-model="form.role"
            :options="roles"
            optionLabel="name"
            optionValue="value"
            placeholder="Role"
            class="w-full md:w-14rem"
          />
          <small
            class="text-error"
            v-if="form.errors.role"
          >
            {{ form.errors.role }}
          </small>
        </div>
        <div class="field">
          <label for="employeeid">Employee ID</label>
          <AutoComplete
            id="employeeid"
            v-model="form.employeeid"
            required="true"
            autofocus
            optionLabel="employeeid"
            :suggestions="employeeIdList"
            @complete="searchEmployeeID"
            @item-select="selectedEmployeeID"
            :class="{ 'p-invalid': form.employeeid == '' }"
          />
          <small
            class="text-error"
            v-if="form.errors.employeeid"
          >
            {{ form.errors.employeeid }}
          </small>
        </div>
        <div class="field">
          <label for="password">Password</label>
          <Password
            id="password"
            type="password"
            toggleMask
            v-model.trim="form.password"
            :required="true"
            autofocus
            :class="{ 'p-invalid': form.password == '' }"
          />
          <small
            class="text-error"
            v-if="form.errors.password"
          >
            {{ form.errors.password }}
          </small>
        </div>
        <div class="field">
          <label for="image">Upload image</label>
          <FileUpload
            id="image"
            mode="basic"
            @input="onUpload"
            accept="image/*"
            :maxFileSize="7000000"
          >
          </FileUpload>
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
        v-model:visible="deleteItemDialog"
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
    users: Object,
    employeeids: Object,
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
      from: null,
      to: null,
      totalRecords: 0,
      usersList: null,
      employeeIdList: [],
      filters: {
        // global: { value: null, matchMode: FilterMatchMode.CONTAINS },
        // firstName: { value: null, matchMode: FilterMatchMode.CONTAINS },
        // middleName: { value: null, matchMode: FilterMatchMode.CONTAINS },
        // lastName: { value: null, matchMode: FilterMatchMode.CONTAINS },
        // suffix: { value: null, matchMode: FilterMatchMode.CONTAINS },
        role: { value: null, matchMode: FilterMatchMode.CONTAINS },
        employeeid: { value: null, matchMode: FilterMatchMode.CONTAINS },
        email: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
      loading: true,
      roles: [
        {
          name: 'super-admin',
          value: 'super-admin',
        },
        {
          name: 'admin',
          value: 'admin',
        },
        {
          name: 'user',
          value: 'user',
        },
      ],
      form: this.$inertia.form({
        image: null,
        // firstName: null,
        // middleName: null,
        // lastName: null,
        // suffix: null,
        role: null,
        employeeid: null,
        // email: null,
        password: null,
      }),
    };
  },
  mounted() {
    this.storeUserToContainer();

    this.loading = false;
  },
  methods: {
    // use storeUserToContainer() function so that every time you make
    // server request such as POST, the data in the table
    // is updated
    storeUserToContainer() {
      this.usersList = this.users.data;
    },
    searchEmployeeID(event) {
      setTimeout(() => {
        if (!event.query.trim().length) {
          this.employeeIdList = [...this.employeeids.employeeid];
        } else {
          this.employeeIdList = this.employeeids.filter((e) => {
            // contains whatever the value
            return e.employeeid.includes(event.query);
          });
        }
      }, 200);
    },
    selectedEmployeeID(e) {
      this.form.employeeid = e.value.employeeid;
    },
    tzone(date) {
      return moment.tz(date, 'Asia/Manila').format('L');
    },
    updateData() {
      this.$inertia.get('users', this.params, {
        preserveState: true,
        preserveScroll: true,
        onFinish: (visit) => {
          this.storeUserToContainer();
        },
      });
    },
    // assign image name to form.image
    onUpload(event) {
      this.form.image = event.target.files[0];
    },
    openCreateItemDialog() {
      this.isUpdate = false;
      this.form.clearErrors();
      this.form.reset();
      this.itemId = null;
      this.createItemDialog = true;
    },
    // emit close dialog
    clickOutsideDialog() {
      this.$emit('hide', (this.isUpdate = false), this.form.clearErrors(), this.form.reset());
    },
    editItem(item) {
      //   console.log(item.roles[0].name);

      this.isUpdate = true;
      this.createItemDialog = true;
      this.itemId = item.id;
      //   this.form.firstName = item.firstName;
      //   this.form.middleName = item.middleName;
      //   this.form.lastName = item.lastName;
      //   this.form.suffix = item.suffix;
      this.form.role = item.roles[0].name;
      this.form.employeeid = item.employeeid;
      //   this.form.email = item.email;
      this.form.password = item.password;
    },
    submit() {
      if (this.isUpdate) {
        router.post(
          `users/${this.itemId}`,
          {
            _method: 'put',
            preserveScroll: true,
            // firstName: this.form.firstName,
            // middleName: this.form.middleName,
            // lastName: this.form.lastName,
            // suffix: this.form.suffix,
            role: this.form.role,
            // permissions: this.form.permissions,
            // email: this.form.email,
            employeeid: this.form.employeeid,
            password: this.form.password,
            image: this.form.image,
          },
          {
            onSuccess: () => {
              this.itemId = null;
              this.createItemDialog = false;
              this.cancel();
              this.updatedMsg();
            },
          }
        );
      } else {
        this.form.post(route('users.store'), {
          preserveScroll: true,
          onSuccess: () => {
            this.itemId = null;
            this.createItemDialog = false;
            this.cancel();
            this.createdMsg();
          },
        });
      }
    },
    confirmDeleteItem(item) {
      this.itemId = item.id;
      //   this.form.firstName = item.firstName;
      //   this.form.middleName = item.middleName;
      //   this.form.lastName = item.lastName;
      //   this.form.suffix = item.suffix;
      this.form.role = item.role;
      this.form.employeeid = item.employeeid;
      //   this.form.email = item.email;
      this.form.password = item.password;
      this.deleteItemDialog = true;
    },
    deleteItem() {
      this.form.delete(route('users.destroy', this.itemId), {
        preserveScroll: true,
        onSuccess: () => {
          this.deleteItemDialog = false;
          this.itemId = null;
          this.storeUserToContainer();
          this.form.clearErrors();
          this.form.reset();
          this.deletedMsg();
        },
      });
    },
    cancel() {
      this.itemId = null;
      this.isUpdate = false;
      this.createItemDialog = false;
      this.form.reset();
      this.form.clearErrors();
      this.storeUserToContainer();
    },
    createdMsg() {
      this.$toast.add({ severity: 'success', summary: 'Success', detail: 'Account created', life: 3000 });
    },
    updatedMsg() {
      this.$toast.add({ severity: 'warn', summary: 'Success', detail: 'Account updated', life: 3000 });
    },
    deletedMsg() {
      this.$toast.add({ severity: 'error', summary: 'Success', detail: 'Account deleted', life: 3000 });
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
  },
  watch: {
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
  },
};
</script>
