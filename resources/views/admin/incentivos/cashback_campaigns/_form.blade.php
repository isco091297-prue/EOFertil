@csrf

<div class="space-y-8">

    <div class="rounded-xl bg-white shadow">

        <div class="border-b border-gray-200 px-6 py-4">

            <h2 class="text-lg font-semibold text-gray-800">

                Información General

            </h2>

            <p class="mt-1 text-sm text-gray-500">

                Cree una campaña de Cashback o un Ranking Acumulado.

            </p>

        </div>

        <div class="grid grid-cols-1 gap-6 p-6 lg:grid-cols-2">

            <div>

                <label class="mb-2 block font-semibold">

                    Nombre

                    <span class="text-red-600">*</span>

                </label>

                <x-input type="text" name="nombre" value="{{ old('nombre', $cashbackCampaign->nombre ?? '') }}"
                    required />

            </div>

            <div>

                <label class="mb-2 block font-semibold">

                    Tipo de campaña

                    <span class="text-red-600">*</span>

                </label>

                <select id="campaign_type" name="campaign_type"
                    class="w-full rounded-xl border border-gray-300 px-4 py-3">

                    <option value="cashback" @selected(old('campaign_type', $cashbackCampaign->campaign_type ?? '') == 'cashback')>

                        Cashback

                    </option>

                    <option value="ranking_accumulated" @selected(old('campaign_type', $cashbackCampaign->campaign_type ?? '') == 'ranking_accumulated')>

                        Ranking Acumulado

                    </option>

                </select>

                <p class="mt-2 text-sm text-gray-500">

                    El Ranking Cashback se configurará dentro de una campaña Cashback.

                </p>

            </div>

        </div>

        <div class="px-6 pb-6">

            <label class="mb-2 block font-semibold">

                Descripción

            </label>

            <textarea name="descripcion" rows="4" class="w-full rounded-xl border border-gray-300 px-4 py-3">{{ old('descripcion', $cashbackCampaign->descripcion ?? '') }}</textarea>

        </div>

    </div>

    <div class="rounded-xl bg-white shadow">

        <div class="border-b border-gray-200 px-6 py-4">

            <h2 class="text-lg font-semibold">

                Participantes

            </h2>

            <p class="mt-1 text-sm text-gray-500">

                Seleccione quiénes participarán en la campaña.

            </p>

        </div>

        <div class="p-6">

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                <label class="flex items-center gap-3">

                    <input type="radio" name="participant_type" value="all" class="participant-type"
                        @checked(old('participant_type', $cashbackCampaign->participant_type ?? 'all') == 'all')>

                    Todos

                </label>

                <label class="flex items-center gap-3">

                    <input type="radio" name="participant_type" value="warehouse" class="participant-type"
                        @checked(old('participant_type', $cashbackCampaign->participant_type ?? '') == 'warehouse')>

                    Almacenes

                </label>

                <label class="flex items-center gap-3">

                    <input type="radio" name="participant_type" value="zone" class="participant-type"
                        @checked(old('participant_type', $cashbackCampaign->participant_type ?? '') == 'zone')>

                    Zonas

                </label>

                <label class="flex items-center gap-3">

                    <input type="radio" name="participant_type" value="branch" class="participant-type"
                        @checked(old('participant_type', $cashbackCampaign->participant_type ?? '') == 'branch')>

                    Sucursales

                </label>

            </div>

            <div id="warehouse-section" class="mt-8 hidden">

                <h3 class="mb-4 text-base font-semibold">

                    Almacenes participantes

                </h3>

                <div class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-3">

                    @foreach ($warehouses as $warehouse)
                        <label class="flex items-center gap-3 rounded-lg border p-3 hover:bg-gray-50">

                            <input type="checkbox" name="warehouse_ids[]" value="{{ $warehouse->id }}">

                            <span>

                                {{ $warehouse->name }}

                            </span>

                        </label>
                    @endforeach

                </div>

            </div>

            <div id="zone-section" class="mt-8 hidden">

                <h3 class="mb-4 text-base font-semibold">

                    Zonas participantes

                </h3>

                <div class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-3">

                    @foreach ($zones as $zone)
                        <label class="flex items-center gap-3 rounded-lg border p-3 hover:bg-gray-50">

                            <input type="checkbox" name="zone_ids[]" value="{{ $zone->id }}">

                            <span>

                                {{ $zone->name }}

                            </span>

                        </label>
                    @endforeach

                </div>

            </div>

            <div id="branch-section" class="mt-8 hidden">

                <h3 class="mb-4 text-base font-semibold">

                    Sucursales participantes

                </h3>

                <div class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-3">

                    @foreach ($branches as $branch)
                        <label class="flex items-center gap-3 rounded-lg border p-3 hover:bg-gray-50">

                            <input type="checkbox" name="branch_ids[]" value="{{ $branch->id }}">

                            <span>

                                {{ $branch->name }}

                            </span>

                        </label>
                    @endforeach

                </div>

            </div>

        </div>

    </div>

    <div class="rounded-xl bg-white shadow">

        <div class="border-b border-gray-200 px-6 py-4">

            <h2 class="text-lg font-semibold">

                Configuración

            </h2>

            <p class="mt-1 text-sm text-gray-500">

                Configure el cashback y, opcionalmente, el Ranking Cashback.

            </p>

        </div>

        <div class="space-y-8 p-6">

            <div id="cashback-config">

                <h3 class="mb-6 text-lg font-semibold text-green-700">

                    Cashback

                </h3>

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

                    <div>

                        <label class="mb-2 block font-semibold">

                            Porcentaje Cashback (%)

                        </label>

                        <x-input type="number" step="0.01" min="0.01" max="100" name="porcentaje"
                            value="{{ old('porcentaje', $cashbackCampaign->porcentaje ?? '') }}" />

                    </div>

                    <div>

                        <label class="mb-2 block font-semibold">

                            Valor mínimo para revisión

                        </label>

                        <x-input type="number" step="0.01" min="0" name="valor_alerta_factura"
                            value="{{ old('valor_alerta_factura', $cashbackCampaign->valor_alerta_factura ?? '') }}" />

                    </div>

                </div>

            </div>

            <hr>

            <div id="ranking-cashback-config" class="hidden">

                <div class="flex items-center gap-3">

                    <input id="enable-ranking" type="checkbox" name="ranking_enabled" value="1"
                        class="h-5 w-5 rounded border-gray-300 text-green-600" @checked(old('ranking_enabled', $cashbackCampaign->ranking_enabled ?? false))>
                    <label for="enable-ranking" class="font-semibold text-lg">

                        Activar Ranking Cashback

                    </label>

                </div>

                <p class="mt-2 text-sm text-gray-500">

                    Al finalizar la campaña se escogerá automáticamente
                    el primer lugar de cada almacén, zona o sucursal
                    participante.

                </p>

                <div id="ranking-options"
                    class="mt-8 {{ old('ranking_enabled', $cashbackCampaign->ranking_enabled ?? false) ? '' : 'hidden' }} space-y-6">
                    <div>

                        <label class="mb-2 block font-semibold">

                            Tipo de Ranking

                        </label>

                        <select name="ranking_type" class="w-full rounded-xl border border-gray-300 px-4 py-3">

                            <option value="cashback" @selected(old('ranking_type', $cashbackCampaign->ranking_type ?? 'cashback') == 'cashback')>

                                Mayor Cashback generado

                            </option>

                            <option value="sales" @selected(old('ranking_type', $cashbackCampaign->ranking_type ?? '') == 'sales')>

                                Mayor valor vendido

                            </option>

                        </select>

                    </div>

                    <div>

                        <label class="mb-2 block font-semibold">

                            Multiplicador del ganador

                        </label>

                        <select name="multiplicador" class="w-full rounded-xl border border-gray-300 px-4 py-3">

                            @for ($i = 2; $i <= 5; $i++)
                                <option value="{{ $i }}" @selected(old('multiplicador', $rankingReward->multiplicador ?? 2) == $i)>

                                    {{ $i }}x Cashback

                                </option>
                            @endfor

                        </select>

                    </div>

                    <div>

                        <label class="mb-2 block font-semibold">

                            Nombre del premio

                        </label>

                        <x-input type="text" name="reward_title"
                            value="{{ old('reward_title', $rankingReward->titulo ?? '') }}"
                            placeholder="Ej: Primer Lugar" />
                    </div>

                    <div>

                        <label class="mb-2 block font-semibold">

                            Descripción

                        </label>

                        <textarea rows="4" name="reward_description" class="w-full rounded-xl border border-gray-300 px-4 py-3">{{ old('reward_description', $rankingReward->descripcion ?? '') }}</textarea>
                    </div>

                    <div>

                        <label class="mb-2 block font-semibold">

                            Valor referencial

                        </label>

                        <x-input type="number" step="0.01" min="0" name="reward_value"
                            value="{{ old('reward_value', $rankingReward->valor_referencial ?? '') }}" />
                    </div>

                </div>

            </div>

            <hr>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

                <div>

                    <label class="mb-2 block font-semibold">

                        Fecha inicio

                        <span class="text-red-600">*</span>

                    </label>

                    <x-input type="date" name="fecha_inicio"
                        value="{{ old('fecha_inicio', isset($cashbackCampaign) ? optional($cashbackCampaign->fecha_inicio)->format('Y-m-d') : '') }}"
                        required />

                </div>

                <div>

                    <label class="mb-2 block font-semibold">

                        Fecha fin

                        <span class="text-red-600">*</span>

                    </label>

                    <x-input type="date" name="fecha_fin"
                        value="{{ old('fecha_fin', isset($cashbackCampaign) ? optional($cashbackCampaign->fecha_fin)->format('Y-m-d') : '') }}"
                        required />

                </div>

            </div>

            <div>

                <label class="mb-2 block font-semibold">

                    Estado

                </label>

                <select name="activo" class="w-full rounded-xl border border-gray-300 px-4 py-3">

                    <option value="1" @selected(old('activo', $cashbackCampaign->activo ?? true))>

                        Activa

                    </option>

                    <option value="0" @selected(old('activo', $cashbackCampaign->activo ?? true) == false)>

                        Inactiva

                    </option>

                </select>

            </div>

        </div>

    </div>

    <div class="flex justify-end gap-4">

        <a href="{{ route('cashback-campaigns.index') }}"
            class="rounded-xl border border-gray-300 px-6 py-3 hover:bg-gray-100">

            Cancelar

        </a>

        <button type="submit" class="rounded-xl bg-green-600 px-6 py-3 font-semibold text-white hover:bg-green-700">

            {{ isset($cashbackCampaign) ? 'Actualizar campaña' : 'Guardar campaña' }}

        </button>

    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {

        const participantTypes =
            document.querySelectorAll('.participant-type');

        const warehouseSection =
            document.getElementById('warehouse-section');

        const zoneSection =
            document.getElementById('zone-section');

        const branchSection =
            document.getElementById('branch-section');

        const campaignType =
            document.getElementById('campaign_type');

        const cashbackConfig =
            document.getElementById('cashback-config');

        const rankingCashback =
            document.getElementById('ranking-cashback-config');

        const enableRanking =
            document.getElementById('enable-ranking');

        const rankingOptions =
            document.getElementById('ranking-options');

        function refreshParticipants() {

            warehouseSection.classList.add('hidden');
            zoneSection.classList.add('hidden');
            branchSection.classList.add('hidden');

            const selected =
                document.querySelector('.participant-type:checked');

            if (!selected) {
                return;
            }

            switch (selected.value) {

                case 'warehouse':
                    warehouseSection.classList.remove('hidden');
                    break;

                case 'zone':
                    zoneSection.classList.remove('hidden');
                    break;

                case 'branch':
                    branchSection.classList.remove('hidden');
                    break;

            }

        }

        function refreshCampaign() {

            if (campaignType.value === 'cashback') {

                cashbackConfig.classList.remove('hidden');
                rankingCashback.classList.remove('hidden');

            } else {

                cashbackConfig.classList.add('hidden');
                rankingCashback.classList.add('hidden');

            }

        }

        function refreshRanking() {

            if (enableRanking.checked) {

                rankingOptions.classList.remove('hidden');

            } else {

                rankingOptions.classList.add('hidden');

            }

        }

        participantTypes.forEach(item => {

            item.addEventListener(
                'change',
                refreshParticipants
            );

        });

        campaignType.addEventListener(
            'change',
            refreshCampaign
        );

        enableRanking.addEventListener(
            'change',
            refreshRanking
        );

        refreshParticipants();
        refreshCampaign();
        refreshRanking();

    });
</script>
