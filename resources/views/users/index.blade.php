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
                    Usuarios
                </h1>

                <p class="text-gray-500">
                    Administración de usuarios.
                </p>
            </div>

            <a
                href="{{ route('users.create') }}"
                class="bg-green-700 hover:bg-green-800 text-white px-6 py-3 rounded-xl"
            >
                Nuevo Usuario
            </a>

        </div>

        <form method="GET" class="mb-6">
            <x-input
                type="text"
                name="search"
                placeholder="Buscar..."
                value="{{ request('search') }}"
            />
        </form>

        <div class="overflow-x-auto">

            <table class="w-full">

                <thead>

                    <tr class="border-b">

                        <th class="py-3 text-left">
                            Nombre
                        </th>

                        <th class="text-left">
                            Rol
                        </th>

                        <th class="text-left">
                            Almacén
                        </th>

                        <th class="text-left">
                            Zona
                        </th>

                        <th class="text-left">
                            Sucursal
                        </th>

                        <th class="text-left">
                            Usuario
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

                    @forelse($users as $user)

                        <tr class="border-b">

                            <td class="py-4">
                                {{ $user->first_name }}
                                {{ $user->last_name }}
                            </td>

                            <td>
                                {{ $user->role?->name }}
                            </td>

                            <td>
                                {{ $user->warehouse?->name }}
                            </td>

                            <td>
                                {{ $user->zone?->name }}
                            </td>

                            <td>
                                {{ $user->branch?->name }}
                            </td>

                            <td>
                                {{ $user->username }}
                            </td>

                            <td>

                                @if ($user->is_active)

                                    <span class="text-green-700 font-semibold">
                                        Activo
                                    </span>

                                @else

                                    <span class="text-yellow-600 font-semibold">
                                        Pendiente
                                    </span>

                                @endif

                            </td>

                            <td>

                                <div class="flex justify-center gap-2 flex-wrap">

                                    <a
                                        href="{{ route('users.show', $user) }}"
                                        class="px-3 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white"
                                    >
                                        Ver
                                    </a>

                                    <a
                                        href="{{ route('users.edit', $user) }}"
                                        class="px-3 py-2 rounded-lg bg-yellow-500 hover:bg-yellow-600 text-white"
                                    >
                                        Editar
                                    </a>

                                    @if(!$user->is_active)

                                        <form
                                            action="{{ route('users.approve', $user) }}"
                                            method="POST"
                                            onsubmit="return confirm('¿Desea aprobar este usuario?')"
                                        >

                                            @csrf
                                            @method('PATCH')

                                            <button
                                                type="submit"
                                                class="px-3 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white"
                                            >
                                                Aprobar
                                            </button>

                                        </form>

                                    @endif

                                  @if($user->role?->name !== 'Administrator')
    <a href="{{ route('users.cleanup.preview', $user) }}" class="px-3 py-2 rounded-lg bg-amber-500 hover:bg-amber-600 text-white">
        🧹 Limpiar
    </a>

    @if($user->id !== auth()->id())
        <a href="{{ route('users.delete.preview', $user) }}" class="px-3 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white">
            🗑️ Eliminar
        </a>
    @endif
@endif

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="8"
                                class="text-center py-10"
                            >
                                No existen usuarios registrados.
                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <div class="mt-8">
            {{ $users->links() }}
        </div>

    </x-card>
@endsection