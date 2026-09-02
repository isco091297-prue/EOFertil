@extends('layouts.app')

@section('title', 'Productos')

@section('content')

    <div class="space-y-6">

        <div class="flex items-center justify-between">

            <div>

                <h1 class="text-3xl font-bold">
                    Productos
                </h1>

                <p class="text-gray-500">
                    Administración de productos.
                </p>

            </div>

            <a href="{{ route('products.create') }}" class="rounded-xl bg-green-600 px-5 py-3 text-white hover:bg-green-700">

                + Nuevo Producto

            </a>

        </div>

        @if (session('success'))
            <x-alert type="success">

                {{ session('success') }}

            </x-alert>
        @endif

        <x-card>

            <form method="GET" class="mb-6">

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">

                    {{-- Buscar --}}
                    <div>

                        <label for="search" class="mb-1 block text-sm font-medium text-gray-700">

                            Buscar producto

                        </label>

                        <x-input type="text" name="search" id="search" placeholder="Buscar por código o nombre..."
                            value="{{ $search }}" />

                    </div>

                    {{-- Marca --}}
                    <div>

                        <label for="brand_id" class="mb-1 block text-sm font-medium text-gray-700">

                            Marca

                        </label>

                        <select name="brand_id" id="brand_id"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 focus:border-green-500 focus:ring-green-500">

                            <option value="">
                                Todas las marcas
                            </option>

                            @foreach ($brands as $brand)
                                <option value="{{ $brand->id }}" @selected((string) $brandId === (string) $brand->id)>

                                    {{ $brand->name }}

                                </option>
                            @endforeach

                        </select>

                    </div>

                    {{-- Categoría --}}
                    <div>

                        <label for="category_id" class="mb-1 block text-sm font-medium text-gray-700">

                            Categoría

                        </label>

                        <select name="category_id" id="category_id"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 focus:border-green-500 focus:ring-green-500">

                            <option value="">
                                Todas las categorías
                            </option>

                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected((string) $categoryId === (string) $category->id)>

                                    {{ $category->name }}

                                </option>
                            @endforeach

                        </select>

                    </div>

                </div>

                <div class="mt-4 flex gap-3">

                    <button type="submit"
                        class="rounded-lg bg-green-600 px-5 py-2.5 font-medium text-white hover:bg-green-700">

                        Filtrar

                    </button>

                    <a href="{{ route('products.index') }}"
                        class="rounded-lg bg-gray-200 px-5 py-2.5 font-medium text-gray-700 hover:bg-gray-300">

                        Limpiar filtros

                    </a>

                </div>

            </form>

            <div class="overflow-x-auto">

                <table class="min-w-full divide-y divide-gray-200">

                    <thead class="bg-gray-50">

                        <tr>

                            <th class="px-4 py-3">Imagen</th>

                            <th class="px-4 py-3">Código</th>

                            <th class="px-4 py-3">Producto</th>

                            <th class="px-4 py-3">Marca</th>

                            <th class="px-4 py-3">Categoría</th>

                            <th class="px-4 py-3">Estado</th>

                            <th class="px-4 py-3 text-center">Acciones</th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($products as $product)
                            <tr>

                                <td class="px-4 py-3">

                                    @if ($product->image_path)
                                        <img src="{{ asset('storage/' . $product->image_path) }}"
                                            class="h-14 w-14 rounded-lg object-cover">
                                    @endif

                                </td>

                                <td>{{ $product->code }}</td>

                                <td>{{ $product->name }}</td>

                                <td>{{ $product->brand->name }}</td>

                                <td>{{ $product->category->name }}</td>

                                <td>

                                    @if ($product->is_active)
                                        <span class="text-green-700">
                                            Activo
                                        </span>
                                    @else
                                        <span class="text-red-700">
                                            Inactivo
                                        </span>
                                    @endif

                                </td>

                                <td>

                                    <div class="flex justify-center gap-2">

                                        <a href="{{ route('products.show', $product) }}" class="rounded border px-3 py-2">

                                            Ver

                                        </a>

                                        <a href="{{ route('products.edit', $product) }}"
                                            class="rounded bg-yellow-500 px-3 py-2 text-white">

                                            Editar

                                        </a>

                                        <form action="{{ route('products.destroy', $product) }}" method="POST">

                                            @csrf
                                            @method('DELETE')

                                            <button onclick="return confirm('¿Eliminar producto?')"
                                                class="rounded bg-red-600 px-3 py-2 text-white">

                                                Eliminar

                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="7" class="py-8 text-center">

                                    No existen productos registrados.

                                </td>

                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>

            <div class="mt-6">

                {{ $products->links() }}

            </div>

        </x-card>

    </div>

@endsection
