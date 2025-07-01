<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PMT | Contriwhiz Technologies</title>

    <!-- Add favicon -->
    <link rel="icon" href="/img/Tablogo.svg" type="image/jpeg">

    @vite(['resources/js/app.js', 'resources/css/home.css'])
</head>
<style>
    *{
        font-family: 'Poppins', sans-serif;
    }
    body {
        margin: 0;
        font-family: 'Poppins', sans-serif;
        background-color: #f4f6f9;
    }
    .header {
        display: flex;
        align-items: center;
        padding: 10px 20px;
        background-color: #ffffff;
        border-bottom: 1px solid #ddd;
    }
    .header .logo {
        height: 100px;
        overflow: hidden;
    }
    .header .logo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .header .app-name {
        font-size: 20px;
        font-weight: bold;
        color: #333;
    }
    .title_heading{
        color: #24292e;
        font-weight: 600;
    }
    .card_heading{
       font-weight: 600;
       font-size: 17px;
       color: #3498db;
    }
</style>
<body>
    <div id="app">
        <router-view></router-view>
    </div>
</body>
</html>
