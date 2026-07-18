@extends('layouts.app')

@section('title', 'Nueva Marca')

@section('content')

<x-card>

    <h1 class="mb-6 text-3xl font-bold">

        Nueva Marca

    </h1>

    <form
        action="{{ route('brands.store') }}"
        method="POST"
        enctype="multipart/form-data">

        @include('brands.form')

    </form>

</x-card>

@endsection
