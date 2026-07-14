@extends('layouts.app')

@section('content')

<x-card>

    <div class="flex justify-between items-center mb-8">

        <div>

            <h1 class="text-3xl font-bold">

                Nuevo Usuario

            </h1>

            <p class="text-gray-500">

                Registro de usuarios del sistema.

            </p>

        </div>

    </div>

    <form
        action="{{ route('users.store') }}"
        method="POST">

        @include('users.form')

    </form>

</x-card>

@endsection
