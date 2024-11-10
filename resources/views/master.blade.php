<!-- resources/views/master.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your App</title>
    @vite(['resources/js/app.js'])
</head>
<style>
    body {
    margin: 0;
    font-family: Arial, sans-serif;
    background-color: #f4f6f9;
}
</style>
<body>
    <div id="app">
        <router-view></router-view>
    </div>
</body>
</html>
