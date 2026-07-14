@csrf

<div class="grid grid-cols-2 gap-6">

    <div>
        <label class="block mb-2 font-semibold">
            Código
        </label>

        <x-input
            type="text"
            name="code"
            value="{{ old('code', $warehouse->code ?? '') }}"
            required />
    </div>

    <div>
        <label class="block mb-2 font-semibold">
            Nombre
        </label>

        <x-input
            type="text"
            name="name"
            value="{{ old('name', $warehouse->name ?? '') }}"
            required />
    </div>

</div>

<div class="mt-6">

    <label class="block mb-2 font-semibold">
        Descripción
    </label>

    <textarea
        name="description"
        rows="4"
        class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-600">{{ old('description', $warehouse->description ?? '') }}</textarea>

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
            {{ old('is_active', $warehouse->is_active ?? 1) == 1 ? 'selected' : '' }}>

            Activo

        </option>

        <option
            value="0"
            {{ old('is_active', $warehouse->is_active ?? 1) == 0 ? 'selected' : '' }}>

            Inactivo

        </option>

    </select>

</div>

<div class="mt-8 flex justify-end gap-4">

    <a
        href="{{ route('warehouses.index') }}"
        class="px-6 py-3 rounded-xl border border-gray-300">

        Cancelar

    </a>

    <x-button>

        Guardar

    </x-button>

</div>
