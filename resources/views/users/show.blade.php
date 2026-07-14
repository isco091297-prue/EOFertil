@extends('layouts.app')

@section('content')

<x-card>

    <h1 class="text-3xl font-bold mb-8">

        Información del Usuario

    </h1>

    <div class="grid grid-cols-2 gap-6">

        <div>

            <strong>Nombres</strong>

            <p>{{ $user->first_name }}</p>

        </div>

        <div>

            <strong>Apellidos</strong>

            <p>{{ $user->last_name }}</p>

        </div>

        <div>

            <strong>Cédula</strong>

            <p>{{ $user->identification }}</p>

        </div>

        <div>

            <strong>Teléfono</strong>

            <p>{{ $user->phone }}</p>

        </div>

        <div>

            <strong>Rol</strong>

            <p>{{ $user->role->name }}</p>

        </div>

        <div>

            <strong>Usuario</strong>

            <p>{{ $user->username }}</p>

        </div>

        <div>

            <strong>Email</strong>

            <p>{{ $user->email }}</p>

        </div>

        <div>

            <strong>Estado</strong>

            <p>

                {{ $user->is_active ? 'Activo' : 'Inactivo' }}

            </p>

        </div>

        @if($user->role_id == 2)

        <div>

            <strong>Almacén</strong>

            <p>{{ $user->warehouse?->name }}</p>

        </div>

        <div>

            <strong>Zona</strong>

            <p>{{ $user->zone?->name }}</p>

        </div>

        <div>

            <strong>Sucursal</strong>

            <p>{{ $user->branch?->name }}</p>

        </div>

        <div>

            <strong>Banco</strong>

            <p>{{ $user->bank }}</p>

        </div>

        <div>

            <strong>Tipo Cuenta</strong>

            <p>{{ $user->account_type }}</p>

        </div>

        <div>

            <strong>Número Cuenta</strong>

            <p>{{ $user->account_number }}</p>

        </div>

        @endif

    </div>

    <div class="mt-10">

        <a
            href="{{ route('users.index') }}"
            class="bg-green-700 text-white px-6 py-3 rounded-xl">

            Volver

        </a>

    </div>

</x-card>

@endsection
