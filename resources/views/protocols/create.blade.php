@extends('layouts.app')

@section('title', 'Nuevo Protocolo')

@section('content')

    <div class="space-y-6">

        <div class="flex items-center justify-between">

            <div>

                <h1 class="text-3xl font-bold text-gray-800">
                    Nuevo Protocolo
                </h1>

                <p class="mt-1 text-gray-500">
                    Registre un nuevo protocolo fitosanitario.
                </p>

            </div>

            <a href="{{ route('protocols.index') }}" class="rounded-lg bg-gray-200 px-5 py-2.5 transition hover:bg-gray-300">
                Regresar
            </a>

        </div>

        @if ($errors->any())

            <div class="rounded-lg border border-red-300 bg-red-100 p-4 text-red-700">

                <p class="font-semibold mb-2">
                    Se encontraron errores:
                </p>

                <ul class="list-disc list-inside space-y-1">

                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach

                </ul>

            </div>

        @endif

        <form action="{{ route('protocols.store') }}" method="POST" id="protocol-form" autocomplete="off">

            @include('protocols._form')

        </form>

    </div>

    @include('protocols._application')

    @include('protocols._product')

    @include('protocols._active_ingredient')

    @include('protocols._active_ingredient_product')

@endsection
