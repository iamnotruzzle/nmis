<template>
  <div class="layout-menu-container">
    <div class="mb-4">
      <span class="font-semibold">
        {{ $page.props.auth.user.location.wardname }}
      </span>
    </div>
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
  mounted() {},
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

<style scoped></style>
