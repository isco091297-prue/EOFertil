@extends('layouts.app')

@section('title', 'Ranking de la Campaña')

@section('content')

    <x-card>

        <div class="flex items-center justify-between">

            <div>

                <h1 class="text-3xl font-bold text-gray-800">

                    Ranking

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

                        Almacén

                    </th>

                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">

                        Zona

                    </th>

                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">

                        Sucursal

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

                @forelse($ranking as $index => $participant)
                    <tr class="hover:bg-gray-50">

                        <td class="px-6 py-4">

                            @if ($index == 0)
                                🥇
                            @elseif($index == 1)
                                🥈
                            @elseif($index == 2)
                                🥉
                            @else
                                {{ $index + 1 }}
                            @endif

                        </td>

                        <td class="px-6 py-4">

                            <div class="font-semibold text-gray-800">

                                {{ $participant->user->first_name }}

                                {{ $participant->user->last_name }}

                            </div>

                        </td>

                        <td class="px-6 py-4">

                            {{ $participant->warehouse?->name ?? '-' }}

                        </td>

                        <td class="px-6 py-4">

                            {{ $participant->zone?->name ?? '-' }}

                        </td>

                        <td class="px-6 py-4">

                            {{ $participant->branch?->name ?? '-' }}

                        </td>

                        <td class="px-6 py-4 text-right font-medium">

                            ${{ number_format($participant->sales_total, 2) }}

                        </td>

                        <td class="px-6 py-4 text-right font-bold text-green-600">

                            ${{ number_format($participant->cashback_total, 2) }}

                        </td>

                        <td class="px-6 py-4 text-center">

                            {{ $participant->invoice_count }}

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">

                            No existen participantes para esta campaña.

                        </td>

                    </tr>
                @endforelse

            </tbody>

        </table>

    </div>

@endsection
