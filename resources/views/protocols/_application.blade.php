<template id="application-template">

    <div class="application-card rounded-xl bg-white shadow">

        {{-- Cabecera --}}
        <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">

            <div>
                <h3 class="text-lg font-semibold text-gray-800">
                    Aplicación
                    <span class="application-number"></span>
                </h3>

                <p class="mt-1 text-sm text-gray-500">
                    Configure los productos EOFertil y los ingredientes activos
                    que forman parte de esta aplicación.
                </p>
            </div>

            <div class="flex items-center gap-3">

                <input type="hidden" class="application-number-input">

                <button type="button"
                    class="btn-remove-application rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-red-700">
                    Eliminar
                </button>

            </div>

        </div>

        <div class="space-y-8 p-6">

            {{-- Tipo de aplicación --}}
            <div>

                <label class="mb-2 block text-sm font-medium text-gray-700">
                    Tipo de aplicación
                </label>

                <input type="text"
                    class="application-type w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                    placeholder="Ej: Foliar, Botón, Drench...">

            </div>

            {{-- Descripción --}}
            <div>

                <label class="mb-2 block text-sm font-medium text-gray-700">
                    Descripción
                </label>

                <textarea rows="3"
                    class="application-description w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                    placeholder="Descripción de la aplicación..."></textarea>

            </div>

            {{-- ========================================================= --}}
            {{-- PRODUCTOS EOFERTIL --}}
            {{-- ========================================================= --}}

            <div class="rounded-xl border border-green-200 bg-green-50/40">

                <div class="flex items-center justify-between border-b border-green-200 px-5 py-4">

                    <div>

                        <h4 class="font-semibold text-green-800">
                            Productos EOFertil
                        </h4>

                        <p class="mt-1 text-sm text-gray-500">
                            Productos EOFertil recomendados directamente
                            para esta aplicación.
                        </p>

                    </div>

                    <button type="button"
                        class="btn-add-product rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-green-700">
                        + Agregar Producto
                    </button>

                </div>

                <div class="overflow-x-auto p-5">

                    <table class="min-w-full border border-gray-200 bg-white">

                        <thead class="bg-gray-100">

                            <tr>

                                <th class="border-b px-4 py-3 text-left text-sm font-semibold">
                                    Producto
                                </th>

                                <th class="w-32 border-b px-4 py-3 text-center text-sm font-semibold">
                                    Dosis
                                </th>

                                <th class="w-36 border-b px-4 py-3 text-center text-sm font-semibold">
                                    Unidad
                                </th>

                                <th class="w-44 border-b px-4 py-3 text-center text-sm font-semibold">
                                    Base aplicación
                                </th>

                                <th class="w-24 border-b px-4 py-3 text-center text-sm font-semibold">
                                    Acción
                                </th>

                            </tr>

                        </thead>

                        <tbody class="products-container divide-y divide-gray-200">
                        </tbody>

                    </table>

                </div>

            </div>

            {{-- ========================================================= --}}
            {{-- INGREDIENTES ACTIVOS --}}
            {{-- ========================================================= --}}

            <div class="rounded-xl border border-blue-200 bg-blue-50/40">

                <div class="flex items-center justify-between border-b border-blue-200 px-5 py-4">

                    <div>

                        <h4 class="font-semibold text-blue-800">
                            Ingredientes activos
                        </h4>

                        <p class="mt-1 text-sm text-gray-500">
                            Agregue ingredientes activos y seleccione los
                            productos recomendados para cada uno.
                        </p>

                    </div>

                    <button type="button"
                        class="btn-add-active-ingredient rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700">
                        + Agregar Ingrediente
                    </button>

                </div>

                <div class="active-ingredients-container space-y-4 p-5">
                </div>

            </div>

        </div>

    </div>

</template>
