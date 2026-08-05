@extends('layouts.app')

@section('title', 'Nueva Campaña Cashback')

@section('content')

    @if ($errors->any())
        <div class="mb-6 rounded-lg bg-red-100 p-4 text-red-700">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <x-card>

        <h1 class="mb-6 text-3xl font-bold">

            Nueva Campaña Cashback

        </h1>

        <form action="{{ route('cashback-campaigns.store') }}" method="POST">

            @include('admin.incentivos.cashback_campaigns._form')

        </form>

    </x-card>

@endsection
