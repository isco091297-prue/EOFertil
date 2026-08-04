@extends('layouts.app')

@section('title', 'Nuevo Premio')

@section('content')

    <x-card>

        <div class="mb-8">

            <h1 class="text-3xl font-bold">

                Nuevo Premio

            </h1>

            <p class="mt-2 text-gray-500">

                Cree un premio para una posición del ranking.

            </p>

        </div>

        <form method="POST" action="{{ route('ranking-rewards.store', $cashbackCampaign) }}">

            @include('admin.incentivos.ranking_rewards._form')

        </form>

    </x-card>

@endsection
