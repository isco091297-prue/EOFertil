@extends('layouts.app')

@section('content')

<div class="max-w-5xl mx-auto">

    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">
            🗑️ Eliminar usuario
        </h1>

        <p class="text-gray-500 mt-1">
            Revisa cuidadosamente la información antes de eliminar esta cuenta.
        </p>
    </div>

    <x-card>

        <div class="flex items-center justify-between border-b pb-5 mb-6">

            <div>
                <h2 class="text-xl font-bold text-gray-800">
                    {{ $user->first_name }} {{ $user->last_name }}
                </h2>

                <p class="text-sm text-gray-500 mt-1">
                    Identificación: {{ $user->identification }}
                    · Usuario: {{ $user->username }}
                </p>
            </div>

            <div class="text-right">
                <span class="inline-flex items-center rounded-full bg-red-100 px-3 py-1 text-sm font-semibold text-red-700">
                    {{ $user->role?->name ?? 'Sin rol' }}
                </span>
            </div>

        </div>

        <div class="rounded-xl border border-red-300 bg-red-50 p-5 mb-6">

            <h3 class="font-bold text-red-900 mb-2">
                ⚠️ Eliminación permanente
            </h3>

            <p class="text-sm text-red-800 leading-6">
                Esta acción eliminará la cuenta del usuario y todos sus datos
                asociados. No podrá deshacerse posteriormente.
            </p>

            <p class="text-sm text-red-800 leading-6 mt-2">
                Los rankings de campañas que todavía no hayan sido procesados
                serán reconstruidos automáticamente. Los resultados históricos
                de campañas ya procesadas no serán recalculados.
            </p>

        </div>

        <div class="mb-6">

            <h3 class="text-lg font-bold text-gray-800 mb-4">
                Datos que serán eliminados
            </h3>

            <div class="overflow-hidden rounded-xl border">

                <table class="min-w-full divide-y divide-gray-200">

                    <tbody class="divide-y divide-gray-200 bg-white">

                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                Facturas
                            </td>
                            <td class="px-4 py-3 text-sm font-bold text-right">
                                {{ $summary['datos']['facturas'] }}
                            </td>
                        </tr>

                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                Productos de facturas
                            </td>
                            <td class="px-4 py-3 text-sm font-bold text-right">
                                {{ $summary['datos']['items_facturas'] }}
                            </td>
                        </tr>

                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                Movimientos de cashback
                            </td>
                            <td class="px-4 py-3 text-sm font-bold text-right">
                                {{ $summary['datos']['movimientos_facturas'] + $summary['datos']['movimientos_sin_factura'] }}
                            </td>
                        </tr>

                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                Rankings
                            </td>
                            <td class="px-4 py-3 text-sm font-bold text-right">
                                {{ $summary['datos']['rankings'] }}
                            </td>
                        </tr>

                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                Ganadores
                            </td>
                            <td class="px-4 py-3 text-sm font-bold text-right">
                                {{ $summary['datos']['ganadores'] }}
                            </td>
                        </tr>

                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                Redenciones
                            </td>
                            <td class="px-4 py-3 text-sm font-bold text-right">
                                {{ $summary['datos']['redenciones'] }}
                            </td>
                        </tr>

                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                Usos de la guía
                            </td>
                            <td class="px-4 py-3 text-sm font-bold text-right">
                                {{ $summary['datos']['usos_guia'] }}
                            </td>
                        </tr>

                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                Sesiones
                            </td>
                            <td class="px-4 py-3 text-sm font-bold text-right">
                                {{ $summary['datos']['sesiones'] }}
                            </td>
                        </tr>

                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                Tokens
                            </td>
                            <td class="px-4 py-3 text-sm font-bold text-right">
                                {{ $summary['datos']['tokens'] }}
                            </td>
                        </tr>

                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                Recuperaciones de contraseña
                            </td>
                            <td class="px-4 py-3 text-sm font-bold text-right">
                                {{ $summary['datos']['recuperaciones_password'] + $summary['datos']['password_reset_legacy'] }}
                            </td>
                        </tr>

                    </tbody>

                </table>

            </div>

        </div>

        <div class="rounded-xl border border-gray-200 bg-gray-50 p-5 mb-6">

            <h3 class="font-bold text-gray-800 mb-3">
                💰 Saldos actuales
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                <div>
                    <p class="text-xs uppercase text-gray-500">
                        Cashback total
                    </p>

                    <p class="text-xl font-bold text-gray-800">
                        ${{ number_format($summary['saldos']['cashback_total'], 2) }}
                    </p>
                </div>

                <div>
                    <p class="text-xs uppercase text-gray-500">
                        Cashback reclamado
                    </p>

                    <p class="text-xl font-bold text-gray-800">
                        ${{ number_format($summary['saldos']['cashback_claimed'], 2) }}
                    </p>
                </div>

                <div>
                    <p class="text-xs uppercase text-gray-500">
                        Cashback disponible
                    </p>

                    <p class="text-xl font-bold text-gray-800">
                        ${{ number_format($summary['saldos']['cashback_available'], 2) }}
                    </p>
                </div>

            </div>

        </div>

        @if($summary['riesgos']['tiene_auditorias_sobre_otros_usuarios'])

            <div class="rounded-xl border border-blue-300 bg-blue-50 p-5 mb-6">

                <h3 class="font-bold text-blue-900 mb-2">
                    ℹ️ Auditorías administrativas
                </h3>

                <p class="text-sm text-blue-800 leading-6">
                    El usuario tiene registros de auditoría asociados como
                    administrador. Esto no bloquea la operación desde esta
                    pantalla; sin embargo, si existen auditorías sobre facturas
                    de otros usuarios, la base de datos puede impedir la
                    eliminación para proteger el historial administrativo.
                </p>

            </div>

        @endif

        <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3">

            <a
                href="{{ route('users.index') }}"
                class="inline-flex justify-center items-center rounded-xl border border-gray-300 bg-white px-5 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50"
            >
                Cancelar
            </a>

            <form
                method="POST"
                action="{{ route('users.destroy', $user) }}"
                onsubmit="return confirm('⚠️ ATENCIÓN: esta acción eliminará permanentemente el usuario y todos sus datos. ¿Deseas continuar?');"
            >

                @csrf
                @method('DELETE')

                <button
                    type="submit"
                    class="w-full sm:w-auto inline-flex justify-center items-center rounded-xl bg-red-600 px-5 py-3 text-sm font-semibold text-white hover:bg-red-700"
                >
                    🗑️ Eliminar definitivamente
                </button>

            </form>

        </div>

    </x-card>

</div>

@endsection