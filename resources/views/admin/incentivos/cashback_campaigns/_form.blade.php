@csrf

<div class="grid grid-cols-2 gap-6">

    <div>

        <label class="block mb-2 font-semibold">
            Nombre <span class="text-red-600">*</span>
        </label>

        <x-input type="text" name="nombre" value="{{ old('nombre', $cashbackCampaign->nombre ?? '') }}" required />

        @error('nombre')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror

    </div>

    <div>

        <label class="block mb-2 font-semibold">
            Porcentaje Cashback (%) <span class="text-red-600">*</span>
        </label>

        <x-input type="number" step="0.01" min="0.01" max="100" name="porcentaje"
            value="{{ old('porcentaje', $cashbackCampaign->porcentaje ?? '') }}" required />

        @error('porcentaje')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror

    </div>

</div>

<div class="mt-6">

    <label class="block mb-2 font-semibold">
        Descripción
    </label>

    <textarea name="descripcion" rows="4" class="w-full rounded-xl border border-gray-300 px-4 py-3">{{ old('descripcion', $cashbackCampaign->descripcion ?? '') }}</textarea>

    @error('descripcion')
        <p class="mt-2 text-sm text-red-600">
            {{ $message }}
        </p>
    @enderror

</div>

<div class="grid grid-cols-2 gap-6 mt-6">

    <div>

        <label class="block mb-2 font-semibold">
            Valor mínimo de factura ($)
        </label>

        <x-input type="number" step="0.01" min="0.01" name="valor_alerta_factura"
            value="{{ old('valor_alerta_factura', $cashbackCampaign->valor_alerta_factura ?? '') }}" />

        @error('valor_alerta_factura')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror

    </div>

    <div>

        <label class="block mb-2 font-semibold">
            Estado
        </label>

        <select name="activo" class="w-full rounded-xl border border-gray-300 px-4 py-3">

            <option value="1" {{ old('activo', $cashbackCampaign->activo ?? 1) == 1 ? 'selected' : '' }}>

                Activa

            </option>

            <option value="0" {{ old('activo', $cashbackCampaign->activo ?? 1) == 0 ? 'selected' : '' }}>

                Inactiva

            </option>

        </select>

        @error('activo')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror

    </div>

</div>

<div class="grid grid-cols-2 gap-6 mt-6">

    <div>

        <label class="block mb-2 font-semibold">
            Fecha inicio <span class="text-red-600">*</span>
        </label>

        <x-input type="date" name="fecha_inicio"
            value="{{ old('fecha_inicio', isset($cashbackCampaign) ? optional($cashbackCampaign->fecha_inicio)->format('Y-m-d') : '') }}"
            required />

        @error('fecha_inicio')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror

    </div>

    <div>

        <label class="block mb-2 font-semibold">
            Fecha fin <span class="text-red-600">*</span>
        </label>

        <x-input type="date" name="fecha_fin"
            value="{{ old('fecha_fin', isset($cashbackCampaign) ? optional($cashbackCampaign->fecha_fin)->format('Y-m-d') : '') }}"
            required />

        @error('fecha_fin')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror

    </div>

</div>

<hr class="my-8">

<div class="flex justify-end gap-4">

    <a href="{{ route('cashback-campaigns.index') }}"
        class="rounded-xl border border-gray-300 px-6 py-3 hover:bg-gray-100">

        Cancelar

    </a>

    <x-button>

        Guardar

    </x-button>

</div>
