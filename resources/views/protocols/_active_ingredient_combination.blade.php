<template id="active-ingredient-combination-template">

    <div class="active-ingredient-combination-card overflow-hidden rounded-xl border border-purple-200 bg-white">

        <div class="bg-purple-50 px-5 py-4">

            <div class="flex items-end gap-4">

                <div class="flex-1">

                    <label class="mb-2 block text-sm font-medium text-gray-700">
                        Combinación de ingredientes activos
                    </label>

                    <select
                        class="active-ingredient-combination-select w-full rounded-lg border border-gray-300 bg-white px-3 py-2 focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-200"
                        required>
                        <option value="">
                            Seleccione una combinación
                        </option>
                    </select>

                </div>

                <button type="button"
                    class="btn-remove-active-ingredient-combination rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-red-700">
                    Eliminar
                </button>

            </div>

            <p class="mt-3 text-sm text-purple-700">
                Los productos asociados a la combinación se cargarán automáticamente.
                Configure la dosis de cada producto por separado.
            </p>

        </div>

        <div class="p-5">

            <div class="mb-4">
                <h5 class="font-semibold text-gray-800">
                    Productos de la combinación
                </h5>
                <p class="mt-1 text-sm text-gray-500">
                    La dosis, unidad y base de aplicación pertenecen a cada producto.
                </p>
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

                    <tbody class="active-ingredient-combination-products-container divide-y divide-gray-200">
                    </tbody>

                </table>

            </div>

        </div>

    </div>

</template>
