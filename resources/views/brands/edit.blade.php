@extends('layouts.app')

@section('title', 'Editar Marca')

@section('content')

<x-card>

    <h1 class="mb-6 text-3xl font-bold">

        Editar Marca

    </h1>

    <form
        action="{{ route('brands.update', $brand) }}"
        method="POST"
        enctype="multipart/form-data">

        @csrf
        @method('PUT')

        @include('brands.form')

    </form>

</x-card>

@endsection
