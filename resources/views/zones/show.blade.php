@extends('layouts.app')

@section('content')

<x-card>

    <h1 class="text-3xl font-bold">

        {{ $zone->name }}

    </h1>

    <hr class="my-6">

    <p>

        <strong>Almacén:</strong>

        {{ $zone->warehouse->name }}

    </p>

    <p class="mt-4">

        <strong>Código:</strong>

        {{ $zone->code }}

    </p>

    <p class="mt-4">

        <strong>Descripción:</strong>

        {{ $zone->description }}

    </p>

</x-card>

@endsection
