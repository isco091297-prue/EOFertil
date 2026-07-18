@extends('layouts.app')

@section('title','Editar Problema')

@section('content')

<x-card>

    <h1 class="mb-6 text-3xl font-bold">

        Editar Problema

    </h1>

    <form
        action="{{ route('problems.update',$problem) }}"
        method="POST"
        enctype="multipart/form-data">

        @csrf
        @method('PUT')

        @include('problems.form')

    </form>

</x-card>

@endsection
