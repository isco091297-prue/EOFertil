@csrf

@php
    $isAccumulated = $cashbackCampaign->campaign_type === 'ranking_accumulated';

    $isCashback = $cashbackCampaign->campaign_type === 'cashback';
@endphp

<div class="space-y-8">

    {{-- =========================================================
         INFORMACIÓN DEL PREMIO
    ========================================================== --}}

    <div class="rounded-xl bg-white shadow">

        <div class="border-b border-gray-200 px-6 py-4">

            <h2 class="text-lg font-semibold text-gray-800">
                Información del premio
            </h2>

            <p class="mt-1 text-sm text-gray-500">

                @if ($isAccumulated)
                    Configure el premio que recibirá el participante
                    que ocupe esta posición en el ranking acumulado.
                @else
                    Configure el premio que recibirá una posición
                    del ranking Cashback.
                @endif

            </p>

        </div>

        <div class="grid grid-cols-1 gap-6 p-6 lg:grid-cols-2">

            {{-- Campaña --}}

            <div>

                <label class="mb-2 block font-semibold">
                    Campaña
                </label>

                <input type="text" readonly value="{{ $cashbackCampaign->nombre }}"
                    class="w-full rounded-xl border border-gray-200 bg-gray-100 px-4 py-3">

            </div>

            {{-- Tipo de campaña --}}

            <div>

                <label class="mb-2 block font-semibold">
                    Tipo de campaña
                </label>

                <input type="text" readonly value="{{ $isAccumulated ? 'Ranking Acumulado' : 'Ranking Cashback' }}"
                    class="w-full rounded-xl border border-gray-200 bg-gray-100 px-4 py-3">

            </div>

            {{-- Posición --}}

            <div>

                <label class="mb-2 block font-semibold">

                    Posición

                    <span class="text-red-600">*</span>

                </label>

                <x-input type="number" min="1" max="100" name="posicion"
                    value="{{ old('posicion', $rankingReward->posicion ?? 1) }}" required />

                @if ($isAccumulated)
                    <p class="mt-2 text-sm text-gray-500">
                        Actualmente puedes configurar cualquier posición.
                        Para esta campaña utilizaremos inicialmente el puesto 1.
                    </p>
                @endif

            </div>

            {{-- Tipo de premio --}}

            <div>

                <label class="mb-2 block font-semibold">

                    Tipo de premio

                    <span class="text-red-600">*</span>

                </label>

                <select id="reward_type" name="reward_type_id" required
                    class="w-full rounded-xl border border-gray-300 px-4 py-3">

                    @if ($rewardTypes->isEmpty())

                        <option value="">
                            No existen tipos de premio registrados
                        </option>
                    @else
                        <option value="">
                            Seleccione un tipo de premio
                        </option>

                        @foreach ($rewardTypes as $type)
                            <option value="{{ $type->id }}" data-code="{{ $type->codigo }}"
                                @selected(old('reward_type_id', $rankingReward->reward_type_id ?? '') == $type->id)>

                                {{ $type->nombre }}

                            </option>
                        @endforeach

                    @endif

                </select>

                @if ($isAccumulated)
                    <p class="mt-2 text-sm text-gray-500">
                        Ejemplos: dinero, teléfono, televisor u otro premio.
                    </p>
                @else
                    <p class="mt-2 text-sm text-gray-500">
                        Seleccione el tipo de premio correspondiente al ranking Cashback.
                    </p>
                @endif

            </div>

            {{-- Estado --}}

            <div>

                <label class="mb-2 block font-semibold">
                    Estado
                </label>

                <select name="activo" class="w-full rounded-xl border border-gray-300 px-4 py-3">

                    <option value="1" @selected(old('activo', $rankingReward->activo ?? true) == true)>
                        Activo
                    </option>

                    <option value="0" @selected(old('activo', $rankingReward->activo ?? true) == false)>
                        Inactivo
                    </option>

                </select>

            </div>

        </div>

    </div>


    {{-- =========================================================
         DATOS DEL PREMIO
    ========================================================== --}}

    <div class="rounded-xl bg-white shadow">

        <div class="border-b border-gray-200 px-6 py-4">

            <h2 class="text-lg font-semibold">
                Premio
            </h2>

        </div>

        <div class="space-y-6 p-6">

            {{-- Título --}}

            <div>

                <label class="mb-2 block font-semibold">

                    Título

                    <span class="text-red-600">*</span>

                </label>

                <x-input type="text" name="titulo" value="{{ old('titulo', $rankingReward->titulo ?? '') }}"
                    placeholder="{{ $isAccumulated ? 'Ej: iPhone 16, $500 en efectivo, TV Samsung 55' : 'Ej: Bono Cashback x2' }}"
                    required />

            </div>

            {{-- Descripción --}}

            <div>

                <label class="mb-2 block font-semibold">
                    Descripción
                </label>

                <textarea name="descripcion" rows="4"
                    placeholder="{{ $isAccumulated ? 'Descripción del premio que recibirá el ganador.' : 'Descripción del premio.' }}"
                    class="w-full rounded-xl border border-gray-300 px-4 py-3">{{ old('descripcion', $rankingReward->descripcion ?? '') }}</textarea>

            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

                {{-- Valor referencial --}}

                <div>

                    <label class="mb-2 block font-semibold">

                        Valor referencial

                    </label>

                    <x-input type="number" step="0.01" min="0" name="valor_referencial"
                        value="{{ old('valor_referencial', $rankingReward->valor_referencial ?? '') }}"
                        placeholder="0.00" />

                    @if ($isAccumulated)
                        <p class="mt-2 text-sm text-gray-500">

                            Valor aproximado del premio.
                            Es informativo y no determina el ganador.

                        </p>
                    @else
                        <p class="mt-2 text-sm text-gray-500">

                            Valor económico referencial del premio.

                        </p>
                    @endif

                </div>


                {{-- Multiplicador Cashback --}}

                <div id="cashback-multiplier-container" class="{{ $isAccumulated ? 'hidden' : '' }}">

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

                        Solo aplica cuando el tipo de premio es
                        Multiplicador de Cashback.

                    </p>

                </div>

            </div>

        </div>

    </div>


    {{-- =========================================================
         BOTONES
    ========================================================== --}}

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

        const container = document.getElementById(
            'cashback-multiplier-container'
        );

        function refresh() {

            if (!reward || !container) {
                return;
            }

            const option =
                reward.options[reward.selectedIndex];

            if (!option) {
                container.classList.add('hidden');
                return;
            }

            const code =
                option.dataset.code;

            if (code === 'cashback_multiplier') {

                container.classList.remove('hidden');

            } else {

                container.classList.add('hidden');

            }

        }

        reward?.addEventListener(
            'change',
            refresh
        );

        refresh();

    });
</script>
