@csrf

<div class="grid grid-cols-2 gap-6">

    <div>

        <label class="block mb-2 font-semibold">
            Campaña Cashback <span class="text-red-600">*</span>
        </label>

        <select name="cashback_campaign_id" class="w-full rounded-xl border border-gray-300 px-4 py-3" required>

            <option value="">
                Seleccione una campaña...
            </option>

            @foreach ($campaigns as $campaign)
                <option value="{{ $campaign->id }}"
                    {{ old('cashback_campaign_id', $rankingReward->cashback_campaign_id ?? '') == $campaign->id ? 'selected' : '' }}>

                    {{ $campaign->nombre }}

                </option>
            @endforeach

        </select>

        @error('cashback_campaign_id')
            <p class="mt-2 text-sm text-red-600">

                {{ $message }}

            </p>
        @enderror

    </div>

    <div>

        <label class="block mb-2 font-semibold">
            Tipo de premio <span class="text-red-600">*</span>
        </label>

        <select id="reward_type_id" name="reward_type_id" class="w-full rounded-xl border border-gray-300 px-4 py-3"
            required>

            <option value="">
                Seleccione un tipo...
            </option>

            @foreach ($rewardTypes as $type)
                <option value="{{ $type->id }}" data-code="{{ $type->codigo }}"
                    {{ old('reward_type_id', $rankingReward->reward_type_id ?? '') == $type->id ? 'selected' : '' }}>

                    {{ $type->nombre }}

                </option>
            @endforeach

        </select>

        @error('reward_type_id')
            <p class="mt-2 text-sm text-red-600">

                {{ $message }}

            </p>
        @enderror

    </div>

</div>

<div class="grid grid-cols-2 gap-6 mt-6">

    <div>

        <label class="block mb-2 font-semibold">
            Posición <span class="text-red-600">*</span>
        </label>

        <x-input type="number" min="1" max="100" name="posicion"
            value="{{ old('posicion', $rankingReward->posicion ?? '') }}" required />

        @error('posicion')
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

            <option value="1" {{ old('activo', $rankingReward->activo ?? 1) == 1 ? 'selected' : '' }}>

                Activo

            </option>

            <option value="0" {{ old('activo', $rankingReward->activo ?? 1) == 0 ? 'selected' : '' }}>

                Inactivo

            </option>

        </select>

    </div>

</div>

<div class="mt-6">

    <label class="block mb-2 font-semibold">
        Título <span class="text-red-600">*</span>
    </label>

    <x-input type="text" name="titulo" value="{{ old('titulo', $rankingReward->titulo ?? '') }}" required />

    @error('titulo')
        <p class="mt-2 text-sm text-red-600">

            {{ $message }}

        </p>
    @enderror

</div>

<div class="mt-6">

    <label class="block mb-2 font-semibold">
        Descripción
    </label>

    <textarea name="descripcion" rows="4" class="w-full rounded-xl border border-gray-300 px-4 py-3">{{ old('descripcion', $rankingReward->descripcion ?? '') }}</textarea>

    @error('descripcion')
        <p class="mt-2 text-sm text-red-600">

            {{ $message }}

        </p>
    @enderror

</div>

<div id="valor_container" class="mt-6">

    <label class="block mb-2 font-semibold">
        Valor referencial
    </label>

    <x-input type="number" step="0.01" min="0.01" name="valor_referencial"
        value="{{ old('valor_referencial', $rankingReward->valor_referencial ?? '') }}" />

    @error('valor_referencial')
        <p class="mt-2 text-sm text-red-600">

            {{ $message }}

        </p>
    @enderror

</div>

<div id="multiplicador_container" class="mt-6 hidden">

    <label class="block mb-2 font-semibold">
        Multiplicador Cashback
    </label>

    <x-input type="number" step="0.01" min="0.01" max="100" name="multiplicador"
        value="{{ old('multiplicador', $rankingReward->multiplicador ?? '') }}" />

    @error('multiplicador')
        <p class="mt-2 text-sm text-red-600">

            {{ $message }}

        </p>
    @enderror

</div>

<hr class="my-8">

<div class="flex justify-end gap-4">

    <a href="{{ route('ranking-rewards.index') }}"
        class="rounded-xl border border-gray-300 px-6 py-3 hover:bg-gray-100">

        Cancelar

    </a>

    <x-button>

        Guardar

    </x-button>

</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {

        const rewardType = document.getElementById('reward_type_id');

        const valor = document.getElementById('valor_container');

        const multiplicador = document.getElementById('multiplicador_container');

        function actualizarFormulario() {

            const option = rewardType.options[rewardType.selectedIndex];

            const code = option.dataset.code;

            if (code === 'cashback_multiplier') {

                valor.classList.add('hidden');

                multiplicador.classList.remove('hidden');

            } else {

                multiplicador.classList.add('hidden');

                valor.classList.remove('hidden');

            }

        }

        rewardType.addEventListener('change', actualizarFormulario);

        actualizarFormulario();

    });
</script>
