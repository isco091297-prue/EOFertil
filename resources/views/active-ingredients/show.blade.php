@extends('layouts.app')

@section('title', 'Detalle Ingrediente Activo')

@section('content')

    <x-card>

        <div class="flex justify-between items-center mb-8">

            <h1 class="text-3xl font-bold">
                Detalle del Ingrediente Activo
            </h1>

            <a href="{{ route('active-ingredients.index') }}" class="rounded-xl border px-5 py-3">

                Regresar

            </a>

        </div>

        <div class="grid grid-cols-1 gap-8 md:grid-cols-2">

            <div>

                <p>
                    <strong>Nombre:</strong>
                    {{ $activeIngredient->name }}
                </p>

                <p class="mt-4">
                    <strong>Estado:</strong>

                    @if ($activeIngredient->is_active)
                        <span class="text-green-700">
                            Activo
                        </span>
                    @else
                        <span class="text-red-700">
                            Inactivo
                        </span>
                    @endif

                </p>

                <p class="mt-4">
                    <strong>Descripción:</strong>
                </p>

                <p class="mt-2 text-gray-600">

                    {{ $activeIngredient->description ?: 'Sin descripción.' }}

                </p>

            </div>

            <div>

                <div class="mb-4 flex items-center justify-between">

                    <h2 class="text-xl font-bold">
                        Productos vinculados
                    </h2>

                    <span class="rounded-full bg-green-50 px-3 py-1 text-sm font-semibold text-green-700">

                        {{ $activeIngredient->products->count() }}

                    </span>

                </div>

                @forelse($activeIngredient->products as $product)
                    <div class="mb-3 rounded-xl border border-gray-200 p-4">

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

                @empty

                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-5 text-gray-600">

                        Este ingrediente activo todavía no tiene productos vinculados.

                    </div>
                @endforelse

            </div>

        </div>

        <div class="mt-8 flex justify-end">

            <a href="{{ route('active-ingredients.edit', $activeIngredient) }}"
                class="rounded-xl bg-yellow-500 px-5 py-3 text-white hover:bg-yellow-600">

                Editar Ingrediente Activo

            </a>

        </div>

    </x-card>

@endsection
