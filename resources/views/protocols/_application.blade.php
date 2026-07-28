<template id="application-template">

    <div class="application-card rounded-xl bg-white shadow">

        <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">

            <div>

                <h3 class="text-lg font-semibold text-gray-800">

                    Aplicación
                    <span class="application-number"></span>

                </h3>

                <p class="mt-1 text-sm text-gray-500">
                    Productos que forman parte de esta aplicación.
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

        <div class="space-y-6 p-6">
            <div>

                <label class="mb-2 block text-sm font-medium text-gray-700">
                    Tipo de aplicación
                </label>

                <input type="text"
                    class="application-type w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                    placeholder="Ej: Foliar, Botón, Drench...">

            </div>

            <div>

                <label class="mb-2 block text-sm font-medium text-gray-700">
                    Descripción
                </label>

                <textarea rows="3"
                    class="application-description w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                    placeholder="Descripción de la aplicación..."></textarea>

            </div>

            <div class="overflow-x-auto">

                <table class="min-w-full border border-gray-200">

                    <thead class="bg-gray-100">

                        <tr>

                            <th class="border-b px-4 py-3 text-left text-sm font-semibold">

                                Producto

                            </th>

                            <th class="border-b px-4 py-3 text-center text-sm font-semibold w-40">

                                Dosis

                            </th>

                            <th class="border-b px-4 py-3 text-left text-sm font-semibold">

                                Observaciones

                            </th>

                            <th class="border-b px-4 py-3 text-center w-24">

                                Acción

                            </th>

                        </tr>

                    </thead>

                    <tbody class="products-container divide-y divide-gray-200">

                    </tbody>

                </table>

            </div>

            <div>

                <button type="button"
                    class="btn-add-product rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700">
                    + Agregar Producto
                </button>

            </div>

        </div>

    </div>

</template>
