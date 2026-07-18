@extends('layouts.app')

@section('title','Problemas')

@section('content')

<div class="space-y-6">

    <div class="flex items-center justify-between">

        <div>

            <h1 class="text-3xl font-bold">

                Problemas

            </h1>

            <p class="text-gray-500">

                Administración de problemas.

            </p>

        </div>

        <a
            href="{{ route('problems.create') }}"
            class="rounded-xl bg-green-600 px-5 py-3 text-white hover:bg-green-700">

            + Nuevo Problema

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
                value="{{ $search }}"
                placeholder="Buscar..." />

        </form>

        <div class="overflow-x-auto">

            <table class="min-w-full divide-y divide-gray-200">

                <thead class="bg-gray-50">

                    <tr>

                        <th class="px-4 py-3">Imagen</th>
                        <th class="px-4 py-3">Código</th>
                        <th class="px-4 py-3">Problema</th>
                        <th class="px-4 py-3">Cultivo</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3 text-center">Acciones</th>

                    </tr>

                </thead>

                <tbody>

                @forelse($problems as $problem)

                    <tr class="border-b hover:bg-gray-50">

                        <td class="px-4 py-3">

                            @if($problem->image_path)

                                <img
                                    src="{{ asset('storage/'.$problem->image_path) }}"
                                    class="h-16 w-16 rounded-lg border object-cover shadow-sm">

                            @else

                                <div class="flex h-16 w-16 items-center justify-center rounded-lg border bg-gray-100 text-xs text-gray-500">

                                    Sin imagen

                                </div>

                            @endif

                        </td>

                        <td class="px-4 py-3">

                            {{ $problem->code }}

                        </td>

                        <td class="px-4 py-3">

                            {{ $problem->name }}

                        </td>

                        <td class="px-4 py-3">

                            {{ $problem->crop->name }}

                        </td>

                        <td class="px-4 py-3">

                            @if($problem->is_active)

                                <span class="font-semibold text-green-700">

                                    Activo

                                </span>

                            @else

                                <span class="font-semibold text-red-700">

                                    Inactivo

                                </span>

                            @endif

                        </td>

                        <td class="px-4 py-3">

                            <div class="flex justify-center gap-2">

                                <a
                                    href="{{ route('problems.show',$problem) }}"
                                    class="rounded border px-3 py-2 hover:bg-gray-100">

                                    Ver

                                </a>

                                <a
                                    href="{{ route('problems.edit',$problem) }}"
                                    class="rounded bg-yellow-500 px-3 py-2 text-white hover:bg-yellow-600">

                                    Editar

                                </a>

                                <form
                                    action="{{ route('problems.destroy',$problem) }}"
                                    method="POST">

                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        onclick="return confirm('¿Eliminar problema?')"
                                        class="rounded bg-red-600 px-3 py-2 text-white hover:bg-red-700">

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
                            class="py-8 text-center text-gray-500">

                            No existen registros.

                        </td>

                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

        <div class="mt-6">

            {{ $problems->links() }}

        </div>

    </x-card>

</div>

@endsection
