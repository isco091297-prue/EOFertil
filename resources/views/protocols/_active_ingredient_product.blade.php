<template id="active-ingredient-product-template">

    <tr class="active-ingredient-product-row hover:bg-gray-50">

        {{-- Producto recomendado --}}
        <td class="px-4 py-3">

            <select
                class="active-ingredient-product-select w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                required>
                <option value="">
                    Seleccione un producto
                </option>
            </select>

        </td>

        {{-- Dosis --}}
        <td class="px-4 py-3">

            <input type="number"
                class="active-ingredient-product-dose w-full rounded-lg border border-gray-300 px-3 py-2 text-center focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                min="0.01" step="0.01" placeholder="0.00" required>

        </td>

        {{-- Unidad --}}
        <td class="px-4 py-3">

            <input type="text"
                class="active-ingredient-product-unit w-full rounded-lg border border-gray-300 px-3 py-2 text-center focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                maxlength="30" placeholder="Ej: cc, gr" required>

        </td>

        {{-- Base de aplicación --}}
        <td class="px-4 py-3">

            <input type="text"
                class="active-ingredient-product-application-base w-full rounded-lg border border-gray-300 px-3 py-2 text-center focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                maxlength="50" placeholder="Ej: litro, tanque" required>

        </td>

        {{-- Acción --}}
        <td class="px-4 py-3 text-center">

            <button type="button"
                class="btn-remove-active-ingredient-product inline-flex items-center justify-center rounded-lg bg-red-600 px-3 py-2 text-sm font-medium text-white transition hover:bg-red-700">
                Eliminar
            </button>

        </td>

    </tr>

</template>
