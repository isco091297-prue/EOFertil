@extends('layouts.app')

@section('title', 'Editar Premio de Ranking')

@section('content')

    <x-card>

        <h1 class="mb-6 text-3xl font-bold">

            Editar Premio de Ranking

        </h1>

        <form action="{{ route('ranking-rewards.update', $rankingReward) }}" method="POST">

            @csrf
            @method('PUT')

            @include('admin.incentivos.ranking_rewards._form')

        </form>

    </x-card>

@endsection
