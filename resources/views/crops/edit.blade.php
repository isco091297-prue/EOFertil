@extends('layouts.app')

@section('title', 'Editar Cultivo')

@section('content')

<div class="max-w-4xl mx-auto">

    <x-card>

        <h1 class="text-2xl font-bold mb-6">
            Editar Cultivo
        </h1>

        <form
            action="{{ route('crops.update', $crop) }}"
            method="POST"
            enctype="multipart/form-data">

            @csrf
            @method('PUT')

            @include('crops.form')

        </form>

    </x-card>

</div>

@endsection
