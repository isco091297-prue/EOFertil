<header class="bg-white shadow px-8 py-4 flex justify-between items-center">

    <div>

        <h2 class="text-xl font-semibold">

            Panel Administrativo

        </h2>

    </div>

    <div class="flex items-center gap-6">

        <span>

            {{ auth()->user()->first_name }}

        </span>

        <form
            action="{{ route('logout') }}"
            method="POST">

            @csrf

            <button class="text-red-600 font-semibold">

                Cerrar sesión

            </button>

        </form>

    </div>

</header>
