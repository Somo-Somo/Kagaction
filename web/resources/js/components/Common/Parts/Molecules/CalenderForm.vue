<template>
    <v-menu
        style="width: 100%"
        ref="calenderMenu"
        v-model="calenderMenu"
        :close-on-content-click="false"
        transition="scale-transition"
        offset-y
        min-width="auto"
    >
        <template class="d-flex" v-slot:activator="{ on, attrs }">
            <v-text-field
                class="d-flex align-self-center ma-0 pt-5"
                v-model="calenderDate"
                :label="dateLabel"
                prepend-icon="mdi-calendar"
                readonly
                v-bind="attrs"
                v-on="on"
            >
            </v-text-field>
        </template>
        <v-date-picker v-model="calenderDate" no-title scrollable>
            <v-spacer></v-spacer>
            <v-btn @click="calenderMenu = false" text> キャンセル </v-btn>
            <v-btn color="primary" @click="onClickSave(date)" text>
                保存
            </v-btn>
        </v-date-picker>
    </v-menu>
</template>

<script>
import CancelBtn from "../Atom/Btn/CancelBtn.vue";
import DoneBtn from "../Atom/Btn/DoneBtn.vue";

export default {
    components: {
        CancelBtn,
        DoneBtn,
    },
    data: () => ({
        calenderMenu: false,
    }),
    props: {
        date: {
            type: String,
        },
        dateLabel: {
            type: String,
        },
    },
    computed: {
        calenderDate: {
            get() {
                return this.date;
            },
            set(newVal) {
                this.$emit("onClickSave", newVal);
            },
        },
    },
    methods: {
        onClickSave(date) {
            this.calenderMenu = false;
            this.$emit("onClickSave", date);
        },
        onClickRemove(date) {
            if (date) this.$emit("onClickRemove");
        },
    },
};
</script>
