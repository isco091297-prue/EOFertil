@extends('layouts.app')

@section('content')

    @if (session('success'))
        <div class="mb-6 rounded-xl bg-green-100 border border-green-300 text-green-700 p-4">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 rounded-xl bg-red-100 border border-red-300 text-red-700 p-4">
            {{ session('error') }}
        </div>
    @endif

    <x-card>
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold">
                    Avisos
                </h1>

                <p class="text-gray-500">
                    Administra los mensajes que aparecerán en la aplicación móvil.
                </p>
            </div>

            <a
                href="{{ route('avisos.create') }}"
                class="bg-green-700 hover:bg-green-800 text-white px-5 py-3 rounded-xl font-semibold"
            >
                + Nuevo aviso
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="py-3 text-left">
                            Aviso
                        </th>

                        <th class="text-left">
                            Tipo
                        </th>

                        <th class="text-left">
                            Estado
                        </th>

                        <th class="text-left">
                            Enlace
                        </th>

                        <th class="text-center">
                            Acción
                        </th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($avisos as $aviso)
                        <tr class="border-b">
                            <td class="py-4">
                                <div class="font-semibold text-gray-900">
                                    {{ $aviso->titulo }}
                                </div>

                                <div class="text-sm text-gray-500 mt-1 max-w-xl whitespace-pre-line">
                                    {{ \Illuminate\Support\Str::limit($aviso->mensaje, 180) }}
                                </div>

                                <div class="text-xs text-gray-400 mt-2">
                                    {{ optional($aviso->created_at)->format('d/m/Y H:i') }}
                                </div>
                            </td>

                            <td>
                                @if ($aviso->tipo === 'informacion')
                                    <span class="text-blue-600 font-semibold">
                                        Información
                                    </span>
                                @elseif ($aviso->tipo === 'promocion')
                                    <span class="text-orange-600 font-semibold">
                                        Promoción
                                    </span>
                                @elseif ($aviso->tipo === 'importante')
                                    <span class="text-red-600 font-semibold">
                                        Importante
                                    </span>
                                @elseif ($aviso->tipo === 'actualizacion')
                                    <span class="text-purple-600 font-semibold">
                                        Actualización
                                    </span>
                                @endif
                            </td>

                            <td>
                                @if ($aviso->activo)
                                    <span class="text-green-700 font-semibold">
                                        ● Activo
                                    </span>
                                @else
                                    <span class="text-gray-500 font-semibold">
                                        ● Inactivo
                                    </span>
                                @endif
                            </td>

                            <td>
                                @if ($aviso->enlace)
                                    <a
                                        href="{{ $aviso->enlace }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="text-blue-600 hover:text-blue-800 font-semibold"
                                    >
                                        Ver enlace
                                    </a>
                                @else
                                    <span class="text-gray-400">
                                        —
                                    </span>
                                @endif
                            </td>

                            <td>
                                <div class="flex justify-center items-center gap-2">

                                    <a
                                        href="{{ route('avisos.edit', $aviso) }}"
                                        class="px-3 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white"
                                    >
                                        Editar
                                    </a>

                                    <form
                                        method="POST"
                                        action="{{ route('avisos.toggle', $aviso) }}"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <button
                                            type="submit"
                                            class="px-3 py-2 rounded-lg {{ $aviso->activo ? 'bg-yellow-500 hover:bg-yellow-600' : 'bg-green-600 hover:bg-green-700' }} text-white"
                                        >
                                            {{ $aviso->activo ? 'Desactivar' : 'Activar' }}
                                        </button>
                                    </form>

                                    <form
                                        method="POST"
                                        action="{{ route('avisos.destroy', $aviso) }}"
                                        onsubmit="return confirm('¿Seguro que deseas eliminar este aviso? Esta acción no se puede deshacer.');"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="px-3 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white"
                                        >
                                            Eliminar
                                        </button>
                                    </form>

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-10 text-gray-500">
                                No existen avisos registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-8">
            {{ $avisos->links() }}
        </div>
    </x-card>

@endsection