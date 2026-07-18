@csrf

<div class="grid grid-cols-2 gap-6">

    <div>

        <label class="block mb-2 font-semibold">
            Código
        </label>

        <x-input
            type="text"
            name="code"
            value="{{ old('code', $product->code ?? '') }}"
            required />

        @error('code')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror

    </div>

    <div>

        <label class="block mb-2 font-semibold">
            Nombre
        </label>

        <x-input
            type="text"
            name="name"
            value="{{ old('name', $product->name ?? '') }}"
            required />

        @error('name')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror

    </div>

</div>

<div class="grid grid-cols-2 gap-6 mt-6">

    <div>

        <label class="block mb-2 font-semibold">
            Marca
        </label>

        <select
            name="brand_id"
            class="w-full rounded-xl border border-gray-300 px-4 py-3">

            <option value="">
                Seleccione...
            </option>

            @foreach($brands as $brand)

                <option
                    value="{{ $brand->id }}"
                    {{ old('brand_id', $product->brand_id ?? '') == $brand->id ? 'selected' : '' }}>

                    {{ $brand->name }}

                </option>

            @endforeach

        </select>

        @error('brand_id')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror

    </div>

    <div>

        <label class="block mb-2 font-semibold">
            Categoría
        </label>

        <select
            name="category_id"
            class="w-full rounded-xl border border-gray-300 px-4 py-3">

            <option value="">
                Seleccione...
            </option>

            @foreach($categories as $category)

                <option
                    value="{{ $category->id }}"
                    {{ old('category_id', $product->category_id ?? '') == $category->id ? 'selected' : '' }}>

                    {{ $category->name }}

                </option>

            @endforeach

        </select>

        @error('category_id')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror

    </div>

</div>

<div class="mt-6">

    <label class="block mb-2 font-semibold">
        Descripción
    </label>

    <textarea
        name="description"
        rows="4"
        class="w-full rounded-xl border border-gray-300 px-4 py-3">{{ old('description', $product->description ?? '') }}</textarea>

</div>

<div class="mt-6">

    <label class="block mb-2 font-semibold">
        Imagen
    </label>

    @if(isset($product) && filled($product->image_path))

        <div class="mb-4">

            <img
                src="{{ asset('storage/'.$product->image_path) }}"
                class="h-40 w-40 rounded-xl border object-cover">

        </div>

    @endif

    <input
        type="file"
        name="image"
        accept=".jpg,.jpeg,.png,.webp"
        class="block w-full rounded-xl border border-gray-300 px-4 py-3">

</div>

<div class="mt-6">

    <label class="block mb-2 font-semibold">

        Estado

    </label>

    <select
        name="is_active"
        class="w-full rounded-xl border border-gray-300 px-4 py-3">

        <option
            value="1"
            {{ old('is_active',$product->is_active ?? 1)==1?'selected':'' }}>

            Activo

        </option>

        <option
            value="0"
            {{ old('is_active',$product->is_active ?? 1)==0?'selected':'' }}>

            Inactivo

        </option>

    </select>

</div>

<div class="mt-8 flex justify-end gap-4">

    <a
        href="{{ route('products.index') }}"
        class="rounded-xl border border-gray-300 px-6 py-3 hover:bg-gray-100">

        Cancelar

    </a>

    <x-button>

        Guardar

    </x-button>

</div>
