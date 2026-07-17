@extends('layouts.app')

@section('title', 'Cultivos')

@section('content')

    <div class="space-y-6">

        <div class="flex items-center justify-between">

            <div>

                <h1 class="text-3xl font-bold">
                    Cultivos
                </h1>

                <p class="text-gray-500">
                    Administración de cultivos.
                </p>

            </div>

            <a href="{{ route('crops.create') }}" class="rounded-xl bg-green-600 px-5 py-3 text-white hover:bg-green-700">

                + Nuevo Cultivo

            </a>

        </div>

        @if (session('success'))
            <x-alert type="success">

                {{ session('success') }}

            </x-alert>
        @endif

        <x-card>

            <form method="GET" class="mb-6">

                <x-input type="text" name="search" placeholder="Buscar por código o nombre..."
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

                        @forelse($crops as $crop)
                            <tr>

                                <td class="px-4 py-3">

                                    @if ($crop->image_path)
                                        <img src="{{ asset('storage/' . $crop->image_path) }}"
                                            class="h-14 w-14 rounded-lg border object-cover">
                                    @else
                                        <div
                                            class="flex h-14 w-14 items-center justify-center rounded-lg bg-gray-100 text-gray-400">

                                            —

                                        </div>
                                    @endif

                                </td>

                                <td class="px-4 py-3">

                                    {{ $crop->code }}

                                </td>

                                <td class="px-4 py-3 font-medium">

                                    {{ $crop->name }}

                                </td>

                                <td class="px-4 py-3 text-center">

                                    @if ($crop->is_active)
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

                                        <a href="{{ route('crops.show', $crop) }}" class="rounded-lg border px-3 py-2">

                                            Ver

                                        </a>

                                        <a href="{{ route('crops.edit', $crop) }}"
                                            class="rounded-lg bg-yellow-500 px-3 py-2 text-white">

                                            Editar

                                        </a>

                                        <form action="{{ route('crops.destroy', $crop) }}" method="POST"
                                            onsubmit="return confirm('¿Eliminar cultivo?')">

                                            @csrf
                                            @method('DELETE')

                                            <button class="rounded-lg bg-red-600 px-3 py-2 text-white">

                                                Eliminar

                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="5" class="py-10 text-center text-gray-500">

                                    No existen cultivos registrados.

                                </td>

                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>

            <div class="mt-6">

                {{ $crops->links() }}

            </div>

        </x-card>

    </div>

@endsection
