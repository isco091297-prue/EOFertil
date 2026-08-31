@extends('layouts.app')

@section('title', 'Editar Combinación de Ingredientes')

@section('content')

    <div class="max-w-6xl mx-auto">

        <div class="mb-8">

            <h1 class="text-2xl font-bold text-gray-900">
                Editar Combinación de Ingredientes
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Modifica los ingredientes y productos asociados a esta combinación.
            </p>

        </div>


        <form method="POST"
            action="{{ route('active-ingredient-combinations.update', $activeIngredientCombination) }}"
            class="rounded-2xl bg-white p-6 shadow-sm">

            @method('PUT')

            @include('active-ingredient-combinations.form')

        </form>

    </div>

@endsection
