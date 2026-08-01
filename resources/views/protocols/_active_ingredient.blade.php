<template id="active-ingredient-template">

    <div class="active-ingredient-card overflow-hidden rounded-xl border border-blue-200 bg-white">

        {{-- Cabecera del ingrediente --}}
        <div class="flex items-center justify-between bg-blue-50 px-5 py-4">

            <div class="flex-1">

                <label class="mb-2 block text-sm font-medium text-gray-700">
                    Ingrediente activo
                </label>

                <select
                    class="active-ingredient-select w-full rounded-lg border border-gray-300 bg-white px-3 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                    required>
                    <option value="">
                        Seleccione un ingrediente activo
                    </option>
                </select>

            </div>

            <div class="ml-4 pt-7">

                <button type="button"
                    class="btn-remove-active-ingredient rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-red-700">
                    Eliminar
                </button>

            </div>

        </div>

        {{-- Productos recomendados --}}
        <div class="p-5">

            <div class="mb-4 flex items-center justify-between">

                <div>

                    <h5 class="font-semibold text-gray-800">
                        Productos recomendados
                    </h5>

                    <p class="mt-1 text-sm text-gray-500">
                        Seleccione los productos vinculados a este ingrediente activo
                        y configure la dosis de cada uno.
                    </p>

                </div>

            </div>

            <div class="overflow-x-auto">

                <table class="min-w-full border border-gray-200">

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

                    <tbody class="active-ingredient-products-container divide-y divide-gray-200">
                    </tbody>

                </table>

            </div>

        </div>

    </div>

</template>
