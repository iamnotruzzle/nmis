<template>
    <app-layout>
        <div class="grid">
            <div class="col-12">
                <div class="card">
                    <h5>Empty Page</h5>
                    <p>
                        {{ userList }}
                    </p>
                </div>
            </div>
        </div>
    </app-layout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout.vue";

export default {
    components: {
        AppLayout,
    },
    props: {
        users: Object,
    },
    data() {
        return {
            word: "USERS",
            userList: [],
        };
    },
    mounted() {
        console.log(this.users);
        this.userList = [...this.users.data];
    },
};
</script>
