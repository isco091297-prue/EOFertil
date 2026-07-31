@extends('layouts.app')

@section('title', 'Nuevo Premio de Ranking')

@section('content')

    <x-card>

        <h1 class="mb-6 text-3xl font-bold">

            Nuevo Premio de Ranking

        </h1>

        <form action="{{ route('ranking-rewards.store') }}" method="POST">

            @include('admin.incentivos.ranking_rewards._form')

        </form>

    </x-card>

@endsection
