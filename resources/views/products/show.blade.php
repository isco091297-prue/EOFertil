@extends('layouts.app')

@section('title','Detalle Producto')

@section('content')

<x-card>

    <div class="flex justify-between items-center mb-8">

        <h1 class="text-3xl font-bold">

            Detalle del Producto

        </h1>

        <a
            href="{{ route('products.index') }}"
            class="rounded-xl border px-5 py-3">

            Regresar

        </a>

    </div>

    <div class="grid grid-cols-2 gap-10">

        <div>

            <p><strong>Código:</strong> {{ $product->code }}</p>

            <p class="mt-4"><strong>Nombre:</strong> {{ $product->name }}</p>

            <p class="mt-4"><strong>Marca:</strong> {{ $product->brand->name }}</p>

            <p class="mt-4"><strong>Categoría:</strong> {{ $product->category->name }}</p>

            <p class="mt-4"><strong>Descripción:</strong></p>

            <p class="mt-2 text-gray-600">

                {{ $product->description }}

            </p>

        </div>

        <div>

            @if($product->image_path)

                <img
                    src="{{ asset('storage/'.$product->image_path) }}"
                    class="max-h-80 rounded-xl border">

            @endif

        </div>

    </div>

</x-card>

@endsection
