@extends('layouts.app')

@section('title', 'Participantes')

@section('content')

    <x-card>

        <div class="flex items-center justify-between mb-8">

            <div>

                <h1 class="text-3xl font-bold">

                    Participantes de la campaña

                </h1>

                <p class="text-gray-500 mt-2">

                    {{ $cashbackCampaign->nombre }}

                </p>

            </div>

            <a href="{{ route('cashback-campaigns.index') }}"
                class="rounded-xl border border-gray-300 px-5 py-3 hover:bg-gray-100">

                Volver

            </a>

        </div>

        @if (session('success'))
            <x-alert type="success">

                {{ session('success') }}

            </x-alert>
        @endif

        <form method="POST" action="{{ route('cashback-campaigns.participants.store', $cashbackCampaign) }}">

            @csrf

            <div class="space-y-10">

                <div>

                    <h2 class="text-xl font-semibold mb-4">

                        Almacenes

                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

                        @foreach ($warehouses as $warehouse)
                            <label class="flex items-center gap-3 rounded-xl border p-4 hover:bg-gray-50">

                                <input type="checkbox" name="warehouse_ids[]" value="{{ $warehouse->id }}"
                                    @checked(in_array($warehouse->id, $selectedWarehouses))>
                                <span>

                                    {{ $warehouse->name }}

                                </span>

                            </label>
                        @endforeach

                    </div>

                </div>

                <div>

                    <h2 class="text-xl font-semibold mb-4">

                        Zonas

                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

                        @foreach ($zones as $zone)
                            <label class="flex items-center gap-3 rounded-xl border p-4 hover:bg-gray-50">

                                <input type="checkbox" name="zone_ids[]" value="{{ $zone->id }}"
                                    @checked(in_array($zone->id, $selectedZones))>
                                <span>

                                    {{ $zone->name }}

                                </span>

                            </label>
                        @endforeach

                    </div>

                </div>

                <div>

                    <h2 class="text-xl font-semibold mb-4">

                        Sucursales

                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

                        @foreach ($branches as $branch)
                            <label class="flex items-center gap-3 rounded-xl border p-4 hover:bg-gray-50">

                                <input type="checkbox" name="branch_ids[]" value="{{ $branch->id }}"
                                    @checked(in_array($branch->id, $selectedBranches))>
                                <span>

                                    {{ $branch->name }}

                                </span>

                            </label>
                        @endforeach

                    </div>

                </div>

                <div class="flex justify-end gap-4">

                    <a href="{{ route('cashback-campaigns.index') }}"
                        class="rounded-xl border border-gray-300 px-6 py-3 hover:bg-gray-100">

                        Cancelar

                    </a>

                    <x-button>

                        Guardar participantes

                    </x-button>

                </div>

            </div>

        </form>

    </x-card>

@endsection
