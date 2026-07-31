@extends('layouts.app')

@section('title', 'Editar Ingrediente Activo')

@section('content')

    <x-card>

        <h1 class="mb-6 text-3xl font-bold">

            Editar Ingrediente Activo

        </h1>

        <form action="{{ route('active-ingredients.update', $activeIngredient) }}" method="POST">

            @csrf
            @method('PUT')

            @include('active-ingredients.form')

        </form>

    </x-card>

@endsection
