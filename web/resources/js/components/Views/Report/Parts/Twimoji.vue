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
import Great from "./TwimojiDraw/Great.vue";
import Good from "./TwimojiDraw/Good.vue";
import OK from "./TwimojiDraw/OK.vue";
import Bad from "./TwimojiDraw/Bad.vue";
import Worse from "./TwimojiDraw/Worse.vue";
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
