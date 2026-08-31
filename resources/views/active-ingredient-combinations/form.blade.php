@csrf

{{-- ============================================================
     INFORMACIÓN DE LA COMBINACIÓN
============================================================ --}}

<div>

    <div class="rounded-xl border border-green-200 bg-green-50 p-4">

        <div class="font-semibold text-green-900">
            Nombre de la combinación
        </div>

        <p class="mt-1 text-sm text-green-700">
            El nombre se generará automáticamente a partir de los ingredientes activos seleccionados.
        </p>

    </div>

</div>


{{-- ============================================================
     DESCRIPCIÓN
============================================================ --}}

<div class="mt-6">

    <label class="block mb-2 font-semibold">
        Descripción
    </label>

    <textarea name="description" rows="3" class="w-full rounded-xl border border-gray-300 px-4 py-3"
        placeholder="Descripción opcional de la combinación">{{ old('description', $activeIngredientCombination->description ?? '') }}</textarea>

    @error('description')
        <p class="mt-2 text-sm text-red-600">
            {{ $message }}
        </p>
    @enderror

</div>


{{-- ============================================================
     ESTADO
============================================================ --}}

<div class="mt-6">

    <label class="block mb-2 font-semibold">
        Estado
    </label>

    <select name="is_active" class="w-full rounded-xl border border-gray-300 px-4 py-3">

        <option value="1"
            {{ old('is_active', $activeIngredientCombination->is_active ?? 1) == 1 ? 'selected' : '' }}>
            Activo
        </option>

        <option value="0"
            {{ old('is_active', $activeIngredientCombination->is_active ?? 1) == 0 ? 'selected' : '' }}>
            Inactivo
        </option>

    </select>

</div>


{{-- ============================================================
     INGREDIENTES ACTIVOS
============================================================ --}}

<div class="mt-8">

    <div class="mb-4">

        <h2 class="text-xl font-bold">
            Ingredientes activos
        </h2>

        <p class="mt-1 text-sm text-gray-500">
            Selecciona los ingredientes activos que forman parte de esta
            combinación. Debes seleccionar al menos dos.
        </p>

    </div>


    @php

        $selectedIngredients = old(
            'active_ingredients',
            isset($activeIngredientCombination)
                ? $activeIngredientCombination->activeIngredients->pluck('id')->toArray()
                : [],
        );

    @endphp


    @if ($activeIngredients->isEmpty())

        <div class="rounded-xl border border-gray-200 bg-gray-50 p-5 text-gray-600">

            No existen ingredientes activos disponibles.

        </div>
    @else
        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">

            @foreach ($activeIngredients as $ingredient)
                <label
                    class="flex cursor-pointer items-start gap-3 rounded-xl border border-gray-200 p-4 transition hover:border-gray-400 hover:bg-gray-50">

                    <input type="checkbox" name="active_ingredients[]" value="{{ $ingredient->id }}"
                        class="mt-1 h-4 w-4 rounded border-gray-300"
                        {{ in_array($ingredient->id, $selectedIngredients) ? 'checked' : '' }}>

                    <div class="min-w-0">

                        <div class="font-semibold text-gray-900">
                            {{ $ingredient->name }}
                        </div>

                        @if ($ingredient->description)
                            <div class="mt-1 text-sm text-gray-500">
                                {{ $ingredient->description }}
                            </div>
                        @endif

                    </div>

                </label>
            @endforeach

        </div>

    @endif


    @error('active_ingredients')
        <p class="mt-2 text-sm text-red-600">
            {{ $message }}
        </p>
    @enderror


    @error('active_ingredients.*')
        <p class="mt-2 text-sm text-red-600">
            {{ $message }}
        </p>
    @enderror

</div>


{{-- ============================================================
     PRODUCTOS
============================================================ --}}

<div class="mt-10">

    <div class="mb-4">

        <h2 class="text-xl font-bold">
            Productos vinculados
        </h2>

        <p class="mt-1 text-sm text-gray-500">
            Aquí aparecen todos los productos activos registrados.
            Selecciona manualmente los productos que corresponden a esta
            combinación.
        </p>

    </div>


    @php

        $selectedProducts = old(
            'products',
            isset($activeIngredientCombination) ? $activeIngredientCombination->products->pluck('id')->toArray() : [],
        );

    @endphp


    @if ($products->isEmpty())

        <div class="rounded-xl border border-gray-200 bg-gray-50 p-5 text-gray-600">

            No existen productos activos disponibles para vincular.

        </div>
    @else
        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">

            @foreach ($products as $product)
                <label
                    class="flex cursor-pointer items-start gap-3 rounded-xl border border-gray-200 p-4 transition hover:border-gray-400 hover:bg-gray-50">

                    <input type="checkbox" name="products[]" value="{{ $product->id }}"
                        class="mt-1 h-4 w-4 rounded border-gray-300"
                        {{ in_array($product->id, $selectedProducts) ? 'checked' : '' }}>

                    <div class="min-w-0">

                        <div class="font-semibold text-gray-900">
                            {{ $product->name }}
                        </div>

                        <div class="mt-1 text-sm text-gray-500">

                            @if ($product->brand)
                                {{ $product->brand->name }}
                            @else
                                Sin marca
                            @endif


                            @if ($product->category)
                                · {{ $product->category->name }}
                            @endif

                        </div>

                    </div>

                </label>
            @endforeach

        </div>

    @endif


    @error('products')
        <p class="mt-2 text-sm text-red-600">
            {{ $message }}
        </p>
    @enderror


    @error('products.*')
        <p class="mt-2 text-sm text-red-600">
            {{ $message }}
        </p>
    @enderror

</div>


{{-- ============================================================
     BOTONES
============================================================ --}}

<div class="mt-8 flex justify-end gap-4">

    <a href="{{ route('active-ingredient-combinations.index') }}"
        class="rounded-xl border border-gray-300 px-6 py-3 hover:bg-gray-100">
        Cancelar
    </a>

    <x-button>
        Guardar
    </x-button>

</div>
