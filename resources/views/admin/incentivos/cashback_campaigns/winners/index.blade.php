@extends('layouts.app')

@section('title', 'Ganadores de la Campaña')

@section('content')

    <x-card>

        <div class="flex items-center justify-between">

            <div>

                <h1 class="text-3xl font-bold text-gray-800">
                    Ganadores
                </h1>

                <p class="mt-2 text-gray-500">
                    {{ $cashbackCampaign->nombre }}
                </p>

            </div>

            <a href="{{ route('cashback-campaigns.index') }}"
                class="rounded-xl bg-gray-100 px-5 py-3 font-semibold hover:bg-gray-200">

                Volver

            </a>

        </div>

    </x-card>


    <div class="mt-6 overflow-hidden rounded-xl bg-white shadow">

        <div class="overflow-x-auto">

            <table class="min-w-full">

                <thead class="bg-gray-50">

                    <tr>

                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">
                            Posición
                        </th>

                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">
                            Participante
                        </th>

                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">
                            Premio
                        </th>

                        <th class="px-6 py-4 text-right text-sm font-semibold text-gray-700">
                            Valor
                        </th>

                        <th class="px-6 py-4 text-right text-sm font-semibold text-gray-700">
                            Ventas
                        </th>

                        <th class="px-6 py-4 text-right text-sm font-semibold text-gray-700">
                            Cashback base
                        </th>

                        <th class="px-6 py-4 text-right text-sm font-semibold text-gray-700">
                            Bonificación
                        </th>

                        <th class="px-6 py-4 text-right text-sm font-semibold text-gray-700">
                            Total cashback
                        </th>

                        <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">
                            Entrega
                        </th>

                    </tr>

                </thead>


                <tbody class="divide-y divide-gray-100 bg-white">

                    @forelse($winners as $winner)

                        <tr class="hover:bg-gray-50">

                            {{-- POSICIÓN --}}
                            <td class="px-6 py-4">

                                <div class="text-lg font-bold">

                                    @switch($winner->ranking_position)
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
                                            {{ $winner->ranking_position }}
                                    @endswitch

                                </div>

                            </td>


                            {{-- PARTICIPANTE --}}
                            <td class="px-6 py-4">

                                <div class="font-semibold text-gray-800">

                                    {{ $winner->user?->first_name }}
                                    {{ $winner->user?->last_name }}

                                </div>

                                <div class="text-sm text-gray-500">

                                    {{ $winner->branch?->name ?? 'Sin sucursal' }}

                                </div>

                            </td>


                            {{-- PREMIO --}}
                            <td class="px-6 py-4">

                                <div class="font-semibold text-gray-800">

                                    {{ $winner->reward_title ?? 'Premio' }}

                                </div>

                            </td>


                            {{-- VALOR DEL PREMIO --}}
                            <td class="px-6 py-4 text-right">

                                @if (!is_null($winner->reward_value))
                                    ${{ number_format((float) $winner->reward_value, 2) }}
                                @elseif (!is_null($winner->reward_multiplier))
                                    {{ number_format((float) $winner->reward_multiplier, 2) }}x
                                @else
                                    -
                                @endif

                            </td>


                            {{-- VENTAS --}}
                            <td class="px-6 py-4 text-right">

                                ${{ number_format((float) $winner->sales_total, 2) }}

                            </td>


                            {{-- CASHBACK BASE --}}
                            <td class="px-6 py-4 text-right">

                                ${{ number_format((float) $winner->cashback_total, 2) }}

                            </td>


                            {{-- BONIFICACIÓN --}}
                            <td class="px-6 py-4 text-right">

                                @php
                                    $cashbackBase = (float) $winner->cashback_total;

                                    $multiplicador = (float) ($winner->reward_multiplier ?? 1);

                                    $bonificacion = $multiplicador > 1 ? $cashbackBase * ($multiplicador - 1) : 0;

                                    $totalCashback = $cashbackBase + $bonificacion;
                                @endphp

                                @if ($bonificacion > 0)
                                    <span class="font-semibold text-green-600">
                                        ${{ number_format($bonificacion, 2) }}
                                    </span>
                                @else
                                    $0.00
                                @endif

                            </td>


                            {{-- TOTAL CASHBACK --}}
                            <td class="px-6 py-4 text-right font-bold text-green-600">

                                ${{ number_format($totalCashback, 2) }}

                            </td>


                            {{-- ENTREGA --}}
                            <td class="px-6 py-4 text-center">

                                @if ($winner->reward_delivered)
                                    <div class="flex flex-col items-center gap-1">

                                        <span
                                            class="rounded-full bg-green-100 px-3 py-1 text-sm font-semibold text-green-700">

                                            Entregado

                                        </span>

                                        @if ($winner->reward_delivered_at)
                                            <span class="text-xs text-gray-500">

                                                {{ $winner->reward_delivered_at->format('d/m/Y H:i') }}

                                            </span>
                                        @endif

                                    </div>
                                @else
                                    <form method="POST"
                                        action="{{ route('cashback-campaigns.winners.deliver', [$cashbackCampaign, $winner]) }}"
                                        onsubmit="return confirm('¿Confirmar que este premio fue entregado?')">

                                        @csrf

                                        @method('PATCH')

                                        <button type="submit"
                                            class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700">

                                            Marcar entregado

                                        </button>

                                    </form>
                                @endif

                            </td>

                        </tr>

                        @empty

                            <tr>

                                <td colspan="9" class="px-6 py-12 text-center text-gray-500">

                                    Todavía no existen ganadores para esta campaña.

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    @endsection
