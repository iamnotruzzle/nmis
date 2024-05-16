<template>
  <div class="layout-topbar">
    <Link
      href="/"
      class="layout-topbar-logo"
    >
      <div class="flex justify-items-center align-items-center">
        <img
          src="images/hosp_logo.png"
          alt="Image"
        />
        <span>NURSE MANAGEMENT INFORMATION SYSTEM</span>
      </div>
    </Link>
    <!--  -->
    <button
      class="p-link layout-menu-button layout-topbar-button"
      @click="onMenuToggle"
    >
      <i class="pi pi-bars"></i>
    </button>

    <button
      class="p-link layout-topbar-menu-button layout-topbar-button"
      @click="onTopBarMenuToggle"
      :class="{
        selector: '@next',
        enterClass: 'hidden',
        enterActiveClass: 'scalein',
        leaveToClass: 'hidden',
        leaveActiveClass: 'fadeout',
        hideOnOutsideClick: true,
      }"
    >
      <i class="pi pi-ellipsis-v"></i>
    </button>

    <!-- Mobile screen -->
    <ul
      v-if="topbarMenuActive"
      class="layout-topbar-menu lg:flex origin-top"
    >
      <li>
        <Button
          class="p-button-link p-link layout-topbar-button"
          @click="openCreateItemDialog"
        >
          <i class="pi pi-user"></i>
          <span>Profile</span>
        </Button>
      </li>
      <li>
        <Button
          class="p-button-link p-link layout-topbar-button"
          @click="logout"
        >
          <i class="pi pi-fw pi-sign-out"></i>
          <span>Logout</span>
        </Button>
      </li>
    </ul>
    <!-- Desktop screen -->
    <ul
      v-else
      class="layout-topbar-menu hidden lg:flex origin-top"
    >
      <li>
        <Button
          class="p-button-link p-link layout-topbar-button"
          @click="openCreateItemDialog"
        >
          <i class="pi pi-user"></i>
          <span>Profile</span>
        </Button>
      </li>
      <li>
        <Button
          class="p-button-link p-link layout-topbar-button"
          @click="logout"
        >
          <i class="pi pi-fw pi-sign-out"></i>
          <span>Logout</span>
        </Button>
      </li>
    </ul>
  </div>

  <Dialog
    v-model:visible="createItemDialog"
    :style="{ width: '450px' }"
    header="Update profile"
    :modal="true"
    class="p-fluid"
    @hide="clickOutsideDialog"
    dismissableMask
  >
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
    <div class="field">
      <label for="password">Password</label>
      <Password
        id="password"
        type="password"
        toggleMask
        v-model.trim="form.password"
        :required="true"
        :class="{ 'p-invalid': form.password == '' }"
        @keyup.enter="submit"
      />
      <small
        class="text-error"
        v-if="form.errors.password"
      >
        {{ form.errors.password }}
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
        label="Save"
        icon="pi pi-check"
        text
        type="submit"
        :disabled="form.processing"
        @click="submit"
      />
    </template>
  </Dialog>
</template>

<script>
import { Link } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Password from 'primevue/password';
import FileUpload from 'primevue/fileupload';
import { FilterMatchMode } from 'primevue/api';
import { router } from '@inertiajs/vue3';

export default {
  emits: ['menu-toggle', 'hide'],
  components: {
    Link,
    Button,
    Dialog,
    FileUpload,
    Password,
  },
  data() {
    return {
      createItemDialog: false,
      topbarMenuActive: false,
      outsideClickListener: null,
      form: this.$inertia.form({
        image: null,
        password: null,
      }),
    };
  },
  mounted() {
    this.bindOutsideClickListener();
  },
  beforeUnmount() {
    this.unbindOutsideClickListener();
  },
  methods: {
    onMenuToggle(event) {
      this.$emit('menu-toggle', event);
    },
    onTopBarMenuToggle() {
      this.topbarMenuActive = !this.topbarMenuActive;
    },
    bindOutsideClickListener() {
      if (!this.outsideClickListener) {
        this.outsideClickListener = (event) => {
          if (this.isOutsideClicked(event)) {
            this.topbarMenuActive = false;
          }
        };
        document.addEventListener('click', this.outsideClickListener);
      }
    },
    unbindOutsideClickListener() {
      if (this.outsideClickListener) {
        document.removeEventListener('click', this.outsideClickListener);
        this.outsideClickListener = null;
      }
    },
    isOutsideClicked(event) {
      if (!this.topbarMenuActive) return;
      const sidebarEl = document.querySelector('.layout-topbar-menu');
      const topbarEl = document.querySelector('.layout-topbar-menu-button');
      return !(
        sidebarEl.isSameNode(event.target) ||
        sidebarEl.contains(event.target) ||
        topbarEl.isSameNode(event.target) ||
        topbarEl.contains(event.target)
      );
    },
    logout() {
      this.$inertia.post(this.route('logout'));
    },
    clickOutsideDialog() {
      this.$emit('hide', this.form.clearErrors(), this.form.reset());
    },
    cancel() {
      this.createItemDialog = false;
      this.form.reset();
      this.form.clearErrors();
    },
    onUpload(event) {
      this.form.image = event.target.files[0];
    },
    openCreateItemDialog() {
      this.form.clearErrors();
      this.form.reset();
      this.createItemDialog = true;
    },
    submit() {
      this.form.post(route('profile.store'), {
        preserveScroll: true,
        onSuccess: () => {
          this.itemId = null;
          this.createItemDialog = false;
          this.cancel();
        },
      });
    },
  },
};
</script>
