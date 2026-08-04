@extends('layouts.app')

@section('content')
    <x-card>

        <h1 class="text-3xl font-bold">

            {{ $zone->name }}

        </h1>

        <hr class="my-6">

        <p>

            <strong>Código:</strong>

            {{ $zone->code }}

        </p>

        <p class="mt-4">

            <strong>Descripción:</strong>

            {{ $zone->description }}

        </p>

        <p class="mt-4">

            <strong>Estado:</strong>

            {{ $zone->is_active ? 'Activo' : 'Inactivo' }}

        </p>

    </x-card>
@endsection
