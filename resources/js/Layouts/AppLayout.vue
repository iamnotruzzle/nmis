<template>
  <div
    :class="containerClass"
    @click="onWrapperClick"
  >
    <AppTopBar @menu-toggle="onMenuToggle" />
    <div
      class="layout-sidebar"
      @click="onSidebarClick"
    >
      <AppMenu
        :model="menu"
        @menuitem-click="onMenuItemClick"
      />
      <!-- <AppConfig /> -->
    </div>

    <div class="layout-main-container">
      <div class="layout-main">
        <slot />
      </div>
    </div>

    <transition name="layout-mask">
      <div
        class="layout-mask p-component-overlay"
        v-if="mobileMenuActive"
      ></div>
    </transition>
  </div>
</template>

<script>
import AppTopBar from '../Components/AppTopbar.vue';
import AppMenu from '../Components/AppMenu.vue';
import AppFooter from '../Components/AppFooter.vue';
import AppConfig from '../Components/AppConfig.vue';

export default {
  data() {
    return {
      layoutMode: 'static',
      layoutColorMode: 'light',
      staticMenuInactive: false,
      overlayMenuActive: false,
      mobileMenuActive: false,
      menu: [
        {
          label: 'Menu',
          items: [
            {
              label: 'Dashboard',
              icon: 'pi pi-fw pi-home',
              to: 'dashboard',
              prefix: 'dashboard',
              comp: 'Dashboard',
            },
            {
              label: 'Categories',
              icon: 'pi pi-fw pi-home',
              to: 'categories',
              prefix: 'categories',
              comp: 'Csr/Inventory/Categories/Index',
            },
            {
              label: 'Items',
              icon: 'pi pi-fw pi-home',
              to: 'items',
              prefix: 'items',
              comp: 'Csr/Inventory/Items/Index',
            },
            {
              label: 'CSR-Stocks',
              icon: 'pi pi-fw pi-home',
              to: 'csr-stocks',
              prefix: 'csr-stocks',
              comp: 'Csr/Inventory/Stocks/Index',
            },
            {
              label: 'Users',
              icon: 'pi pi-fw pi-home',
              to: 'users',
              prefix: 'users',
              comp: 'Users/Index',
            },
            {
              label: 'Sign out',
              icon: 'pi pi-fw pi-sign-out',
              command: () => {
                this.$inertia.post(this.route('logout'));
              },
            },
          ],
        },
      ],
      menuForNonAdmin: null,
    };
  },
  watch: {
    $route() {
      this.menuActive = false;
      this.$toast.removeAllGroups();
    },
  },
  mounted() {
    this.$nextTick(() => {
      this.onload();
    });
    this.removeRoutesIfNonAdmin();
  },
  methods: {
    removeRoutesIfNonAdmin() {
      if (this.user.roles[0] == 'admin' || this.user.roles[0] == 'user') {
        this.menu[0].items.splice(1, 1); //remove user route
      }
    },
    onload() {
      document.documentElement.style.fontSize = 13 + 'px';
    },
    onWrapperClick() {
      if (!this.menuClick) {
        this.overlayMenuActive = false;
        this.mobileMenuActive = false;
      }

      this.menuClick = false;
    },
    onMenuToggle() {
      this.menuClick = true;

      if (this.isDesktop()) {
        if (this.layoutMode === 'overlay') {
          if (this.mobileMenuActive === true) {
            this.overlayMenuActive = true;
          }

          this.overlayMenuActive = !this.overlayMenuActive;
          this.mobileMenuActive = false;
        } else if (this.layoutMode === 'static') {
          this.staticMenuInactive = !this.staticMenuInactive;
        }
      } else {
        this.mobileMenuActive = !this.mobileMenuActive;
      }

      event.preventDefault();
    },
    onSidebarClick() {
      this.menuClick = true;
    },
    onMenuItemClick(event) {
      if (event.item && !event.item.items) {
        this.overlayMenuActive = false;
        this.mobileMenuActive = false;
      }
    },
    onLayoutChange(layoutMode) {
      this.layoutMode = layoutMode;
    },
    onLayoutColorChange(layoutColorMode) {
      this.layoutColorMode = layoutColorMode;
    },
    addClass(element, className) {
      if (element.classList) element.classList.add(className);
      else element.className += ' ' + className;
    },
    removeClass(element, className) {
      if (element.classList) element.classList.remove(className);
      else
        element.className = element.className.replace(
          new RegExp('(^|\\b)' + className.split(' ').join('|') + '(\\b|$)', 'gi'),
          ' '
        );
    },
    isDesktop() {
      return window.innerWidth >= 992;
    },
    isSidebarVisible() {
      if (this.isDesktop()) {
        if (this.layoutMode === 'static') return !this.staticMenuInactive;
        else if (this.layoutMode === 'overlay') return this.overlayMenuActive;
      }

      return true;
    },
  },
  computed: {
    user() {
      return this.$page.props.auth.user;
    },
    containerClass() {
      return [
        'layout-wrapper',
        {
          'layout-overlay': this.layoutMode === 'overlay',
          'layout-static': this.layoutMode === 'static',
          'layout-static-sidebar-inactive': this.staticMenuInactive && this.layoutMode === 'static',
          'layout-overlay-sidebar-active': this.overlayMenuActive && this.layoutMode === 'overlay',
          'layout-mobile-sidebar-active': this.mobileMenuActive,
          'p-input-filled': this.$primevue.config.inputStyle === 'filled',
          'p-ripple-disabled': this.$primevue.config.ripple === false,
        },
      ];
    },
    logo() {
      return this.layoutColorMode === 'dark' ? 'images/logo-white.svg' : 'images/logo.svg';
    },
  },
  beforeUpdate() {
    if (this.mobileMenuActive) this.addClass(document.body, 'body-overflow-hidden');
    else this.removeClass(document.body, 'body-overflow-hidden');
  },
  components: {
    AppTopBar: AppTopBar,
    AppMenu: AppMenu,
    AppFooter: AppFooter,
    AppConfig: AppConfig,
  },
};
</script>
