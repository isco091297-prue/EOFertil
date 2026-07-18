<template id="product-template">

    <tr class="product-row hover:bg-gray-50">

        <td class="px-4 py-3">

            <select
                class="product-select w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                required
            >

                <option value="">
                    Seleccione un producto
                </option>

            </select>

        </td>

        <td class="px-4 py-3">

            <input
                type="number"
                class="dose-input w-full rounded-lg border border-gray-300 px-3 py-2 text-center focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                min="0.01"
                step="0.01"
                placeholder="0.00"
                required
            >

        </td>

        <td class="px-4 py-3">

            <input
                type="text"
                class="observations-input w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                maxlength="255"
                placeholder="Observaciones (opcional)"
            >

        </td>

        <td class="px-4 py-3 text-center">

            <button
                type="button"
                class="btn-remove-product inline-flex items-center justify-center rounded-lg bg-red-600 px-3 py-2 text-sm font-medium text-white transition hover:bg-red-700"
            >
                Eliminar
            </button>

        </td>

    </tr>

</template>
