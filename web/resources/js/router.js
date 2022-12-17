import Vue from "vue";
import VueRouter from "vue-router";
import store from "./store";

// ページコンポーネントをインポートする
import Setting from "./components/Views/Setting/Setting.vue";
import Login from "./components/Views/Login/Login.vue";
import Mock from "./components/Views/Mock/Mock.vue";
import Onboarding from "./components/Views/Onboarding/Onboarding.vue";
import TodoList from "./components/Views/TodoList/TodoList.vue";
import TodoDetail from "./components/Views/TodoDetail/TodoDetail.vue";
import Schedule from "./components/Views/Schedule/Schedule.vue";
import SystemError from "./components/Views/Error/SystemError.vue";
import NotFound from "./components/Views/Error/NotFound.vue";
import WeeklyReport from "./components/WeeklyReport.vue";

// VueRouterプラグインを使用する
// これによって<RouterView />コンポーネントなどを使うことができる
Vue.use(VueRouter);

// パスとコンポーネントのマッピング
const routes = [
    {
        path: "/debug",
        component: WeeklyReport,
    },
    {
        path: "/login",
        component: Login,
        beforeEnter(to, from, next) {
            if (store.getters["auth/check"]) {
                next("/setting");
            } else {
                next();
            }
        },
    },
];

// VueRouterインスタンスを作成する
const router = new VueRouter({
    mode: "history",
    routes,
});

// VueRouterインスタンスをエクスポートする
// app.jsでインポートするため
export default router;
