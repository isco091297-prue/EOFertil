<header class="bg-white shadow px-8 py-4 flex justify-between items-center">

    <div>

        <h2 class="text-2xl font-bold text-gray-800">

            ERP EOFertil

        </h2>

    </div>

    <div class="flex items-center gap-5">

        <div class="text-right">

            <p class="font-semibold text-gray-800">

                {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}

            </p>

            <p class="text-sm text-gray-500">

                {{ auth()->user()->role->name }}

            </p>

        </div>

        <form action="{{ route('logout') }}" method="POST">

            @csrf

            <button
                type="submit"
                class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 transition-all duration-200 text-white px-5 py-2 rounded-xl shadow">

                🚪

                <span>

                    Cerrar sesión

                </span>

            </button>

        </form>

    </div>

</header>
