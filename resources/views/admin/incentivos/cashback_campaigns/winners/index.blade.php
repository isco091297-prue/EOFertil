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

                        Valor Referencial

                    </th>

                    <th class="px-6 py-4 text-right text-sm font-semibold text-gray-700">

                        Ventas

                    </th>

                    <th class="px-6 py-4 text-right text-sm font-semibold text-gray-700">

                        Cashback

                    </th>

                    <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">

                        Facturas

                    </th>

                </tr>

            </thead>

            <tbody class="divide-y divide-gray-100 bg-white">

                @forelse($winners as $winner)
                    <tr class="hover:bg-gray-50">

                        <td class="px-6 py-4">

                            @switch($winner->reward->posicion)
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
                                    {{ $winner->reward->posicion }}
                            @endswitch

                        </td>

                        <td class="px-6 py-4">

                            <div class="font-semibold text-gray-800">

                                {{ $winner->user->first_name }}

                                {{ $winner->user->last_name }}

                            </div>

                            <div class="text-sm text-gray-500">

                                {{ $winner->branch?->name }}

                            </div>

                        </td>

                        <td class="px-6 py-4">

                            <div class="font-semibold">

                                {{ $winner->reward->titulo }}

                            </div>

                            @if ($winner->reward->descripcion)
                                <div class="mt-1 text-sm text-gray-500">

                                    {{ $winner->reward->descripcion }}

                                </div>
                            @endif

                        </td>

                        <td class="px-6 py-4 text-right">

                            @if ($winner->reward->valor_referencial)
                                ${{ number_format($winner->reward->valor_referencial, 2) }}
                            @else
                                -
                            @endif

                        </td>

                        <td class="px-6 py-4 text-right">

                            ${{ number_format($winner->sales_total, 2) }}

                        </td>

                        <td class="px-6 py-4 text-right font-bold text-green-600">

                            ${{ number_format($winner->cashback_total, 2) }}

                        </td>

                        <td class="px-6 py-4 text-center">

                            {{ $winner->invoice_count }}

                        </td>

                    </tr>

                    @empty

                        <tr>

                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">

                                Todavía no existen ganadores para esta campaña.

                            </td>

                        </tr>
                    @endforelse

                </tbody>

            </table>

        </div>

    @endsection
$invoice->total_productos_participantes
