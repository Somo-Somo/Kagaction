<template>
    <div class="hello">
        <svg
            ref="svgCard"
            width="1200"
            height="800"
            viewBox="0 0 1200 800"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
        >
            <g id="Weekly Report ">
                <rect
                    id="Report Image"
                    width="1200"
                    height="800"
                    fill="white"
                />
                <PieChart
                    :conditions="conditions"
                    :feelings="feelings"
                    v-if="ready"
                />
                <g id="Service Name">
                    <path
                        id="#Agathon"
                        d="M69.2403 752H73.3203L74.3603 743.56H78.8803L77.8803 752H82.0003L83.0003 743.56H87.4003V739.04H83.5203L84.1603 734.2H88.3203V729.68H84.7203L85.6003 722.24H81.5203L80.6003 729.68H76.0403L76.9203 722.24H72.8803L71.9603 729.68H67.8003V734.2H71.3603L70.7603 739.04H66.8803V743.56H70.2403L69.2403 752ZM74.8403 739.04L75.4403 734.2H80.0003L79.4003 739.04H74.8403ZM89.3753 752H96.6553L100.375 737.12C101.135 734.16 101.935 730.68 102.655 727.56H102.815C103.615 730.6 104.375 734.16 105.175 737.12L108.895 752H116.415L107.175 722.2H98.6153L89.3753 752ZM95.6553 745.16H110.055V739.64H95.6553V745.16ZM127.142 761.56C135.062 761.56 140.062 758.16 140.062 753.36C140.062 749.24 136.862 747.52 131.262 747.52H127.662C125.222 747.52 124.302 747.04 124.302 746C124.302 745.2 124.542 744.88 125.022 744.4C126.022 744.68 126.942 744.8 127.702 744.8C132.742 744.8 136.782 742.48 136.782 737.16C136.782 736.08 136.502 735.04 136.182 734.4H139.662V729.24H131.302C130.262 728.88 129.022 728.68 127.702 728.68C122.782 728.68 118.182 731.4 118.182 736.92C118.182 739.6 119.662 741.72 121.302 742.84V743C119.782 744.04 118.742 745.64 118.742 747.24C118.742 749.16 119.622 750.36 120.822 751.16V751.36C118.702 752.44 117.622 753.96 117.622 755.84C117.622 759.92 121.902 761.56 127.142 761.56ZM127.702 740.52C126.062 740.52 124.822 739.32 124.822 736.92C124.822 734.64 126.062 733.44 127.702 733.44C129.382 733.44 130.622 734.64 130.622 736.92C130.622 739.32 129.382 740.52 127.702 740.52ZM128.262 757.12C125.382 757.12 123.462 756.32 123.462 754.72C123.462 753.96 123.782 753.32 124.502 752.68C125.222 752.88 126.102 752.96 127.742 752.96H129.902C132.022 752.96 133.222 753.24 133.222 754.6C133.222 756.04 131.182 757.12 128.262 757.12ZM148.257 752.56C150.777 752.56 152.897 751.4 154.777 749.72H154.977L155.457 752H161.257V739.08C161.257 731.96 157.937 728.68 152.057 728.68C148.497 728.68 145.257 729.88 142.257 731.68L144.737 736.36C147.017 735.08 148.897 734.36 150.697 734.36C153.017 734.36 153.977 735.44 154.137 737.28C145.257 738.24 141.497 740.84 141.497 745.64C141.497 749.44 144.057 752.56 148.257 752.56ZM150.697 747.04C149.217 747.04 148.257 746.4 148.257 745.08C148.257 743.48 149.697 742.16 154.137 741.6V745.24C153.097 746.36 152.137 747.04 150.697 747.04ZM174.582 752.56C176.902 752.56 178.542 752.08 179.742 751.72L178.622 746.56C178.062 746.76 177.262 747 176.542 747C174.862 747 173.662 746 173.662 743.48V734.8H179.022V729.24H173.662V723.2H167.782L166.982 729.24L163.462 729.52V734.8H166.542V743.56C166.542 748.92 168.862 752.56 174.582 752.56ZM183.435 752H190.555V736.8C191.915 735.48 192.875 734.76 194.475 734.76C196.275 734.76 197.115 735.64 197.115 738.8V752H204.235V737.92C204.235 732.24 202.115 728.68 197.155 728.68C194.075 728.68 191.835 730.24 190.195 731.76L190.555 727.72V720.04H183.435V752ZM219.643 752.56C225.403 752.56 230.763 748.16 230.763 740.6C230.763 733.08 225.403 728.68 219.643 728.68C213.843 728.68 208.483 733.08 208.483 740.6C208.483 748.16 213.843 752.56 219.643 752.56ZM219.643 746.8C216.963 746.8 215.763 744.4 215.763 740.6C215.763 736.84 216.963 734.44 219.643 734.44C222.283 734.44 223.483 736.84 223.483 740.6C223.483 744.4 222.283 746.8 219.643 746.8ZM235.232 752H242.352V736.8C243.712 735.48 244.672 734.76 246.272 734.76C248.072 734.76 248.912 735.64 248.912 738.8V752H256.032V737.92C256.032 732.24 253.912 728.68 248.952 728.68C245.872 728.68 243.552 730.24 241.672 732.08H241.552L241.032 729.24H235.232V752Z"
                        fill="#000F99"
                    />
                </g>
                <g id="Date">
                    <text
                        id="start"
                        fill="black"
                        xml:space="preserve"
                        font-size="24"
                        font-weight="900"
                        letter-spacing="0em"
                    >
                        <tspan x="578.332" y="746.708">
                            {{ period.start }}
                        </tspan>
                    </text>
                    <text
                        id="~"
                        fill="black"
                        xml:space="preserve"
                        style="white-space: pre"
                        font-size="28"
                        font-weight="900"
                        letter-spacing="0em"
                    >
                        <tspan x="942.469" y="746.708">~</tspan>
                    </text>
                    <text
                        id="end"
                        fill="black"
                        xml:space="preserve"
                        font-size="24"
                        font-weight="900"
                        letter-spacing="0em"
                    >
                        <tspan x="768.332" y="746.708">
                            {{ period.end }}
                        </tspan>
                    </text>
                </g>
                <ConditionRank :conditions="conditions" v-if="ready" />
                <FeelingRank :feelings="feelings" v-if="ready" />
            </g>
            <defs>
                <clipPath id="clip0_140_4">
                    <rect
                        width="56"
                        height="56"
                        fill="white"
                        transform="translate(742 447)"
                    />
                </clipPath>
                <clipPath id="clip1_140_4">
                    <rect
                        width="56"
                        height="56"
                        fill="white"
                        transform="translate(966 447)"
                    />
                </clipPath>
                <clipPath id="clip2_140_4">
                    <rect
                        width="56"
                        height="56"
                        fill="white"
                        transform="translate(742 531)"
                    />
                </clipPath>
                <clipPath id="clip3_140_4">
                    <rect
                        width="56"
                        height="56"
                        fill="white"
                        transform="translate(966 531)"
                    />
                </clipPath>
                <clipPath id="clip4_140_4">
                    <rect
                        width="56"
                        height="56"
                        fill="white"
                        transform="translate(742 615)"
                    />
                </clipPath>
                <clipPath id="clip5_140_4">
                    <rect
                        width="56"
                        height="56"
                        fill="white"
                        transform="translate(742 133)"
                    />
                </clipPath>
                <clipPath id="clip6_140_4">
                    <rect
                        width="56"
                        height="56"
                        fill="white"
                        transform="translate(966 133)"
                    />
                </clipPath>
                <clipPath id="clip7_140_4">
                    <rect
                        width="56"
                        height="56"
                        fill="white"
                        transform="translate(742 217)"
                    />
                </clipPath>
                <clipPath id="clip8_140_4">
                    <rect
                        width="56"
                        height="56"
                        fill="white"
                        transform="translate(966 217)"
                    />
                </clipPath>
                <clipPath id="clip9_140_4">
                    <rect
                        width="56"
                        height="56"
                        fill="white"
                        transform="translate(742 301)"
                    />
                </clipPath>
            </defs>
        </svg>
        <!--  updateに検知されるため -->
        <div>{{ $route.params.id }}</div>
        <div id="result"><img :src="dataURL" /></div>
    </div>
</template>

<script>
import PieChart from "./Views/Report/Template/PieChart.vue";
import ConditionRank from "./Views/Report/Template/ConditionRank.vue";
import FeelingRank from "./Views/Report/Template/FeelingRank.vue";
// Import the functions you need from the SDKs you need
import { initializeApp } from "firebase/app";
import {
    getStorage,
    ref,
    uploadString,
    getDownloadURL,
} from "firebase/storage";
import { v4 as uuidv4 } from "uuid";

// const config = {
//     apiKey: propcess.env.FIREBASE_API_KEY,
//     authDomain: propcess.env.FIREBASE_AUTH_DOMAIN,
//     projectId: propcess.env.FIREBASE_PROJECT_ID,
//     storageBucket: propcess.env.FIREBASE_STORAGE_BUCKET,
//     messagingSenderId: propcess.env.FIREBASE_MESSAGING_SENDER_ID,
//     appId: propcess.env.FIREBASE_APP_ID,
//     measurementId: propcess.env.FIREBASE_MEASUREMENT_ID,
// };

const firebaseConfig = {
    apiKey: "AIzaSyCWFF-fxzg4yBMO7nc51V_y73XscSMY7ZY",
    authDomain: "agathon-2022-12.firebaseapp.com",
    projectId: "agathon-2022-12",
    storageBucket: "agathon-2022-12.appspot.com",
    messagingSenderId: "715234137429",
    appId: "1:715234137429:web:a0c46e601550795fd24bdc",
    measurementId: "G-8MJG0PWKYM",
};

// Initialize Firebase
const firebase = initializeApp(firebaseConfig);
const firestorage = getStorage(firebase);

export default {
    components: {
        PieChart,
        ConditionRank,
        FeelingRank,
    },
    data() {
        return {
            id: null,
            error: null,
            dataURL: null,
            ready: false,
            conditions: null,
            feelings: null,
            userUuids: [],
            uploadUserLimitNum: 0,
            uploadUserCountNum: 0,
            period: {
                start: null,
                end: null,
            },
        };
    },
    props: {
        data: {
            type: Array,
        },
    },
    computed: {},
    methods: {
        svg2imageData(svgElement, successCallback, errorCallback) {
            var canvas = document.createElement("canvas");
            canvas.width = svgElement.width.baseVal.value;
            canvas.height = svgElement.height.baseVal.value;
            var ctx = canvas.getContext("2d");
            var image = new Image();
            image.onload = () => {
                ctx.drawImage(image, 0, 0, image.width, image.height);
                successCallback(canvas.toDataURL());
            };
            image.onerror = (e) => {
                errorCallback(e);
            };
            var svgData = new XMLSerializer().serializeToString(svgElement);
            image.src =
                "data:image/svg+xml;charset=utf-8;base64," +
                btoa(unescape(encodeURIComponent(svgData)));
        },
        calcData(data, userUuidKeys) {
            const conditions = data.conditions;
            const feelings = data.feelings;
            const calcConditions = [];
            const calcFeelings = [];
            for (let i = 0; i < userUuidKeys.length; i++) {
                const weeklyConditions = conditions[userUuidKeys[i]];
                const weeklyFeelings = feelings[userUuidKeys[i]];
                const calcCondition = {
                    total: weeklyConditions.length,
                    type: [
                        { name: "great", num: 0 },
                        { name: "good", num: 0 },
                        { name: "ok", num: 0 },
                        { name: "bad", num: 0 },
                        { name: "worse", num: 0 },
                    ],
                };
                for (
                    let conditionKey = 0;
                    conditionKey < weeklyConditions.length;
                    conditionKey++
                ) {
                    calcCondition.type[
                        5 - weeklyConditions[conditionKey].evaluation
                    ].num += 1;
                }
                const calcFeeling = {
                    total: weeklyFeelings.length,
                    type: {
                        wakuwaku: 0,
                        happy: 0,
                        glad: 0,
                        fun: 0,
                        calm: 0,
                        angry: 0,
                        kuyashi: 0,
                        moyamoya: 0,
                        lethargic: 0,
                        tired: 0,
                        anxious: 0,
                        sad: 0,
                        hard: 0,
                    },
                };
                for (
                    let feelingKey = 0;
                    feelingKey < weeklyFeelings.length;
                    feelingKey++
                ) {
                    calcFeeling.type[
                        weeklyFeelings[feelingKey].feeling_type
                    ] += 1;
                }
                for (const feelingType in calcFeeling.type) {
                    if (calcFeeling.type[feelingType] === 0) {
                        delete calcFeeling.type[feelingType];
                    }
                }
                calcConditions[userUuidKeys[i]] = calcCondition;
                calcFeelings[userUuidKeys[i]] = calcFeeling;
            }
            return {
                calcConditions: calcConditions,
                calcFeelings: calcFeelings,
            };
        },
    },
    created() {
        axios
            .get("/api/report/monthly/1")
            .then((res) => {
                const userUuidKeys = Object.keys(res.data.conditions);
                const period = res.data.period;
                const { calcConditions, calcFeelings } = this.calcData(
                    res.data,
                    userUuidKeys
                );
                this.conditions = calcConditions;
                this.feelings = calcFeelings;
                this.userUuidKeys = userUuidKeys;
                this.uploadUserLimitNum = userUuidKeys.length;
                this.period.start = period.start;
                this.period.end = period.end;
                console.log(this.period);
                console.log(period);

                this.ready = true;
                this.$router.push({
                    name: "weekly-report",
                    params: { id: this.userUuidKeys[this.uploadUserCountNum] },
                });
            })
            .catch((err) => {
                this.error = err;
                console.log(err);
            });
    },
    async mounted() {},
    async updated() {
        if (this.ready) {
            if (this.uploadUserCountNum !== this.uploadUserLimitNum) {
                await this.$nextTick();
                this.svg2imageData(this.$refs.svgCard, (data) => {
                    this.dataURL = data;
                    const storageRef = ref(
                        firestorage,
                        "users/" + "/images/" + this.$route.params.id + ".png"
                    );
                    uploadString(storageRef, data, "data_url").then(() => {
                        console.log("Uploaded a file!");
                        getDownloadURL(storageRef)
                            .then((url) => {
                                console.log(url);
                                this.uploadUserCountNum += 1;
                                if (
                                    this.uploadUserCountNum <
                                    this.uploadUserLimitNum
                                ) {
                                    this.$router.push({
                                        name: "weekly-report",
                                        params: {
                                            id: this.userUuidKeys[
                                                this.uploadUserCountNum
                                            ],
                                        },
                                    });
                                }
                            })
                            .catch((err) => console.log(err));
                    });
                });
            }
        }
    },
};
</script>
