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

        </div>

        <div class="p-5">

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">

                {{-- Dosis --}}
                <div>

                    <label class="mb-2 block text-sm font-medium text-gray-700">
                        Dosis
                    </label>

                    <input type="number" min="0.01" step="0.01"
                        class="active-ingredient-combination-dose w-full rounded-lg border border-gray-300 px-3 py-2 text-center focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-200"
                        placeholder="0.00" required>

                </div>

                {{-- Unidad --}}
                <div>

                    <label class="mb-2 block text-sm font-medium text-gray-700">
                        Unidad
                    </label>

                    <input type="text" maxlength="30"
                        class="active-ingredient-combination-unit w-full rounded-lg border border-gray-300 px-3 py-2 text-center focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-200"
                        placeholder="Ej: cc, gr" required>

                </div>

                {{-- Base --}}
                <div>

                    <label class="mb-2 block text-sm font-medium text-gray-700">
                        Base de aplicación
                    </label>

                    <input type="text" maxlength="50"
                        class="active-ingredient-combination-application-base w-full rounded-lg border border-gray-300 px-3 py-2 text-center focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-200"
                        placeholder="Ej: litro, tanque" required>

                </div>

            </div>

        </div>

    </div>

</template>
