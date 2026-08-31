@extends('layouts.app')

@section('title', 'Detalle de la Receta')

@section('content')

    <div class="space-y-6">

        {{-- ========================================================= --}}
        {{-- ENCABEZADO --}}
        {{-- ========================================================= --}}

        <div class="flex items-center justify-between">

            <div>

                <h1 class="text-3xl font-bold text-gray-800">
                    {{ $protocol->code }}
                </h1>

                <p class="mt-1 text-gray-500">
                    Información completa del receta.
                </p>

            </div>

            <div class="flex gap-3">

                <a href="{{ route('protocols.edit', $protocol) }}"
                    class="rounded-lg bg-amber-500 px-5 py-2.5 text-white transition hover:bg-amber-600">
                    Editar
                </a>

                <a href="{{ route('protocols.index') }}"
                    class="rounded-lg bg-gray-200 px-5 py-2.5 transition hover:bg-gray-300">
                    Regresar
                </a>

            </div>

        </div>

        {{-- ========================================================= --}}
        {{-- INFORMACIÓN GENERAL --}}
        {{-- ========================================================= --}}

        <div class="rounded-xl bg-white shadow">

            <div class="border-b px-6 py-4">

                <h2 class="text-lg font-semibold text-gray-800">
                    Información General
                </h2>

            </div>

            <div class="grid grid-cols-1 gap-6 p-6 md:grid-cols-3">

                <div>

                    <p class="text-sm text-gray-500">
                        Código
                    </p>

                    <p class="text-lg font-semibold">
                        {{ $protocol->code }}
                    </p>

                </div>

                <div>

                    <p class="text-sm text-gray-500">
                        Cultivo
                    </p>

                    <p class="font-semibold">
                        {{ $protocol->crop->name }}
                    </p>

                </div>

                <div>

                    <p class="text-sm text-gray-500">
                        Problema
                    </p>

                    <p class="font-semibold">
                        {{ $protocol->problem->name }}
                    </p>

                </div>

            </div>

        </div>

        {{-- ========================================================= --}}
        {{-- APLICACIONES --}}
        {{-- ========================================================= --}}

        @foreach ($protocol->applications as $application)
            <div class="overflow-hidden rounded-xl bg-white shadow">

                {{-- Cabecera aplicación --}}

                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">

                    <div>

                        <h2 class="text-xl font-semibold text-gray-800">
                            Aplicación {{ $application->application_number }}
                        </h2>

                        @if ($application->application_type)
                            <p class="mt-1 text-sm text-gray-500">
                                {{ $application->application_type }}
                            </p>
                        @endif

                    </div>

                </div>

                <div class="space-y-8 p-6">

                    {{-- Descripción --}}

                    @if ($application->description)
                        <div>

                            <h3 class="mb-2 font-semibold text-gray-700">
                                Descripción
                            </h3>

                            <p class="whitespace-pre-line text-gray-600">
                                {{ $application->description }}
                            </p>

                        </div>
                    @endif


                    {{-- ================================================= --}}
                    {{-- PRODUCTOS EOFERTIL --}}
                    {{-- ================================================= --}}

                    <div class="overflow-hidden rounded-xl border border-green-200">

                        <div class="border-b border-green-200 bg-green-50 px-5 py-4">

                            <h3 class="font-semibold text-green-800">
                                Productos EOFertil
                            </h3>

                            <p class="mt-1 text-sm text-gray-500">
                                Productos recomendados directamente para esta aplicación.
                            </p>

                        </div>

                        <div class="overflow-x-auto">

                            <table class="min-w-full">

                                <thead class="bg-gray-50">

                                    <tr>

                                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">
                                            Producto
                                        </th>

                                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">
                                            Marca
                                        </th>

                                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">
                                            Categoría
                                        </th>

                                        <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">
                                            Dosis
                                        </th>

                                    </tr>

                                </thead>

                                <tbody class="divide-y divide-gray-200">

                                    @forelse ($application->products as $item)
                                        <tr>

                                            <td class="px-4 py-3">

                                                <div class="font-medium text-gray-800">
                                                    {{ $item->product->code }}
                                                </div>

                                                <div class="text-sm text-gray-500">
                                                    {{ $item->product->name }}
                                                </div>

                                            </td>

                                            <td class="px-4 py-3 text-gray-700">

                                                {{ $item->product->brand?->name ?? '-' }}

                                            </td>

                                            <td class="px-4 py-3 text-gray-700">

                                                {{ $item->product->category?->name ?? '-' }}

                                            </td>

                                            <td class="px-4 py-3 text-center">

                                                <span
                                                    class="inline-flex rounded-lg bg-green-100 px-3 py-1 font-semibold text-green-800">

                                                    {{ $item->dose }}
                                                    {{ $item->unit }}
                                                    /
                                                    {{ $item->application_base }}

                                                </span>

                                            </td>

                                        </tr>

                                    @empty

                                        <tr>

                                            <td colspan="4" class="px-4 py-6 text-center text-gray-500">

                                                Esta aplicación no tiene productos EOFertil directos.

                                            </td>

                                        </tr>
                                    @endforelse

                                </tbody>

                            </table>

                        </div>

                    </div>


                    {{-- ================================================= --}}
                    {{-- INGREDIENTES ACTIVOS --}}
                    {{-- ================================================= --}}

                    <div class="overflow-hidden rounded-xl border border-blue-200">

                        <div class="border-b border-blue-200 bg-blue-50 px-5 py-4">

                            <h3 class="font-semibold text-blue-800">
                                Ingredientes Activos
                            </h3>

                            <p class="mt-1 text-sm text-gray-500">
                                Ingredientes activos y productos recomendados.
                            </p>

                        </div>

                        <div class="space-y-5 p-5">

                            @forelse ($application->activeIngredients as $protocolActiveIngredient)
                                <div class="overflow-hidden rounded-xl border border-gray-200">

                                    {{-- Ingrediente activo --}}

                                    <div class="bg-gray-50 px-5 py-4">

                                        <div class="flex items-center justify-between">

                                            <div>

                                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                                    Ingrediente activo
                                                </p>

                                                <h4 class="mt-1 text-lg font-bold text-blue-800">

                                                    {{ $protocolActiveIngredient->activeIngredient->name }}

                                                </h4>

                                            </div>

                                            <span
                                                class="rounded-full bg-blue-100 px-3 py-1 text-sm font-medium text-blue-700">

                                                {{ $protocolActiveIngredient->products->count() }}
                                                producto(s)

                                            </span>

                                        </div>

                                    </div>

                                    {{-- Productos recomendados --}}

                                    <div class="overflow-x-auto">

                                        <table class="min-w-full">

                                            <thead class="bg-white">

                                                <tr>

                                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">
                                                        Producto recomendado
                                                    </th>

                                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">
                                                        Marca
                                                    </th>

                                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">
                                                        Categoría
                                                    </th>

                                                    <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">
                                                        Dosis
                                                    </th>

                                                </tr>

                                            </thead>

                                            <tbody class="divide-y divide-gray-200">

                                                @forelse ($protocolActiveIngredient->products as $item)
                                                    <tr>

                                                        <td class="px-4 py-3">

                                                            <div class="font-medium text-gray-800">

                                                                {{ $item->product->code }}

                                                            </div>

                                                            <div class="text-sm text-gray-500">

                                                                {{ $item->product->name }}

                                                            </div>

                                                        </td>

                                                        <td class="px-4 py-3 text-gray-700">

                                                            {{ $item->product->brand?->name ?? '-' }}

                                                        </td>

                                                        <td class="px-4 py-3 text-gray-700">

                                                            {{ $item->product->category?->name ?? '-' }}

                                                        </td>

                                                        <td class="px-4 py-3 text-center">

                                                            <span
                                                                class="inline-flex rounded-lg bg-blue-100 px-3 py-1 font-semibold text-blue-800">

                                                                {{ $item->dose }}
                                                                {{ $item->unit }}
                                                                /
                                                                {{ $item->application_base }}

                                                            </span>

                                                        </td>

                                                    </tr>

                                                @empty

                                                    <tr>

                                                        <td colspan="4" class="px-4 py-6 text-center text-gray-500">

                                                            No existen productos recomendados para este ingrediente activo.

                                                        </td>

                                                    </tr>
                                                @endforelse

                                            </tbody>

                                        </table>

                                    </div>

                                </div>

                            @empty

                                <div class="py-6 text-center text-gray-500">

                                    Esta aplicación no tiene ingredientes activos registrados.

                                </div>
                            @endforelse

                        </div>

                    </div>
                    {{-- ========================================================= --}}
                    {{-- COMBINACIONES DE INGREDIENTES ACTIVOS --}}
                    {{-- ========================================================= --}}

                    <div class="overflow-hidden rounded-xl border border-purple-200">

                        <div class="border-b border-purple-200 bg-purple-50 px-5 py-4">

                            <h3 class="font-semibold text-purple-800">
                                Combinaciones de Ingredientes Activos
                            </h3>

                            <p class="mt-1 text-sm text-gray-500">
                                Combinaciones de ingredientes activos recomendadas para esta aplicación.
                            </p>

                        </div>

                        <div class="overflow-x-auto">

                            <table class="min-w-full">

                                <thead class="bg-gray-50">

                                    <tr>

                                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">
                                            Combinación
                                        </th>

                                        <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">
                                            Dosis
                                        </th>

                                        <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">
                                            Unidad
                                        </th>

                                        <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">
                                            Base de aplicación
                                        </th>

                                    </tr>

                                </thead>

                                <tbody class="divide-y divide-gray-200">

                                    @forelse ($application->activeIngredientCombinations as $item)
                                        <tr>

                                            <td class="px-4 py-3">

                                                <div class="font-medium text-purple-800">
                                                    {{ $item->activeIngredientCombination->name }}
                                                </div>

                                                @if ($item->activeIngredientCombination->description)
                                                    <div class="text-sm text-gray-500">
                                                        {{ $item->activeIngredientCombination->description }}
                                                    </div>
                                                @endif

                                            </td>

                                            <td class="px-4 py-3 text-center">

                                                <span
                                                    class="inline-flex rounded-lg bg-purple-100 px-3 py-1 font-semibold text-purple-800">

                                                    {{ $item->dose }}

                                                </span>

                                            </td>

                                            <td class="px-4 py-3 text-center text-gray-700">

                                                {{ $item->unit }}

                                            </td>

                                            <td class="px-4 py-3 text-center text-gray-700">

                                                {{ $item->application_base }}

                                            </td>

                                        </tr>

                                    @empty

                                        <tr>

                                            <td colspan="4" class="px-4 py-6 text-center text-gray-500">

                                                Esta aplicación no tiene combinaciones de ingredientes activos registradas.

                                            </td>

                                        </tr>
                                    @endforelse

                                </tbody>

                            </table>

                        </div>

                    </div>
                </div>

            </div>
        @endforeach

    </div>

@endsection
