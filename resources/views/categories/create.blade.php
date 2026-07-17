@extends('layouts.app')

@section('title', 'Nueva Categoría')

@section('content')

<div class="max-w-4xl mx-auto">

    <x-card>

        <h1 class="text-2xl font-bold mb-6">
            Nueva Categoría
        </h1>

        @if ($errors->any())
            <div class="mb-6 rounded-lg border border-red-300 bg-red-50 p-4 text-red-700">

                <strong>Se encontraron los siguientes errores:</strong>

                <ul class="mt-2 list-disc pl-5">

                    @foreach ($errors->all() as $error)

                        <li>{{ $error }}</li>

                    @endforeach

                </ul>

            </div>
        @endif

        @if(session('error'))

            <div class="mb-6 rounded-lg border border-red-300 bg-red-50 p-4 text-red-700">

                {{ session('error') }}

            </div>

        @endif

        <form
            action="{{ route('categories.store') }}"
            method="POST"
            enctype="multipart/form-data">

            @include('categories.form')

        </form>

    </x-card>

</div>

@endsection
