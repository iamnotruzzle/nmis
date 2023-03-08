<template>
    <app-layout>
        <div class="grid">
            <div class="col-12">
                <div class="card">
                    <h5>Empty Page</h5>
                    <p>
                        {{ word }}
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
    data() {
        return {
            word: "SUPPPP",
        };
    },
};
</script>
