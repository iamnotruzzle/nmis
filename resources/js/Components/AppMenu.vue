<template>
  <div class="layout-menu-container">
    <div class="flex justify-content-center w-full">
      <div class="flex justify-content-center align-content-center flex-column">
        <div class="flex flex-column align-items-center">
          <img
            v-if="image != null"
            :src="`storage/${image}`"
            class="w-10rem h-10rem rounded-card m-auto"
          />
          <img
            v-else
            src="images/no_profile.png"
            class="w-10rem h-10rem rounded-card"
          />
        </div>
        <div class="flex flex-column align-items-center mt-2">
          <!-- <p class="text-lg my-0 py-0">{{ employeeId }}</p> -->
          <p class="text-2xl text-primary font-bold my-1 py-0">{{ name }}</p>
          <p class="text-lg font-italic my-0 py-0">{{ authLocation }}</p>
        </div>
        <!-- <div class="m-0 p-0">
          <p class="font-italic">{{ email }}</p>
        </div> -->
      </div>
    </div>

    <div class="my-4"></div>

    <AppSubmenu
      :items="model"
      class="layout-menu"
      :root="true"
      @menuitem-click="onMenuItemClick"
    />
  </div>
</template>

<script>
import AppSubmenu from './AppSubmenu';

export default {
  props: {
    model: Array,
  },
  data() {
    return {
      image: null,
      employeeId: null,
      name: null,
      authLocation: null,
    };
  },
  created() {
    // console.log(this.$page.props.auth.user);
    this.image = this.$page.props.user.image;
    this.employeeId = this.$page.props.auth.user.userDetail.employeeid;
    this.name = this.$page.props.auth.user.userDetail.firstname + ' ' + this.$page.props.auth.user.userDetail.lastname;
    this.authLocation = this.$page.props.auth.user.location.location_name.wardname;
  },
  methods: {
    onMenuItemClick(event) {
      this.$emit('menuitem-click', event);
    },
  },
  components: {
    AppSubmenu: AppSubmenu,
  },
};
</script>

<style scoped>
.rounded-card {
  border-radius: 50%;
  /* min-height: 100px;
  min-width: 100px; */
}
</style>
