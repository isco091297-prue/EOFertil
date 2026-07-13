<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EOFertil</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])
</head>

<body class="bg-gray-100">

<div class="flex min-h-screen">

    <x-sidebar />

    <div class="flex-1 flex flex-col">

        <x-navbar />

        <main class="p-8">

            @yield('content')

        </main>

    </div>

</div>

</body>

</html>
