<template id="product-template">

    <tr class="product-row hover:bg-gray-50">

        {{-- Producto --}}
        <td class="px-4 py-3">

            <select
                class="product-select w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                required>
                <option value="">
                    Seleccione un producto
                </option>
            </select>

        </td>

        {{-- Dosis --}}
        <td class="px-4 py-3">

            <input type="number"
                class="dose-input w-full rounded-lg border border-gray-300 px-3 py-2 text-center focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                min="0.01" step="0.01" placeholder="0.00" required>

        </td>

        {{-- Unidad --}}
        <td class="px-4 py-3">

            <input type="text"
                class="unit-input w-full rounded-lg border border-gray-300 px-3 py-2 text-center focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                maxlength="30" placeholder="Ej: cc, gr" required>

        </td>

        {{-- Base de aplicación --}}
        <td class="px-4 py-3">

            <input type="text"
                class="application-base-input w-full rounded-lg border border-gray-300 px-3 py-2 text-center focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                maxlength="50" placeholder="Ej: litro, tanque" required>

        </td>

        {{-- Acción --}}
        <td class="px-4 py-3 text-center">

            <button type="button"
                class="btn-remove-product inline-flex items-center justify-center rounded-lg bg-red-600 px-3 py-2 text-sm font-medium text-white transition hover:bg-red-700">
                Eliminar
            </button>

        </td>

    </tr>

</template>
