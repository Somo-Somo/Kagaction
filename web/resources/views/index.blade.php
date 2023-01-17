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
