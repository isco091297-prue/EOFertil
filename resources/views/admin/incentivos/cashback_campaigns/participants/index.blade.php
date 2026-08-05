@extends('layouts.app')

@section('title', 'Participantes')

@section('content')

<x-card>

    <div class="mb-8 flex items-center justify-between">

        <div>

            <h1 class="text-3xl font-bold">
                Participantes de la campaña
            </h1>

            <p class="mt-2 text-gray-500">
                {{ $cashbackCampaign->nombre }}
            </p>

        </div>

        <a
            href="{{ route('cashback-campaigns.index') }}"
            class="rounded-xl border border-gray-300 px-5 py-3 hover:bg-gray-100">

            Volver

        </a>

    </div>

    @if(session('success'))

        <x-alert type="success">

            {{ session('success') }}

        </x-alert>

    @endif

    <form
        method="POST"
        action="{{ route('cashback-campaigns.participants.store',$cashbackCampaign) }}">

        @csrf

        {{-- ===================================================== --}}
        {{-- TIPO DE PARTICIPANTES --}}
        {{-- ===================================================== --}}

        <div class="rounded-xl border p-6 mb-8">

            <h2 class="text-xl font-semibold mb-5">

                Tipo de participantes

            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

                <label class="flex items-center gap-3 rounded-lg border p-4 cursor-pointer">

                    <input
                        class="participant-type"
                        type="radio"
                        name="participant_type"
                        value="all"
                        {{ $cashbackCampaign->participant_type == 'all' ? 'checked' : '' }}>

                    <span>

                        Todos

                    </span>

                </label>

                <label class="flex items-center gap-3 rounded-lg border p-4 cursor-pointer">

                    <input
                        class="participant-type"
                        type="radio"
                        name="participant_type"
                        value="warehouse"
                        {{ $cashbackCampaign->participant_type == 'warehouse' ? 'checked' : '' }}>

                    <span>

                        Almacenes

                    </span>

                </label>

                <label class="flex items-center gap-3 rounded-lg border p-4 cursor-pointer">

                    <input
                        class="participant-type"
                        type="radio"
                        name="participant_type"
                        value="zone"
                        {{ $cashbackCampaign->participant_type == 'zone' ? 'checked' : '' }}>

                    <span>

                        Zonas

                    </span>

                </label>

                <label class="flex items-center gap-3 rounded-lg border p-4 cursor-pointer">

                    <input
                        class="participant-type"
                        type="radio"
                        name="participant_type"
                        value="branch"
                        {{ $cashbackCampaign->participant_type == 'branch' ? 'checked' : '' }}>

                    <span>

                        Sucursales

                    </span>

                </label>

            </div>

        </div>

        {{-- ===================================================== --}}
        {{-- ALMACENES --}}
        {{-- ===================================================== --}}

        <div
            id="warehouse-section"
            class="{{ $cashbackCampaign->participant_type == 'warehouse' ? '' : 'hidden' }} mb-8">

            <h2 class="mb-4 text-xl font-semibold">

                Almacenes

            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

                @foreach($warehouses as $warehouse)

                    <label class="flex items-center gap-3 rounded-xl border p-4 hover:bg-gray-50">

                        <input
                            type="checkbox"
                            name="warehouse_ids[]"
                            value="{{ $warehouse->id }}"
                            @checked(in_array($warehouse->id,$selectedWarehouses))

                        >

                        <span>

                            {{ $warehouse->name }}

                        </span>

                    </label>

                @endforeach

            </div>

        </div>

        {{-- ===================================================== --}}
        {{-- ZONAS --}}
        {{-- ===================================================== --}}

        <div
            id="zone-section"
            class="{{ $cashbackCampaign->participant_type == 'zone' ? '' : 'hidden' }} mb-8">

            <h2 class="mb-4 text-xl font-semibold">

                Zonas

            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

                @foreach($zones as $zone)

                    <label class="flex items-center gap-3 rounded-xl border p-4 hover:bg-gray-50">

                        <input
                            type="checkbox"
                            name="zone_ids[]"
                            value="{{ $zone->id }}"
                            @checked(in_array($zone->id,$selectedZones))

                        >

                        <span>

                            {{ $zone->name }}

                        </span>

                    </label>

                @endforeach

            </div>

        </div>

        {{-- ===================================================== --}}
        {{-- SUCURSALES --}}
        {{-- ===================================================== --}}

        <div
            id="branch-section"
            class="{{ $cashbackCampaign->participant_type == 'branch' ? '' : 'hidden' }} mb-8">

            <h2 class="mb-4 text-xl font-semibold">

                Sucursales

            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

                @foreach($branches as $branch)

                    <label class="flex items-center gap-3 rounded-xl border p-4 hover:bg-gray-50">

                        <input
                            type="checkbox"
                            name="branch_ids[]"
                            value="{{ $branch->id }}"
                            @checked(in_array($branch->id,$selectedBranches))

                        >

                        <span>

                            {{ $branch->name }}

                        </span>

                    </label>

                @endforeach

            </div>

        </div>

        <div class="flex justify-end gap-4">

            <a
                href="{{ route('cashback-campaigns.index') }}"
                class="rounded-xl border border-gray-300 px-6 py-3 hover:bg-gray-100">

                Cancelar

            </a>

            <x-button>

                Guardar participantes

            </x-button>

        </div>

    </form>

</x-card>

<script>

document.addEventListener('DOMContentLoaded', function () {

    const radios = document.querySelectorAll('.participant-type');

    const warehouse = document.getElementById('warehouse-section');
    const zone = document.getElementById('zone-section');
    const branch = document.getElementById('branch-section');

    function updateSections(){

        warehouse.classList.add('hidden');
        zone.classList.add('hidden');
        branch.classList.add('hidden');

        const selected = document.querySelector('.participant-type:checked');

        if(!selected) return;

        if(selected.value === 'warehouse'){
            warehouse.classList.remove('hidden');
        }

        if(selected.value === 'zone'){
            zone.classList.remove('hidden');
        }

        if(selected.value === 'branch'){
            branch.classList.remove('hidden');
        }

    }

    radios.forEach(radio=>{

        radio.addEventListener('change',updateSections);

    });

    updateSections();

});

</script>

@endsection
