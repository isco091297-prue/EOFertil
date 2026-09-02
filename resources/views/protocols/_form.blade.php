@csrf

@if (isset($protocol))
    <script
    id="protocol-data"
    type="application/json"
>
{!! json_encode($protocol->load([
    'applications.products.product',
    'applications.activeIngredients.activeIngredient',
    'applications.activeIngredients.products.product',
    'applications.activeIngredientCombinations.activeIngredientCombination',
    'applications.activeIngredientCombinations.products.product',
])) !!}
</script>
@endif

<div class="space-y-6">

    {{-- ========================================================= --}}
    {{-- INFORMACIÓN GENERAL --}}
    {{-- ========================================================= --}}

    <div class="rounded-xl bg-white shadow">

        <div class="border-b border-gray-200 px-6 py-4">

            <h2 class="text-lg font-semibold text-gray-800">
                Información del receta
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Seleccione el cultivo y el problema para este receta.
            </p>

        </div>

        <div class="grid grid-cols-1 gap-6 p-6 lg:grid-cols-2">

            {{-- Cultivo --}}
            <div>

                <label for="crop_id" class="mb-2 block text-sm font-medium text-gray-700">
                    Cultivo
                </label>

                <select id="crop_id" name="crop_id" required
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200">
                    <option value="">
                        Seleccione un cultivo
                    </option>
                </select>

            </div>

            {{-- Problema --}}
            <div>

                <label for="problem_id" class="mb-2 block text-sm font-medium text-gray-700">
                    Problema
                </label>

                <select id="problem_id" name="problem_id" required
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200">
                    <option value="">
                        Seleccione un problema
                    </option>
                </select>

            </div>

        </div>

    </div>

    {{-- ========================================================= --}}
    {{-- APLICACIONES --}}
    {{-- ========================================================= --}}

    <div>

        <div class="mb-4 flex items-center justify-between">

            <div>

                <h2 class="text-lg font-semibold text-gray-800">
                    Aplicaciones
                </h2>

                <p class="text-sm text-gray-500">
                    Agregue una o varias aplicaciones al receta.
                </p>

            </div>

            <button type="button" id="btn-add-application"
                class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700">
                + Agregar Aplicación
            </button>

        </div>

        <div id="applications-container" class="space-y-6"></div>

    </div>

    {{-- ========================================================= --}}
    {{-- BOTONES --}}
    {{-- ========================================================= --}}

    <div class="flex justify-end gap-3 border-t border-gray-200 pt-6">

        <a href="{{ route('protocols.index') }}"
            class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-gray-700 hover:bg-gray-100">
            Cancelar
        </a>

        <button type="submit" class="rounded-lg bg-green-600 px-6 py-2.5 font-medium text-white hover:bg-green-700">
            {{ isset($protocol) ? 'Actualizar Receta' : 'Guardar Receta' }}
        </button>

    </div>

</div>
