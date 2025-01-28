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
import { router } from '@inertiajs/vue3';
import { clearConfigCache } from 'prettier';

export default {
  components: {
    // Echo,
  },
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
              icon: 'co-chart-line',
              to: 'admindashboard',
              prefix: 'admindashboard',
              comp: 'Admin/Dashboard/Index',
            },
            {
              label: 'Dashboard',
              icon: 'co-chart-line',
              to: 'csrdashboard',
              prefix: 'csrdashboard',
              comp: 'Csr/Dashboard/Index',
            },
            {
              label: 'Stock balance',
              icon: 'la-boxes-solid',
              to: 'csrstockbal',
              prefix: 'csrstockbal',
              comp: 'Balance/Index',
            },
            // {
            //   label: 'Dashboard',
            //   icon: 'co-chart-line',
            //   to: 'warddashboard',
            //   prefix: 'warddashboard',
            //   comp: 'Ward/Dashboard/Index',
            // },
            {
              label: 'Reports',
              icon: 'si-microsoftexcel',
              to: 'csrreports',
              prefix: 'csrreports',
              comp: 'Csr/Reports/Index',
            },
            {
              label: 'RIS',
              icon: 'md-handshake-sharp',
              to: 'issueitems',
              prefix: 'issueitems',
              comp: 'Csr/IssueItems/Index',
              badge: null,
            },
            {
              label: 'Wards Inventory',
              icon: 'bi-boxes',
              to: 'wardsinv',
              prefix: 'wardsinv',
              comp: 'Csr/WardsInventory/Index',
              badge: null,
            },
            {
              label: 'Returned Items',
              icon: 'gi-return-arrow',
              to: 'returneditems',
              prefix: 'returneditems',
              comp: 'Csr/ReturnedItems/Index',
              badge: null,
            },
            {
              label: 'Patients',
              icon: 'fa-user-injured',
              to: 'wardspatients',
              prefix: 'wardspatients',
              comp: 'Wards/Patients/Index',
            },
            {
              label: 'Transfer stocks',
              icon: 'oi-arrow-switch',
              to: 'transferstock',
              prefix: 'transferstock',
              comp: 'Wards/TransferStock/Index',
            },
            {
              label: 'CSR Inventory',
              icon: 'bi-boxes',
              to: 'csrinv',
              prefix: 'csrinv',
              comp: 'Wards/CsrInventory/Index',
              badge: null,
            },
            {
              label: 'Reports',
              icon: 'si-microsoftexcel',
              to: 'wardreports',
              prefix: 'wardreports',
              comp: 'Ward/Reports/Index',
            },
            {
              label: 'Users',
              icon: 'la-users-solid',
              to: 'users',
              prefix: 'users',
              comp: 'Users/Index',
            },
            {
              label: 'Packages',
              icon: 'oi-package',
              to: 'packages',
              prefix: 'packages',
              comp: 'Tools/Packages/Index',
            },
            {
              label: 'ECG',
              icon: 'si-microsoftexcel',
              to: 'ecgreports',
              prefix: 'ecgreports',
              comp: 'Ward/Census/ECG/Index',
            },
          ],
        },
        {
          label: 'Library',
          items: [
            {
              label: 'Categories',
              icon: 'co-border-all',
              to: 'categories',
              prefix: 'categories',
              comp: 'Csr/Inventory/Categories/Index',
            },
            {
              label: 'Items',
              icon: 'la-capsules-solid',
              to: 'items',
              prefix: 'items',
              comp: 'Csr/Inventory/Items/Index',
            },
            {
              label: 'Stock balance',
              icon: 'la-boxes-solid',
              to: 'stockbal',
              prefix: 'stockbal',
              comp: 'Balance/Index',
            },
            {
              label: 'Inventory',
              icon: 'bi-journal-check',
              to: 'requeststocks',
              prefix: 'requeststocks',
              comp: 'Wards/RequestStocks/Index',
            },
            {
              label: 'FAQ',
              icon: 'bi-question-circle',
              to: 'faq',
              prefix: 'faq',
              comp: 'Wards/Faq/Index',
            },
            {
              label: 'Deliveries',
              icon: 'la-truck-moving-solid',
              to: 'csrstocks',
              prefix: 'csrstocks',
              comp: 'Csr/Inventory/Stocks/Index',
            },
          ],
        },
        // {
        //   label: 'Tools',
        //   items: [
        //     {
        //       label: 'Manual report',
        //       icon: 'si-microsoftexcel',
        //       to: 'csrmanualreports',
        //       prefix: 'csrmanualreports',
        //       comp: 'Csr/ManualReport/Index',
        //     },
        //     {
        //       label: 'Manual report',
        //       icon: 'si-microsoftexcel',
        //       to: 'wardsmanualreports',
        //       prefix: 'wardsmanualreports',
        //       comp: 'Wards/ManualReport/Index',
        //     },
        //   ],
        // },
      ],
    };
  },
  watch: {
    $route() {
      this.menuActive = false;
      this.$toast.removeAllGroups();
    },
  },
  mounted() {
    // window.Echo.channel('request').listen('RequestStock', (args) => {
    //   //   console.log(args);

    //   //   use foreach instead of filter
    //   if (this.$page.props.user.designation == 'csr') {
    //     this.menu[0].items = this.menu[0].items.filter(function (obj) {
    //       //   console.log(obj);
    //       if (obj.to == 'issueitems') {
    //         obj.badge = args;
    //       }
    //     });
    //   }
    // });

    this.removeRoutesIfNonAdmin();

    this.$nextTick(() => {
      this.onload();
    });
  },
  methods: {
    removeRoutesIfNonAdmin() {
      // admin
      if (this.$page.props.user.designation == 'admin') {
        this.menu.splice(1, 1);
        this.menu[0].items = [];
        this.menu[0].items.push(
          {
            label: 'Dashboard',
            icon: 'co-chart-line',
            to: 'admindashboard',
            prefix: 'admindashboard',
            comp: 'Admin/Dashboard/Index',
          },
          {
            label: 'Dashboard',
            icon: 'co-chart-line',
            to: 'csrdashboard',
            prefix: 'csrdashboard',
            comp: 'Csr/Dashboard/Index',
          },
          {
            label: 'Categories',
            icon: 'co-border-all',
            to: 'categories',
            prefix: 'categories',
            comp: 'Csr/Inventory/Categories/Index',
          },
          {
            label: 'Items',
            icon: 'la-capsules-solid',
            to: 'items',
            prefix: 'items',
            comp: 'Csr/Inventory/Items/Index',
          },
          {
            label: 'Packages',
            icon: 'oi-package',
            to: 'packages',
            prefix: 'packages',
            comp: 'Tools/Packages/Index',
          },
          {
            label: 'Users',
            icon: 'la-users-solid',
            to: 'users',
            prefix: 'users',
            comp: 'Users/Index',
          }
        );
      }
      //   if (this.$page.props.user.designation == 'admin') {
      //     this.menu[1].items = this.menu[1].items.filter(function (obj) {
      //       return obj.to !== 'csrmanualreports';
      //     });
      //   }
      //   if (this.$page.props.user.designation == 'admin') {
      //     this.menu[1].items = this.menu[1].items.filter(function (obj) {
      //       return obj.to !== 'wardsmanualreports';
      //     });
      //   }
      // end admin

      // csr
      // if auth users designation is csr, remove requeststocks page in the array
      if (this.$page.props.user.designation == 'csr') {
        this.menu[0].items = this.menu[0].items.filter(function (obj) {
          return obj.to !== 'dashboard';
        });
      }
      if (this.$page.props.user.designation == 'csr') {
        this.menu[1].items = this.menu[1].items.filter(function (obj) {
          return obj.to !== 'requeststocks';
        });
      }
      if (this.$page.props.user.designation == 'csr') {
        this.menu[0].items = this.menu[0].items.filter(function (obj) {
          return obj.to !== 'wardspatients';
        });
      }
      if (this.$page.props.user.designation == 'csr') {
        this.menu[0].items = this.menu[0].items.filter(function (obj) {
          return obj.to !== 'csrinv';
        });
      }
      if (this.$page.props.user.designation == 'csr') {
        this.menu[0].items = this.menu[0].items.filter(function (obj) {
          return obj.to !== 'transferstock';
        });
      }
      if (this.$page.props.user.designation == 'csr') {
        this.menu[1].items = this.menu[1].items.filter(function (obj) {
          return obj.to !== 'stockbal';
        });
      }
      if (this.$page.props.user.designation == 'csr') {
        this.menu[0].items = this.menu[0].items.filter(function (obj) {
          return obj.to !== 'users';
        });
      }
      if (this.$page.props.user.designation == 'csr') {
        this.menu[0].items = this.menu[0].items.filter(function (obj) {
          return obj.to !== 'packages';
        });
      }
      if (this.$page.props.user.designation == 'csr') {
        this.menu[0].items = this.menu[0].items.filter(function (obj) {
          return obj.to !== 'wardreports';
        });
      }
      if (this.$page.props.user.designation == 'csr') {
        this.menu[0].items = this.menu[0].items.filter(function (obj) {
          return obj.to !== 'ecgreports';
        });
      }
      if (this.$page.props.user.designation == 'csr') {
        this.menu[0].items = this.menu[0].items.filter(function (obj) {
          return obj.to !== 'admindashboard';
        });
      }

      //   if (this.$page.props.user.designation == 'csr') {
      //     this.menu[2].items = this.menu[2].items.filter(function (obj) {
      //       return obj.to !== 'wardsmanualreports';
      //     });
      //   }
      // end csr

      // ward
      if (this.$page.props.user.designation == 'ward') {
        this.menu[1].items = this.menu[1].items.filter(function (obj) {
          return obj.to !== 'categories' && obj.to !== 'items' && obj.to !== 'csrstocks';
        });
      }
      if (this.$page.props.user.designation == 'ward') {
        this.menu[0].items = this.menu[0].items.filter(function (obj) {
          return obj.to !== 'csrstockbal';
        });
      }
      if (this.$page.props.user.designation == 'ward') {
        this.menu[0].items = this.menu[0].items.filter(function (obj) {
          return obj.to !== 'issueitems';
        });
      }
      if (this.$page.props.user.designation == 'ward') {
        this.menu[0].items = this.menu[0].items.filter(function (obj) {
          return obj.to !== 'users';
        });
      }
      if (this.$page.props.user.designation == 'ward') {
        this.menu[0].items = this.menu[0].items.filter(function (obj) {
          return obj.to !== 'packages';
        });
      }
      if (this.$page.props.user.designation == 'ward') {
        this.menu[0].items = this.menu[0].items.filter(function (obj) {
          return obj.to !== 'csrreports';
        });
      }
      if (this.$page.props.user.designation == 'ward') {
        this.menu[0].items = this.menu[0].items.filter(function (obj) {
          return obj.to !== 'wardsinv';
        });
      }
      if (this.$page.props.user.designation == 'ward') {
        this.menu[0].items = this.menu[0].items.filter(function (obj) {
          return obj.to !== 'returneditems';
        });
      }
      if (this.$page.props.user.designation == 'ward') {
        this.menu[0].items = this.menu[0].items.filter(function (obj) {
          return obj.to !== 'admindashboard';
        });
      }
      if (this.$page.props.user.designation == 'ward') {
        this.menu[0].items = this.menu[0].items.filter(function (obj) {
          return obj.to !== 'csrdashboard';
        });
      }
      //   if (this.$page.props.user.designation == 'ward') {
      //     this.menu[2].items = this.menu[2].items.filter(function (obj) {
      //       return obj.to !== 'csrmanualreports';
      //     });
      //   }
      // end ward
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
