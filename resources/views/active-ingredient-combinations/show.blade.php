@extends('layouts.app')

@section('title', 'Detalle de Combinación')

@section('content')

    <div class="max-w-6xl mx-auto">

        <div class="mb-8 flex items-center justify-between gap-4">

            <div>

                <h1 class="text-2xl font-bold text-gray-900">
                    {{ $activeIngredientCombination->name }}
                </h1>

                <p class="mt-1 text-sm text-gray-500">
                    Detalle de la combinación de ingredientes.
                </p>

            </div>

            <div class="flex gap-3">

                <a href="{{ route('active-ingredient-combinations.edit', $activeIngredientCombination) }}"
                    class="rounded-xl bg-green-700 px-5 py-3 font-semibold text-white hover:bg-green-800">
                    Editar
                </a>

                <a href="{{ route('active-ingredient-combinations.index') }}"
                    class="rounded-xl border border-gray-300 px-5 py-3 hover:bg-gray-100">
                    Volver
                </a>

            </div>

        </div>


        {{-- Información --}}

        <div class="mb-8 rounded-2xl bg-white p-6 shadow-sm">

            <h2 class="mb-4 text-xl font-bold">
                Información
            </h2>

            @if ($activeIngredientCombination->description)
                <p class="mb-4 text-gray-600">
                    {{ $activeIngredientCombination->description }}
                </p>
            @endif


            @if ($activeIngredientCombination->is_active)
                <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                    Activo
                </span>
            @else
                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">
                    Inactivo
                </span>
            @endif

        </div>


        {{-- Ingredientes --}}

        <div class="mb-8 rounded-2xl bg-white p-6 shadow-sm">

            <h2 class="mb-4 text-xl font-bold">
                Ingredientes activos
            </h2>

            @if ($activeIngredientCombination->activeIngredients->isEmpty())

                <p class="text-gray-500">
                    No existen ingredientes asociados.
                </p>
            @else
                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">

                    @foreach ($activeIngredientCombination->activeIngredients as $ingredient)
                        <div class="rounded-xl border border-gray-200 p-4">

                            <div class="font-semibold text-gray-900">
                                {{ $ingredient->name }}
                            </div>

                            @if ($ingredient->description)
                                <div class="mt-1 text-sm text-gray-500">
                                    {{ $ingredient->description }}
                                </div>
                            @endif

                        </div>
                    @endforeach

                </div>

            @endif

        </div>


        {{-- Productos --}}

        <div class="rounded-2xl bg-white p-6 shadow-sm">

            <h2 class="mb-4 text-xl font-bold">
                Productos vinculados
            </h2>

            @if ($activeIngredientCombination->products->isEmpty())

                <p class="text-gray-500">
                    No existen productos asociados.
                </p>
            @else
                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">

                    @foreach ($activeIngredientCombination->products as $product)
                        <div class="rounded-xl border border-gray-200 p-4">

                            <div class="font-semibold text-gray-900">
                                {{ $product->name }}
                            </div>

                            <div class="mt-1 text-sm text-gray-500">

                                @if ($product->brand)
                                    {{ $product->brand->name }}
                                @else
                                    Sin marca
                                @endif

                                @if ($product->category)
                                    · {{ $product->category->name }}
                                @endif

                            </div>

                        </div>
                    @endforeach

                </div>

            @endif

        </div>

    </div>

@endsection
