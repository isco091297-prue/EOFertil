@extends('layouts.app')

@section('content')

<x-card>

    <h1 class="text-3xl font-bold">

        {{ $warehouse->name }}

    </h1>

    <hr class="my-6">

    <p>

        <strong>Código:</strong>

        {{ $warehouse->code }}

    </p>

    <p class="mt-4">

        <strong>Descripción:</strong>

        {{ $warehouse->description }}

    </p>

</x-card>
<div class="mt-8">

    <a
        href="{{ route('warehouses.index') }}"
        class="bg-green-700 text-white px-6 py-3 rounded-xl">

        Volver

    </a>

</div>
@endsection
