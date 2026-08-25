@extends('layouts.app')

@section('title', 'Campañas Cashback')

@section('content')

    <div class="space-y-6">

        <div class="flex items-center justify-between">

            <div>

                <h1 class="text-3xl font-bold">
                    Campañas Cashback
                </h1>

                <p class="text-gray-500">
                    Administración de campañas de cashback.
                </p>

            </div>

            <a href="{{ route('cashback-campaigns.create') }}"
                class="rounded-xl bg-green-600 px-5 py-3 text-white hover:bg-green-700">

                + Nueva Campaña

            </a>

        </div>

        @if (session('success'))
            <x-alert type="success">

                {{ session('success') }}

            </x-alert>
        @endif

        <x-card>

            <form method="GET" action="{{ route('cashback-campaigns.index') }}" class="mb-6 flex gap-4">

                <x-input type="text" name="search" placeholder="Buscar campaña..." value="{{ $search }}" />

                <x-button>
                    Buscar
                </x-button>

                @if ($search)
                    <a href="{{ route('cashback-campaigns.index') }}"
                        class="rounded-lg border border-gray-300 px-4 py-2 hover:bg-gray-100">

                        Limpiar

                    </a>
                @endif

            </form>

            <div class="overflow-x-auto">

                <table class="min-w-full divide-y divide-gray-200">

                    <thead class="bg-gray-50">

                        <tr>

                            <th class="px-4 py-3 text-left">
                                Nombre
                            </th>

                            <th class="px-4 py-3 text-center">
                                Cashback %
                            </th>

                            <th class="px-4 py-3 text-center">
                                Factura mínima
                            </th>

                            <th class="px-4 py-3 text-center">
                                Vigencia
                            </th>

                            <th class="px-4 py-3 text-center">
                                Estado
                            </th>

                            <th class="px-4 py-3 text-center">
                                Acciones
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($campaigns as $campaign)
                            <tr class="border-b">

                                <td class="px-4 py-3">

                                    <div class="font-semibold">
                                        {{ $campaign->nombre }}
                                    </div>

                                    @if ($campaign->descripcion)
                                        <div class="text-sm text-gray-500">
                                            {{ $campaign->descripcion }}
                                        </div>
                                    @endif

                                </td>

                                <td class="px-4 py-3 text-center">

                                    {{ number_format($campaign->porcentaje, 2) }} %

                                </td>

                                <td class="px-4 py-3 text-center">

                                    $ {{ number_format($campaign->valor_alerta_factura, 2) }}

                                </td>

                                <td class="px-4 py-3 text-center">

                                    {{ $campaign->fecha_inicio->format('d/m/Y') }}

                                    <br>

                                    <span class="text-gray-400">
                                        hasta
                                    </span>

                                    <br>

                                    {{ $campaign->fecha_fin->format('d/m/Y') }}

                                </td>

                                <td class="px-4 py-3 text-center">

                                    @switch($campaign->estado)
                                        @case('inactiva')
                                            <span class="rounded-full bg-gray-100 px-3 py-1 text-sm font-medium text-gray-700">
                                                Inactiva
                                            </span>
                                        @break

                                        @case('proxima')
                                            <span class="rounded-full bg-yellow-100 px-3 py-1 text-sm font-medium text-yellow-700">
                                                Próxima
                                            </span>
                                        @break

                                        @case('finalizada')
                                            <span class="rounded-full bg-red-100 px-3 py-1 text-sm font-medium text-red-700">
                                                Finalizada
                                            </span>
                                        @break

                                        @default
                                            <span class="rounded-full bg-green-100 px-3 py-1 text-sm font-medium text-green-700">
                                                Vigente
                                            </span>
                                    @endswitch

                                </td>

                                <td>

                                    <div class="flex flex-wrap justify-center gap-2">

                                        <a href="{{ route('cashback-campaigns.edit', $campaign) }}"
                                            class="rounded-lg bg-yellow-500 px-3 py-2 text-sm font-medium text-white hover:bg-yellow-600">

                                            ✏️ Editar

                                        </a>

                                        <a href="{{ route('cashback-campaigns.participants', $campaign) }}"
                                            class="rounded-lg bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700">

                                            👥 Participantes

                                        </a>

                                        @if ($campaign->campaign_type === 'ranking_accumulated' || $campaign->ranking_enabled)
                                            <a href="{{ route('ranking-rewards.index', $campaign) }}"
                                                class="rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700">

                                                🎁 Premios

                                            </a>

                                            <a href="{{ route('cashback-campaigns.ranking', $campaign) }}"
                                                class="rounded-lg bg-green-600 px-3 py-2 text-sm font-medium text-white hover:bg-green-700">

                                                🏆 Ranking

                                            </a>

                                            <a href="{{ route('cashback-campaigns.winners', $campaign) }}"
                                                class="rounded-lg bg-purple-600 px-3 py-2 text-sm font-medium text-white hover:bg-purple-700">

                                                🥇 Ganadores

                                            </a>
                                        @endif

                                        <form action="{{ route('cashback-campaigns.destroy', $campaign) }}" method="POST">

                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" onclick="return confirm('¿Eliminar campaña?')"
                                                class="rounded-lg bg-red-600 px-3 py-2 text-sm font-medium text-white hover:bg-red-700">

                                                🗑 Eliminar

                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>

                            @empty

                                <tr>

                                    <td colspan="6" class="py-10 text-center text-gray-500">

                                        No existen campañas registradas.

                                    </td>

                                </tr>
                            @endforelse

                        </tbody>

                    </table>

                </div>

                <div class="mt-6">

                    {{ $campaigns->links() }}

                </div>

            </x-card>

        </div>

    @endsection
