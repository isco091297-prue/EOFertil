@extends('layouts.auth')

@section('content')

<x-card class="w-full max-w-md">

    <x-logo />
@if ($errors->any())

<div class="mb-6 rounded-xl bg-red-100 border border-red-300 p-4 text-red-700">

    {{ $errors->first() }}

</div>

@endif
    <form
        action="{{ route('login.store') }}"
        method="POST"
        class="mt-8 space-y-6">

        @csrf

        <div>

            <label class="block text-sm font-semibold text-gray-700 mb-2">
                Usuario
            </label>

            <x-input
                type="text"
                name="username"
                placeholder="Ingrese su usuario"
                autocomplete="username"
                required />

        </div>

        <div>

            <label class="block text-sm font-semibold text-gray-700 mb-2">
                Contraseña
            </label>

            <div class="relative">

                <x-input
                    id="password"
                    type="password"
                    name="password"
                    placeholder="Ingrese su contraseña"
                    autocomplete="current-password"
                    required />

                <button
                    type="button"
                    id="togglePassword"
                    class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 hover:text-green-700">

                    👁

                </button>

            </div>

        </div>

        <x-button>
            Ingresar
        </x-button>

    </form>

</x-card>

<script>
document.addEventListener('DOMContentLoaded', () => {

    const password = document.getElementById('password');
    const button = document.getElementById('togglePassword');

    button.addEventListener('click', () => {

        password.type =
            password.type === 'password'
                ? 'text'
                : 'password';

    });

});
</script>

@endsection
