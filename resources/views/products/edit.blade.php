@extends('layouts.app')

@section('title','Editar Producto')

@section('content')

<x-card>

    <h1 class="mb-6 text-3xl font-bold">

        Editar Producto

    </h1>

    <form
        action="{{ route('products.update',$product) }}"
        method="POST"
        enctype="multipart/form-data">

        @csrf
        @method('PUT')

        @include('products.form')

    </form>

</x-card>

@endsection
