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
        isDiff: [
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
            ,
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
            let isDiff = this.isDiff[this.rankPlace];
            console.info(draw);
            for (let i = 0; i < draw.length; i++) {
                if (i === 0) {
                    connectedD += draw[i];
                } else {
                    if (this.rankPlace === 0) {
                        connectedD += draw[i];
                    } else if (this.rankPlace === 1) {
                        if (draw[i - 3] === "H") {
                            connectedD += this.diffRow + draw[i];
                            isDiff = [false, false, true, false];
                            draw = draw.slice[i - 1];
                        } else {
                            connectedD += isDiff[(i + 3) % 4]
                                ? this.diffRow + draw[i]
                                : draw[i];
                        }

                        if (numOfPath === 3) {
                            console.info([i]);
                            console.info(isDiff[(i + 3) % 4]);
                            console.info(connectedD);
                        }
                    } else if (this.rankPlace === 2) {
                        if (draw[i - 3] === "H") {
                            connectedD += draw[i];
                            isDiff = [true, false, false, false];
                        } else {
                            connectedD += isDiff[(i + 3) % 4]
                                ? this.diffColumn + draw[i]
                                : draw[i];
                        }
                    } else if (this.rankPlace === 3) {
                        if (isDiff[(i + 3) % 4]) {
                            connectedD +=
                                (i + 3) % 4 === 0
                                    ? this.diffRow + draw[i]
                                    : this.diffColumn + draw[i];
                        } else {
                            connectedD += draw[i];
                        }
                    } else if (this.rankPlace === 4) {
                        if (draw[i - 3] === "H") {
                            connectedD += draw[i];
                            isDiff = [false, false, true, false];
                        } else if (numOfPath !== 0 && i === draw.length) {
                            connectedD += this.diffColumn * 2 + draw[i];
                        } else {
                            connectedD += isDiff[(i + 3) % 4]
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
