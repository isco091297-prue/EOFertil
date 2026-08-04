@csrf

<div>

    <label class="block mb-2 font-semibold">
        Código
    </label>

    <x-input
        name="code"
        value="{{ old('code', $zone->code ?? '') }}"
        required />

</div>

<div class="mt-6">

    <label class="block mb-2 font-semibold">
        Nombre
    </label>

    <x-input
        name="name"
        value="{{ old('name', $zone->name ?? '') }}"
        required />

</div>

<div class="mt-6">

    <label class="block mb-2 font-semibold">
        Descripción
    </label>

    <textarea
        name="description"
        rows="4"
        class="w-full rounded-xl border border-gray-300 px-4 py-3">{{ old('description', $zone->description ?? '') }}</textarea>

</div>

<div class="mt-6">

    <label class="block mb-2 font-semibold">
        Estado
    </label>

    <select
        name="is_active"
        class="w-full rounded-xl border border-gray-300 px-4 py-3">

        <option value="1"
            @selected(old('is_active', $zone->is_active ?? 1))>

            Activo

        </option>

        <option value="0"
            @selected(old('is_active', $zone->is_active ?? 1) == 0)>

            Inactivo

        </option>

    </select>

</div>

<div class="mt-8 flex justify-end gap-4">

    <a
        href="{{ route('zones.index') }}"
        class="px-6 py-3 rounded-xl border">

        Cancelar

    </a>

    <x-button>

        Guardar

    </x-button>

</div>
