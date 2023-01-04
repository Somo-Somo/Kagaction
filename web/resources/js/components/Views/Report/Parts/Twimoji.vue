<template>
    <g>
        <path
            id="Vector_21"
            v-for="(draw, index) in connectedDraws"
            :key="index"
            :fill="twimoji[twimojiType].fill[index]"
            :d="draw.d"
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
                path: Great.draw,
                fill: ["#FFCC4D", "#664500", "white"],
            },
            good: {
                path: Good.draw,
                fill: ["#FFCC4D", "#664500", "#664500", "#664500"],
            },
            ok: {
                path: OK.draw,
                fill: ["#FFCC4D", "#664500", "#664500", "#664500"],
            },
            bad: {
                path: Bad.draw,
                fill: ["#FFCC4D", "#664500", "#664500", "#664500"],
            },
            worse: {
                path: Worse.draw,
                fill: ["#FFCC4D", "#664500"],
            },
        },
        isDiff: [
            [
                // 全て1個目がfalse,２個目からこれらのループ
                // 5つ目だけループが終わった後に追加で
                // 1個目false,false
                // 2個目true,false,
                // 3個目true,false,
                // 4個目true,false になる
                [false, false, false, false],
                [true, false, false, false],
                [false, false, true, false],
                [true, false, true, false],
                [false, false, true, false],
            ],
        ],
        diffRow: 224,
        diffColumn: 84,
        connectedDraws: [],
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
    methods: {
        connectedD(draw, numOfPath) {
            let connectedD = "";
            for (let i = 0; i < draw.length; i++) {
                if (i === 0) {
                    connectedD += draw[i];
                } else {
                    if (this.rankPlace === 0) {
                        connectedD += draw[i];
                    } else if (this.rankPlace === 1) {
                        connectedD +=
                            (i + 3) % 4 === 0
                                ? this.diffRow + draw[i]
                                : draw[i];
                    } else if (this.rankPlace === 2 || this.rankPlace === 3) {
                        connectedD +=
                            (i + 3) % 4 === 2
                                ? this.diffColumn + draw[i]
                                : draw[i];
                    } else if (this.rankPlace === 4) {
                        if ((i + 3) % 4 === 0) {
                            connectedD += this.diffRow + draw[i];
                        } else if ((i + 3) % 4 === 2) {
                            connectedD += this.diffColumn + draw[i];
                        } else {
                            connectedD = draw[i];
                        }
                    } else if (this.rankPlace === 5) {
                        if (numOfPath !== 0 && i === draw.length) {
                            connectedD += this.diffColumn * 2 + draw[i];
                        } else {
                            connectedD +=
                                (i + 3) % 4 === 2
                                    ? this.diffColumn * 2 + draw[i]
                                    : draw[i];
                        }
                    }
                }
            }
            return connectedD;
        },
        loopPath() {
            const connectedDs = [];
            for (
                let i = 0;
                i < this.twimoji[this.twimojiType].path.length;
                i++
            ) {
                connectedDs.push({
                    d: this.connectedD(
                        this.twimoji[this.twimojiType].path[i],
                        i
                    ),
                });
            }
            return connectedDs;
        },
    },
    async mounted() {
        this.connectedDraws = await this.loopPath();
    },
};
</script>
