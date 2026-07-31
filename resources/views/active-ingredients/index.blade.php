@extends('layouts.app')

@section('title', 'Ingredientes Activos')

@section('content')

    <div class="space-y-6">

        <div class="flex items-center justify-between">

            <div>

                <h1 class="text-3xl font-bold">
                    Ingredientes Activos
                </h1>

                <p class="text-gray-500">
                    Administración de ingredientes activos y productos vinculados.
                </p>

            </div>

            <a href="{{ route('active-ingredients.create') }}"
                class="rounded-xl bg-green-600 px-5 py-3 text-white hover:bg-green-700">

                + Nuevo Ingrediente Activo

            </a>

        </div>

        @if (session('success'))
            <x-alert type="success">
                {{ session('success') }}
            </x-alert>
        @endif

        <x-card>

            <form method="GET" class="mb-6">

                <x-input type="text" name="search" placeholder="Buscar ingrediente activo..."
                    value="{{ $search }}" />

            </form>

            <div class="overflow-x-auto">

                <table class="min-w-full divide-y divide-gray-200">

                    <thead class="bg-gray-50">

                        <tr>

                            <th class="px-4 py-3">
                                Ingrediente Activo
                            </th>

                            <th class="px-4 py-3">
                                Descripción
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

                        @forelse($activeIngredients as $activeIngredient)
                            <tr>

                                <td class="px-4 py-3 font-semibold">

                                    {{ $activeIngredient->name }}

                                </td>

                                <td class="px-4 py-3 text-gray-600">

                                    @if ($activeIngredient->description)
                                        {{ \Illuminate\Support\Str::limit($activeIngredient->description, 70) }}
                                    @else
                                        <span class="text-gray-400">
                                            Sin descripción
                                        </span>
                                    @endif

                                </td>

                                <td class="px-4 py-3 text-center">

                                    <span
                                        class="inline-flex rounded-full bg-green-50 px-3 py-1 text-sm font-semibold text-green-700">

                                        {{ $activeIngredient->products_count }}

                                    </span>

                                </td>

                                <td class="px-4 py-3">

                                    @if ($activeIngredient->is_active)
                                        <span class="text-green-700">
                                            Activo
                                        </span>
                                    @else
                                        <span class="text-red-700">
                                            Inactivo
                                        </span>
                                    @endif

                                </td>

                                <td class="px-4 py-3">

                                    <div class="flex justify-center gap-2">

                                        <a href="{{ route('active-ingredients.show', $activeIngredient) }}"
                                            class="rounded border px-3 py-2">

                                            Ver

                                        </a>

                                        <a href="{{ route('active-ingredients.edit', $activeIngredient) }}"
                                            class="rounded bg-yellow-500 px-3 py-2 text-white">

                                            Editar

                                        </a>

                                        <form
                                            action="{{ route('active-ingredients.destroy', $activeIngredient) }}"
                                            method="POST">

                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" onclick="return confirm('¿Eliminar ingrediente activo?')"
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

                                    No existen ingredientes activos registrados.

                                </td>

                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>

            <div class="mt-6">

                {{ $activeIngredients->links() }}

            </div>

        </x-card>

    </div>

@endsection
