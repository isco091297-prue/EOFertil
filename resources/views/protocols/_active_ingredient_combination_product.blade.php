<template id="active-ingredient-combination-product-template">

    <tr class="active-ingredient-combination-product-row hover:bg-gray-50">

        <td class="px-4 py-3">
            <select
                class="active-ingredient-combination-product-select w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-200"
                required>
                <option value="">
                    Seleccione un producto
                </option>
            </select>
        </td>

        <td class="px-4 py-3">
            <input type="number"
                class="active-ingredient-combination-product-dose w-full rounded-lg border border-gray-300 px-3 py-2 text-center focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-200"
                min="0.01" step="0.01" placeholder="0.00" required>
        </td>

        <td class="px-4 py-3">
            <input type="text"
                class="active-ingredient-combination-product-unit w-full rounded-lg border border-gray-300 px-3 py-2 text-center focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-200"
                maxlength="30" placeholder="Ej: cc, gr" required>
        </td>

        <td class="px-4 py-3">
            <input type="text"
                class="active-ingredient-combination-product-application-base w-full rounded-lg border border-gray-300 px-3 py-2 text-center focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-200"
                maxlength="50" placeholder="Ej: litro, tanque" required>
        </td>

        <td class="px-4 py-3 text-center">
            <button type="button"
                class="btn-remove-active-ingredient-combination-product inline-flex items-center justify-center rounded-lg bg-red-600 px-3 py-2 text-sm font-medium text-white transition hover:bg-red-700">
                Eliminar
            </button>
        </td>

    </tr>

</template>
