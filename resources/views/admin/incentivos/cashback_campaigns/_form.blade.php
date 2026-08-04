@csrf

<div class="space-y-8">

    {{-- ====================================================== --}}
    {{-- INFORMACIÓN GENERAL --}}
    {{-- ====================================================== --}}

    <div class="rounded-xl bg-white shadow">

        <div class="border-b border-gray-200 px-6 py-4">

            <h2 class="text-lg font-semibold text-gray-800">
                Información General
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Configure la campaña de incentivos.
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

                    <option value="ranking_cashback" @selected(old('campaign_type', $cashbackCampaign->campaign_type ?? '') == 'ranking_cashback')>

                        Ranking Cashback

                    </option>

                    <option value="ranking_accumulated" @selected(old('campaign_type', $cashbackCampaign->campaign_type ?? '') == 'ranking_accumulated')>

                        Ranking Acumulado

                    </option>

                </select>

            </div>

        </div>

        <div class="px-6 pb-6">

            <label class="mb-2 block font-semibold">

                Descripción

            </label>

            <textarea name="descripcion" rows="4" class="w-full rounded-xl border border-gray-300 px-4 py-3">{{ old('descripcion', $cashbackCampaign->descripcion ?? '') }}</textarea>

        </div>

    </div>

    {{-- ====================================================== --}}
    {{-- PARTICIPANTES --}}
    {{-- ====================================================== --}}

    <div class="rounded-xl bg-white shadow">

        <div class="border-b border-gray-200 px-6 py-4">

            <h2 class="text-lg font-semibold">
                Participantes
            </h2>

            <p class="mt-1 text-sm text-gray-500">

                Seleccione quiénes participarán en esta campaña.

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
            {{-- ====================================================== --}}
            {{-- ALMACENES --}}
            {{-- ====================================================== --}}

            <div id="warehouse-section" class="mt-8 hidden">

                <h3 class="mb-4 text-base font-semibold text-gray-800">
                    Almacenes participantes
                </h3>

                <div class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-3">

                    @foreach ($warehouses as $warehouse)
                        <label class="flex items-center gap-3 rounded-lg border border-gray-200 p-3 hover:bg-gray-50">

                            <input type="checkbox" name="warehouse_ids[]" value="{{ $warehouse->id }}"
                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">

                            <span>
                                {{ $warehouse->name }}
                            </span>

                        </label>
                    @endforeach

                </div>

            </div>

            {{-- ====================================================== --}}
            {{-- ZONAS --}}
            {{-- ====================================================== --}}

            <div id="zone-section" class="mt-8 hidden">

                <h3 class="mb-4 text-base font-semibold text-gray-800">
                    Zonas participantes
                </h3>

                <div class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-3">

                    @foreach ($zones as $zone)
                        <label class="flex items-center gap-3 rounded-lg border border-gray-200 p-3 hover:bg-gray-50">

                            <input type="checkbox" name="zone_ids[]" value="{{ $zone->id }}"
                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">

                            <span>

                                {{ $zone->name }}

                            </span>

                        </label>
                    @endforeach

                </div>

            </div>

            {{-- ====================================================== --}}
            {{-- SUCURSALES --}}
            {{-- ====================================================== --}}

            <div id="branch-section" class="mt-8 hidden">

                <h3 class="mb-4 text-base font-semibold text-gray-800">
                    Sucursales participantes
                </h3>

                <div class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-3">

                    @foreach ($branches as $branch)
                        <label class="flex items-center gap-3 rounded-lg border border-gray-200 p-3 hover:bg-gray-50">

                            <input type="checkbox" name="branch_ids[]" value="{{ $branch->id }}"
                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">

                            <span>

                                {{ $branch->name }}

                            </span>

                        </label>
                    @endforeach

                </div>

            </div>

        </div>

    </div>
    {{-- ====================================================== --}}
    {{-- CONFIGURACIÓN DE LA CAMPAÑA --}}
    {{-- ====================================================== --}}

    <div class="rounded-xl bg-white shadow">

        <div class="border-b border-gray-200 px-6 py-4">

            <h2 class="text-lg font-semibold text-gray-800">
                Configuración
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Los campos cambian automáticamente según el tipo de campaña.
            </p>

        </div>

        <div class="space-y-6 p-6">

            {{-- =============================================== --}}
            {{-- CASHBACK --}}
            {{-- =============================================== --}}

            <div id="cashback-config" class="space-y-6">

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

                            Valor mínimo factura

                        </label>

                        <x-input type="number" step="0.01" min="0" name="valor_alerta_factura"
                            value="{{ old('valor_alerta_factura', $cashbackCampaign->valor_alerta_factura ?? '') }}" />

                    </div>

                </div>

            </div>

            {{-- =============================================== --}}
            {{-- RANKING CASHBACK --}}
            {{-- =============================================== --}}

            <div id="ranking-cashback-config" class="hidden">

                <label class="mb-2 block font-semibold">

                    Multiplicador para el ganador

                </label>

                <select name="multiplicador" class="w-full rounded-xl border border-gray-300 px-4 py-3">

                    <option value="2">
                        2x Cashback
                    </option>

                    <option value="3">
                        3x Cashback
                    </option>

                    <option value="4">
                        4x Cashback
                    </option>

                    <option value="5">
                        5x Cashback
                    </option>

                </select>

                <p class="mt-3 text-sm text-gray-500">

                    El primer lugar de cada almacén participante recibirá este
                    multiplicador sobre el cashback generado durante la campaña.

                </p>

            </div>

            {{-- =============================================== --}}
            {{-- RANKING ACUMULADO --}}
            {{-- =============================================== --}}

            <div id="ranking-accumulated-config" class="hidden space-y-6">

                <div>

                    <label class="mb-2 block font-semibold">

                        Premio

                    </label>

                    <x-input type="text" name="reward_title" placeholder="Ej: iPhone 17" />

                </div>

                <div>

                    <label class="mb-2 block font-semibold">

                        Valor referencial

                    </label>

                    <x-input type="number" step="0.01" min="0" name="reward_value" />

                </div>

                <div>

                    <label class="mb-2 block font-semibold">

                        Descripción del premio

                    </label>

                    <textarea name="reward_description" rows="4" class="w-full rounded-xl border border-gray-300 px-4 py-3"></textarea>

                </div>

            </div>

        </div>

    </div>
