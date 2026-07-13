<aside class="w-72 bg-green-800 text-white">

    <div class="p-6 border-b border-green-700">

        <x-logo />

    </div>

    <nav class="mt-6 space-y-2 px-4">

        <a href="{{ route('dashboard') }}" class="block rounded-xl px-4 py-3 hover:bg-green-700">

            🏠 Dashboard

        </a>

        <a href="{{ route('users.index') }}" class="block rounded-xl px-4 py-3 hover:bg-green-700">

            👥 Usuarios

        </a>

    </nav>

</aside>
