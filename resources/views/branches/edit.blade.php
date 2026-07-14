@extends('layouts.app')

@section('content')

<x-card>

    <h1 class="text-3xl font-bold mb-8">

        Editar Sucursal

    </h1>

    <div class="mb-6">

        <a
            href="{{ route('branches.index') }}"
            class="text-green-700 font-semibold">

            ← Volver

        </a>

    </div>

    <form
        action="{{ route('branches.update',$branch) }}"
        method="POST">

        @csrf
        @method('PUT')

        @include('branches.form')

    </form>

</x-card>

@endsection
