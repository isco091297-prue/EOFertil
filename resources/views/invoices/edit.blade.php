@extends('layouts.app')

@section('content')

    @php
        $existingProductIds = $invoice->items->pluck('product_id')->map(fn($id) => (int) $id)->values()->all();

        $availableProducts = \App\Models\Product::query()
            ->with('brand')
            ->where('is_active', true)
            ->whereHas('brand', function ($query) {
                $query->whereRaw('UPPER(TRIM(name)) = ?', ['EOFERTIL']);
            })
            ->whereNotIn('id', $existingProductIds)
            ->orderBy('name')
            ->get();
    @endphp

    @if (session('success'))
        <div class="mx-auto mb-5 max-w-6xl rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mx-auto mb-5 max-w-6xl rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mx-auto mb-5 max-w-6xl rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <p class="font-semibold">Hay errores en el formulario:</p>

            <ul class="mt-1 list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mx-auto max-w-6xl">

        <div class="mb-5">
            <div class="flex items-center gap-2 text-sm text-gray-500">
                <a href="{{ route('invoices.show', $invoice) }}" class="font-medium hover:text-gray-900">
                    ← Factura
                </a>

                <span>/</span>

                <span>Modificar</span>
            </div>

            <div class="mt-2 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-gray-900">
                        Modificar factura {{ $invoice->numero_factura_original }}
                    </h1>

                    <p class="mt-1 text-sm text-gray-500">
                        Corrige valores o agrega productos EOFERTIL que no fueron registrados originalmente.
                    </p>
                </div>

                <div class="rounded-lg bg-amber-50 px-3 py-2 text-xs font-medium text-amber-800">
                    Pendiente de revisión
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('invoices.update', $invoice) }}" id="invoice-edit-form">
            @csrf
            @method('PUT')

            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">

                <div class="border-b border-gray-200 bg-gray-50 px-5 py-4">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-base font-bold text-gray-900">
                                Información de la factura
                            </h2>

                            <p class="mt-0.5 text-xs text-gray-500">
                                Los datos originales no pueden modificarse.
                            </p>
                        </div>

                        <div class="hidden text-xs text-gray-500 sm:block">
                            ID #{{ $invoice->id }}
                        </div>
                    </div>
                </div>

                <div class="px-5 py-5">

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">

                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-gray-600">
                                Número de factura
                            </label>

                            <input type="text" value="{{ $invoice->numero_factura_original }}" readonly
                                class="w-full cursor-not-allowed rounded-lg border-gray-200 bg-gray-100 px-3 py-2 text-sm font-medium text-gray-700 shadow-sm">
                        </div>

                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-gray-600">
                                Fecha
                            </label>

                            <input type="text" value="{{ optional($invoice->fecha_factura)->format('d/m/Y') }}" readonly
                                class="w-full cursor-not-allowed rounded-lg border-gray-200 bg-gray-100 px-3 py-2 text-sm font-medium text-gray-700 shadow-sm">
                        </div>

                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-gray-600">
                                Total factura
                            </label>

                            <div
                                class="flex h-[38px] overflow-hidden rounded-lg border border-gray-200 bg-gray-100 shadow-sm">
                                <span
                                    class="flex w-9 shrink-0 items-center justify-center border-r border-gray-200 text-sm font-semibold text-gray-500">
                                    $
                                </span>

                                <input type="text"
                                    value="{{ number_format((float) $invoice->total_factura, 2, '.', '') }}" readonly
                                    class="min-w-0 flex-1 border-0 bg-transparent px-3 py-2 text-sm font-semibold text-gray-700 focus:ring-0">
                            </div>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-gray-600">
                                Total participante
                            </label>

                            <div
                                class="flex h-[38px] overflow-hidden rounded-lg border border-blue-200 bg-blue-50 shadow-sm">
                                <span
                                    class="flex w-9 shrink-0 items-center justify-center border-r border-blue-200 text-sm font-semibold text-blue-500">
                                    $
                                </span>

                                <input type="text" id="total_productos_participantes_visible"
                                    value="{{ number_format((float) $invoice->total_productos_participantes, 2, '.', '') }}"
                                    readonly
                                    class="min-w-0 flex-1 border-0 bg-transparent px-3 py-2 text-sm font-bold text-blue-900 focus:ring-0">
                            </div>
                        </div>

                    </div>

                    <div class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-3">

                        <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                Perchero
                            </p>

                            <p class="mt-1 text-sm font-semibold text-gray-900">
                                {{ $invoice->user?->first_name }}
                                {{ $invoice->user?->last_name }}
                            </p>

                            <p class="text-xs text-gray-500">
                                {{ $invoice->user?->identification }}
                            </p>
                        </div>

                        <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                Sucursal
                            </p>

                            <p class="mt-1 text-sm font-semibold text-gray-900">
                                {{ $invoice->branch?->name ?? 'Sin sucursal' }}
                            </p>
                        </div>

                        <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                Campaña
                            </p>

                            <p class="mt-1 truncate text-sm font-semibold text-gray-900">
                                {{ $invoice->cashbackCampaign?->nombre ?? 'Sin campaña' }}
                            </p>
                        </div>

                    </div>

                    <div class="my-6 border-t border-gray-200"></div>

                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h2 class="text-base font-bold text-gray-900">
                                Cashback
                            </h2>

                            <p class="mt-0.5 text-xs text-gray-500">
                                Se calcula usando la campaña asociada a esta factura.
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            <span class="text-xs font-medium text-gray-500">
                                Porcentaje:
                            </span>

                            <span class="rounded-lg bg-gray-100 px-3 py-1.5 text-sm font-bold text-gray-800">
                                {{ number_format((float) ($invoice->cashbackCampaign?->porcentaje ?? 0), 2, '.', '') }}%
                            </span>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-col gap-3 sm:flex-row">

                        <div class="flex-1 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3">
                            <p class="text-xs text-gray-500">
                                Campaña utilizada
                            </p>

                            <p class="mt-1 truncate text-sm font-semibold text-gray-800">
                                {{ $invoice->cashbackCampaign?->nombre ?? 'Sin campaña' }}
                            </p>
                        </div>

                        <div
                            class="flex items-center justify-between rounded-xl border border-green-200 bg-green-50 px-4 py-3 sm:min-w-[230px]">
                            <div>
                                <p class="text-xs font-medium text-green-700">
                                    Cashback calculado
                                </p>

                                <p class="text-xs text-green-600">
                                    Actualización automática
                                </p>
                            </div>

                            <span id="cashback-calculado" class="ml-4 whitespace-nowrap text-xl font-bold text-green-800">
                                $0.00
                            </span>
                        </div>

                    </div>

                    <div class="my-6 border-t border-gray-200"></div>

                    <div>

                        <div class="mb-4 flex flex-wrap items-end justify-between gap-2">
                            <div>
                                <h2 class="text-base font-bold text-gray-900">
                                    Productos registrados
                                </h2>

                                <p class="mt-0.5 text-xs text-gray-500">
                                    Modifica el valor de los productos o agrega productos EOFERTIL que faltaron.
                                </p>
                            </div>

                            <span id="product-count" class="text-xs text-gray-400">
                                {{ $invoice->items->count() }}
                                {{ $invoice->items->count() === 1 ? 'producto' : 'productos' }}
                            </span>
                        </div>

                        @if ($invoice->items->count())
                            <div class="overflow-hidden rounded-xl border border-gray-200">

                                <div
                                    class="hidden grid-cols-[1fr_190px] gap-4 border-b border-gray-200 bg-gray-50 px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-gray-500 sm:grid">
                                    <div>
                                        Producto
                                    </div>

                                    <div>
                                        Valor registrado
                                    </div>
                                </div>

                                <div id="existing-products-container" class="divide-y divide-gray-200">

                                    @foreach ($invoice->items as $item)
                                        <div
                                            class="grid grid-cols-1 gap-3 px-4 py-3 sm:grid-cols-[1fr_190px] sm:items-center sm:gap-4">

                                            <div class="min-w-0">
                                                <div class="flex items-center gap-3">

                                                    <div
                                                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-sm">
                                                        📦
                                                    </div>

                                                    <div class="min-w-0">
                                                        <p class="truncate text-sm font-bold text-gray-900">
                                                            {{ $item->product?->name ?? 'Producto eliminado' }}
                                                        </p>

                                                        @if ($item->product)
                                                            <p class="mt-0.5 text-xs text-gray-500">
                                                                ID: {{ $item->product->id }}
                                                            </p>
                                                        @endif
                                                    </div>

                                                </div>
                                            </div>

                                            <div>
                                                <label for="item-{{ $item->id }}"
                                                    class="mb-1.5 block text-xs font-semibold text-gray-600 sm:hidden">
                                                    Valor registrado
                                                </label>

                                                <div
                                                    class="flex h-[40px] overflow-hidden rounded-lg border border-gray-300 bg-white shadow-sm transition focus-within:border-blue-500 focus-within:ring-2 focus-within:ring-blue-100">

                                                    <span
                                                        class="flex w-9 shrink-0 items-center justify-center border-r border-gray-200 bg-gray-50 text-sm font-semibold text-gray-500">
                                                        $
                                                    </span>

                                                    <input type="number" id="item-{{ $item->id }}"
                                                        name="items[{{ $item->id }}][valor]"
                                                        value="{{ old('items.' . $item->id . '.valor', number_format((float) $item->valor, 2, '.', '')) }}"
                                                        min="0" step="0.01" inputmode="decimal" required
                                                        data-product-value
                                                        class="min-w-0 flex-1 border-0 bg-white px-3 py-2 text-sm font-bold text-gray-900 outline-none focus:border-0 focus:ring-0">

                                                </div>

                                                @error('items.' . $item->id . '.valor')
                                                    <p class="mt-1 text-xs font-medium text-red-600">
                                                        {{ $message }}
                                                    </p>
                                                @enderror
                                            </div>

                                        </div>
                                    @endforeach

                                </div>

                            </div>
                        @else
                            <div
                                class="rounded-xl border border-dashed border-gray-300 bg-gray-50 px-5 py-8 text-center text-sm text-gray-500">
                                Esta factura no tiene productos registrados.
                            </div>
                        @endif

                        <div class="mt-4 rounded-xl border border-blue-200 bg-blue-50 p-4">

                            <div class="flex flex-col gap-4">

                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <div>
                                        <h3 class="text-sm font-bold text-blue-900">
                                            Agregar producto EOFERTIL
                                        </h3>

                                        <p class="mt-0.5 text-xs text-blue-700">
                                            Úsalo cuando el perchero olvidó registrar un producto participante.
                                        </p>
                                    </div>

                                    <span
                                        class="rounded-lg bg-white px-2.5 py-1 text-[11px] font-bold text-blue-700 shadow-sm">
                                        Solo EOFERTIL
                                    </span>
                                </div>

                                <div class="grid grid-cols-1 gap-3 md:grid-cols-[1fr_180px_auto]">

                                    <div>
                                        <label for="new-product-select"
                                            class="mb-1.5 block text-xs font-semibold text-blue-900">
                                            Producto
                                        </label>

                                        <select id="new-product-select"
                                            class="w-full rounded-lg border-gray-300 bg-white px-3 py-2.5 text-sm font-medium text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100">

                                            <option value="">
                                                Selecciona un producto EOFERTIL
                                            </option>

                                            @foreach ($availableProducts as $product)
                                                <option value="{{ $product->id }}"
                                                    data-product-name="{{ $product->name }}">
                                                    {{ $product->name }}
                                                    @if ($product->code)
                                                        — {{ $product->code }}
                                                    @endif
                                                </option>
                                            @endforeach

                                        </select>

                                        @if ($availableProducts->isEmpty())
                                            <p class="mt-1.5 text-xs font-medium text-blue-700">
                                                No hay productos EOFERTIL disponibles para agregar.
                                            </p>
                                        @endif
                                    </div>

                                    <div>
                                        <label for="new-product-value"
                                            class="mb-1.5 block text-xs font-semibold text-blue-900">
                                            Valor participante
                                        </label>

                                        <div
                                            class="flex h-[42px] overflow-hidden rounded-lg border border-gray-300 bg-white shadow-sm focus-within:border-blue-500 focus-within:ring-2 focus-within:ring-blue-100">

                                            <span
                                                class="flex w-9 shrink-0 items-center justify-center border-r border-gray-200 bg-gray-50 text-sm font-semibold text-gray-500">
                                                $
                                            </span>

                                            <input type="number" id="new-product-value" min="0" step="0.01"
                                                inputmode="decimal" placeholder="0.00"
                                                class="min-w-0 flex-1 border-0 bg-white px-3 py-2 text-sm font-bold text-gray-900 outline-none focus:ring-0">

                                        </div>
                                    </div>

                                    <div class="flex items-end">
                                        <button type="button" id="add-product-button"
                                            class="inline-flex h-[42px] w-full items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 md:w-auto">
                                            + Agregar
                                        </button>
                                    </div>

                                </div>

                                <div id="new-products-container" class="hidden">

                                    <div class="overflow-hidden rounded-xl border border-blue-200 bg-white">

                                        <div
                                            class="grid grid-cols-[1fr_140px_44px] gap-3 border-b border-blue-100 bg-blue-50 px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-blue-800">
                                            <div>
                                                Producto agregado
                                            </div>

                                            <div>
                                                Valor
                                            </div>

                                            <div></div>
                                        </div>

                                        <div id="new-products-list" class="divide-y divide-gray-200"></div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                    <div class="my-6 border-t border-gray-200"></div>

                    <div>
                        <h2 class="text-base font-bold text-gray-900">
                            Resumen
                        </h2>

                        <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-3">

                            <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3">
                                <p class="text-xs text-gray-500">
                                    Total factura
                                </p>

                                <p class="mt-1 text-lg font-bold text-gray-900">
                                    ${{ number_format((float) $invoice->total_factura, 2, '.', '') }}
                                </p>
                            </div>

                            <div class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-3">
                                <p class="text-xs text-blue-700">
                                    Total participante
                                </p>

                                <p id="resumen-total" class="mt-1 text-lg font-bold text-blue-900">
                                    ${{ number_format((float) $invoice->total_productos_participantes, 2, '.', '') }}
                                </p>
                            </div>

                            <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3">
                                <p class="text-xs text-green-700">
                                    Cashback
                                </p>

                                <p id="resumen-cashback" class="mt-1 text-lg font-bold text-green-900">
                                    $0.00
                                </p>
                            </div>

                        </div>
                    </div>

                    <div class="my-6 border-t border-gray-200"></div>

                    <div>
                        <div class="mb-3">
                            <h2 class="text-base font-bold text-gray-900">
                                Motivo de la modificación
                            </h2>

                            <p class="mt-0.5 text-xs text-gray-500">
                                Este motivo quedará registrado en la auditoría administrativa.
                            </p>
                        </div>

                        <textarea name="motivo" rows="3" required maxlength="1000"
                            placeholder="Ejemplo: Se corrigió el valor registrado y se agregó un producto EOFERTIL que faltaba."
                            class="block w-full resize-none rounded-xl border-gray-300 px-4 py-3 text-sm text-gray-900 shadow-sm outline-none transition placeholder:text-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">{{ old('motivo') }}</textarea>

                        @error('motivo')
                            <p class="mt-1.5 text-xs font-medium text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                </div>

                <div
                    class="flex flex-col-reverse gap-3 border-t border-gray-200 bg-gray-50 px-5 py-4 sm:flex-row sm:justify-end">

                    <a href="{{ route('invoices.show', $invoice) }}"
                        class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-100">
                        Cancelar
                    </a>

                    <button type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                        onclick="return confirm('¿Estás seguro de guardar esta corrección? La factura continuará pendiente de revisión.');">
                        Guardar modificación
                    </button>

                </div>

            </div>

        </form>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const form = document.getElementById('invoice-edit-form');

            const productInputs = document.querySelectorAll('[data-product-value]');

            const totalVisible =
                document.getElementById('total_productos_participantes_visible');

            const cashbackElement =
                document.getElementById('cashback-calculado');

            const resumenTotal =
                document.getElementById('resumen-total');

            const resumenCashback =
                document.getElementById('resumen-cashback');

            const productCount =
                document.getElementById('product-count');

            const newProductSelect =
                document.getElementById('new-product-select');

            const newProductValue =
                document.getElementById('new-product-value');

            const addProductButton =
                document.getElementById('add-product-button');

            const newProductsContainer =
                document.getElementById('new-products-container');

            const newProductsList =
                document.getElementById('new-products-list');

            const porcentaje =
                {{ (float) ($invoice->cashbackCampaign?->porcentaje ?? 0) }};

            let newProductIndex = 0;

            const selectedNewProducts = new Set();

            function formatMoney(value) {
                return Number(value).toFixed(2);
            }

            function getAllProductInputs() {
                return document.querySelectorAll('[data-product-value]');
            }

            function recalcular() {

                let total = 0;

                getAllProductInputs().forEach(function(input) {

                    let value = parseFloat(input.value);

                    if (isNaN(value) || value < 0) {
                        value = 0;
                    }

                    total += value;
                });

                total =
                    Math.round(
                        (total + Number.EPSILON) * 100
                    ) / 100;

                const cashback =
                    Math.round(
                        (
                            total * porcentaje / 100 +
                            Number.EPSILON
                        ) * 100
                    ) / 100;

                if (totalVisible) {
                    totalVisible.value =
                        formatMoney(total);
                }

                if (cashbackElement) {
                    cashbackElement.textContent =
                        '$' + formatMoney(cashback);
                }

                if (resumenTotal) {
                    resumenTotal.textContent =
                        '$' + formatMoney(total);
                }

                if (resumenCashback) {
                    resumenCashback.textContent =
                        '$' + formatMoney(cashback);
                }

                actualizarContador();
            }

            function actualizarContador() {

                const existingCount =
                    document.querySelectorAll(
                        '[data-product-value][data-existing-product="true"]'
                    ).length;

                const newCount =
                    document.querySelectorAll(
                        '[data-product-value][data-new-product="true"]'
                    ).length;

                const total =
                    existingCount + newCount;

                if (productCount) {
                    productCount.textContent =
                        total +
                        (total === 1 ?
                            ' producto' :
                            ' productos');
                }
            }

            function actualizarOpcionesProductos() {

                if (!newProductSelect) {
                    return;
                }

                Array.from(
                    newProductSelect.options
                ).forEach(function(option) {

                    if (!option.value) {
                        return;
                    }

                    option.disabled =
                        selectedNewProducts.has(
                            Number(option.value)
                        );
                });

                if (
                    newProductSelect.value &&
                    selectedNewProducts.has(
                        Number(newProductSelect.value)
                    )
                ) {
                    newProductSelect.value = '';
                }
            }

            function mostrarContenedorNuevos() {

                if (!newProductsContainer) {
                    return;
                }

                if (selectedNewProducts.size > 0) {
                    newProductsContainer.classList.remove('hidden');
                } else {
                    newProductsContainer.classList.add('hidden');
                }
            }

            function agregarProducto() {

                if (!newProductSelect || !newProductValue) {
                    return;
                }

                const productId =
                    parseInt(
                        newProductSelect.value,
                        10
                    );

                const valor =
                    parseFloat(
                        newProductValue.value
                    );

                if (
                    !productId ||
                    isNaN(productId)
                ) {
                    alert(
                        'Selecciona un producto EOFERTIL.'
                    );

                    newProductSelect.focus();

                    return;
                }

                if (
                    isNaN(valor) ||
                    valor < 0
                ) {
                    alert(
                        'Ingresa un valor válido para el producto.'
                    );

                    newProductValue.focus();

                    return;
                }

                if (
                    selectedNewProducts.has(productId)
                ) {
                    alert(
                        'Ese producto ya fue agregado.'
                    );

                    return;
                }

                const selectedOption =
                    newProductSelect.options[
                        newProductSelect.selectedIndex
                    ];

                const productName =
                    selectedOption.dataset.productName ||
                    selectedOption.textContent.trim();

                const index =
                    newProductIndex++;

                selectedNewProducts.add(productId);

                const row =
                    document.createElement('div');

                row.className =
                    'grid grid-cols-[1fr_140px_44px] gap-3 px-4 py-3 items-center';

                row.dataset.productId =
                    productId;

                row.innerHTML = `
                    <div class="min-w-0">
                        <div class="flex items-center gap-3">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-100 text-sm">
                                📦
                            </div>

                            <div class="min-w-0">
                                <p class="truncate text-sm font-bold text-gray-900">
                                    ${escapeHtml(productName)}
                                </p>

                                <p class="mt-0.5 text-xs text-blue-600">
                                    Producto EOFERTIL agregado
                                </p>
                            </div>
                        </div>

                        <input
                            type="hidden"
                            name="new_items[${index}][product_id]"
                            value="${productId}"
                        >
                    </div>

                    <div>
                        <div class="flex h-[40px] overflow-hidden rounded-lg border border-gray-300 bg-white shadow-sm">
                            <span class="flex w-9 shrink-0 items-center justify-center border-r border-gray-200 bg-gray-50 text-sm font-semibold text-gray-500">
                                $
                            </span>

                            <input
                                type="number"
                                name="new_items[${index}][valor]"
                                value="${formatMoney(valor)}"
                                min="0"
                                step="0.01"
                                inputmode="decimal"
                                required
                                data-product-value
                                data-new-product="true"
                                class="min-w-0 flex-1 border-0 bg-white px-3 py-2 text-sm font-bold text-gray-900 outline-none focus:ring-0"
                            >
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button
                            type="button"
                            data-remove-new-product
                            class="flex h-9 w-9 items-center justify-center rounded-lg border border-red-200 bg-red-50 text-sm font-bold text-red-600 transition hover:bg-red-100"
                            title="Quitar producto"
                        >
                            ×
                        </button>
                    </div>
                `;

                newProductsList.appendChild(row);

                const valueInput =
                    row.querySelector(
                        '[data-product-value]'
                    );

                if (valueInput) {
                    valueInput.addEventListener(
                        'input',
                        recalcular
                    );

                    valueInput.addEventListener(
                        'change',
                        recalcular
                    );
                }

                const removeButton =
                    row.querySelector(
                        '[data-remove-new-product]'
                    );

                if (removeButton) {

                    removeButton.addEventListener(
                        'click',
                        function() {

                            selectedNewProducts.delete(
                                productId
                            );

                            row.remove();

                            actualizarOpcionesProductos();

                            mostrarContenedorNuevos();

                            recalcular();
                        }
                    );
                }

                newProductSelect.value = '';

                newProductValue.value = '';

                actualizarOpcionesProductos();

                mostrarContenedorNuevos();

                recalcular();
            }

            function escapeHtml(value) {

                const div =
                    document.createElement('div');

                div.textContent =
                    value;

                return div.innerHTML;
            }

            if (addProductButton) {
                addProductButton.addEventListener(
                    'click',
                    agregarProducto
                );
            }

            if (newProductValue) {
                newProductValue.addEventListener(
                    'keydown',
                    function(event) {

                        if (
                            event.key === 'Enter'
                        ) {
                            event.preventDefault();

                            agregarProducto();
                        }
                    }
                );
            }

            document
                .querySelectorAll('[data-product-value]')
                .forEach(function(input) {

                    input.addEventListener(
                        'input',
                        recalcular
                    );

                    input.addEventListener(
                        'change',
                        recalcular
                    );
                });

            actualizarOpcionesProductos();

            recalcular();

            /*
            |--------------------------------------------------------------------------
            | Protección visual adicional
            |--------------------------------------------------------------------------
            |
            | El backend continúa siendo la protección real.
            | Este control solamente evita enviar accidentalmente
            | el formulario mientras se agrega un producto.
            |--------------------------------------------------------------------------
            */

            if (form) {

                form.addEventListener(
                    'submit',
                    function() {

                        if (newProductsContainer) {
                            newProductsContainer
                                .classList.remove('hidden');
                        }
                    }
                );
            }
        });
    </script>

@endsection
