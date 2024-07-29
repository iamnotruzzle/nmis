<template>
  <app-layout>
    <div
      class="p-d-flex p-ai-center p-jc-center"
      style="height: 100vh; text-align: center"
    >
      <div>
        <i
          class="pi pi-cog pi-spin"
          style="font-size: 4em; color: #888"
        ></i>
        <h1>Page is under maintenance</h1>
        <!-- <p>We are currently performing scheduled maintenance. We will be back online shortly!</p> -->
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
};
</script>

<style scoped>
h1 {
  margin-top: 0.5em;
  font-size: 2em;
  color: #333;
}
p {
  font-size: 1.2em;
  color: #666;
}
</style>
