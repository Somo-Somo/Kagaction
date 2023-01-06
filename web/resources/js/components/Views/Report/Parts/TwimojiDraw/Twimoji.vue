<template>
    <g>
        <path
            id="Vector_21"
            v-for="(draw, index) in twimoji[twimojiType].draw[rankPlace]"
            :key="index"
            :fill="twimoji[twimojiType].fill[index]"
            :d="draw"
        />
    </g>
</template>

<script>
import Great from "./Condition/Great.vue";
import Good from "./Condition/Good.vue";
import OK from "./Condition/OK.vue";
import Bad from "./Condition/Bad.vue";
import Worse from "./Condition/Worse.vue";
import Anxious from "./Feeling/Anxious.vue";
import Hard from "./Feeling/Hard.vue";
import Sadness from "./Feeling/Sadness.vue";
export default {
    data: () => ({
        twimoji: {
            great: {
                draw: Great.draw,
                fill: Great.fill,
            },
            good: {
                draw: Good.draw,
                fill: Good.fill,
            },
            ok: {
                draw: OK.draw,
                fill: OK.fill,
            },
            bad: {
                draw: Bad.draw,
                fill: Bad.fill,
            },
            worse: {
                draw: Worse.draw,
                fill: Worse.fill,
            },
            anxious: {
                draw: Anxious.draw,
                fill: Anxious.fill,
            },
            hard: {
                draw: Hard.draw,
                fill: Hard.fill,
            },
            sadness: {
                draw: Sadness.draw,
                fill: Sadness.fill,
            },
        },
    }),
    props: {
        rankPlace: {
            type: Number,
        },
        twimojiType: {
            type: String,
        },
    },
    computed: {},
    methods: {},
    mounted() {},
};
</script>
