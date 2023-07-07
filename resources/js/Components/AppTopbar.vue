<template>
  <div class="layout-topbar">
    <Link
      href="/"
      class="layout-topbar-logo"
    >
      InvenTrackr
    </Link>
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
          @click="goToDashboard"
        >
          <i class="pi pi-calendar"></i>
          <span>Events</span>
        </Button>
      </li>
      <li>
        <Button
          class="p-button-link p-link layout-topbar-button"
          @click="goToDashboard"
        >
          <i class="pi pi-cog"></i>
          <span>Settings</span>
        </Button>
      </li>
      <li>
        <Button
          class="p-button-link p-link layout-topbar-button"
          @click="goToDashboard"
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
          @click="goToDashboard"
        >
          <i class="pi pi-calendar"></i>
          <span>Events</span>
        </Button>
      </li>
      <li>
        <Button
          class="p-button-link p-link layout-topbar-button"
          @click="goToDashboard"
        >
          <i class="pi pi-cog"></i>
          <span>Settings</span>
        </Button>
      </li>
      <li>
        <Button
          class="p-button-link p-link layout-topbar-button"
          @click="goToDashboard"
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
</template>

<script>
import { Link } from '@inertiajs/vue3';
import Button from 'primevue/button';

export default {
  components: {
    Link,
    Button,
  },
  data() {
    return {
      topbarMenuActive: false,
      outsideClickListener: null,
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
    goToDashboard() {
      this.$inertia.get(this.route('dashboard'));
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
  },
};
</script>
