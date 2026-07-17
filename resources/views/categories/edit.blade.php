@extends('layouts.app')

@section('title', 'Editar Categoría')

@section('content')

<div class="max-w-4xl mx-auto">

    <x-card>

        <h1 class="text-2xl font-bold mb-6">

            Editar Categoría

        </h1>

        <form
            action="{{ route('categories.update', $category) }}"
            method="POST"
            enctype="multipart/form-data">

            @csrf
            @method('PUT')

            @include('categories.form')

        </form>

    </x-card>

</div>

@endsection
