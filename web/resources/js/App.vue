<template>
    <v-app id="inspire">
        <p>{{ user }}</p>
    </v-app>
</template>

<script>
export default {
    data() {
        return {
            user: null,
            error: null,
        };
    },
    created() {
        this.user = axios.get("/api/".this.$route.path).catch((err) => {
            this.error = err;
        });
        console.info(this.data);
    },
};
</script>
