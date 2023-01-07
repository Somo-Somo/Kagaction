<template>
    <g id="Feeling Rank">
        <path
            id="Feeling"
            d="M680.098 426H686.542V415.776H695.686V410.412H686.542V404.544H697.198V399.18H680.098V426ZM709.589 426.504C711.965 426.504 714.773 425.676 716.897 424.164L714.773 420.312C713.297 421.176 711.929 421.608 710.453 421.608C707.861 421.608 705.917 420.456 705.341 417.648H717.365C717.509 417.144 717.653 416.028 717.653 414.876C717.653 409.296 714.773 405.012 708.869 405.012C703.937 405.012 699.149 409.116 699.149 415.74C699.149 422.544 703.685 426.504 709.589 426.504ZM705.233 413.436C705.665 411.024 707.213 409.908 708.977 409.908C711.317 409.908 712.217 411.42 712.217 413.436H705.233ZM731.034 426.504C733.41 426.504 736.218 425.676 738.342 424.164L736.218 420.312C734.742 421.176 733.374 421.608 731.898 421.608C729.306 421.608 727.362 420.456 726.786 417.648H738.81C738.954 417.144 739.098 416.028 739.098 414.876C739.098 409.296 736.218 405.012 730.314 405.012C725.382 405.012 720.594 409.116 720.594 415.74C720.594 422.544 725.13 426.504 731.034 426.504ZM726.678 413.436C727.11 411.024 728.658 409.908 730.422 409.908C732.762 409.908 733.662 411.42 733.662 413.436H726.678ZM749.024 426.504C750.464 426.504 751.508 426.252 752.192 425.964L751.436 421.248C751.076 421.32 750.932 421.32 750.716 421.32C750.212 421.32 749.6 420.924 749.6 419.556V397.236H743.192V419.34C743.192 423.624 744.632 426.504 749.024 426.504ZM755.145 426H761.553V405.516H755.145V426ZM758.349 402.672C760.473 402.672 761.913 401.376 761.913 399.36C761.913 397.38 760.473 396.084 758.349 396.084C756.189 396.084 754.785 397.38 754.785 399.36C754.785 401.376 756.189 402.672 758.349 402.672ZM766.711 426H773.119V412.32C774.343 411.132 775.207 410.484 776.647 410.484C778.267 410.484 779.023 411.276 779.023 414.12V426H785.431V413.328C785.431 408.216 783.523 405.012 779.059 405.012C776.287 405.012 774.199 406.416 772.507 408.072H772.399L771.931 405.516H766.711V426ZM797.75 434.604C804.878 434.604 809.378 431.544 809.378 427.224C809.378 423.516 806.498 421.968 801.458 421.968H798.218C796.022 421.968 795.194 421.536 795.194 420.6C795.194 419.88 795.41 419.592 795.842 419.16C796.742 419.412 797.57 419.52 798.254 419.52C802.79 419.52 806.426 417.432 806.426 412.644C806.426 411.672 806.174 410.736 805.886 410.16H809.018V405.516H801.494C800.558 405.192 799.442 405.012 798.254 405.012C793.826 405.012 789.686 407.46 789.686 412.428C789.686 414.84 791.018 416.748 792.494 417.756V417.9C791.126 418.836 790.19 420.276 790.19 421.716C790.19 423.444 790.982 424.524 792.062 425.244V425.424C790.154 426.396 789.182 427.764 789.182 429.456C789.182 433.128 793.034 434.604 797.75 434.604ZM798.254 415.668C796.778 415.668 795.662 414.588 795.662 412.428C795.662 410.376 796.778 409.296 798.254 409.296C799.766 409.296 800.882 410.376 800.882 412.428C800.882 414.588 799.766 415.668 798.254 415.668ZM798.758 430.608C796.166 430.608 794.438 429.888 794.438 428.448C794.438 427.764 794.726 427.188 795.374 426.612C796.022 426.792 796.814 426.864 798.29 426.864H800.234C802.142 426.864 803.222 427.116 803.222 428.34C803.222 429.636 801.386 430.608 798.758 430.608Z"
            fill="#91C5BE"
        />
        <g id="Upper">
            <g id="first" v-if="rankTable.length > 0">
                <text
                    id="1st:"
                    fill="#91C5BE"
                    xml:space="preserve"
                    style="white-space: pre"
                    font-family="Noto Sans JP"
                    font-size="24"
                    font-weight="bold"
                    letter-spacing="0em"
                    ><tspan x="681.832" y="486.464">{{
                        rankTable[0].rank
                    }}</tspan></text
                >
                <text
                    id="40%"
                    fill="#91C5BE"
                    xml:space="preserve"
                    style="white-space: pre"
                    font-family="Noto Sans JP"
                    font-size="20"
                    font-weight="bold"
                    letter-spacing="0em"
                    ><tspan x="812.789" y="485.464"
                        >{{ rankTable[0].percent }} %</tspan
                    ></text
                >
                <g id="twemoji:frowning-face" clip-path="url(#clip0_140_4)">
                    <Twimoji :rankPlace="0" :twimojiType="'kuyashi'" />
                </g>
            </g>
            <g id="second" v-if="rankTable.length > 1">
                <text
                    id="2nd:"
                    fill="#91C5BE"
                    xml:space="preserve"
                    style="white-space: pre"
                    font-family="Noto Sans JP"
                    font-size="24"
                    font-weight="bold"
                    letter-spacing="0em"
                    ><tspan x="901.109" y="486.464">{{
                        rankTable[1].rank
                    }}</tspan></text
                >
                <text
                    id="40%_2"
                    fill="#91C5BE"
                    xml:space="preserve"
                    style="white-space: pre"
                    font-family="Noto Sans JP"
                    font-size="20"
                    font-weight="bold"
                    letter-spacing="0em"
                    ><tspan x="1036.79" y="485.464"
                        >{{ rankTable[1].percent }} %</tspan
                    ></text
                >
                <g id="twemoji:frowning-face_2" clip-path="url(#clip1_140_4)">
                    <Twimoji :rankPlace="1" :twimojiType="'kuyashi'" />
                </g>
            </g>
        </g>
        <g id="Middle">
            <g id="third" v-if="rankTable.length > 2">
                <text
                    id="3rd:"
                    fill="#91C5BE"
                    xml:space="preserve"
                    style="white-space: pre"
                    font-family="Noto Sans JP"
                    font-size="24"
                    font-weight="bold"
                    letter-spacing="0em"
                    ><tspan x="679.758" y="570.464">{{
                        rankTable[2].rank
                    }}</tspan></text
                >
                <text
                    id="40%_3"
                    fill="#91C5BE"
                    xml:space="preserve"
                    style="white-space: pre"
                    font-family="Noto Sans JP"
                    font-size="20"
                    font-weight="bold"
                    letter-spacing="0em"
                    ><tspan x="812.789" y="569.464"
                        >{{ rankTable[2].percent }} %</tspan
                    ></text
                >
                <g id="twemoji:frowning-face_3" clip-path="url(#clip2_140_4)">
                    <Twimoji :rankPlace="2" :twimojiType="'kuyashi'" />
                </g>
            </g>
            <g id="fourth" v-if="rankTable.length > 3">
                <text
                    id="4th:"
                    fill="#91C5BE"
                    xml:space="preserve"
                    style="white-space: pre"
                    font-family="Noto Sans JP"
                    font-size="24"
                    font-weight="bold"
                    letter-spacing="0em"
                    ><tspan x="903.793" y="570.464">{{
                        rankTable[3].rank
                    }}</tspan></text
                >
                <text
                    id="40%_4"
                    fill="#91C5BE"
                    xml:space="preserve"
                    style="white-space: pre"
                    font-family="Noto Sans JP"
                    font-size="20"
                    font-weight="bold"
                    letter-spacing="0em"
                    ><tspan x="1036.79" y="569.464"
                        >{{ rankTable[3].percent }} %</tspan
                    ></text
                >
                <g id="twemoji:frowning-face_4" clip-path="url(#clip3_140_4)">
                    <Twimoji :rankPlace="3" :twimojiType="'kuyashi'" />
                </g>
            </g>
        </g>
        <g id="Bottom">
            <g id="fifth" v-if="rankTable.length > 4">
                <text
                    id="5th:"
                    fill="#91C5BE"
                    xml:space="preserve"
                    style="white-space: pre"
                    font-family="Noto Sans JP"
                    font-size="24"
                    font-weight="bold"
                    letter-spacing="0em"
                    ><tspan x="679.793" y="654.464">{{
                        rankTable[4].rank
                    }}</tspan></text
                >
                <text
                    id="40%_5"
                    fill="#91C5BE"
                    xml:space="preserve"
                    style="white-space: pre"
                    font-family="Noto Sans JP"
                    font-size="20"
                    font-weight="bold"
                    letter-spacing="0em"
                    ><tspan x="812.789" y="653.464"
                        >{{ rankTable[4].percent }} %</tspan
                    ></text
                >
                <g id="twemoji:frowning-face_5" clip-path="url(#clip4_140_4)">
                    <Twimoji :rankPlace="4" :twimojiType="'kuyashi'" />
                </g>
            </g>
        </g>
    </g>
</template>

<script>
import Twimoji from "../Parts/TwimojiDraw/Twimoji.vue";
export default {
    components: {
        Twimoji,
    },
    data: () => ({
        rankTable: [],
    }),
    props: {
        feeling: {
            type: Object,
        },
    },
    computed: {},
    methods: {
        sortFeeling() {
            return this.feeling.type.sort(function (a, b) {
                if (a.num > b.num) return -1;
                if (a.num < b.num) return 1;
            });
        },
        putRank(sortFeeling) {
            let rank = 1;
            let rank_string = "";
            const limit = sortFeeling.length > 5 ? 5 : sortFeeling.length;
            for (let i = 0; i < limit; i++) {
                if (i === 0) {
                    rank_string = "1st:";
                } else {
                    rank =
                        sortFeeling[i].num === sortFeeling[i - 1].num
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
                        (sortFeeling[i].num / this.feeling.total) * 100
                    ),
                });
            }
        },
    },
    mounted() {
        const sortFeeling = this.sortFeeling();
        this.putRank(sortFeeling);
    },
};
</script>
