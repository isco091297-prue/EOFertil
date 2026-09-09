@extends('layouts.app')

@section('content')
    <div class="p-6">

        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">
                📊 Uso de la Guía
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Consulta la utilización de la Guía Técnica por usuario,
                sucursal, cultivo y problema.
            </p>
        </div>


        {{-- ========================================================= --}}
        {{-- FILTROS --}}
        {{-- ========================================================= --}}

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-6">

            <form method="GET" action="{{ route('guide-usage.index') }}">

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

                    {{-- FECHA DESDE --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Desde
                        </label>

                        <input type="date" name="date_from" value="{{ $dateFrom }}"
                            class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500">
                    </div>


                    {{-- FECHA HASTA --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Hasta
                        </label>

                        <input type="date" name="date_to" value="{{ $dateTo }}"
                            class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500">
                    </div>


                    {{-- SUCURSAL --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Sucursal
                        </label>

                        <select name="branch_id"
                            class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500">
                            <option value="">
                                Todas las sucursales
                            </option>

                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" @selected($branchId == $branch->id)>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>


                    {{-- USUARIO --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Usuario
                        </label>

                        <select name="user_id"
                            class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500">
                            <option value="">
                                Todos los usuarios
                            </option>

                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" @selected($userId == $user->id)>
                                    {{ $user->first_name }}
                                    {{ $user->last_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>


                    {{-- CULTIVO --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Cultivo
                        </label>

                        <select name="crop_id"
                            class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500">
                            <option value="">
                                Todos los cultivos
                            </option>

                            @foreach ($crops as $crop)
                                <option value="{{ $crop->id }}" @selected($cropId == $crop->id)>
                                    {{ $crop->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>


                    {{-- PROBLEMA --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Problema
                        </label>

                        <select name="problem_id"
                            class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500">
                            <option value="">
                                Todos los problemas
                            </option>

                            @foreach ($problems as $problem)
                                <option value="{{ $problem->id }}" @selected($problemId == $problem->id)>
                                    {{ $problem->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                </div>


                {{-- BOTONES --}}

                <div class="mt-5 flex flex-wrap gap-3">

                    <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-green-700 text-white font-semibold hover:bg-green-800">
                        🔍 Consultar reporte
                    </button>

                    <a href="{{ route('guide-usage.index') }}"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 font-semibold hover:bg-gray-50">
                        Limpiar filtros
                    </a>

                </div>

            </form>

        </div>


        {{-- ========================================================= --}}
        {{-- RESUMEN --}}
        {{-- ========================================================= --}}

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">

            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
                <p class="text-sm text-gray-500">
                    Consultas realizadas
                </p>

                <p class="mt-1 text-3xl font-bold text-green-700">
                    {{ number_format($totalConsultas) }}
                </p>
            </div>


            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
                <p class="text-sm text-gray-500">
                    Usuarios activos
                </p>

                <p class="mt-1 text-3xl font-bold text-gray-800">
                    {{ number_format($usuariosActivos) }}
                </p>
            </div>

        </div>


        {{-- ========================================================= --}}
        {{-- RANKING DE USUARIOS --}}
        {{-- ========================================================= --}}

        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm mb-6">

            <div class="p-5 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-800">
                    🏆 Ranking de usuarios
                </h2>
            </div>

            <div class="overflow-x-auto">

                <table class="min-w-full">

                    <thead class="bg-gray-50">

                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-bold uppercase text-gray-500">
                                #
                            </th>

                            <th class="px-5 py-3 text-left text-xs font-bold uppercase text-gray-500">
                                Usuario
                            </th>

                            <th class="px-5 py-3 text-left text-xs font-bold uppercase text-gray-500">
                                Sucursal
                            </th>

                            <th class="px-5 py-3 text-right text-xs font-bold uppercase text-gray-500">
                                Consultas
                            </th>
                        </tr>

                    </thead>

                    <tbody class="divide-y divide-gray-100">

                        @forelse($rankingUsuarios as $index => $item)
                            <tr class="hover:bg-gray-50">

                                <td class="px-5 py-3 font-bold text-gray-700">
                                    @if ($index === 0)
                                        🥇
                                    @elseif($index === 1)
                                        🥈
                                    @elseif($index === 2)
                                        🥉
                                    @else
                                        {{ $index + 1 }}
                                    @endif
                                </td>

                                <td class="px-5 py-3 font-semibold text-gray-800">
                                    {{ $item->user->first_name }}
                                    {{ $item->user->last_name }}
                                </td>

                                <td class="px-5 py-3 text-gray-600">
                                    {{ $item->user->branch->name ?? 'Sin sucursal' }}
                                </td>

                                <td class="px-5 py-3 text-right font-bold text-green-700">
                                    {{ number_format($item->total) }}
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="4" class="px-5 py-8 text-center text-gray-500">
                                    No existen consultas para los filtros seleccionados.
                                </td>
                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>


        {{-- ========================================================= --}}
        {{-- RESUMEN POR SUCURSAL --}}
        {{-- ========================================================= --}}

        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm mb-6">

            <div class="p-5 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-800">
                    🏢 Consultas por sucursal
                </h2>
            </div>

            <div class="overflow-x-auto">

                <table class="min-w-full">

                    <thead class="bg-gray-50">

                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-bold uppercase text-gray-500">
                                Sucursal
                            </th>

                            <th class="px-5 py-3 text-right text-xs font-bold uppercase text-gray-500">
                                Consultas
                            </th>
                        </tr>

                    </thead>

                    <tbody class="divide-y divide-gray-100">

                        @forelse($rankingSucursales as $item)
                            <tr class="hover:bg-gray-50">

                                <td class="px-5 py-3 font-semibold text-gray-800">
                                    {{ $item->name }}
                                </td>

                                <td class="px-5 py-3 text-right font-bold text-green-700">
                                    {{ number_format($item->total) }}
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="2" class="px-5 py-8 text-center text-gray-500">
                                    No existen datos.
                                </td>
                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>


        {{-- ========================================================= --}}
        {{-- CULTIVOS Y PROBLEMAS --}}
        {{-- ========================================================= --}}

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

            {{-- CULTIVOS --}}

            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm">

                <div class="p-5 border-b border-gray-200">
                    <h2 class="text-lg font-bold text-gray-800">
                        🌱 Cultivos más consultados
                    </h2>
                </div>

                <div class="p-5">

                    @forelse($rankingCultivos as $item)
                        <div class="flex items-center justify-between py-3 border-b last:border-b-0">

                            <span class="font-semibold text-gray-700">
                                {{ $item->name }}
                            </span>

                            <span class="font-bold text-green-700">
                                {{ number_format($item->total) }}
                            </span>

                        </div>

                    @empty

                        <p class="text-center text-gray-500 py-5">
                            No existen datos.
                        </p>
                    @endforelse

                </div>

            </div>


            {{-- PROBLEMAS --}}

            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm">

                <div class="p-5 border-b border-gray-200">
                    <h2 class="text-lg font-bold text-gray-800">
                        ⚠️ Problemas más consultados
                    </h2>
                </div>

                <div class="p-5">

                    @forelse($rankingProblemas as $item)
                        <div class="flex items-center justify-between py-3 border-b last:border-b-0">

                            <span class="font-semibold text-gray-700">
                                {{ $item->name }}
                            </span>

                            <span class="font-bold text-red-600">
                                {{ number_format($item->total) }}
                            </span>

                        </div>

                    @empty

                        <p class="text-center text-gray-500 py-5">
                            No existen datos.
                        </p>
                    @endforelse

                </div>

            </div>

        </div>


        {{-- ========================================================= --}}
        {{-- DETALLE DE CONSULTAS --}}
        {{-- ========================================================= --}}

        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm">

            <div class="p-5 border-b border-gray-200">

                <h2 class="text-lg font-bold text-gray-800">
                    📋 Detalle de consultas
                </h2>

                <p class="text-sm text-gray-500 mt-1">
                    Registro individual de las consultas realizadas.
                </p>

            </div>


            <div class="overflow-x-auto">

                <table class="min-w-full">

                    <thead class="bg-gray-50">

                        <tr>

                            <th class="px-5 py-3 text-left text-xs font-bold uppercase text-gray-500">
                                Fecha
                            </th>

                            <th class="px-5 py-3 text-left text-xs font-bold uppercase text-gray-500">
                                Usuario
                            </th>

                            <th class="px-5 py-3 text-left text-xs font-bold uppercase text-gray-500">
                                Sucursal
                            </th>

                            <th class="px-5 py-3 text-left text-xs font-bold uppercase text-gray-500">
                                Cultivo
                            </th>

                            <th class="px-5 py-3 text-left text-xs font-bold uppercase text-gray-500">
                                Problema
                            </th>

                        </tr>

                    </thead>


                    <tbody class="divide-y divide-gray-100">

                        @forelse($usages as $usage)
                            <tr class="hover:bg-gray-50">

                                <td class="px-5 py-3 text-sm text-gray-600">
                                    {{ $usage->created_at->format('d/m/Y H:i') }}
                                </td>

                                <td class="px-5 py-3 font-semibold text-gray-800">
                                    {{ $usage->user->first_name }}
                                    {{ $usage->user->last_name }}
                                </td>

                                <td class="px-5 py-3 text-sm text-gray-600">
                                    {{ $usage->user->branch->name ?? 'Sin sucursal' }}
                                </td>

                                <td class="px-5 py-3 font-semibold text-gray-700">
                                    {{ $usage->crop->name }}
                                </td>

                                <td class="px-5 py-3 text-gray-700">
                                    {{ $usage->problem->name }}
                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="5" class="px-5 py-10 text-center text-gray-500">

                                    No existen consultas para los filtros seleccionados.

                                </td>

                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>


            {{-- PAGINACIÓN --}}

            @if ($usages->hasPages())
                <div class="p-5 border-t border-gray-200">
                    {{ $usages->links() }}
                </div>
            @endif

        </div>

    </div>

@endsection
