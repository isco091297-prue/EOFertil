@extends('layouts.app')

@section('content')

<x-card>

    <h1 class="text-3xl font-bold mb-8">

        Nuevo Almacén

    </h1>
<div class="mb-6">

    <a
        href="{{ route('warehouses.index') }}"
        class="text-green-700 font-semibold">

        ← Volver

    </a>

</div>
    <form
        action="{{ route('warehouses.store') }}"
        method="POST">

        @include('warehouses.form')

    </form>

</x-card>

@endsection
