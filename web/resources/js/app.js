import "./bootstrap";
import Vue from "vue";
import router from "./router";
import store from "./store";
import App from "./App.vue";
import WeeklyReport from "./components/WeeklyReport.vue";
import Vuetify from "vuetify";
import "vuetify/dist/vuetify.min.css";
import "@mdi/font/css/materialdesignicons.css";
import "material-design-icons-iconfont/dist/material-design-icons.css";

Vue.use(Vuetify);

Vue.component(
    "weekly-report",
    require("./components/WeeklyReport.vue").default
);

const app = new Vue({
    el: "#app",
    router,
    components: {
        WeeklyReport,
    },
});
