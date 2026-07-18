@csrf

<div class="grid grid-cols-2 gap-6">

    <div>

        <label class="block mb-2 font-semibold">
            Código
        </label>

        <x-input
            type="text"
            name="code"
            value="{{ old('code', $brand->code ?? '') }}"
            required />

        @error('code')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror

    </div>

    <div>

        <label class="block mb-2 font-semibold">
            Nombre
        </label>

        <x-input
            type="text"
            name="name"
            value="{{ old('name', $brand->name ?? '') }}"
            required />

        @error('name')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror

    </div>

</div>

<div class="mt-6">

    <label class="block mb-2 font-semibold">
        Logo
    </label>

    @if(isset($brand) && filled($brand->logo_path))

        <div class="mb-4">

            <img
                src="{{ asset('storage/' . $brand->logo_path) }}"
                alt="{{ $brand->name }}"
                class="h-40 w-40 rounded-xl border object-cover">

        </div>

    @endif

    <input
        type="file"
        name="logo"
        accept=".jpg,.jpeg,.png,.webp"
        class="block w-full rounded-xl border border-gray-300 px-4 py-3">

    @error('logo')
        <p class="mt-2 text-sm text-red-600">
            {{ $message }}
        </p>
    @enderror

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
            {{ old('is_active', $brand->is_active ?? 1) == 1 ? 'selected' : '' }}>
            Activo
        </option>

        <option
            value="0"
            {{ old('is_active', $brand->is_active ?? 1) == 0 ? 'selected' : '' }}>
            Inactivo
        </option>

    </select>

    @error('is_active')
        <p class="mt-2 text-sm text-red-600">
            {{ $message }}
        </p>
    @enderror

</div>

<div class="mt-8 flex justify-end gap-4">

    <a
        href="{{ route('brands.index') }}"
        class="rounded-xl border border-gray-300 px-6 py-3 hover:bg-gray-100">

        Cancelar

    </a>

    <x-button>

        Guardar

    </x-button>

</div>
