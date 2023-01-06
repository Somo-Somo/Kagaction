<template>
    <g id="Condition Rank">
        <path
            id="Condition"
            d="M691.883 111.504C695.375 111.504 698.363 110.172 700.631 107.544L697.247 103.512C695.987 104.88 694.295 105.96 692.135 105.96C688.355 105.96 685.907 102.864 685.907 97.536C685.907 92.316 688.751 89.22 692.207 89.22C694.115 89.22 695.519 90.084 696.887 91.308L700.235 87.204C698.399 85.332 695.591 83.712 692.099 83.712C685.295 83.712 679.319 88.824 679.319 97.752C679.319 106.824 685.079 111.504 691.883 111.504ZM712.943 111.504C718.127 111.504 722.951 107.544 722.951 100.74C722.951 93.972 718.127 90.012 712.943 90.012C707.723 90.012 702.899 93.972 702.899 100.74C702.899 107.544 707.723 111.504 712.943 111.504ZM712.943 106.32C710.531 106.32 709.451 104.16 709.451 100.74C709.451 97.356 710.531 95.196 712.943 95.196C715.319 95.196 716.399 97.356 716.399 100.74C716.399 104.16 715.319 106.32 712.943 106.32ZM726.973 111H733.381V97.32C734.605 96.132 735.469 95.484 736.909 95.484C738.529 95.484 739.285 96.276 739.285 99.12V111H745.693V98.328C745.693 93.216 743.785 90.012 739.321 90.012C736.549 90.012 734.461 91.416 732.769 93.072H732.661L732.193 90.516H726.973V111ZM758.012 111.504C759.956 111.504 761.936 110.46 763.376 109.02H763.52L763.988 111H769.208V82.236H762.8V89.076L763.016 92.136C761.684 90.84 760.352 90.012 758.084 90.012C753.836 90.012 749.624 94.044 749.624 100.74C749.624 107.436 752.9 111.504 758.012 111.504ZM759.704 106.248C757.508 106.248 756.212 104.592 756.212 100.668C756.212 96.888 757.832 95.232 759.596 95.232C760.676 95.232 761.828 95.556 762.8 96.456V104.52C761.9 105.852 760.928 106.248 759.704 106.248ZM774.363 111H780.771V90.516H774.363V111ZM777.567 87.672C779.691 87.672 781.131 86.376 781.131 84.36C781.131 82.38 779.691 81.084 777.567 81.084C775.407 81.084 774.003 82.38 774.003 84.36C774.003 86.376 775.407 87.672 777.567 87.672ZM793.994 111.504C796.082 111.504 797.558 111.072 798.638 110.748L797.63 106.104C797.126 106.284 796.406 106.5 795.758 106.5C794.246 106.5 793.166 105.6 793.166 103.332V95.52H797.99V90.516H793.166V85.08H787.874L787.154 90.516L783.986 90.768V95.52H786.758V103.404C786.758 108.228 788.846 111.504 793.994 111.504ZM801.961 111H808.369V90.516H801.961V111ZM805.165 87.672C807.289 87.672 808.729 86.376 808.729 84.36C808.729 82.38 807.289 81.084 805.165 81.084C803.005 81.084 801.601 82.38 801.601 84.36C801.601 86.376 803.005 87.672 805.165 87.672ZM822.42 111.504C827.604 111.504 832.428 107.544 832.428 100.74C832.428 93.972 827.604 90.012 822.42 90.012C817.2 90.012 812.376 93.972 812.376 100.74C812.376 107.544 817.2 111.504 822.42 111.504ZM822.42 106.32C820.008 106.32 818.928 104.16 818.928 100.74C818.928 97.356 820.008 95.196 822.42 95.196C824.796 95.196 825.876 97.356 825.876 100.74C825.876 104.16 824.796 106.32 822.42 106.32ZM836.449 111H842.857V97.32C844.081 96.132 844.945 95.484 846.385 95.484C848.005 95.484 848.761 96.276 848.761 99.12V111H855.169V98.328C855.169 93.216 853.261 90.012 848.797 90.012C846.025 90.012 843.937 91.416 842.245 93.072H842.137L841.669 90.516H836.449V111Z"
            fill="#91C5BE"
        />
        <g id="Upper_2">
            <g id="first_2">
                <text
                    id="1st:_2"
                    fill="#91C5BE"
                    xml:space="preserve"
                    style="white-space: pre"
                    font-family="Noto Sans JP"
                    font-size="24"
                    font-weight="bold"
                    letter-spacing="0em"
                    ><tspan x="678.832" y="172.464">{{
                        rankTable[0].rank
                    }}</tspan></text
                >
                <text
                    id="40%_6"
                    fill="#91C5BE"
                    xml:space="preserve"
                    style="white-space: pre"
                    font-family="Noto Sans JP"
                    font-size="20"
                    font-weight="bold"
                    letter-spacing="0em"
                >
                    <tspan x="814.789" y="171.464"
                        >{{ rankTable[0].percent }} %</tspan
                    >
                </text>
                <g id="twemoji:frowning-face_6" clip-path="url(#clip5_140_4)">
                    <Twimoji :rankPlace="0" :twimojiType="'ok'" />
                </g>
            </g>
            <g id="second_2">
                <text
                    id="2nd:_2"
                    fill="#91C5BE"
                    xml:space="preserve"
                    style="white-space: pre"
                    font-family="Noto Sans JP"
                    font-size="24"
                    font-weight="bold"
                    letter-spacing="0em"
                    ><tspan x="895.109" y="172.464">{{
                        rankTable[1].rank
                    }}</tspan></text
                >
                <text
                    id="40%_7"
                    fill="#91C5BE"
                    xml:space="preserve"
                    style="white-space: pre"
                    font-family="Noto Sans JP"
                    font-size="20"
                    font-weight="bold"
                    letter-spacing="0em"
                >
                    <tspan x="1038.79" y="171.464"
                        >{{ rankTable[1].percent }} %</tspan
                    >
                </text>
                <g id="twemoji:frowning-face_7" clip-path="url(#clip6_140_4)">
                    <Twimoji :rankPlace="1" :twimojiType="'ok'" />
                </g>
            </g>
        </g>
        <g id="Middle_2">
            <g id="third_2">
                <text
                    id="3rd:_2"
                    fill="#91C5BE"
                    xml:space="preserve"
                    style="white-space: pre"
                    font-family="Noto Sans JP"
                    font-size="24"
                    font-weight="bold"
                    letter-spacing="0em"
                    ><tspan x="678.832" y="256.464">{{
                        rankTable[2].rank
                    }}</tspan></text
                >
                <text
                    id="40%_8"
                    fill="#91C5BE"
                    xml:space="preserve"
                    style="white-space: pre"
                    font-family="Noto Sans JP"
                    font-size="20"
                    font-weight="bold"
                    letter-spacing="0em"
                >
                    <tspan x="814.789" y="255.464"
                        >{{ rankTable[2].percent }} %</tspan
                    >
                </text>
                <g id="twemoji:frowning-face_8" clip-path="url(#clip7_140_4)">
                    <Twimoji :rankPlace="2" :twimojiType="'ok'" />
                </g>
            </g>
            <g id="fourth_2">
                <text
                    id="4th:_2"
                    fill="#91C5BE"
                    xml:space="preserve"
                    style="white-space: pre"
                    font-family="Noto Sans JP"
                    font-size="24"
                    font-weight="bold"
                    letter-spacing="0em"
                    ><tspan x="900.109" y="256.464">{{
                        rankTable[3].rank
                    }}</tspan></text
                >
                <text
                    id="40%_9"
                    fill="#91C5BE"
                    xml:space="preserve"
                    style="white-space: pre"
                    font-family="Noto Sans JP"
                    font-size="20"
                    font-weight="bold"
                    letter-spacing="0em"
                >
                    <tspan x="1038.79" y="255.464"
                        >{{ rankTable[3].percent }} %</tspan
                    >
                </text>
                <g id="twemoji:frowning-face_9" clip-path="url(#clip8_140_4)">
                    <Twimoji :rankPlace="3" :twimojiType="'ok'" />
                </g>
            </g>
        </g>
        <g id="Bottom_2">
            <g id="fifth_2">
                <text
                    id="5th:_2"
                    fill="#91C5BE"
                    xml:space="preserve"
                    style="white-space: pre"
                    font-family="Noto Sans JP"
                    font-size="24"
                    font-weight="bold"
                    letter-spacing="0em"
                    ><tspan x="679.793" y="340.464">{{
                        rankTable[4].rank
                    }}</tspan></text
                >
                <text
                    id="40%_10"
                    fill="#91C5BE"
                    xml:space="preserve"
                    style="white-space: pre"
                    font-family="Noto Sans JP"
                    font-size="20"
                    font-weight="bold"
                    letter-spacing="0em"
                >
                    <tspan x="814.789" y="339.464"
                        >{{ rankTable[4].percent }} %</tspan
                    >
                </text>
                <g id="twemoji:frowning-face_10" clip-path="url(#clip9_140_4)">
                    <Twimoji :rankPlace="4" :twimojiType="'ok'" />
                </g>
            </g>
        </g>
    </g>
</template>

<script>
import WorseFace from "../Parts/WorseFace.vue";
import Twimoji from "../Parts/Twimoji.vue";
export default {
    components: {
        WorseFace,
        Twimoji,
    },
    data: () => ({
        rankTable: [],
    }),
    props: {
        condition: {
            type: Object,
        },
    },
    computed: {},
    methods: {
        sortCondition() {
            return this.condition.type.sort(function (a, b) {
                if (a.num > b.num) return -1;
                if (a.num < b.num) return 1;
            });
        },
        putRank(sortCondition) {
            let rank = 1;
            let rank_string = "";
            for (let i = 0; i < sortCondition.length; i++) {
                if (i === 0) {
                    rank_string = "1st:";
                } else {
                    rank =
                        sortCondition[i].num === sortCondition[i - 1].num
                            ? rank
                            : i + 1;
                    if (rank === 1) {
                        rank_string = rank + "st:";
                    } else if (rank === 2) {
                        rank_string = rank + "nd:";
                    } else if (rank === 3) {
                        rank_string = rank + "rd:";
                    } else {
                        rank_string = rank + "th:";
                    }
                }
                this.rankTable.push({
                    rank: rank_string,
                    percent: Math.round(
                        (sortCondition[i].num / this.condition.total) * 100
                    ),
                });
            }
        },
    },
    mounted() {
        const sortCondition = this.sortCondition();
        this.putRank(sortCondition);
    },
};
</script>
