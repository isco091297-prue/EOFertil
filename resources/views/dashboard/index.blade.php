@extends('layouts.app')

@section('content')

<h1 class="text-3xl font-bold">

Dashboard

</h1>

<div class="grid grid-cols-4 gap-6 mt-8">

    <x-card>

        <h2 class="text-gray-500">

            Usuarios

        </h2>

        <p class="text-5xl font-bold text-green-700 mt-4">

            {{ \App\Models\User::count() }}

        </p>

    </x-card>

</div>

@endsection
