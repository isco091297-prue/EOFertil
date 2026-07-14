@extends('layouts.app')

@section('content')

<x-card>

    <h1 class="text-3xl font-bold mb-8">

        Editar Zona

    </h1>

    <div class="mb-6">

        <a
            href="{{ route('zones.index') }}"
            class="text-green-700 font-semibold">

            ← Volver

        </a>

    </div>

    <form
        action="{{ route('zones.update',$zone) }}"
        method="POST">

        @csrf
        @method('PUT')

        @include('zones.form')

    </form>

</x-card>

@endsection
