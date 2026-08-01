@extends('layouts.app')

@section('title', 'Recetas')

@section('content')

    <div class="space-y-6">

        {{-- Encabezado --}}
        <div class="flex items-center justify-between">

            <div>

                <h1 class="text-3xl font-bold text-gray-800">
                    Recetas
                </h1>

                <p class="text-gray-500 mt-1">
                    Administración de recetas fitosanitarios.
                </p>

            </div>

            <a href="{{ route('protocols.create') }}"
                class="inline-flex items-center rounded-lg bg-indigo-600 px-5 py-2.5 text-white hover:bg-indigo-700 transition">
                Nueva Receta
            </a>

        </div>


        {{-- Tabla --}}
        <div class="overflow-hidden rounded-xl bg-white shadow">

            <table class="min-w-full">

                <thead class="bg-gray-100">

                    <tr class="text-left text-sm font-semibold text-gray-700">

                        <th class="px-6 py-4">
                            Código
                        </th>

                        <th class="px-6 py-4">
                            Cultivo
                        </th>

                        <th class="px-6 py-4">
                            Problema
                        </th>

                        <th class="px-6 py-4 text-center">
                            Estado
                        </th>

                        <th class="px-6 py-4 text-center">
                            Acciones
                        </th>

                    </tr>

                </thead>

                <tbody class="divide-y divide-gray-200">

                    @forelse($protocols as $protocol)
                        <tr class="hover:bg-gray-50">

                            <td class="px-6 py-4 font-semibold">

                                {{ $protocol->code }}

                            </td>

                            <td class="px-6 py-4">

                                {{ $protocol->crop->name }}

                            </td>

                            <td class="px-6 py-4">

                                {{ $protocol->problem->name }}

                            </td>

                            <td class="px-6 py-4 text-center">

                                @if ($protocol->is_active)
                                    <span class="rounded-full bg-green-100 px-3 py-1 text-sm text-green-700">

                                        Activo

                                    </span>
                                @else
                                    <span class="rounded-full bg-red-100 px-3 py-1 text-sm text-red-700">

                                        Inactivo

                                    </span>
                                @endif

                            </td>

                            <td class="px-6 py-4">

                                <div class="flex justify-center gap-2">

                                    <a href="{{ route('protocols.show', $protocol) }}"
                                        class="rounded bg-sky-500 px-3 py-2 text-white hover:bg-sky-600">
                                        Ver
                                    </a>

                                    <a href="{{ route('protocols.edit', $protocol) }}"
                                        class="rounded bg-amber-500 px-3 py-2 text-white hover:bg-amber-600">
                                        Editar
                                    </a>

                                    <form action="{{ route('protocols.destroy', $protocol) }}" method="POST"
                                        onsubmit="return confirm('¿Eliminar esta receta?')">

                                        @csrf
                                        @method('DELETE')

                                        <button class="rounded bg-red-600 px-3 py-2 text-white hover:bg-red-700">
                                            Eliminar
                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="5" class="px-6 py-10 text-center text-gray-500">

                                No existen recetas registrados.

                            </td>

                        </tr>
                    @endforelse

                </tbody>

            </table>

        </div>

        {{-- Paginación --}}
        <div>

            {{ $protocols->links() }}

        </div>

    </div>

@endsection
