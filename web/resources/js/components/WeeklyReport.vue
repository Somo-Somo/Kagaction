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
import firebase from "firebase";
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
};
</script>
