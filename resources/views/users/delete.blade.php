<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Eliminar usuario
                </h2>

                <p class="text-sm text-red-600 mt-1">
                    Esta acción es permanente.
                </p>
            </div>

            <a
                href="{{ route('users.index') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-200"
            >
                Volver
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white rounded-xl shadow-sm border border-red-200 overflow-hidden">

                <div class="p-6 border-b border-red-100">
                    <h3 class="text-lg font-semibold text-gray-900">
                        {{ $summary['usuario']['nombre'] }}
                    </h3>

                    <div class="mt-2 text-sm text-gray-500 space-y-1">
                        <div>
                            <strong>ID:</strong>
                            {{ $summary['usuario']['id'] }}
                        </div>

                        <div>
                            <strong>Identificación:</strong>
                            {{ $summary['usuario']['identificacion'] }}
                        </div>

                        <div>
                            <strong>Usuario:</strong>
                            {{ $summary['usuario']['username'] }}
                        </div>

                        <div>
                            <strong>Rol:</strong>
                            {{ $summary['usuario']['rol'] ?? 'Sin rol' }}
                        </div>
                    </div>
                </div>

                <div class="p-6">

                    <div class="rounded-lg bg-red-50 border border-red-200 p-4 mb-6">
                        <div class="font-semibold text-red-900">
                            ⚠️ Eliminación definitiva
                        </div>

                        <p class="text-sm text-red-800 mt-1">
                            Se eliminará la cuenta y todos los datos asociados
                            que correspondan a este usuario.
                            Esta acción no se puede deshacer.
                        </p>

                        <p class="text-sm text-red-800 mt-2">
                            Si sus facturas participan en rankings,
                            los rankings afectados serán reconstruidos
                            automáticamente después de la eliminación.
                        </p>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">

                        @foreach([
                            'facturas' => 'Facturas',
                            'items_facturas' => 'Items de facturas',
                            'movimientos_facturas' => 'Movimientos de facturas',
                            'movimientos_sin_factura' => 'Movimientos sin factura',
                            'rankings' => 'Rankings',
                            'ganadores' => 'Ganadores',
                            'redenciones' => 'Redenciones',
                            'usos_guia' => 'Usos de guía',
                            'recuperaciones_password' => 'Recuperaciones',
                            'sesiones' => 'Sesiones',
                            'tokens' => 'Tokens',
                            'auditorias_como_admin' => 'Auditorías'
                        ] as $key => $label)

                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                                <div class="text-xs text-gray-500">
                                    {{ $label }}
                                </div>

                                <div class="text-2xl font-bold text-gray-900 mt-1">
                                    {{ $summary['datos'][$key] ?? 0 }}
                                </div>
                            </div>

                        @endforeach

                    </div>

                    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">

                        <div class="rounded-lg border border-gray-200 p-4">
                            <div class="text-sm text-gray-500">
                                Cashback total
                            </div>

                            <div class="text-xl font-bold text-gray-900 mt-1">
                                ${{ number_format($summary['saldos']['cashback_total'], 2) }}
                            </div>
                        </div>

                        <div class="rounded-lg border border-gray-200 p-4">
                            <div class="text-sm text-gray-500">
                                Cashback reclamado
                            </div>

                            <div class="text-xl font-bold text-gray-900 mt-1">
                                ${{ number_format($summary['saldos']['cashback_claimed'], 2) }}
                            </div>
                        </div>

                        <div class="rounded-lg border border-gray-200 p-4">
                            <div class="text-sm text-gray-500">
                                Cashback disponible
                            </div>

                            <div class="text-xl font-bold text-gray-900 mt-1">
                                ${{ number_format($summary['saldos']['cashback_available'], 2) }}
                            </div>
                        </div>

                    </div>

                    <div class="mt-6 rounded-lg bg-red-50 border border-red-200 p-4">
                        <div class="font-semibold text-red-900">
                            🚨 Antes de continuar
                        </div>

                        <p class="text-sm text-red-800 mt-1">
                            Se eliminarán permanentemente la cuenta,
                            facturas, movimientos de cashback, rankings,
                            redenciones, usos de guía, sesiones, tokens
                            y archivos asociados a este usuario.
                        </p>
                    </div>

                    <div class="mt-8 flex justify-end gap-3">

                        <a
                            href="{{ route('users.index') }}"
                            class="px-5 py-2.5 rounded-lg bg-gray-100 text-gray-700 text-sm font-medium hover:bg-gray-200"
                        >
                            Cancelar
                        </a>

                        <form
                            method="POST"
                            action="{{ route('users.destroy', $user) }}"
                            onsubmit="return confirm('⚠️ ATENCIÓN: Esta acción eliminará DEFINITIVAMENTE el usuario y todos sus datos asociados. Esta operación no se puede deshacer. ¿Desea continuar?');"
                        >
                            @csrf
                            @method('DELETE')

                            <button
                                type="submit"
                                class="px-5 py-2.5 rounded-lg bg-red-600 text-white text-sm font-medium hover:bg-red-700"
                            >
                                🗑️ Eliminar definitivamente
                            </button>
                        </form>

                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>