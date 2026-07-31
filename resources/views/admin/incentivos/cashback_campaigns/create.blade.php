@extends('layouts.app')

@section('title', 'Nueva Campaña Cashback')

@section('content')

    <x-card>

        <h1 class="mb-6 text-3xl font-bold">

            Nueva Campaña Cashback

        </h1>

        <form action="{{ route('cashback-campaigns.store') }}" method="POST">

            @include('admin.incentivos.cashback_campaigns._form')

        </form>

    </x-card>

@endsection
