@extends('layouts.app')

@section('title', 'Editar Campaña Cashback')

@section('content')

    <x-card>

        <h1 class="mb-6 text-3xl font-bold">

            Editar Campaña Cashback

        </h1>

        <form action="{{ route('cashback-campaigns.update', $cashbackCampaign) }}" method="POST">

            @csrf
            @method('PUT')

            @include('admin.incentivos.cashback_campaigns._form')

        </form>

    </x-card>

@endsection
