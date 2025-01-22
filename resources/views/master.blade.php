<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CONTRIWHIZ</title>

    <!-- Add favicon -->
    <link rel="icon" href="img/CWlogo.jpeg" type="image/jpeg">

    @vite(['resources/js/app.js'])
</head>
<style>
    body {
        margin: 0;
        font-family: Arial, sans-serif;
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
        width: 50px;
        height: 100px;
        border-radius: 50%;
        overflow: hidden;
        margin-right: 15px;
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
</style>
<body>
    <div id="app">
        <router-view></router-view>
    </div>
</body>
</html>
