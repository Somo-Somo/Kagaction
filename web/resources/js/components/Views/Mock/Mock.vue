<template>
    <div>
        <h1>aaa</h1>
    </div>
</template>

<script>
import liff from "@line/liff";

export default {
    name: "App",
    data() {
        return {
            userName: undefined,
            isInClient: undefined,
            isLoggedIn: false,
        };
    },
    created() {
        liff.init({
            liffId: "1657690379-MG15W7yl",
        }).then(() => {
            this.isLoggedIn = liff.isLoggedIn();
            if (!liff.isInClient() && !liff.isLoggedIn()) {
                liff.login();
            } else {
                this.isInClient = liff.isInClient();
                liff.getProfile().then((profile) => {
                    this.userName = profile.displayName;
                });
            }
        });
    },
    methods: {},
};
</script>

<style>
#app {
    font-family: Avenir, Helvetica, Arial, sans-serif;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    text-align: center;
    color: #2c3e50;
    margin-top: 60px;
}
.btn-square {
    display: inline-block;
    padding: 0.5em 1em;
    text-decoration: none;
    background: #42b983;
    color: #fff;
    border-bottom: solid 4px #627295;
    border-radius: 3px;
}
.btn-square:active {
    -webkit-transform: translateY(4px);
    transform: translateY(4px);
    border-bottom: none;
}
</style>
