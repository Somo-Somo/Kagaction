<template>
    <div class="hello">
        <svg ref="svgCard">
            <text
                transform="translate(103.29 347.281)"
                fill="#e51f4e"
                font-size="29"
                font-family="HiraginoSans-W5, Hiragino Sans"
                letter-spacing="-0.002em"
            >
                <tspan x="0" y="26">{{ data }}</tspan>
            </text>
        </svg>
        <input v-model="msg" type="text" />
        <button @click="create">create</button>
    </div>
</template>

<script>
// Import the functions you need from the SDKs you need
import firebase from "firebase";
import { v4 as uuidv4 } from "uuid";

const config = {
    apiKey: propcess.env.FIREBASE_API_KEY,
    authDomain: propcess.env.FIREBASE_AUTH_DOMAIN,
    projectId: propcess.env.FIREBASE_PROJECT_ID,
    storageBucket: propcess.env.FIREBASE_STORAGE_BUCKET,
    messagingSenderId: propcess.env.FIREBASE_MESSAGING_SENDER_ID,
    appId: propcess.env.FIREBASE_APP_ID,
    measurementId: propcess.env.FIREBASE_MEASUREMENT_ID,
};

// Initialize Firebase
const app = firebase.initializeApp(config);
const db = firebase.firestore();

function svg2imageData(svgElement, successCallback, errorCallback) {
    var canvas = document.createElement("canvas");
    canvas.width = 1200;
    canvas.height = 630;
    var ctx = canvas.getContext("2d");
    var image = new Image();
    image.onload = () => {
        ctx.drawImage(image, 0, 0, 1200, 630);
        successCallback(canvas.toDataURL());
    };
    image.onerror = (e) => {
        errorCallback(e);
    };
    var svgData = new XMLSerializer().serializeToString(svgElement);
    image.src =
        "data:image/svg+xml;charset=utf-8;base64," +
        btoa(unescape(encodeURIComponent(svgData)));
}

export default {
    props: {
        data: {
            type: String,
        },
    },
    computed: {
        data: function () {
            return axios.post("/api/debug", "abc");
        },
    },
    methods: {
        create() {
            // refでsvgCardをsvgに設定しているのでthis.$refs.svgCardで要素を取れます
            svg2imageData(this.$refs.svgCard, (data) => {
                const sRef = firebase.storage().ref();
                const fileRef = sRef.child(`${uuid}.png`);
                const uuid = uuidv4();

                // Cloud Storageにアップロード
                fileRef
                    .putString(data, "data_url")
                    .then((snapshot) => {
                        // Firestoreに保存しておく
                        const card = db.collection("cards").doc(uuid);

                        return card.set(
                            {
                                message: this.description,
                            },
                            { merge: false }
                        );
                    })
                    .then((docRef) => {
                        console.log(docRef);
                    })
                    .catch((err) => {
                        console.error(err);
                    });
            });
        },
    },
};
</script>
