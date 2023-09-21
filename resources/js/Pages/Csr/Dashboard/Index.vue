<template>
  <app-layout>
    <div class="surface-ground">
      <div class="grid">
        <div class="col-12 md:col-6 lg:col-3">
          <div class="surface-card shadow-2 p-3 border-round">
            <div class="flex justify-content-between mb-3">
              <div>
                <span class="block text-500 font-medium mb-3">Completed request</span>
                <div>
                  <p>
                    <span class="text-900 font-medium text-xl">{{ completed_request_week_container }}&nbsp</span>
                    <span>this week</span>
                  </p>
                </div>
              </div>
              <div
                class="flex align-items-center justify-content-center bg-blue-100 border-round"
                style="width: 2.5rem; height: 2.5rem"
              >
                <i class="pi pi-send text-blue-500 text-xl"></i>
              </div>
            </div>
            <span class="text-green-500 font-medium">{{ completed_request_today_container }}&nbsp</span>
            <span class="text-500">today</span>
          </div>
        </div>
        <div class="col-12 md:col-6 lg:col-3">
          <div class="surface-card shadow-2 p-3 border-round">
            <div class="flex justify-content-between mb-3">
              <div>
                <span class="block text-500 font-medium mb-3">Revenue</span>
                <div class="text-900 font-medium text-xl">₱2.100</div>
              </div>
              <div
                class="flex align-items-center justify-content-center bg-orange-100 border-round"
                style="width: 2.5rem; height: 2.5rem"
              >
                <i class="pi pi-map-marker text-orange-500 text-xl"></i>
              </div>
            </div>
            <span class="text-green-500 font-medium">%52+ </span>
            <span class="text-500">since last week</span>
          </div>
        </div>
        <div class="col-12 md:col-6 lg:col-3">
          <div class="surface-card shadow-2 p-3 border-round">
            <div class="flex justify-content-between mb-3">
              <div>
                <span class="block text-500 font-medium mb-3">Customers</span>
                <div class="text-900 font-medium text-xl">28441</div>
              </div>
              <div
                class="flex align-items-center justify-content-center bg-cyan-100 border-round"
                style="width: 2.5rem; height: 2.5rem"
              >
                <i class="pi pi-inbox text-cyan-500 text-xl"></i>
              </div>
            </div>
            <span class="text-green-500 font-medium">520 </span>
            <span class="text-500">newly registered</span>
          </div>
        </div>
        <div class="col-12 md:col-6 lg:col-3">
          <div class="surface-card shadow-2 p-3 border-round">
            <div class="flex justify-content-between mb-3">
              <div>
                <span class="block text-500 font-medium mb-3">Comments</span>
                <div class="text-900 font-medium text-xl">152 Unread</div>
              </div>
              <div
                class="flex align-items-center justify-content-center bg-purple-100 border-round"
                style="width: 2.5rem; height: 2.5rem"
              >
                <i class="pi pi-comment text-purple-500 text-xl"></i>
              </div>
            </div>
            <span class="text-green-500 font-medium">85 </span>
            <span class="text-500">responded</span>
          </div>
        </div>
      </div>
    </div>
  </app-layout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';

export default {
  components: {
    AppLayout,
  },
  props: {
    completed_request_this_week: Number,
    completed_request_today: Number,
  },
  data() {
    return {
      completed_request_week_container: null,
      completed_request_today_container: null,
    };
  },
  mounted() {
    console.log(this.completed_request_this_week);
    this.storeCompletedRequestsCount();
  },
  methods: {
    storeCompletedRequestsCount() {
      this.completed_request_week_container = this.completed_request_this_week;
      this.completed_request_today_container = this.completed_request_today;
    },
  },
};
</script>
