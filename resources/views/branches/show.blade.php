@extends('layouts.app')

@section('content')
    <x-card>

        <h1 class="text-3xl font-bold">

            {{ $branch->name }}

        </h1>

        <hr class="my-6">

        <p>

            <strong>Almacén:</strong>

            {{ $branch->warehouse->name }}

        </p>

        <p class="mt-4">

            <strong>Zona:</strong>

            {{ $branch->zone->name }}

        </p>

        <p class="mt-4">

            <strong>Código:</strong>

            {{ $branch->code }}

        </p>

        <p class="mt-4">

            <strong>Dirección:</strong>

            {{ $branch->address }}

        </p>

        <p class="mt-4">

            <strong>Teléfono:</strong>

            {{ $branch->phone }}

        </p>

        <p class="mt-4">

            <strong>Descripción:</strong>

            {{ $branch->description }}

        </p>

        <div class="mt-8">

            <a href="{{ route('branches.index') }}" class="bg-green-700 text-white px-6 py-3 rounded-xl">

                Volver

            </a>

        </div>

    </x-card>
@endsection
