@extends('layouts.app')

@section('content')

    @if (session('success'))
        <div class="mb-6 rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-green-800 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-green-100 text-green-700">
                    ✓
                </div>
                <p class="font-medium">
                    {{ session('success') }}
                </p>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-red-800 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-red-100 text-red-700">
                    !
                </div>
                <p class="font-medium">
                    {{ session('error') }}
                </p>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-red-800 shadow-sm">
            <p class="mb-2 font-semibold">
                Hay errores en el formulario:
            </p>

            <ul class="list-disc space-y-1 pl-5 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mx-auto max-w-6xl space-y-6">

        <div>
            <div class="flex items-center gap-3 text-sm">
                <a href="{{ route('invoices.show', $invoice) }}"
                    class="font-medium text-gray-500 transition hover:text-gray-900">
                    ← Factura
                </a>

                <span class="text-gray-300">/</span>

                <span class="text-gray-600">
                    Modificar
                </span>
            </div>

            <div class="mt-4">
                <h1 class="text-3xl font-bold tracking-tight text-gray-900">
                    Modificar factura {{ $invoice->numero_factura_original }}
                </h1>

                <p class="mt-2 max-w-3xl text-gray-500">
                    Corrige los valores de los productos registrados por el perchero.
                    Los demás datos de la factura permanecerán sin cambios.
                </p>
            </div>
        </div>

        <div class="rounded-2xl border border-blue-200 bg-blue-50 px-6 py-5 shadow-sm">
            <div class="flex items-start gap-4">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-xl">
                    ✏️
                </div>

                <div>
                    <h2 class="font-semibold text-blue-900">
                        Corrección administrativa
                    </h2>

                    <p class="mt-1 text-sm leading-6 text-blue-800">
                        Puedes corregir únicamente el valor de cada producto participante.
                        El sistema calculará automáticamente el total participante y el
                        cashback correspondiente a la campaña de esta factura.
                    </p>
                </div>
            </div>
        </div>

        <x-card>

            <div class="mb-6">
                <h2 class="text-xl font-bold text-gray-900">
                    Información registrada
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Datos originales de la factura.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">

                <div class="rounded-xl border border-gray-200 bg-gray-50 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Perchero
                    </p>

                    <p class="mt-2 font-semibold text-gray-900">
                        {{ $invoice->user?->first_name }}
                        {{ $invoice->user?->last_name }}
                    </p>

                    <p class="mt-1 text-sm text-gray-500">
                        {{ $invoice->user?->identification }}
                    </p>
                </div>

                <div class="rounded-xl border border-gray-200 bg-gray-50 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Sucursal
                    </p>

                    <p class="mt-2 font-semibold text-gray-900">
                        {{ $invoice->branch?->name ?? 'Sin sucursal' }}
                    </p>
                </div>

                <div class="rounded-xl border border-gray-200 bg-gray-50 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Campaña
                    </p>

                    <p class="mt-2 font-semibold text-gray-900">
                        {{ $invoice->cashbackCampaign?->nombre ?? 'Sin campaña' }}
                    </p>
                </div>

            </div>

        </x-card>

        <form method="POST" action="{{ route('invoices.update', $invoice) }}" class="space-y-6" id="invoice-edit-form">
            @csrf
            @method('PUT')

            <x-card>

                <div class="mb-6">
                    <h2 class="text-xl font-bold text-gray-900">
                        Datos de la factura
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        Estos datos pertenecen a la factura original y no pueden modificarse.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-700">
                            Número de factura
                        </label>

                        <input type="text" value="{{ $invoice->numero_factura_original }}" readonly
                            class="w-full rounded-xl border-gray-200 bg-gray-100 px-4 py-3 font-medium text-gray-700 shadow-sm cursor-not-allowed">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-700">
                            Fecha de factura
                        </label>

                        <input type="text" value="{{ optional($invoice->fecha_factura)->format('d/m/Y') }}" readonly
                            class="w-full rounded-xl border-gray-200 bg-gray-100 px-4 py-3 font-medium text-gray-700 shadow-sm cursor-not-allowed">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-700">
                            Total de factura
                        </label>

                        <div class="relative">
                            <span
                                class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-sm font-semibold text-gray-500">
                                $
                            </span>

                            <input type="text" value="{{ number_format((float) $invoice->total_factura, 2, '.', '') }}"
                                readonly
                                class="w-full rounded-xl border-gray-200 bg-gray-100 py-3 pl-10 pr-4 font-semibold text-gray-700 shadow-sm cursor-not-allowed">
                        </div>

                        <p class="mt-2 text-xs text-gray-500">
                            Total registrado en la factura original.
                        </p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-700">
                            Total productos participantes
                        </label>

                        <div class="relative">
                            <span
                                class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-sm font-semibold text-gray-500">
                                $
                            </span>

                            <input type="text" id="total_productos_participantes_visible"
                                value="{{ number_format((float) $invoice->total_productos_participantes, 2, '.', '') }}"
                                readonly
                                class="w-full rounded-xl border-gray-200 bg-gray-100 py-3 pl-10 pr-4 font-semibold text-gray-700 shadow-sm cursor-not-allowed">
                        </div>

                        <p class="mt-2 text-xs text-gray-500">
                            Se actualiza automáticamente al modificar los productos.
                        </p>
                    </div>

                </div>

            </x-card>

            <x-card>

                <div class="mb-6">
                    <h2 class="text-xl font-bold text-gray-900">
                        Cashback aplicado
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        El cálculo utiliza la campaña que corresponde a esta factura.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                    <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Campaña utilizada
                        </p>

                        <p class="mt-2 font-semibold text-gray-900">
                            {{ $invoice->cashbackCampaign?->nombre ?? 'Sin campaña' }}
                        </p>

                        <p class="mt-2 text-sm leading-5 text-gray-500">
                            Esta factura conserva esta campaña para sus cálculos.
                        </p>
                    </div>

                    <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Porcentaje de cashback
                        </p>

                        <p id="cashback-porcentaje" class="mt-2 text-3xl font-bold text-gray-900">
                            {{ number_format((float) ($invoice->cashbackCampaign?->porcentaje ?? 0), 2, '.', '') }}%
                        </p>

                        <p class="mt-2 text-sm leading-5 text-gray-500">
                            Porcentaje perteneciente a la campaña de esta factura.
                        </p>
                    </div>

                </div>

                <div class="mt-6 rounded-2xl border border-green-200 bg-green-50 p-6">
                    <div class="flex items-center justify-between gap-6">

                        <div>
                            <p class="text-sm font-semibold text-green-700">
                                Cashback calculado
                            </p>

                            <p class="mt-1 text-sm text-green-600">
                                Se actualiza automáticamente según los valores ingresados.
                            </p>
                        </div>

                        <div id="cashback-calculado" class="whitespace-nowrap text-3xl font-bold text-green-800">
                            $0.00
                        </div>

                    </div>
                </div>

            </x-card>

            <x-card>

                <div class="mb-6">
                    <h2 class="text-xl font-bold text-gray-900">
                        Productos registrados
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        Modifica únicamente el valor correspondiente a cada producto.
                    </p>
                </div>

                @if ($invoice->items->count())
                    <div class="space-y-4">

                        @foreach ($invoice->items as $item)
                            <div
                                class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:border-gray-300 hover:shadow-md">

                                <div class="grid grid-cols-1 gap-6 lg:grid-cols-[1fr_280px] lg:items-center">

                                    <div>
                                        <div class="flex items-center gap-3">

                                            <div
                                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gray-100 text-gray-600">
                                                📦
                                            </div>

                                            <div>
                                                <p class="text-lg font-bold text-gray-900">
                                                    {{ $item->product?->name ?? 'Producto eliminado' }}
                                                </p>

                                                @if ($item->product)
                                                    <p class="mt-0.5 text-sm text-gray-500">
                                                        ID del producto: {{ $item->product->id }}
                                                    </p>
                                                @endif
                                            </div>

                                        </div>

                                        <p class="mt-4 text-sm text-gray-500">
                                            Ingresa el valor correcto registrado para este producto.
                                        </p>
                                    </div>

                                    <div>
                                        <label for="item-{{ $item->id }}"
                                            class="mb-2 block text-sm font-semibold text-gray-700">
                                            Valor registrado
                                        </label>

                                        <div class="relative">

                                            <div
                                                class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                                                <span class="text-base font-semibold text-gray-500">
                                                    $
                                                </span>
                                            </div>

                                            <input type="number" id="item-{{ $item->id }}"
                                                name="items[{{ $item->id }}][valor]"
                                                value="{{ old('items.' . $item->id . '.valor', number_format((float) $item->valor, 2, '.', '')) }}"
                                                min="0" step="0.01" inputmode="decimal" required
                                                data-product-value
                                                class="block w-full rounded-xl border-gray-300 bg-white py-3 pl-11 pr-4 text-lg font-bold text-gray-900 shadow-sm outline-none transition placeholder:text-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">

                                        </div>

                                        @error('items.' . $item->id . '.valor')
                                            <p class="mt-2 text-sm font-medium text-red-600">
                                                {{ $message }}
                                            </p>
                                        @enderror

                                    </div>

                                </div>

                            </div>
                        @endforeach

                    </div>
                @else
                    <div class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 px-6 py-12 text-center">
                        <p class="text-gray-500">
                            Esta factura no tiene productos registrados.
                        </p>
                    </div>
                @endif

            </x-card>

            <x-card>

                <div class="mb-6">
                    <h2 class="text-xl font-bold text-gray-900">
                        Resumen de la corrección
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        Estos valores se actualizan automáticamente mientras modificas los productos.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">

                    <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5">
                        <p class="text-sm font-medium text-gray-500">
                            Total de factura
                        </p>

                        <p class="mt-2 text-2xl font-bold text-gray-900">
                            ${{ number_format((float) $invoice->total_factura, 2, '.', '') }}
                        </p>
                    </div>

                    <div class="rounded-2xl border border-blue-200 bg-blue-50 p-5">
                        <p class="text-sm font-medium text-blue-700">
                            Total participante
                        </p>

                        <p id="resumen-total" class="mt-2 text-2xl font-bold text-blue-900">
                            ${{ number_format((float) $invoice->total_productos_participantes, 2, '.', '') }}
                        </p>
                    </div>

                    <div class="rounded-2xl border border-green-200 bg-green-50 p-5">
                        <p class="text-sm font-medium text-green-700">
                            Cashback calculado
                        </p>

                        <p id="resumen-cashback" class="mt-2 text-2xl font-bold text-green-900">
                            $0.00
                        </p>
                    </div>

                </div>

                <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 p-5">
                    <div class="flex items-start gap-3">
                        <div class="mt-0.5 text-lg">
                            ⚠️
                        </div>

                        <div>
                            <p class="font-semibold text-amber-900">
                                La modificación no aprueba la factura
                            </p>

                            <p class="mt-1 text-sm leading-6 text-amber-800">
                                Después de guardar los cambios, la factura continuará
                                pendiente de revisión. Deberá ser aprobada posteriormente
                                si toda la información es correcta.
                            </p>
                        </div>
                    </div>
                </div>

            </x-card>

            <x-card>

                <div class="mb-5">
                    <h2 class="text-xl font-bold text-gray-900">
                        Motivo de la modificación
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        Explica por qué estás corrigiendo los valores. Este motivo quedará
                        registrado en la auditoría administrativa.
                    </p>
                </div>

                <textarea name="motivo" rows="4" required maxlength="1000"
                    placeholder="Ejemplo: El perchero registró incorrectamente el valor de un producto participante."
                    class="block w-full rounded-xl border-gray-300 px-4 py-3 text-gray-900 shadow-sm outline-none transition placeholder:text-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">{{ old('motivo') }}</textarea>

                @error('motivo')
                    <p class="mt-2 text-sm font-medium text-red-600">
                        {{ $message }}
                    </p>
                @enderror

            </x-card>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

                <a href="{{ route('invoices.show', $invoice) }}"
                    class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-6 py-3 font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50">
                    Cancelar
                </a>

                <button type="submit"
                    class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-6 py-3 font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                    onclick="return confirm('¿Estás seguro de guardar esta corrección? La factura continuará pendiente de revisión.');">
                    Guardar modificación
                </button>

            </div>

        </form>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const productInputs = document.querySelectorAll('[data-product-value]');
            const totalVisible = document.getElementById('total_productos_participantes_visible');
            const cashbackElement = document.getElementById('cashback-calculado');
            const resumenTotal = document.getElementById('resumen-total');
            const resumenCashback = document.getElementById('resumen-cashback');

            const porcentaje = {{ (float) ($invoice->cashbackCampaign?->porcentaje ?? 0) }};

            function formatMoney(value) {
                return Number(value).toFixed(2);
            }

            function recalcular() {

                let total = 0;

                productInputs.forEach(function(input) {

                    let value = parseFloat(input.value);

                    if (isNaN(value) || value < 0) {
                        value = 0;
                    }

                    total += value;
                });

                total = Math.round((total + Number.EPSILON) * 100) / 100;

                const cashback = Math.round(
                    (total * porcentaje / 100 + Number.EPSILON) * 100
                ) / 100;

                if (totalVisible) {
                    totalVisible.value = formatMoney(total);
                }

                if (cashbackElement) {
                    cashbackElement.textContent = '$' + formatMoney(cashback);
                }

                if (resumenTotal) {
                    resumenTotal.textContent = '$' + formatMoney(total);
                }

                if (resumenCashback) {
                    resumenCashback.textContent = '$' + formatMoney(cashback);
                }
            }

            productInputs.forEach(function(input) {

                input.addEventListener('input', recalcular);
                input.addEventListener('change', recalcular);

            });

            recalcular();

        });
    </script>

@endsection
