@extends('layouts.app')

@section('content')

@if(session('success'))
    <div class="mb-6 rounded-xl bg-green-100 border border-green-300 text-green-700 p-4">
        {{ session('success') }}
    </div>
@endif

<x-card>

    <div class="flex justify-between items-center mb-6">

        <div>

            <h1 class="text-3xl font-bold">

                Sucursales

            </h1>

            <p class="text-gray-500">

                Administración de sucursales.

            </p>

        </div>

        <a
            href="{{ route('branches.create') }}"
            class="bg-green-700 hover:bg-green-800 text-white px-6 py-3 rounded-xl">

            Nueva Sucursal

        </a>

    </div>

    <form method="GET" class="mb-6">

        <x-input
            type="text"
            name="search"
            placeholder="Buscar..."
            value="{{ request('search') }}" />

    </form>

    <table class="w-full">

        <thead>

            <tr class="border-b">

                <th class="text-left py-3">

                    Código

                </th>

                <th class="text-left">

                    Nombre

                </th>

                <th class="text-left">

                    Almacén

                </th>

                <th class="text-left">

                    Zona

                </th>

                <th class="text-left">

                    Estado

                </th>

                <th class="text-center">

                    Acciones

                </th>

            </tr>

        </thead>

        <tbody>

        @forelse($branches as $branch)

            <tr class="border-b">

                <td class="py-4">

                    {{ $branch->code }}

                </td>

                <td>

                    {{ $branch->name }}

                </td>

                <td>

                    {{ $branch->warehouse->name }}

                </td>

                <td>

                    {{ $branch->zone->name }}

                </td>

                <td>

                    {{ $branch->is_active ? 'Activo' : 'Inactivo' }}

                </td>

                <td>

                    <div class="flex justify-center items-center gap-2">

                        <a
                            href="{{ route('branches.show',$branch) }}"
                            class="inline-flex items-center px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">

                            Ver

                        </a>

                        <a
                            href="{{ route('branches.edit',$branch) }}"
                            class="inline-flex items-center px-3 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg">

                            Editar

                        </a>

                        <form
                            action="{{ route('branches.destroy',$branch) }}"
                            method="POST"
                            onsubmit="return confirm('¿Eliminar esta sucursal?')">

                            @csrf
                            @method('DELETE')

                            <button
                                type="submit"
                                class="inline-flex items-center px-3 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg">

                                Eliminar

                            </button>

                        </form>

                    </div>

                </td>

            </tr>

        @empty

            <tr>

                <td
                    colspan="6"
                    class="text-center py-10">

                    No existen registros.

                </td>

            </tr>

        @endforelse

        </tbody>

    </table>

    <div class="mt-6">

        {{ $branches->links() }}

    </div>

</x-card>

@endsection
