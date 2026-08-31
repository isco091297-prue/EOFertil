@extends('layouts.app')

@section('title', 'Editar Receta')

@section('content')

    <div class="space-y-6">

        <div class="flex items-center justify-between">

            <div>

                <h1 class="text-3xl font-bold text-gray-800">
                    Editar Receta
                </h1>

                <p class="mt-1 text-gray-500">
                    Modifique la información del receta.
                </p>

            </div>

            <a href="{{ route('protocols.index') }}" class="rounded-lg bg-gray-200 px-5 py-2.5 transition hover:bg-gray-300">
                Regresar
            </a>

        </div>

        @if ($errors->any())

            <div class="rounded-lg border border-red-300 bg-red-50 p-4">

                <h3 class="mb-2 font-semibold text-red-700">
                    Se encontraron errores
                </h3>

                <ul class="list-disc space-y-1 pl-5 text-red-600">

                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach

                </ul>

            </div>

        @endif

        <form action="{{ route('protocols.update', $protocol) }}" method="POST" id="protocol-form" autocomplete="off">

            @csrf
            @method('PUT')

            @include('protocols._form')

        </form>

    </div>

    @include('protocols._application')

    @include('protocols._product')

    @include('protocols._active_ingredient')

    @include('protocols._active_ingredient_product')
    
    @include('protocols._active_ingredient_combination')

@endsection
