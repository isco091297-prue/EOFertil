@extends('layouts.app')

@section('title', 'Detalle del Cultivo')

@section('content')

<div class="max-w-4xl mx-auto">

    <x-card>

        <div class="flex justify-between items-center mb-6">

            <h1 class="text-2xl font-bold">
                {{ $crop->name }}
            </h1>

            <a
                href="{{ route('crops.edit', $crop) }}"
                class="px-5 py-2 rounded-xl bg-green-600 text-white hover:bg-green-700">

                Editar

            </a>

        </div>

        <div class="grid grid-cols-2 gap-8">

            <div>

                <p class="text-sm text-gray-500">Código</p>

                <p class="font-semibold">
                    {{ $crop->code }}
                </p>

            </div>

            <div>

                <p class="text-sm text-gray-500">Estado</p>

                <span class="inline-flex px-3 py-1 rounded-full text-sm font-semibold {{ $crop->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                    {{ $crop->is_active ? 'Activo' : 'Inactivo' }}
                </span>

            </div>

        </div>

        @if($crop->image_path)

            <div class="mt-8">

                <p class="text-sm text-gray-500 mb-3">
                    Imagen
                </p>

                <img
                    src="{{ asset('storage/'.$crop->image_path) }}"
                    class="h-64 rounded-xl border object-cover">

            </div>

        @endif

        <div class="mt-8">

            <a
                href="{{ route('crops.index') }}"
                class="px-5 py-2 rounded-xl border border-gray-300">

                Regresar

            </a>

        </div>

    </x-card>

</div>

@endsection
