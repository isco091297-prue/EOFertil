@extends('layouts.app')

@section('content')
    @if (session('success'))
        <div class="mb-6 rounded-xl bg-green-100 border border-green-300 text-green-700 p-4">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 rounded-xl bg-red-100 border border-red-300 text-red-700 p-4">
            {{ session('error') }}
        </div>
    @endif

    <x-card>

        <div class="flex justify-between items-center mb-8">

            <div>

                <h1 class="text-3xl font-bold">
                    Ventas
                </h1>

                <p class="text-gray-500">
                    Administración y revisión de facturas.
                </p>

            </div>

        </div>

        {{-- Filtros --}}

        <form method="GET" class="mb-6">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                <x-input type="text" name="search" placeholder="Buscar factura, nombre o cédula..."
                    value="{{ request('search') }}" />

                <select name="estado" class="border border-gray-300 rounded-xl px-4 py-3">

                    <option value="">
                        Todos los estados
                    </option>

                    <option value="procesando" @selected(request('estado') === 'procesando')>
                        Pendientes
                    </option>

                    <option value="confirmada" @selected(request('estado') === 'confirmada')>
                        Aprobadas
                    </option>

                    <option value="anulada" @selected(request('estado') === 'anulada')>
                        Anuladas
                    </option>

                </select>

                <button type="submit" class="bg-green-700 hover:bg-green-800 text-white px-6 py-3 rounded-xl">
                    Buscar
                </button>

            </div>

        </form>

        {{-- Tabla --}}

        <div class="overflow-x-auto">

            <table class="w-full">

                <thead>

                    <tr class="border-b">

                        <th class="py-3 text-left">
                            Factura
                        </th>

                        <th class="text-left">
                            Perchero
                        </th>

                        <th class="text-left">
                            Sucursal
                        </th>

                        <th class="text-left">
                            Fecha
                        </th>

                        <th class="text-left">
                            Total
                        </th>

                        <th class="text-left">
                            Cashback
                        </th>

                        <th class="text-left">
                            Estado
                        </th>

                        <th class="text-center">
                            Acción
                        </th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($invoices as $invoice)
                        <tr class="border-b">

                            <td class="py-4 font-semibold">
                                {{ $invoice->numero_factura_original }}
                            </td>

                            <td>
                                {{ $invoice->user?->first_name }}
                                {{ $invoice->user?->last_name }}
                            </td>

                            <td>
                                {{ $invoice->branch?->name }}
                            </td>

                            <td>
                                {{ optional($invoice->fecha_factura)->format('d/m/Y') }}
                            </td>

                            <td>
                                ${{ number_format((float) $invoice->total_factura, 2) }}
                            </td>

                            <td>
                                ${{ number_format((float) $invoice->cashback_generado, 2) }}
                            </td>

                            <td>

                                @if ($invoice->estado === 'procesando')
                                    <span class="text-yellow-600 font-semibold">
                                        Pendiente
                                    </span>
                                @elseif ($invoice->estado === 'confirmada')
                                    <span class="text-green-700 font-semibold">
                                        Aprobada
                                    </span>
                                @elseif ($invoice->estado === 'anulada')
                                    <span class="text-red-600 font-semibold">
                                        Anulada
                                    </span>
                                @else
                                    <span class="text-gray-600 font-semibold">
                                        {{ ucfirst($invoice->estado) }}
                                    </span>
                                @endif

                            </td>

                            <td>

                                <div class="flex justify-center">

                                    <a href="{{ route('invoices.show', $invoice) }}"
                                        class="px-3 py-2 rounded-lg bg-blue-600 text-white">
                                        Ver
                                    </a>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="8" class="text-center py-10">
                                No existen facturas registradas.
                            </td>

                        </tr>
                    @endforelse

                </tbody>

            </table>

        </div>

        <div class="mt-8">

            {{ $invoices->links() }}

        </div>

    </x-card>
@endsection
