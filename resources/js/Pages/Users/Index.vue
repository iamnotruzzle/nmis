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
        lazy
        paginator
        :rows="rows"
        ref="dt"
        :totalRecords="totalRecords"
        @page="onPage($event)"
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
                  placeholder="Search Employee ID"
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
          field="employeeid"
          header="Employee ID"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.employeeid }}
          </template>
        </Column>
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
        <Column
          field="lastname"
          header="Last name"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.lastname }}
          </template>
        </Column>
        <Column
          field="firstname"
          header="First name"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.firstname }}
          </template>
        </Column>
        <Column
          field="middlename"
          header="Middle name"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.middlename }}
          </template>
        </Column>
        <Column
          field="empsuffix"
          header="Suffix"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.empsuffix }}
          </template>
        </Column>
        <Column
          field="role"
          header="Role"
          style="min-width: 12rem"
        >
          <template #body="{ data }">
            {{ data.role }}
          </template>
        </Column>
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
        <div class="field">
          <label for="role">Role</label>
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
            @complete="autoCompleteEmployeeID"
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
    users: Object,
    employeeids: Object,
  },
  data() {
    return {
      // paginator
      loading: false,
      totalRecords: null,
      rows: null,
      // end paginator
      itemId: null,
      isUpdate: false,
      createItemDialog: false,
      deleteItemDialog: false,
      search: '',
      options: {},
      params: {},
      from: null,
      to: null,
      usersList: [],
      employeeIdList: [],
      filters: {
        firstname: { value: null, matchMode: FilterMatchMode.CONTAINS },
        middlename: { value: null, matchMode: FilterMatchMode.CONTAINS },
        lastname: { value: null, matchMode: FilterMatchMode.CONTAINS },
        empsuffix: { value: null, matchMode: FilterMatchMode.CONTAINS },
        role: { value: null, matchMode: FilterMatchMode.EQUALS },
        employeeid: { value: null, matchMode: FilterMatchMode.CONTAINS },
        email: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
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
        role: null,
        employeeid: null,
        password: null,
      }),
    };
  },
  // created will be initialize before mounted
  created() {
    this.totalRecords = this.users.total;
    this.params.page = this.users.current_page;
    this.rows = this.users.per_page;
  },
  mounted() {
    this.storeUserInContainer();

    this.loading = false;
  },
  methods: {
    // use storeUserInContainer() function so that every time you make
    // server request such as POST, the data in the table
    // is updated
    storeUserInContainer() {
      this.users.data.forEach((e) => {
        if (e.user_detail != null || e.user_detail == '') {
          this.usersList.push({
            id: e.id,
            image: e.image,
            employeeid: e.employeeid,
            role: e.roles[0].name,
            lastname: e.user_detail.lastname,
            firstname: e.user_detail.firstname,
            middlename: e.user_detail.middlename,
            empsuffix: e.user_detail.empsuffix,
            created_at: e.created_at,
          });
        } else {
          this.usersList.push({
            id: e.id,
            image: e.image,
            employeeid: e.employeeid,
            role: e.roles[0].name,
            created_at: e.created_at,
          });
        }
      });
    },
    autoCompleteEmployeeID(event) {
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
    onPage(event) {
      this.params.page = event.page + 1;
      this.updateData();
    },
    updateData() {
      this.usersList = [];
      this.loading = true;

      this.$inertia.get('users', this.params, {
        preserveState: true,
        preserveScroll: true,
        onFinish: (visit) => {
          this.totalRecords = this.users.total;
          this.usersList = [];
          this.storeUserInContainer();
          this.loading = false;
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
      //   console.log(item);

      this.isUpdate = true;
      this.createItemDialog = true;
      this.itemId = item.id;
      this.form.role = item.role;
      this.form.employeeid = item.employeeid;
      this.form.password = item.password;
    },
    submit() {
      if (this.isUpdate) {
        router.post(
          `users/${this.itemId}`,
          {
            _method: 'put',
            preserveScroll: true,
            role: this.form.role,
            employeeid: this.form.employeeid,
            password: this.form.password,
            image: this.form.image,
          },
          {
            onSuccess: () => {
              this.itemId = null;
              this.createItemDialog = false;
              this.cancel();
              this.updateData();
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
            this.updateData();
            this.createdMsg();
          },
        });
      }
    },
    confirmDeleteItem(item) {
      this.itemId = item.id;
      this.form.role = item.role;
      this.form.employeeid = item.employeeid;
      this.form.password = item.password;
      this.deleteItemDialog = true;
    },
    deleteItem() {
      this.form.delete(route('users.destroy', this.itemId), {
        preserveScroll: true,
        onSuccess: () => {
          this.usersList = [];
          this.deleteItemDialog = false;
          this.itemId = null;
          this.form.clearErrors();
          this.form.reset();
          this.updateData();
          this.deletedMsg();
          this.storeUserInContainer();
        },
      });
    },
    cancel() {
      this.itemId = null;
      this.isUpdate = false;
      this.createItemDialog = false;
      this.form.reset();
      this.form.clearErrors();
      this.usersList = [];
      this.storeUserInContainer();
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
