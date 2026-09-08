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


    <div class="space-y-6">

        {{-- ============================================================
             ENCABEZADO
        ============================================================ --}}

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

            <div>

                <div class="flex items-center gap-3">

                    <a href="{{ route('invoices.index') }}" class="text-gray-500 hover:text-gray-800">
                        ← Ventas
                    </a>

                    <span class="text-gray-300">/</span>

                    <span class="text-gray-600">
                        Factura #{{ $invoice->numero_factura_original }}
                    </span>

                </div>

                <h1 class="text-3xl font-bold mt-3">
                    Factura {{ $invoice->numero_factura_original }}
                </h1>

                <p class="text-gray-500 mt-1">
                    Detalle y revisión de la venta registrada.
                </p>

            </div>


            {{-- ESTADO --}}

            <div>

                @if ($invoice->estado === 'procesando')
                    <span class="inline-flex items-center px-4 py-2 rounded-xl bg-yellow-100 text-yellow-800 font-semibold">
                        Pendiente de revisión
                    </span>
                @elseif ($invoice->estado === 'confirmada')
                    <span class="inline-flex items-center px-4 py-2 rounded-xl bg-green-100 text-green-800 font-semibold">
                        Aprobada
                    </span>
                @elseif ($invoice->estado === 'anulada')
                    <span class="inline-flex items-center px-4 py-2 rounded-xl bg-red-100 text-red-800 font-semibold">
                        Anulada
                    </span>
                @else
                    <span class="inline-flex items-center px-4 py-2 rounded-xl bg-gray-100 text-gray-800 font-semibold">
                        {{ ucfirst($invoice->estado) }}
                    </span>
                @endif

            </div>

        </div>


        {{-- ============================================================
             INFORMACIÓN GENERAL
        ============================================================ --}}

        <x-card>

            <h2 class="text-xl font-bold mb-6">
                Información de la factura
            </h2>


            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

                <div>

                    <p class="text-sm text-gray-500">
                        Número de factura
                    </p>

                    <p class="font-semibold mt-1">
                        {{ $invoice->numero_factura_original }}
                    </p>

                </div>


                <div>

                    <p class="text-sm text-gray-500">
                        Fecha de factura
                    </p>

                    <p class="font-semibold mt-1">
                        {{ optional($invoice->fecha_factura)->format('d/m/Y') }}
                    </p>

                </div>


                <div>

                    <p class="text-sm text-gray-500">
                        Fecha de registro
                    </p>

                    <p class="font-semibold mt-1">
                        {{ optional($invoice->created_at)->format('d/m/Y H:i') }}
                    </p>

                </div>


                <div>

                    <p class="text-sm text-gray-500">
                        Origen
                    </p>

                    <p class="font-semibold mt-1">
                        {{ $invoice->origen === 'ocr' ? 'OCR' : 'Registro manual' }}
                    </p>

                </div>

            </div>

        </x-card>


        {{-- ============================================================
             PERCHERO / SUCURSAL
        ============================================================ --}}

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <x-card>

                <h2 class="text-xl font-bold mb-6">
                    Perchero
                </h2>


                <div class="space-y-4">

                    <div>

                        <p class="text-sm text-gray-500">
                            Nombre
                        </p>

                        <p class="font-semibold mt-1">
                            {{ $invoice->user?->first_name }}
                            {{ $invoice->user?->last_name }}
                        </p>

                    </div>


                    <div>

                        <p class="text-sm text-gray-500">
                            Identificación
                        </p>

                        <p class="font-semibold mt-1">
                            {{ $invoice->user?->identification }}
                        </p>

                    </div>


                    <div>

                        <p class="text-sm text-gray-500">
                            Usuario
                        </p>

                        <p class="font-semibold mt-1">
                            {{ $invoice->user?->username }}
                        </p>

                    </div>

                </div>

            </x-card>


            <x-card>

                <h2 class="text-xl font-bold mb-6">
                    Sucursal
                </h2>


                <div class="space-y-4">

                    <div>

                        <p class="text-sm text-gray-500">
                            Sucursal
                        </p>

                        <p class="font-semibold mt-1">
                            {{ $invoice->branch?->name ?? 'Sin sucursal' }}
                        </p>

                    </div>


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

        </div>


        {{-- ============================================================
             VALORES
        ============================================================ --}}

        <x-card>

            <h2 class="text-xl font-bold mb-6">
                Valores de la venta
            </h2>


            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <div class="rounded-xl bg-gray-50 p-5">

                    <p class="text-sm text-gray-500">
                        Total de factura
                    </p>

                    <p class="text-2xl font-bold mt-2">
                        ${{ number_format((float) $invoice->total_factura, 2) }}
                    </p>

                </div>


                <div class="rounded-xl bg-gray-50 p-5">

                    <p class="text-sm text-gray-500">
                        Productos participantes
                    </p>

                    <p class="text-2xl font-bold mt-2">
                        ${{ number_format((float) $invoice->total_productos_participantes, 2) }}
                    </p>

                </div>


                <div class="rounded-xl bg-gray-50 p-5">

                    <p class="text-sm text-gray-500">
                        Cashback generado
                    </p>

                    <p class="text-2xl font-bold mt-2">
                        ${{ number_format((float) $invoice->cashback_generado, 2) }}
                    </p>

                    <p class="text-sm text-gray-500 mt-1">
                        {{ number_format((float) $invoice->porcentaje_cashback, 2) }}%
                    </p>

                </div>

            </div>

        </x-card>


        {{-- ============================================================
             PRODUCTOS
        ============================================================ --}}

        <x-card>

            <div class="flex justify-between items-center mb-6">

                <div>

                    <h2 class="text-xl font-bold">
                        Productos registrados
                    </h2>

                    <p class="text-sm text-gray-500">
                        Productos ingresados en esta factura.
                    </p>

                </div>

                <span class="text-sm text-gray-500">
                    {{ $invoice->items->count() }} producto(s)
                </span>

            </div>


            <div class="overflow-x-auto">

                <table class="w-full">

                    <thead>

                        <tr class="border-b">

                            <th class="py-3 text-left">
                                Producto
                            </th>

                            <th class="py-3 text-left">
                                Valor
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @forelse ($invoice->items as $item)
                            <tr class="border-b">

                                <td class="py-4">

                                    <div class="font-semibold">
                                        {{ $item->product?->name ?? 'Producto eliminado' }} </div>

                                    @if ($item->product)
                                        <div class="text-sm text-gray-500">
                                            ID: {{ $item->product->id }}
                                        </div>
                                    @endif

                                </td>


                                <td class="py-4 font-semibold">

                                    ${{ number_format((float) $item->valor, 2) }}

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="2" class="py-8 text-center text-gray-500">
                                    No existen productos registrados en esta factura.
                                </td>

                            </tr>
                        @endforelse

                    </tbody>


                    @if ($invoice->items->count())
                        <tfoot>

                            <tr>

                                <td class="py-4 font-bold">
                                    Total
                                </td>

                                <td class="py-4 font-bold">
                                    ${{ number_format((float) $invoice->items->sum('valor'), 2) }}
                                </td>

                            </tr>

                        </tfoot>
                    @endif

                </table>

            </div>

        </x-card>


        {{-- ============================================================
             FOTO DE FACTURA
        ============================================================ --}}

        <x-card>

            <h2 class="text-xl font-bold mb-6">
                Comprobante de factura
            </h2>


            @if ($invoice->foto_factura)
                <div class="flex justify-center">

                    <a href="{{ asset('storage/' . $invoice->foto_factura) }}" target="_blank" class="block">

                        <img src="{{ asset('storage/' . $invoice->foto_factura) }}"
                            alt="Foto de factura {{ $invoice->numero_factura_original }}"
                            class="max-h-[600px] max-w-full rounded-xl border object-contain">

                    </a>

                </div>

                <p class="text-center text-sm text-gray-500 mt-4">
                    Haz clic en la imagen para abrir el comprobante completo.
                </p>
            @else
                <div class="rounded-xl bg-gray-50 p-8 text-center text-gray-500">
                    Esta factura no tiene una fotografía registrada.
                </div>
            @endif

        </x-card>


        {{-- ============================================================
             TRANSACCIONES DE CASHBACK
        ============================================================ --}}

        @if ($invoice->cashbackTransactions->count())
            <x-card>

                <h2 class="text-xl font-bold mb-6">
                    Movimientos de cashback
                </h2>


                <div class="overflow-x-auto">

                    <table class="w-full">

                        <thead>

                            <tr class="border-b">

                                <th class="py-3 text-left">
                                    Tipo
                                </th>

                                <th class="py-3 text-left">
                                    Movimiento
                                </th>

                                <th class="py-3 text-left">
                                    Valor
                                </th>

                                <th class="py-3 text-left">
                                    Saldo después
                                </th>

                                <th class="py-3 text-left">
                                    Descripción
                                </th>

                                <th class="py-3 text-left">
                                    Fecha
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @foreach ($invoice->cashbackTransactions as $transaction)
                                <tr class="border-b">

                                    <td class="py-4">
                                        {{ ucfirst($transaction->tipo) }}
                                    </td>

                                    <td class="py-4">

                                        @if ($transaction->movimiento === 'ingreso')
                                            <span class="text-green-700 font-semibold">
                                                Ingreso
                                            </span>
                                        @else
                                            <span class="text-red-700 font-semibold">
                                                Egreso
                                            </span>
                                        @endif

                                    </td>

                                    <td class="py-4 font-semibold">
                                        ${{ number_format((float) $transaction->valor, 2) }}
                                    </td>

                                    <td class="py-4">
                                        ${{ number_format((float) $transaction->saldo_despues, 2) }}
                                    </td>

                                    <td class="py-4">
                                        {{ $transaction->descripcion }}
                                    </td>

                                    <td class="py-4">
                                        {{ optional($transaction->created_at)->format('d/m/Y H:i') }}
                                    </td>

                                </tr>
                            @endforeach

                        </tbody>

                    </table>

                </div>

            </x-card>
        @endif

        {{-- ============================================================
     ADMINISTRACIÓN
============================================================ --}}

        <div class="mt-8">

            <h2 class="text-xl font-bold">
                Administración
            </h2>

            @if ($invoice->estado === 'procesando')
                <p class="text-gray-500 mt-2">
                    Esta factura está pendiente de revisión. Selecciona una
                    de las acciones disponibles para determinar su estado.
                </p>

                <div class="flex flex-wrap gap-3 mt-5">

                    {{-- ====================================================
                 APROBAR
            ==================================================== --}}

                    <form method="POST" action="{{ route('invoices.approve', $invoice) }}"
                        onsubmit="return confirm('¿Estás seguro de aprobar esta factura? Una vez aprobada, se considerará correcta y se acreditará el cashback correspondiente.');">

                        @csrf

                        <button type="submit"
                            class="px-5 py-3 rounded-xl bg-green-600 hover:bg-green-700 text-white font-semibold">
                            ✓ Aprobar factura
                        </button>

                    </form>


                    {{-- ====================================================
                 MODIFICAR
            ==================================================== --}}

                    <a href="{{ route('invoices.edit', $invoice) }}"
                        class="px-5 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold">
                        ✎ Modificar factura
                    </a>

                    {{-- ====================================================
     ANULAR
==================================================== --}}

                    <div>

                        <button type="button" onclick="document.getElementById('annul-modal').classList.remove('hidden')"
                            class="px-5 py-3 rounded-xl bg-red-600 hover:bg-red-700 text-white font-semibold">
                            ✕ Anular factura
                        </button>

                    </div>


                    {{-- ====================================================
     MODAL DE ANULACIÓN
==================================================== --}}

                    <div id="annul-modal"
                        class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">

                        <div class="w-full max-w-lg rounded-2xl bg-white shadow-2xl p-6">

                            <div class="flex items-center justify-between mb-5">

                                <h3 class="text-xl font-bold text-gray-900">
                                    Anular factura
                                </h3>

                                <button type="button"
                                    onclick="document.getElementById('annul-modal').classList.add('hidden')"
                                    class="text-gray-400 hover:text-gray-700 text-2xl">
                                    ×
                                </button>

                            </div>


                            <p class="text-sm text-gray-600 mb-5">
                                Esta acción anulará la factura y hará que deje de participar
                                en el cashback y en los valores acumulados/ranking.
                            </p>


                            <form method="POST" action="{{ route('invoices.annul', $invoice) }}">

                                @csrf

                                <label for="motivo" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Motivo de la anulación
                                </label>

                                <textarea id="motivo" name="motivo" rows="4" required maxlength="1000"
                                    placeholder="Indica por qué esta factura no cumple con las condiciones requeridas."
                                    class="w-full rounded-xl border-gray-300 focus:border-red-500 focus:ring-red-500"></textarea>


                                <div class="flex justify-end gap-3 mt-5">

                                    <button type="button"
                                        onclick="document.getElementById('annul-modal').classList.add('hidden')"
                                        class="px-5 py-3 rounded-xl border border-gray-300 hover:bg-gray-50">
                                        Cancelar
                                    </button>

                                    <button type="submit"
                                        class="px-5 py-3 rounded-xl bg-red-600 hover:bg-red-700 text-white font-semibold"
                                        onclick="return confirm('¿Estás seguro de anular esta factura?');">
                                        Confirmar anulación
                                    </button>

                                </div>

                            </form>

                        </div>

                    </div>

                    {{-- ========================================================
             FACTURA YA APROBADA
        ========================================================= --}}

                    <div class="mt-4 rounded-xl bg-green-50 border border-green-200 p-5">

                        <p class="font-semibold text-green-800">
                            ✓ Factura confirmada
                        </p>

                        <p class="text-sm text-green-700 mt-1">
                            Esta factura ya fue revisada y aprobada correctamente.
                            No tiene acciones administrativas pendientes.
                        </p>

                    </div>
                @elseif ($invoice->estado === 'anulada')
                    {{-- ========================================================
             FACTURA ANULADA
        ========================================================= --}}

                    <div class="mt-4 rounded-xl bg-red-50 border border-red-200 p-5">

                        <p class="font-semibold text-red-800">
                            ✕ Factura anulada
                        </p>

                        <p class="text-sm text-red-700 mt-1">
                            Esta factura fue anulada y no participa en el cashback
                            ni en los valores acumulados.
                        </p>

                    </div>
            @endif


            {{-- ============================================================
         VOLVER
    ============================================================ --}}

            <div class="mt-5">

                <a href="{{ route('invoices.index') }}"
                    class="inline-block px-5 py-3 rounded-xl border border-gray-300 hover:bg-gray-50">
                    ← Volver a ventas
                </a>

            </div>

        </div>
    @endsection
