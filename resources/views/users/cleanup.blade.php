<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Limpiar datos de prueba
                </h2>

                <p class="text-sm text-gray-500 mt-1">
                    La cuenta del usuario se conservará.
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

            @if(session('error'))
                <div class="mb-5 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

                <div class="p-6 border-b border-gray-200">
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

                    <div class="rounded-lg bg-amber-50 border border-amber-200 p-4 mb-6">
                        <div class="font-semibold text-amber-900">
                            🧹 Se limpiarán los datos de prueba
                        </div>

                        <p class="text-sm text-amber-800 mt-1">
                            La cuenta del usuario, sus datos personales,
                            rol, sucursal, zona, bodega y configuración
                            permanecerán intactos.
                        </p>

                        <p class="text-sm text-amber-800 mt-2">
                            Se eliminarán sus datos transaccionales de prueba
                            y los rankings afectados serán reconstruidos
                            automáticamente con los datos que permanezcan
                            en el sistema.
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

                    <div class="mt-6 rounded-lg bg-blue-50 border border-blue-200 p-4">
                        <div class="font-semibold text-blue-900">
                            ℹ️ ¿Qué se conserva?
                        </div>

                        <p class="text-sm text-blue-800 mt-1">
                            Se conservarán la cuenta, nombres, identificación,
                            teléfono, correo, rol, almacén, zona, sucursal,
                            datos bancarios y demás configuración del usuario.
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
                            action="{{ route('users.cleanup', $user) }}"
                            onsubmit="return confirm('¿Está seguro de limpiar todos los datos de prueba de este usuario? La cuenta se conservará, pero sus datos transaccionales serán eliminados.');"
                        >
                            @csrf

                            <button
                                type="submit"
                                class="px-5 py-2.5 rounded-lg bg-amber-600 text-white text-sm font-medium hover:bg-amber-700"
                            >
                                🧹 Limpiar datos de prueba
                            </button>
                        </form>

                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>