@extends('layouts.app')

@section('title', 'Detalle de Marca')

@section('content')

<x-card>

    <div class="flex items-center justify-between mb-8">

        <h1 class="text-3xl font-bold">

            Detalle de Marca

        </h1>

        <a
            href="{{ route('brands.index') }}"
            class="rounded-xl border border-gray-300 px-5 py-3 hover:bg-gray-100">

            Regresar

        </a>

    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-10">

        <div>

            <h2 class="mb-6 text-xl font-semibold">

                Información General

            </h2>

            <div class="space-y-5">

                <div>

                    <span class="font-semibold">
                        Código:
                    </span>

                    {{ $brand->code }}

                </div>

                <div>

                    <span class="font-semibold">
                        Nombre:
                    </span>

                    {{ $brand->name }}

                </div>

                <div>

                    <span class="font-semibold">
                        Estado:
                    </span>

                    @if($brand->is_active)

                        <span class="rounded-full bg-green-100 px-3 py-1 text-sm text-green-700">

                            Activo

                        </span>

                    @else

                        <span class="rounded-full bg-red-100 px-3 py-1 text-sm text-red-700">

                            Inactivo

                        </span>

                    @endif

                </div>

            </div>

        </div>

        <div>

            <h2 class="mb-6 text-xl font-semibold">

                Logo

            </h2>

            @if($brand->logo_path)

                <img
                    src="{{ asset('storage/' . $brand->logo_path) }}"
                    alt="{{ $brand->name }}"
                    class="max-h-72 rounded-xl border bg-white p-4 object-contain">

            @else

                <div class="flex h-72 items-center justify-center rounded-xl border bg-gray-100 text-gray-400">

                    Sin logo

                </div>

            @endif

        </div>

    </div>

</x-card>

@endsection
