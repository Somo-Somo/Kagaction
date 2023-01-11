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
import Sad from "./Feeling/Sadness.vue";
import Tired from "./Feeling/Tired.vue";
import Angry from "./Feeling/Anger.vue";
import Kuyashi from "./Feeling/Kuyashi.vue";
import Lethargic from "./Feeling/Lethargic.vue";
import Glad from "./Feeling/Glad.vue";
import Fun from "./Feeling/Fun.vue";
import Calm from "./Feeling/Calm.vue";
import Happy from "./Feeling/Happy.vue";
import Wakuwaku from "./Feeling/Wakuwaku.vue";
import Moyamoya from "./Feeling/Moyamoya.vue";

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
            sad: {
                draw: Sad.draw,
                fill: Sad.fill,
            },
            tired: {
                draw: Tired.draw,
                fill: Tired.fill,
            },
            angry: {
                draw: Angry.draw,
                fill: Angry.fill,
            },
            kuyashi: {
                draw: Kuyashi.draw,
                fill: Kuyashi.fill,
            },
            lethargic: {
                draw: Lethargic.draw,
                fill: Lethargic.fill,
            },
            glad: {
                draw: Glad.draw,
                fill: Glad.fill,
            },
            fun: {
                draw: Fun.draw,
                fill: Fun.fill,
            },
            calm: {
                draw: Calm.draw,
                fill: Calm.fill,
            },
            happy: {
                draw: Happy.draw,
                fill: Happy.fill,
            },
            wakuwaku: {
                draw: Wakuwaku.draw,
                fill: Wakuwaku.fill,
            },
            moyamoya: {
                draw: Moyamoya.draw,
                fill: Moyamoya.fill,
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
