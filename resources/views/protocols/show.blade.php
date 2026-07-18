@extends('layouts.app')

@section('title', 'Detalle del Protocolo')

@section('content')

    <div class="space-y-6">

        <div class="flex items-center justify-between">

            <div>

                <h1 class="text-3xl font-bold text-gray-800">
                    {{ $protocol->code }}
                </h1>

                <p class="mt-1 text-gray-500">
                    Información completa del protocolo.
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

        @foreach ($protocol->applications as $application)
            <div class="rounded-xl bg-white shadow">

                <div class="flex items-center justify-between border-b px-6 py-4">

                    <h2 class="text-lg font-semibold">

                        Aplicación {{ $application->application_number }}

                    </h2>

                </div>

                <div class="p-6">

                    @if ($application->description)
                        <div class="mb-6">

                            <h3 class="mb-2 font-semibold text-gray-700">
                                Descripción
                            </h3>

                            <p class="whitespace-pre-line text-gray-600">

                                {{ $application->description }}

                            </p>

                        </div>
                    @endif

                    <div class="overflow-x-auto">

                        <table class="min-w-full">

                            <thead class="bg-gray-100">

                                <tr>

                                    <th class="px-4 py-3 text-left">
                                        Producto
                                    </th>

                                    <th class="px-4 py-3 text-left">
                                        Marca
                                    </th>

                                    <th class="px-4 py-3 text-left">
                                        Categoría
                                    </th>

                                    <th class="px-4 py-3 text-center">
                                        Dosis
                                    </th>

                                    <th class="px-4 py-3 text-left">
                                        Observaciones
                                    </th>

                                </tr>

                            </thead>

                            <tbody class="divide-y divide-gray-200">

                                @forelse($application->products as $item)
                                    <tr>

                                        <td class="px-4 py-3">

                                            <div class="font-medium">

                                                {{ $item->product->code }}

                                            </div>

                                            <div class="text-sm text-gray-500">

                                                {{ $item->product->name }}

                                            </div>

                                        </td>

                                        <td class="px-4 py-3">

                                            {{ $item->product->brand->name }}

                                        </td>

                                        <td class="px-4 py-3">

                                            {{ $item->product->category->name }}

                                        </td>

                                        <td class="px-4 py-3 text-center font-semibold">

                                            {{ $item->dose }}

                                        </td>

                                        <td class="px-4 py-3">

                                            {{ $item->observations ?: '-' }}

                                        </td>

                                    </tr>

                                @empty

                                    <tr>

                                        <td colspan="5" class="px-4 py-6 text-center text-gray-500">

                                            Esta aplicación no tiene productos registrados.

                                        </td>

                                    </tr>
                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>
        @endforeach

    </div>

@endsection
