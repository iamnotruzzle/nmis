<template>
  <app-layout>
    <div class="surface-ground">
      <div class="grid">
        <div class="col-12 md:col-6 lg:col-3">
          <div class="surface-card shadow-2 p-3 border-round h-full flex flex-column justify-between">
            <div class="flex justify-content-between mb-3">
              <div>
                <span class="block text-500 font-medium mb-3">Patient charges today</span>
                <div class="text-900 font-medium text-xl">
                  {{ patient_charges_total.toLocaleString('en-PH', { style: 'currency', currency: 'PHP' }) }}
                </div>
              </div>
              <div
                class="flex align-items-center justify-content-center bg-blue-100 border-round"
                style="width: 2.5rem; height: 2.5rem"
              >
                <i class="pi pi-credit-card text-blue-500 text-xl"></i>
              </div>
            </div>
            <span class="text-500">Total items charged to patients today</span>
          </div>
        </div>

        <div class="col-12 md:col-6 lg:col-3">
          <Link
            href="wardinv"
            class="no-underline text-inherit block h-full"
          >
            <div
              class="surface-card shadow-2 p-3 border-round h-full flex flex-column justify-between transition-all duration-200 hover:border-r-4 hover:border-green-500 cursor-pointer"
              style="border-right: 4px solid transparent"
            >
              <div class="flex justify-content-between mb-3">
                <div>
                  <span class="block text-500 font-medium mb-3">Low stock items</span>
                  <div class="text-900 font-medium text-xl">{{ low_stock_items }}</div>
                </div>
                <div
                  class="flex align-items-center justify-content-center bg-orange-100 border-round"
                  style="width: 2.5rem; height: 2.5rem"
                >
                  <i class="pi pi-exclamation-triangle text-orange-500 text-xl"></i>
                </div>
              </div>
              <span class="text-500">Need restocking (below reorder level)</span>
            </div>
          </Link>
        </div>

        <div class="col-12 md:col-6 lg:col-3">
          <Link
            href="requeststocks?page=1&status=FILLED"
            class="no-underline text-inherit block h-full"
          >
            <div
              class="surface-card shadow-2 p-3 border-round h-full flex flex-column justify-between transition-all duration-200 hover:border-r-4 hover:border-green-500 cursor-pointer"
              style="border-right: 4px solid transparent"
            >
              <div class="flex justify-content-between mb-3">
                <div>
                  <span class="block text-500 font-medium mb-3">Ready to Received</span>
                  <div class="text-900 font-medium text-xl">{{ ready_to_received }}</div>
                </div>
                <div
                  class="flex align-items-center justify-content-center bg-green-100 border-round"
                  style="width: 2.5rem; height: 2.5rem"
                >
                  <i class="pi pi-inbox text-green-500 text-xl"></i>
                </div>
              </div>
              <span class="text-500">Marked as filled by Central Supply</span>
            </div>
          </Link>
        </div>

        <div class="col-12 md:col-6 lg:col-3">
          <Link
            href="wardinv"
            class="no-underline text-inherit block h-full"
          >
            <div
              class="surface-card shadow-2 p-3 border-round h-full flex flex-column justify-between transition-all duration-200 hover:border-r-4 hover:border-green-500 cursor-pointer"
              style="border-right: 4px solid transparent"
            >
              <div class="flex justify-content-between mb-3">
                <div>
                  <span class="block text-500 font-medium mb-3">Expiring Soon</span>
                  <div class="text-900 font-medium text-xl">{{ expiring_soon }}</div>
                </div>
                <div
                  class="flex align-items-center justify-content-center bg-purple-100 border-round"
                  style="width: 2.5rem; height: 2.5rem"
                >
                  <i class="pi pi-clock text-purple-500 text-xl"></i>
                </div>
              </div>
              <span class="text-500">Within the next 30 days</span>
            </div>
          </Link>
        </div>
      </div>
    </div>
  </app-layout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

export default {
  components: {
    AppLayout,
    Link,
  },
  props: {
    patient_charges_total: Number,
    low_stock_items: Number,
    ready_to_received: Number,
    expiring_soon: Number,
  },
  data() {
    return {
      //   patient_charges_total: null,
    };
  },
  mounted() {},
};
</script>
