@extends('layouts.app')

@section('title','Nuevo Producto')

@section('content')

<x-card>

    <h1 class="mb-6 text-3xl font-bold">

        Nuevo Producto

    </h1>

    <form
        action="{{ route('products.store') }}"
        method="POST"
        enctype="multipart/form-data">

        @include('products.form')

    </form>

</x-card>

@endsection
