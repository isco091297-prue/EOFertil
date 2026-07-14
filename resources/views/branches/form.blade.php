@csrf

<div class="grid grid-cols-2 gap-6">

    <div>

        <label class="block mb-2 font-semibold">

            Almacén

        </label>

        <select
            name="warehouse_id"
            class="w-full rounded-xl border border-gray-300 px-4 py-3">

            @foreach($warehouses as $warehouse)

                <option
                    value="{{ $warehouse->id }}"
                    @selected(old('warehouse_id', $branch->warehouse_id ?? '') == $warehouse->id)>

                    {{ $warehouse->name }}

                </option>

            @endforeach

        </select>

    </div>

    <div>

        <label class="block mb-2 font-semibold">

            Zona

        </label>

        <select
            name="zone_id"
            class="w-full rounded-xl border border-gray-300 px-4 py-3">

            @foreach($zones as $zone)

                <option
                    value="{{ $zone->id }}"
                    @selected(old('zone_id', $branch->zone_id ?? '') == $zone->id)>

                    {{ $zone->name }}

                </option>

            @endforeach

        </select>

    </div>

</div>

<div class="grid grid-cols-2 gap-6 mt-6">

    <div>

        <label class="block mb-2 font-semibold">

            Código

        </label>

        <x-input
            name="code"
            value="{{ old('code', $branch->code ?? '') }}"
            required />

    </div>

    <div>

        <label class="block mb-2 font-semibold">

            Nombre

        </label>

        <x-input
            name="name"
            value="{{ old('name', $branch->name ?? '') }}"
            required />

    </div>

</div>

<div class="mt-6">

    <label class="block mb-2 font-semibold">

        Dirección

    </label>

    <x-input
        name="address"
        value="{{ old('address', $branch->address ?? '') }}" />

</div>

<div class="grid grid-cols-2 gap-6 mt-6">

    <div>

        <label class="block mb-2 font-semibold">

            Teléfono

        </label>

        <x-input
            name="phone"
            value="{{ old('phone', $branch->phone ?? '') }}" />

    </div>

    <div>

        <label class="block mb-2 font-semibold">

            Estado

        </label>

        <select
            name="is_active"
            class="w-full rounded-xl border border-gray-300 px-4 py-3">

            <option
                value="1"
                @selected(old('is_active', $branch->is_active ?? 1)==1)>

                Activo

            </option>

            <option
                value="0"
                @selected(old('is_active', $branch->is_active ?? 1)==0)>

                Inactivo

            </option>

        </select>

    </div>

</div>

<div class="mt-6">

    <label class="block mb-2 font-semibold">

        Descripción

    </label>

    <textarea
        name="description"
        rows="4"
        class="w-full rounded-xl border border-gray-300 px-4 py-3">{{ old('description', $branch->description ?? '') }}</textarea>

</div>

<div class="mt-8 flex justify-end gap-4">

    <a
        href="{{ route('branches.index') }}"
        class="px-6 py-3 rounded-xl border">

        Cancelar

    </a>

    <x-button>

        Guardar

    </x-button>

</div>
