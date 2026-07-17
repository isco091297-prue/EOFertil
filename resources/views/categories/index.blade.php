@extends('layouts.app')

@section('title', 'Categorías')

@section('content')

<div class="space-y-6">

    <div class="flex items-center justify-between">

        <div>

            <h1 class="text-3xl font-bold">
                Categorías
            </h1>

            <p class="text-gray-500">
                Administración de categorías.
            </p>

        </div>

        <a
            href="{{ route('categories.create') }}"
            class="rounded-xl bg-green-600 px-5 py-3 text-white hover:bg-green-700">

            + Nueva Categoría

        </a>

    </div>

    @if(session('success'))

        <x-alert type="success">

            {{ session('success') }}

        </x-alert>

    @endif

    <x-card>

        <form method="GET" class="mb-6">

            <x-input
                type="text"
                name="search"
                placeholder="Buscar por código o nombre..."
                value="{{ $search }}" />

        </form>

        <div class="overflow-x-auto">

            <table class="min-w-full divide-y divide-gray-200">

                <thead class="bg-gray-50">

                    <tr>

                        <th class="px-4 py-3 text-left">
                            Imagen
                        </th>

                        <th class="px-4 py-3 text-left">
                            Código
                        </th>

                        <th class="px-4 py-3 text-left">
                            Nombre
                        </th>

                        <th class="px-4 py-3 text-center">
                            Estado
                        </th>

                        <th class="px-4 py-3 text-center">
                            Acciones
                        </th>

                    </tr>

                </thead>

                <tbody class="divide-y divide-gray-200 bg-white">

                    @forelse($categories as $category)

                        <tr>

                            <td class="px-4 py-3">

                                @if($category->image_path)

                                    <img
                                        src="{{ asset('storage/' . $category->image_path) }}"
                                        class="h-14 w-14 rounded-lg border object-cover">

                                @else

                                    <div class="flex h-14 w-14 items-center justify-center rounded-lg bg-gray-100 text-gray-400">

                                        —

                                    </div>

                                @endif

                            </td>

                            <td class="px-4 py-3">

                                {{ $category->code }}

                            </td>

                            <td class="px-4 py-3 font-medium">

                                {{ $category->name }}

                            </td>

                            <td class="px-4 py-3 text-center">

                                @if($category->is_active)

                                    <span class="rounded-full bg-green-100 px-3 py-1 text-sm text-green-700">

                                        Activo

                                    </span>

                                @else

                                    <span class="rounded-full bg-red-100 px-3 py-1 text-sm text-red-700">

                                        Inactivo

                                    </span>

                                @endif

                            </td>

                            <td class="px-4 py-3">

                                <div class="flex justify-center gap-2">

                                    <a
                                        href="{{ route('categories.show', $category) }}"
                                        class="rounded-lg border px-3 py-2">

                                        Ver

                                    </a>

                                    <a
                                        href="{{ route('categories.edit', $category) }}"
                                        class="rounded-lg bg-yellow-500 px-3 py-2 text-white">

                                        Editar

                                    </a>

                                    <form
                                        action="{{ route('categories.destroy', $category) }}"
                                        method="POST"
                                        onsubmit="return confirm('¿Eliminar categoría?')">

                                        @csrf
                                        @method('DELETE')

                                        <button
                                            class="rounded-lg bg-red-600 px-3 py-2 text-white">

                                            Eliminar

                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="5" class="py-10 text-center text-gray-500">

                                No existen categorías registradas.

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <div class="mt-6">

            {{ $categories->links() }}

        </div>

    </x-card>

</div>

@endsection
