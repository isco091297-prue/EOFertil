@extends('layouts.app')

@section('title', 'Combinaciones de Ingredientes')

@section('content')

    <div class="space-y-6">

        {{-- ENCABEZADO --}}
        <div class="flex items-center justify-between">

            <div>

                <h1 class="text-3xl font-bold">
                    Combinaciones de Ingredientes
                </h1>

                <p class="text-gray-500">
                    Administra combinaciones de ingredientes activos y sus productos.
                </p>

            </div>

            <a href="{{ route('active-ingredient-combinations.create') }}"
                class="rounded-xl bg-green-600 px-5 py-3 text-white hover:bg-green-700">

                + Nueva Combinación

            </a>

        </div>


        {{-- MENSAJE DE ÉXITO --}}
        @if (session('success'))
            <x-alert type="success">
                {{ session('success') }}
            </x-alert>
        @endif


        {{-- CONTENIDO --}}
        <x-card>

            {{-- BUSCADOR --}}
            <form method="GET" action="{{ route('active-ingredient-combinations.index') }}" class="mb-6">

                <x-input type="text" name="search" placeholder="Buscar combinación..." value="{{ $search }}" />

            </form>


            {{-- TABLA --}}
            <div class="overflow-x-auto">

                <table class="min-w-full divide-y divide-gray-200">

                    <thead class="bg-gray-50">

                        <tr>

                            <th class="px-4 py-3 text-left">
                                Combinación
                            </th>

                            <th class="px-4 py-3 text-center">
                                Ingredientes
                            </th>

                            <th class="px-4 py-3 text-center">
                                Productos
                            </th>

                            <th class="px-4 py-3">
                                Estado
                            </th>

                            <th class="px-4 py-3 text-center">
                                Acciones
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @forelse($combinations as $combination)
                            <tr>

                                {{-- COMBINACIÓN --}}
                                <td class="px-4 py-3">

                                    <div class="font-semibold">

                                        {{ $combination->name }}

                                    </div>

                                    @if ($combination->description)
                                        <div class="mt-1 text-sm text-gray-500">

                                            {{ \Illuminate\Support\Str::limit($combination->description, 70) }}

                                        </div>
                                    @endif

                                </td>


                                {{-- INGREDIENTES --}}
                                <td class="px-4 py-3 text-center">

                                    <span
                                        class="inline-flex rounded-full bg-blue-50 px-3 py-1 text-sm font-semibold text-blue-700">

                                        {{ $combination->active_ingredients_count }}

                                    </span>

                                </td>


                                {{-- PRODUCTOS --}}
                                <td class="px-4 py-3 text-center">

                                    <span
                                        class="inline-flex rounded-full bg-green-50 px-3 py-1 text-sm font-semibold text-green-700">

                                        {{ $combination->products_count }}

                                    </span>

                                </td>


                                {{-- ESTADO --}}
                                <td class="px-4 py-3">

                                    @if ($combination->is_active)
                                        <span class="text-green-700">
                                            Activo
                                        </span>
                                    @else
                                        <span class="text-red-700">
                                            Inactivo
                                        </span>
                                    @endif

                                </td>


                                {{-- ACCIONES --}}
                                <td class="px-4 py-3">

                                    <div class="flex justify-center gap-2">

                                        {{-- VER --}}
                                        <a href="{{ route('active-ingredient-combinations.show', $combination) }}"
                                            class="rounded border px-3 py-2">

                                            Ver

                                        </a>


                                        {{-- EDITAR --}}
                                        <a href="{{ route('active-ingredient-combinations.edit', $combination) }}"
                                            class="rounded bg-yellow-500 px-3 py-2 text-white">

                                            Editar

                                        </a>


                                        {{-- ELIMINAR --}}
                                        <form action="{{ route('active-ingredient-combinations.destroy', $combination) }}"
                                            method="POST">

                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" onclick="return confirm('¿Eliminar esta combinación?')"
                                                class="rounded bg-red-600 px-3 py-2 text-white">

                                                Eliminar

                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="5" class="py-8 text-center">

                                    No existen combinaciones de ingredientes registradas.

                                </td>

                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>


            {{-- PAGINACIÓN --}}
            <div class="mt-6">

                {{ $combinations->links() }}

            </div>

        </x-card>

    </div>

@endsection
