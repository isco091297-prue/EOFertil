@extends('layouts.app')

@section('title','Productos')

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

        <a
            href="{{ route('products.create') }}"
            class="rounded-xl bg-green-600 px-5 py-3 text-white hover:bg-green-700">

            + Nuevo Producto

        </a>

    </div>

    @if(session('success'))

        <x-alert type="success">

            {{ session('success') }}

        </x-alert>

    @endif

    <x-card>

        <form method="GET" class="mb-6">

            <x-input
                type="text"
                name="search"
                placeholder="Buscar..."
                value="{{ $search }}" />

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

                            @if($product->image_path)

                                <img
                                    src="{{ asset('storage/'.$product->image_path) }}"
                                    class="h-14 w-14 rounded-lg object-cover">

                            @endif

                        </td>

                        <td>{{ $product->code }}</td>

                        <td>{{ $product->name }}</td>

                        <td>{{ $product->brand->name }}</td>

                        <td>{{ $product->category->name }}</td>

                        <td>

                            @if($product->is_active)

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

                                <a
                                    href="{{ route('products.show',$product) }}"
                                    class="rounded border px-3 py-2">

                                    Ver

                                </a>

                                <a
                                    href="{{ route('products.edit',$product) }}"
                                    class="rounded bg-yellow-500 px-3 py-2 text-white">

                                    Editar

                                </a>

                                <form
                                    action="{{ route('products.destroy',$product) }}"
                                    method="POST">

                                    @csrf
                                    @method('DELETE')

                                    <button
                                        onclick="return confirm('¿Eliminar producto?')"
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
