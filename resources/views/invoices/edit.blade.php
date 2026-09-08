@extends('layouts.app')

@section('content')

    {{-- ============================================================
         MENSAJES
    ============================================================ --}}

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

    @if ($errors->any())
        <div class="mb-6 rounded-xl bg-red-100 border border-red-300 text-red-700 p-4">
            <p class="font-semibold mb-2">
                Hay errores en el formulario:
            </p>

            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    <div class="space-y-6">

        {{-- ============================================================
             ENCABEZADO
        ============================================================ --}}

        <div>

            <div class="flex items-center gap-3">

                <a href="{{ route('invoices.show', $invoice) }}" class="text-gray-500 hover:text-gray-800">
                    ← Factura
                </a>

                <span class="text-gray-300">
                    /
                </span>

                <span class="text-gray-600">
                    Revisar / editar
                </span>

            </div>

            <h1 class="text-3xl font-bold mt-3">
                Revisar factura {{ $invoice->numero_factura_original }}
            </h1>

            <p class="text-gray-500 mt-1">
                Modifica los valores registrados por el perchero antes de procesar la factura.
            </p>

        </div>


        {{-- ============================================================
             ADVERTENCIA
        ============================================================ --}}

        <div class="rounded-xl bg-yellow-50 border border-yellow-200 p-5">

            <div class="flex gap-3">

                <div class="text-xl">
                    ⚠️
                </div>

                <div>

                    <p class="font-semibold text-yellow-900">
                        Revisión administrativa
                    </p>

                    <p class="text-sm text-yellow-800 mt-1">
                        Los cambios realizados aquí pueden modificar el cashback
                        y los valores acumulados/ranking asociados a esta factura.
                        El sistema registrará los valores anteriores, los nuevos
                        valores y el motivo de la modificación.
                    </p>

                </div>

            </div>

        </div>


        {{-- ============================================================
             INFORMACIÓN REGISTRADA - NO EDITABLE
        ============================================================ --}}

        <x-card>

            <h2 class="text-xl font-bold mb-6">
                Información registrada
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                {{-- Perchero --}}

                <div>

                    <p class="text-sm text-gray-500">
                        Perchero
                    </p>

                    <p class="font-semibold mt-1">
                        {{ $invoice->user?->first_name }}
                        {{ $invoice->user?->last_name }}
                    </p>

                    <p class="text-sm text-gray-500 mt-1">
                        {{ $invoice->user?->identification }}
                    </p>

                </div>


                {{-- Sucursal --}}

                <div>

                    <p class="text-sm text-gray-500">
                        Sucursal
                    </p>

                    <p class="font-semibold mt-1">
                        {{ $invoice->branch?->name ?? 'Sin sucursal' }}
                    </p>

                </div>


                {{-- Campaña --}}

                <div>

                    <p class="text-sm text-gray-500">
                        Campaña
                    </p>

                    <p class="font-semibold mt-1">
                        {{ $invoice->cashbackCampaign?->nombre ?? 'Sin campaña' }}
                    </p>

                </div>

            </div>

        </x-card>


        {{-- ============================================================
             FORMULARIO
        ============================================================ --}}

        <form method="POST" action="{{ route('invoices.update', $invoice) }}" class="space-y-6">

            @csrf

            @method('PUT')


            {{-- ========================================================
                 DATOS DE LA FACTURA
            ======================================================== --}}

            <x-card>

                <h2 class="text-xl font-bold mb-6">
                    Datos de la factura
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    {{-- Número de factura --}}

                    <div>

                        <label for="numero_factura_original" class="block text-sm font-semibold text-gray-700 mb-2">
                            Número de factura
                        </label>

                        <input type="text" id="numero_factura_original" name="numero_factura_original"
                            value="{{ old('numero_factura_original', $invoice->numero_factura_original) }}" required
                            class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500">

                        @error('numero_factura_original')
                            <p class="text-sm text-red-600 mt-1">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- Fecha --}}

                    <div>

                        <label for="fecha_factura" class="block text-sm font-semibold text-gray-700 mb-2">
                            Fecha de factura
                        </label>

                        <input type="date" id="fecha_factura" name="fecha_factura"
                            value="{{ old('fecha_factura', optional($invoice->fecha_factura)->format('Y-m-d')) }}" required
                            class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500">

                        @error('fecha_factura')
                            <p class="text-sm text-red-600 mt-1">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- Total factura --}}

                    <div>

                        <label for="total_factura" class="block text-sm font-semibold text-gray-700 mb-2">
                            Total de factura
                        </label>

                        <div class="relative">

                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">
                                $
                            </span>

                            <input type="number" id="total_factura" name="total_factura"
                                value="{{ old('total_factura', $invoice->total_factura) }}" min="0.01" step="0.01"
                                required
                                class="w-full pl-8 rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500">

                        </div>

                        @error('total_factura')
                            <p class="text-sm text-red-600 mt-1">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- Total productos participantes --}}

                    <div>

                        <label for="total_productos_participantes" class="block text-sm font-semibold text-gray-700 mb-2">
                            Total productos participantes
                        </label>

                        <div class="relative">

                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">
                                $
                            </span>

                            <input type="number" id="total_productos_participantes" name="total_productos_participantes"
                                value="{{ old('total_productos_participantes', $invoice->total_productos_participantes) }}"
                                min="0" step="0.01" required
                                class="w-full pl-8 rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500">

                        </div>

                        <p class="text-xs text-gray-500 mt-1">
                            Este valor determina cuánto participa en el cálculo del cashback.
                        </p>

                        @error('total_productos_participantes')
                            <p class="text-sm text-red-600 mt-1">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                </div>

            </x-card>


            {{-- ========================================================
                 PRODUCTOS REGISTRADOS
            ======================================================== --}}

            <x-card>

                <div class="mb-6">

                    <h2 class="text-xl font-bold">
                        Productos registrados
                    </h2>

                    <p class="text-sm text-gray-500 mt-1">
                        Revisa y modifica los valores registrados para cada producto.
                    </p>

                </div>


                @if ($invoice->items->count())
                    <div class="overflow-x-auto">

                        <table class="w-full">

                            <thead>

                                <tr class="border-b">

                                    <th class="py-3 text-left">
                                        Producto
                                    </th>

                                    <th class="py-3 text-left">
                                        Valor registrado
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                                @foreach ($invoice->items as $item)
                                    <tr class="border-b">

                                        {{-- Producto --}}

                                        <td class="py-4">

                                            <div class="font-semibold">
                                                {{ $item->product?->name ?? 'Producto eliminado' }}
                                            </div>

                                            @if ($item->product)
                                                <div class="text-sm text-gray-500">
                                                    ID: {{ $item->product->id }}
                                                </div>
                                            @endif

                                        </td>


                                        {{-- Valor --}}

                                        <td class="py-4">

                                            <div class="relative max-w-xs">

                                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">
                                                    $
                                                </span>

                                                <input type="number" name="items[{{ $item->id }}][valor]"
                                                    value="{{ old('items.' . $item->id . '.valor', $item->valor) }}"
                                                    min="0" step="0.01" required
                                                    class="w-full pl-8 rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500">

                                            </div>

                                        </td>

                                    </tr>
                                @endforeach

                            </tbody>

                        </table>

                    </div>
                @else
                    <div class="rounded-xl bg-gray-50 p-8 text-center text-gray-500">
                        Esta factura no tiene productos registrados.
                    </div>
                @endif

            </x-card>


            {{-- ========================================================
                 MOTIVO
            ======================================================== --}}

            <x-card>

                <h2 class="text-xl font-bold mb-2">
                    Motivo de la modificación
                </h2>

                <p class="text-sm text-gray-500 mb-5">
                    Indica por qué estás corrigiendo la información de la factura.
                    Este dato quedará registrado en la auditoría.
                </p>

                <textarea name="motivo" rows="4" required maxlength="1000"
                    placeholder="Ejemplo: El perchero registró incorrectamente el total de productos participantes."
                    class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500">{{ old('motivo') }}</textarea>

                @error('motivo')
                    <p class="text-sm text-red-600 mt-1">
                        {{ $message }}
                    </p>
                @enderror

            </x-card>


            {{-- ========================================================
                 ACCIONES
            ======================================================== --}}

            <div class="flex flex-wrap gap-3">

                <a href="{{ route('invoices.show', $invoice) }}"
                    class="px-5 py-3 rounded-xl border border-gray-300 hover:bg-gray-50">
                    ← Cancelar
                </a>

                <button type="submit" class="px-5 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold"
                    onclick="return confirm('¿Estás seguro de guardar estos cambios? El sistema recalculará el cashback y registrará la modificación en la auditoría.');">
                    Guardar modificación
                </button>

            </div>

        </form>

    </div>

@endsection
