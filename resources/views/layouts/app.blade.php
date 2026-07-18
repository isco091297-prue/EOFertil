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

    @if(session('success'))

        <div class="mb-6 rounded-lg border border-green-300 bg-green-100 px-4 py-3 text-green-800">

            {{ session('success') }}

        </div>

    @endif

    @if(session('error'))

        <div class="mb-6 rounded-lg border border-red-300 bg-red-100 px-4 py-3 text-red-800">

            {{ session('error') }}

        </div>

    @endif

    @yield('content')

</main>

    </div>

</div>

</body>

</html>
