@extends('layouts.app')

@section('content')

<x-card>

    <h1 class="text-3xl font-bold mb-8">

        Nueva Zona

    </h1>

    <div class="mb-6">

        <a
            href="{{ route('zones.index') }}"
            class="text-green-700 font-semibold">

            ← Volver

        </a>

    </div>

    <form
        action="{{ route('zones.store') }}"
        method="POST">

        @include('zones.form')

    </form>

</x-card>

@endsection
