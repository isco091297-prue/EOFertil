@extends('layouts.app')

@section('title', 'Detalle de la Categoría')

@section('content')

<div class="max-w-4xl mx-auto">

    <x-card>

        <div class="flex items-center justify-between mb-6">

            <h1 class="text-2xl font-bold">

                {{ $category->name }}

            </h1>

            <a
                href="{{ route('categories.edit', $category) }}"
                class="rounded-xl bg-green-600 px-5 py-2 text-white hover:bg-green-700">

                Editar

            </a>

        </div>

        <div class="grid grid-cols-2 gap-8">

            <div>

                <p class="text-sm text-gray-500">

                    Código

                </p>

                <p class="font-semibold">

                    {{ $category->code }}

                </p>

            </div>

            <div>

                <p class="text-sm text-gray-500">

                    Estado

                </p>

                <span class="inline-flex rounded-full px-3 py-1 text-sm font-semibold {{ $category->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">

                    {{ $category->is_active ? 'Activo' : 'Inactivo' }}

                </span>

            </div>

        </div>

        @if($category->image_path)

            <div class="mt-8">

                <p class="mb-3 text-sm text-gray-500">

                    Imagen

                </p>

                <img
                    src="{{ asset('storage/' . $category->image_path) }}"
                    class="h-64 rounded-xl border object-cover">

            </div>

        @endif

        <div class="mt-8">

            <a
                href="{{ route('categories.index') }}"
                class="rounded-xl border border-gray-300 px-5 py-2">

                Regresar

            </a>

        </div>

    </x-card>

</div>

@endsection
