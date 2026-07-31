@extends('layouts.app')

@section('title','Nuevo Ingrediente Activo')

@section('content')

<x-card>

    <h1 class="mb-6 text-3xl font-bold">
        Nuevo Ingrediente Activo
    </h1>

    <form
        action="{{ route('active-ingredients.store') }}"
        method="POST">

        @include('active-ingredients.form')

    </form>

</x-card>

@endsection
