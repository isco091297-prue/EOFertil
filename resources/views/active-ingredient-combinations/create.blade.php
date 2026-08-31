@extends('layouts.app')

@section('title', 'Nueva Combinación de Ingredientes')

@section('content')

    <div class="max-w-6xl mx-auto">

        <div class="mb-8">

            <h1 class="text-2xl font-bold text-gray-900">
                Nueva Combinación de Ingredientes
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Combina ingredientes activos existentes y selecciona los productos correspondientes.
            </p>

        </div>


        <form method="POST" action="{{ route('active-ingredient-combinations.store') }}"
            class="rounded-2xl bg-white p-6 shadow-sm">

            @include('active-ingredient-combinations.form')

        </form>

    </div>

@endsection
