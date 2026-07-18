@extends('layouts.app')

@section('title','Nuevo Problema')

@section('content')

<x-card>

    <h1 class="mb-6 text-3xl font-bold">

        Nuevo Problema

    </h1>

    <form
        action="{{ route('problems.store') }}"
        method="POST"
        enctype="multipart/form-data">

        @include('problems.form')

    </form>

</x-card>

@endsection
