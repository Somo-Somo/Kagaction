<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kagaction</title>

    <!-- Scripts -->
    <script src="{{ mix('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Merriweather|Roboto:400">
    <link rel="stylesheet" href="https://unpkg.com/ionicons@4.2.2/dist/css/ionicons.min.css">


    <style>
        .v-application {
            font-family:
                "Helvetica Neue", Arial,
                "Hiragino Kaku Gothic ProN",
                "Hiragino Sans",
                Meiryo,
                sans-serif !important;
            flex: 1;
            overflow-x: hidden;
        }

        main {
            flex: 1;
            overflow-x: hidden;
        }

        /* 描画エリアの指定 */
        .pie-container {
            position: relative;
            padding-bottom: 20rem;
            width: 20rem;
        }

        .pie-svg {
            display: block;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: visible;
        }

        /* グラフ部分の指定 */
        .pie {
            fill: transparent;
            cx: 32;
            cy: 32;
            r: 16;
            stroke-width: 32;
            stroke-dashoffset: 25;
        }

        .pieA {
            stroke: #009cff;
            stroke-dasharray: 65 35;
        }

        .pieB {
            stroke: #4cbaff;
            stroke-dasharray: 0 65 18 17;
        }

        .pieC {
            stroke: #7fcdff;
            stroke-dasharray: 0 83 17 0;
        }
    </style>
</head>

<body>
    <div id="app">
        <weekly-report></weekly-report>
    </div>
</body>

</html>
<?php

use Illuminate\Support\Facades\Log;
