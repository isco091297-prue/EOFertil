@extends('layouts.app')

@section('title', 'Premios de Ranking')

@section('content')

    <x-card>

        <div class="mb-6 flex items-center justify-between">

            <div>

                <h1 class="text-3xl font-bold">
                    Premios de Ranking
                </h1>

                <p class="mt-1 text-gray-500">
                    Campaña:
                    <strong>{{ $cashbackCampaign->nombre }}</strong>
                </p>

            </div>

            <a href="{{ route('ranking-rewards.create', $cashbackCampaign) }}"
                class="rounded-lg bg-green-600 px-5 py-3 font-semibold text-white hover:bg-green-700">

                + Nuevo Premio

            </a>

        </div>

        @if (session('success'))
            <x-alert type="success">

                {{ session('success') }}

            </x-alert>
        @endif

        <form method="GET" class="mb-6">

            <div class="flex gap-3">

                <input type="text" name="search" value="{{ $search }}" placeholder="Buscar premio..."
                    class="w-full rounded-lg border border-gray-300 px-4 py-3">

                <button class="rounded-lg bg-gray-800 px-6 py-3 font-semibold text-white hover:bg-black">

                    Buscar

                </button>

                @if ($search)
                    <a href="{{ route('ranking-rewards.index', $cashbackCampaign) }}"
                        class="rounded-lg border border-gray-300 px-6 py-3 hover:bg-gray-100">

                        Limpiar

                    </a>
                @endif

            </div>

        </form>

        <div class="overflow-x-auto">

            <table class="min-w-full divide-y divide-gray-200">

                <thead class="bg-gray-100">

                    <tr>

                        <th class="px-4 py-3 text-center">
                            Posición
                        </th>

                        <th class="px-4 py-3">
                            Premio
                        </th>

                        <th class="px-4 py-3">
                            Tipo
                        </th>

                        <th class="px-4 py-3 text-center">
                            Valor
                        </th>

                        <th class="px-4 py-3 text-center">
                            Estado
                        </th>

                        <th class="px-4 py-3 text-center">
                            Acciones
                        </th>

                    </tr>

                </thead>

                <tbody class="divide-y divide-gray-200 bg-white">

                    @forelse($rankingRewards as $reward)
                        <tr>

                            <td class="px-4 py-4 text-center font-bold">

                                @switch($reward->posicion)
                                    @case(1)
                                        🥇
                                    @break

                                    @case(2)
                                        🥈
                                    @break

                                    @case(3)
                                        🥉
                                    @break

                                    @default
                                        {{ $reward->posicion }}
                                @endswitch

                            </td>

                            <td class="px-4 py-4">

                                <div class="font-semibold">
                                    {{ $reward->titulo }}
                                </div>

                                @if ($reward->descripcion)
                                    <div class="text-sm text-gray-500">
                                        {{ $reward->descripcion }}
                                    </div>
                                @endif

                            </td>

                            <td class="px-4 py-4">

                                {{ $reward->rewardType->nombre }}

                            </td>

                            <td class="px-4 py-4 text-center">

                                @if ($reward->rewardType->codigo === 'cashback_multiplier')
                                    {{ number_format($reward->multiplicador, 2) }}x
                                @elseif (!is_null($reward->valor_referencial))
                                    $ {{ number_format($reward->valor_referencial, 2) }}
                                @else
                                    —
                                @endif

                            </td>

                            <td class="px-4 py-4 text-center">

                                @if ($reward->activo)
                                    <span class="rounded-full bg-green-100 px-3 py-1 text-sm font-semibold text-green-700">
                                        Activo
                                    </span>
                                @else
                                    <span class="rounded-full bg-red-100 px-3 py-1 text-sm font-semibold text-red-700">
                                        Inactivo
                                    </span>
                                @endif

                            </td>

                            <td class="px-4 py-4">

                                <div class="flex justify-center gap-2">

                                    <a href="{{ route('ranking-rewards.edit', [$cashbackCampaign, $reward]) }}"
                                        class="rounded-lg bg-blue-600 px-3 py-2 text-white hover:bg-blue-700">

                                        Editar

                                    </a>

                                    <form action="{{ route('ranking-rewards.destroy', [$cashbackCampaign, $reward]) }}"
                                        method="POST" onsubmit="return confirm('¿Desea eliminar este premio?')">

                                        @csrf

                                        @method('DELETE')

                                        <button class="rounded-lg bg-red-600 px-3 py-2 text-white hover:bg-red-700">

                                            Eliminar

                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                        @empty

                            <tr>

                                <td colspan="6" class="py-10 text-center text-gray-500">

                                    No existen premios registrados.

                                </td>

                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>

            <div class="mt-6">

                {{ $rankingRewards->links() }}

            </div>

        </x-card>

    @endsection
