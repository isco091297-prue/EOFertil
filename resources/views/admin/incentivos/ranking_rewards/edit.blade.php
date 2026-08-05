@extends('layouts.app')

@section('title', 'Editar Premio')

@section('content')

    <x-card>

        <div class="mb-8">

            <h1 class="text-3xl font-bold">

                Editar Premio

            </h1>

            <p class="mt-2 text-gray-500">

                Campaña:
                <strong>{{ $cashbackCampaign->nombre }}</strong>

            </p>

        </div>

        <form method="POST"
            action="{{ route('cashback-campaigns.ranking-rewards.update', [$cashbackCampaign, $rankingReward]) }}">

            @csrf

            @method('PUT')

            @include('admin.incentivos.ranking_rewards._form')

        </form>

    </x-card>

@endsection
