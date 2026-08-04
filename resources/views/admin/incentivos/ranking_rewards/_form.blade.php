@csrf

<div class="space-y-8">

    <div class="rounded-xl bg-white shadow">

        <div class="border-b border-gray-200 px-6 py-4">

            <h2 class="text-lg font-semibold text-gray-800">

                Información del premio

            </h2>

            <p class="mt-1 text-sm text-gray-500">

                Configure el premio que recibirá una posición del ranking.

            </p>

        </div>

        <div class="grid grid-cols-1 gap-6 p-6 lg:grid-cols-2">

            <div>

                <label class="mb-2 block font-semibold">

                    Campaña

                    <span class="text-red-600">*</span>

                </label>

                <select name="cashback_campaign_id" class="w-full rounded-xl border border-gray-300 px-4 py-3">

                    <option value="">
                        Seleccione una campaña
                    </option>

                    @foreach ($campaigns as $campaign)
                        <option value="{{ $campaign->id }}" @selected(old('cashback_campaign_id', $rankingReward->cashback_campaign_id ?? request()->route('cashbackCampaign')?->id) == $campaign->id)>

                            {{ $campaign->nombre }}

                        </option>
                    @endforeach

                </select>

            </div>

            <div>

                <label class="mb-2 block font-semibold">

                    Posición

                    <span class="text-red-600">*</span>

                </label>

                <x-input type="number" min="1" name="posicion"
                    value="{{ old('posicion', $rankingReward->posicion ?? 1) }}" required />

            </div>

            <div>

                <label class="mb-2 block font-semibold">

                    Tipo de premio

                    <span class="text-red-600">*</span>

                </label>

                <select id="reward_type" name="reward_type_id"
                    class="w-full rounded-xl border border-gray-300 px-4 py-3">

                    @foreach ($rewardTypes as $type)
                        <option value="{{ $type->id }}" data-code="{{ $type->codigo }}"
                            @selected(old('reward_type_id', $rankingReward->reward_type_id ?? '') == $type->id)>

                            {{ $type->nombre }}

                        </option>
                    @endforeach

                </select>

            </div>

            <div>

                <label class="mb-2 block font-semibold">

                    Estado

                </label>

                <select name="activo" class="w-full rounded-xl border border-gray-300 px-4 py-3">

                    <option value="1" @selected(old('activo', $rankingReward->activo ?? true))>

                        Activo

                    </option>

                    <option value="0" @selected(old('activo', $rankingReward->activo ?? true) == false)>

                        Inactivo

                    </option>

                </select>

            </div>

        </div>

    </div>

    <div class="rounded-xl bg-white shadow">

        <div class="border-b border-gray-200 px-6 py-4">

            <h2 class="text-lg font-semibold">

                Premio

            </h2>

        </div>

        <div class="space-y-6 p-6">

            <div>

                <label class="mb-2 block font-semibold">

                    Título

                    <span class="text-red-600">*</span>

                </label>

                <x-input type="text" name="titulo" value="{{ old('titulo', $rankingReward->titulo ?? '') }}"
                    required />

            </div>

            <div>

                <label class="mb-2 block font-semibold">

                    Descripción

                </label>

                <textarea name="descripcion" rows="4" class="w-full rounded-xl border border-gray-300 px-4 py-3">{{ old('descripcion', $rankingReward->descripcion ?? '') }}</textarea>

            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

                <div>

                    <label class="mb-2 block font-semibold">

                        Valor referencial

                    </label>

                    <x-input type="number" step="0.01" min="0" name="valor_referencial"
                        value="{{ old('valor_referencial', $rankingReward->valor_referencial ?? '') }}" />

                </div>

                <div id="cashback-multiplier-container">

                    <label class="mb-2 block font-semibold">

                        Multiplicador Cashback

                    </label>

                    <select name="multiplicador" class="w-full rounded-xl border border-gray-300 px-4 py-3">

                        <option value="">
                            No aplica
                        </option>

                        <option value="2" @selected(old('multiplicador', $rankingReward->multiplicador ?? '') == 2)>

                            2x Cashback

                        </option>

                        <option value="3" @selected(old('multiplicador', $rankingReward->multiplicador ?? '') == 3)>

                            3x Cashback

                        </option>

                        <option value="4" @selected(old('multiplicador', $rankingReward->multiplicador ?? '') == 4)>

                            4x Cashback

                        </option>

                        <option value="5" @selected(old('multiplicador', $rankingReward->multiplicador ?? '') == 5)>

                            5x Cashback

                        </option>

                    </select>

                    <p class="mt-2 text-sm text-gray-500">

                        Solo aplica cuando el premio es Cashback.

                    </p>

                </div>

            </div>

        </div>

    </div>

    <div class="flex justify-end gap-4">

        <a href="{{ url()->previous() }}" class="rounded-xl border border-gray-300 px-6 py-3 hover:bg-gray-100">

            Cancelar

        </a>

        <x-button>

            Guardar premio

        </x-button>

    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {

        const reward = document.getElementById('reward_type');

        const container = document.getElementById('cashback-multiplier-container');

        function refresh() {

            const option = reward.options[reward.selectedIndex];

            const code = option.dataset.code;

            container.style.display = (code === 'cashback') ?
                'block' :
                'none';

        }

        reward.addEventListener('change', refresh);

        refresh();

    });
</script>
