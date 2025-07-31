<template>
  <app-layout>
    <Head title="NMIS - Wards Inventory" />

    <Toast />

    <div
      class="flex flex-row justify-content-around"
      style="width: 100%"
    >
      <div
        class="card"
        style="width: 100%"
      >
        <DataTable
          class="p-datatable-sm w-full"
          v-model:filters="filters"
          :value="csrInventoryList"
          paginator
          :rows="20"
          :rowsPerPageOptions="[20, 30, 40]"
          dataKey="id"
          sortField="item_desc"
          :sortOrder="1"
          removableSort
          :globalFilterFields="['item_desc']"
          showGridlines
          :loading="isCsrInventoryLoading"
        >
          <template #header>
            <div class="flex flex-wrap align-items-center justify-content-between gap-2">
              <span class="text-xl text-900 font-bold text-primary">CSR INVENTORY</span>
              <div class="flex">
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
            </div>
          </template>
          <template #empty> No data found. </template>
          <template #loading> Loading data. Please wait. </template>
          <!-- <Column
          field="cl1comb"
          header="ID"
          style="width: 5%"
        >
        </Column> -->
          <Column
            field="item_desc"
            header="ITEM"
            sortable
          >
          </Column>
          <!-- breakpoint -->
          <Column
            field="quantity"
            header="QUANTITY"
            sortable
            style="width: 5%; text-align: right"
            :pt="{ headerContent: 'justify-content-end' }"
          >
            <template #body="{ data }">
              <p class="text-right">
                {{ data.quantity }}
              </p>
            </template>
          </Column>
        </DataTable>
      </div>

      <div class="mx-2"></div>

      <div
        class="card"
        style="width: 100%"
      >
        <DataTable
          class="p-datatable-sm w-full"
          v-model:filters="filtersCurrentStock"
          :value="currentStockList"
          paginator
          :rows="20"
          :rowsPerPageOptions="[20, 30, 40]"
          dataKey="id"
          sortField="item_desc"
          :sortOrder="1"
          removableSort
          :globalFilterFields="['item_desc']"
          showGridlines
          :loading="isCurrentStocksLoading"
        >
          <template #header>
            <div class="flex flex-wrap align-items-center justify-content-between gap-2">
              <span class="text-xl text-900 font-bold text-green-500">CURRENT STOCK</span>
              <div class="flex">
                <div class="mr-2">
                  <div class="p-inputgroup">
                    <span class="p-inputgroup-addon">
                      <i class="pi pi-search"></i>
                    </span>
                    <InputText
                      id="searchInput"
                      v-model="filtersCurrentStock['global'].value"
                      placeholder="Search"
                    />
                  </div>
                </div>
              </div>
            </div>
          </template>
          <template #empty> No data found. </template>
          <template #loading> Loading data. Please wait. </template>
          <!-- <Column
          field="cl1comb"
          header="ID"
          style="width: 5%"
        >
        </Column> -->
          <Column
            field="item_desc"
            header="ITEM"
            sortable
          >
          </Column>
          <!-- breakpoint -->
          <Column
            field="quantity"
            header="QUANTITY"
            sortable
            style="width: 5%; text-align: right"
            :pt="{ headerContent: 'justify-content-end' }"
          >
            <!-- <template #body="{ data }">
              <p class="text-right">
                {{ data.quantity }}
              </p>
            </template> -->
          </Column>
        </DataTable>
      </div>
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
// import IconField from 'primevue/iconField';
import Tag from 'primevue/tag';
import moment from 'moment';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';

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
    Link,
    // IconField,
  },
  props: {
    // csrInventory: Object,
  },
  data() {
    return {
      CACHE_CONFIG: {
        CSR_INVENTORY: {
          key: 'CsrInventory_csrInventoryCache',
          timestamp: 'CsrInventory_csrInventoryCacheTimestamp',
        },
        CURRENT_STOCKS: {
          key: 'CsrInventory_currentStockCache',
          timestamp: 'CsrInventory_currentStockCacheTimestamp',
        },
      },
      CACHE_DURATION_MS: 1000 * 60 * 5, // 5 minutes
      // loading states
      isCsrInventoryLoading: false,
      isCurrentStocksLoading: false,

      csrInventoryList: [],
      currentStockList: [],

      error: null,

      // filters
      filters: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
        item_desc: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
      filtersCurrentStock: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
        item_desc: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
    };
  },
  mounted() {
    this.fetchCsrInventory();
    this.fetchCurrentStocks();
  },
  methods: {
    // Generic localStorage cache methods
    getCachedData(cacheType) {
      const config = this.CACHE_CONFIG[cacheType];
      const cached = localStorage.getItem(config.key);
      const timestamp = localStorage.getItem(config.timestamp);

      if (!cached || !timestamp) return null;

      const age = Date.now() - parseInt(timestamp);
      if (age > this.CACHE_DURATION_MS) {
        console.log(`⚠️ Cache expired for ${cacheType}`);
        this.clearCacheData(cacheType);
        return null;
      }

      return JSON.parse(cached);
    },

    setCachedData(cacheType, data) {
      const config = this.CACHE_CONFIG[cacheType];
      localStorage.setItem(config.key, JSON.stringify(data));
      localStorage.setItem(config.timestamp, Date.now().toString());
    },

    clearCacheData(cacheType) {
      const config = this.CACHE_CONFIG[cacheType];
      localStorage.removeItem(config.key);
      localStorage.removeItem(config.timestamp);
    },

    clearAllCaches() {
      Object.keys(this.CACHE_CONFIG).forEach((cacheType) => {
        this.clearCacheData(cacheType);
      });
    },

    async fetchCsrInventory(forceRefresh = false) {
      this.isCsrInventoryLoading = true;
      this.error = null;

      const cached = this.getCachedData('CSR_INVENTORY');

      if (cached && !forceRefresh) {
        // console.log('🟢 Using cached csr inventory from localStorage');
        this.csrInventoryList = cached;
        this.isCsrInventoryLoading = false;
        return;
      }

      try {
        const response = await axios.get('csrinv/getCsrInventory');

        response.data.forEach((e) => {
          this.csrInventoryList.push({
            item_desc: e.item_desc,
            quantity: e.quantity,
          });
        });
        this.setCachedData('CSR_INVENTORY', this.csrInventoryList);
        // console.log('🔵 Fetched fresh ward stocks and cached to localStorage');
      } catch (err) {
        this.error = err.response?.data ?? err.message;
        console.error('❌ Failed to fetch csr inventory:', this.error);
      } finally {
        this.isCsrInventoryLoading = false;
      }
    },
    async fetchCurrentStocks(forceRefresh = false) {
      this.isCurrentStocksLoading = true;
      this.error = null;

      const cached = this.getCachedData('CURRENT_STOCKS');

      if (cached && !forceRefresh) {
        // console.log('🟢 Using cached csr inventory from localStorage');
        this.currentStockList = cached;
        this.isCurrentStocksLoading = false;
        return;
      }

      try {
        const response = await axios.get('csrinv/getCurrentStocks');

        response.data.forEach((e) => {
          this.currentStockList.push({
            item_desc: e.item_desc,
            quantity: e.quantity,
          });
        });
        this.setCachedData('CURRENT_STOCKS', this.currentStockList);
        // console.log('🔵 Fetched fresh ward stocks and cached to localStorage');
      } catch (err) {
        this.error = err.response?.data ?? err.message;
        console.error('❌ Failed to fetch current stocks:', this.error);
      } finally {
        this.isCurrentStocksLoading = false;
      }
    },

    tzone(date) {
      return moment.tz(date, 'Asia/Manila').format('L');
    },
  },
};
</script>
<style scoped>
/* Remove arrow for input type number */
/* Chrome, Safari, Edge, Opera */
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

/* Firefox */
input[type='number'] {
  -moz-appearance: textfield;
}
/* END Remove arrow for input type number */
</style>
