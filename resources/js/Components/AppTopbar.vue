<template>
    <div class="layout-topbar">
        <Link href="/" class="layout-topbar-logo"> TEMPLATE </Link>
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

        <ul
            v-if="topbarMenuActive"
            class="layout-topbar-menu lg:flex origin-top"
        >
            <li>
                <button class="p-link layout-topbar-button">
                    <i class="pi pi-calendar"></i>
                    <span>Events</span>
                </button>
            </li>
            <li>
                <Button
                    class="p-button-link p-link layout-topbar-button"
                    @click="goToSettings"
                >
                    <i class="pi pi-cog"></i>
                    <span>Settings</span>
                </Button>
            </li>
            <li>
                <Button class="p-button-link p-link layout-topbar-button">
                    <i class="pi pi-user"></i>
                    <span>Profile</span>
                </Button>
            </li>
        </ul>

        <ul v-else class="layout-topbar-menu hidden lg:flex origin-top">
            <li>
                <button class="p-link layout-topbar-button">
                    <i class="pi pi-calendar"></i>
                    <span>Events</span>
                </button>
            </li>
            <li>
                <Button
                    class="p-button-link p-link layout-topbar-button"
                    @click="goToSettings"
                >
                    <i class="pi pi-cog"></i>
                    <span>Settings</span>
                </Button>
            </li>
            <li>
                <Button class="p-button-link p-link layout-topbar-button">
                    <i class="pi pi-user"></i>
                    <span>Profile</span>
                </Button>
            </li>
        </ul>
    </div>
</template>

<script>
import { Link } from "@inertiajs/inertia-vue3";
import Button from "primevue/button";

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
    methods: {
        onMenuToggle(event) {
            this.$emit("menu-toggle", event);
        },
        // onTopbarMenuToggle(event) {
        //     this.$emit("topbar-menu-toggle", event);
        // },
        onTopBarMenuToggle(event) {
            this.topbarMenuActive = !this.topbarMenuActive;
        },
        goToSettings() {
            this.$inertia.get(this.route("profile.show"));
        },
        bindOutsideClickListener() {
            if (!this.outsideClickListener) {
                this.outsideClickListener = (event) => {
                    if (isOutsideClicked(event)) {
                        topbarMenuActive.value = false;
                    }
                };
                document.addEventListener("click", this.outsideClickListener);
            }
        },
        unbindOutsideClickListener() {
            if (this.outsideClickListener) {
                document.removeEventListener("click", outsideClickListener);
                this.outsideClickListener = null;
            }
        },
        isOutsideClicked(event) {
            if (!this.topbarMenuActive) return;
            const sidebarEl = document.querySelector(".layout-topbar-menu");
            const topbarEl = document.querySelector(
                ".layout-topbar-menu-button"
            );
            return !(
                sidebarEl.isSameNode(event.target) ||
                sidebarEl.contains(event.target) ||
                topbarEl.isSameNode(event.target) ||
                topbarEl.contains(event.target)
            );
        },
    },
};
</script>
